<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class JobApplicationService
{
    /**
     * Attempts to apply the given applicant to the given job posting.
     * Re-checks the posting is still Published at this exact moment —
     * a posting closed after someone started applying must block the
     * submission, not silently let it through.
     *
     * @return array{success: bool, message: string, application?: Application}
     */
    public static function apply(int $appId, int $jobPostingId): array
    {
        $posting = DB::connection('zen')->table('tbl_job_posting')
            ->where('id', $jobPostingId)
            ->first();

        if (!$posting) {
            return ['success' => false, 'message' => 'This job posting could not be found.'];
        }

        if ($posting->status !== 'Published') {
            return ['success' => false, 'message' => 'This job offer is no longer available.'];
        }

        $existing = self::existingApplication($appId, $jobPostingId);

        if ($existing) {
            return self::alreadyApplied($existing);
        }

        try {
            $application = Application::create([
                'app_id' => $appId,
                'request_position_id' => $posting->request_position_id,
                'job_posting_id' => $jobPostingId,
                'status' => 'Applied',
                'applied_at' => now(),
            ]);
        } catch (QueryException $e) {
            // The unique index on (app_id, job_posting_id) rejected the insert,
            // meaning a concurrent submission won the race between the check
            // above and this insert. That is the same outcome as the check
            // having caught it, so report it identically rather than as an
            // error. Anything else is a real failure and must surface.
            if (!self::isDuplicateEntry($e)) {
                throw $e;
            }

            return self::alreadyApplied(self::existingApplication($appId, $jobPostingId));
        }

        return ['success' => true, 'message' => 'Application submitted successfully.', 'application' => $application];
    }

    private static function existingApplication(int $appId, int $jobPostingId): ?Application
    {
        return Application::where('app_id', $appId)
            ->where('job_posting_id', $jobPostingId)
            ->first();
    }

    private static function alreadyApplied(?Application $application): array
    {
        return [
            'success' => true,
            'message' => 'You have already applied to this position.',
            'application' => $application,
        ];
    }

    /** MySQL reports a unique-constraint violation as error 1062 (SQLSTATE 23000). */
    private static function isDuplicateEntry(QueryException $e): bool
    {
        return ($e->errorInfo[1] ?? null) === 1062;
    }
}