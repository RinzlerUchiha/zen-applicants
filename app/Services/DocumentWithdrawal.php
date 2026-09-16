<?php

namespace App\Services;

use App\Models\DocumentProcess;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The applicant withdrawing from the document-completion run HR started.
 *
 * This is the one transition the applicant owns. Everything else about a run —
 * starting it, the deadline, the attempt allowance, accepting and rejecting —
 * belongs to HR in zen-admin (App\Services\Recruitment\DocumentCompletion).
 *
 * Withdrawing is independent of both the deadline and the attempt counter and
 * is allowed at any time, but only while the run is still going: a run that has
 * already ended keeps the outcome it reached first.
 *
 * The record is kept, never deleted — a withdrawn candidate stays in the
 * Candidate Pool.
 */
class DocumentWithdrawal
{
    public static function withdraw(int $appId, ?string $note = null): ?DocumentProcess
    {
        return DB::transaction(function () use ($appId, $note) {
            // The same applicant-row lock every other document write takes, so
            // withdrawing cannot cross with an HR decision.
            User::where('app_id', $appId)->lockForUpdate()->first();

            $process = DocumentProcess::where('app_id', $appId)->active()->latest('id')->first();

            if (!$process) {
                return null;
            }

            $process->update([
                'status' => DocumentProcess::WITHDRAWN,
                'outcome_at' => now(),
                'closed_by' => null,
                'closed_note' => $note,
            ]);

            // Nothing is waited on any more. The requests are cancelled rather
            // than removed, so what HR asked for is still on record.
            DocumentRequest::where('process_id', $process->id)
                ->active()
                ->update(['status' => 'cancelled', 'closed_at' => now()]);

            if ($process->application_id) {
                DB::table('tblapp_applications')
                    ->where('id', $process->application_id)
                    ->update([
                        'status' => config('documents.completion.application_status.withdrawn', 'Withdrawn'),
                        'updated_at' => now(),
                    ]);
            }

            return $process->fresh();
        });
    }
}
