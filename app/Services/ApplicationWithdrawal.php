<?php

namespace App\Services;

use App\Models\Application;
use App\Models\DocumentProcess;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The applicant withdrawing ONE of their applications.
 *
 * The same rule HR applies in zen-admin (App\Services\Recruitment\
 * ApplicationDecision), recorded the same way:
 *
 *   - the application becomes Withdrawn, with closed_at and the applicant's
 *     note; closed_by stays empty because no HR user decided it;
 *   - that application's own active document process ends as withdrawn, and
 *     that process's unresolved requests are cancelled (kept, not deleted).
 *
 * The applicant's other applications, their profile, their documents, and any
 * request that is not part of this application's process are not touched.
 * Whether the candidate is in the Candidate Pool follows from the status, for
 * this application only.
 */
class ApplicationWithdrawal
{
    public static function withdraw(int $appId, int $applicationId, ?string $note = null): Application
    {
        return DB::transaction(function () use ($appId, $applicationId, $note) {
            // The same applicant-row lock every document write takes, so a
            // withdrawal cannot cross with an HR decision or the deadline job.
            User::where('app_id', $appId)->lockForUpdate()->first();

            // Found only among the signed-in applicant's own applications.
            $application = Application::where('id', $applicationId)->where('app_id', $appId)->first();

            if (!$application) {
                throw ValidationException::withMessages([
                    'application' => 'That application could not be found.',
                ]);
            }

            if ($application->is_closed || $application->closed_at !== null) {
                throw ValidationException::withMessages([
                    'application' => 'This application has already been closed.',
                ]);
            }

            $application->update([
                'status' => Application::WITHDRAWN,
                'closed_at' => now(),
                'closed_by' => null,
                'closed_note' => $note,
            ]);

            $process = DocumentProcess::where('application_id', $application->id)->active()->first();

            if ($process) {
                $process->update([
                    'status' => DocumentProcess::WITHDRAWN,
                    'outcome_at' => now(),
                    'closed_by' => null,
                    'closed_note' => $note,
                ]);

                DocumentRequest::where('process_id', $process->id)
                    ->active()
                    ->update(['status' => 'cancelled', 'closed_at' => now()]);
            }

            return $application->refresh();
        });
    }
}
