<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
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
     * Every way to apply (the posting page, sign-in with a job, sign-up with a
     * job) comes through here, so these rules hold for all of them:
     *
     *   - One OPEN application per posting. Asking again while one is open is
     *     reported as already applied.
     *   - An earlier application to this posting that closed with a cooldown
     *     status (config/applications.php) blocks a new one until the cooldown
     *     ends. Other postings are never affected.
     *   - Otherwise a new application is created, and every earlier closed
     *     application to the posting is kept as history.
     *
     * @return array{success: bool, message: string, application?: Application, reapply_on?: \Illuminate\Support\Carbon}
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

        return DB::transaction(function () use ($appId, $jobPostingId, $posting) {
            // The applicant-row lock HR's decisions also take, so an application
            // cannot be created in the moment between a Not Selected decision and
            // the cooldown it starts.
            User::where('app_id', $appId)->lockForUpdate()->first();

            $existing = self::openApplication($appId, $jobPostingId);

            if ($existing) {
                return self::alreadyApplied($existing);
            }

            $reapplyOn = ReapplicationPolicy::blockedUntil($appId, $jobPostingId);

            if ($reapplyOn) {
                return [
                    'success' => false,
                    'message' => 'You can apply for this position again on ' . $reapplyOn->format('F j, Y') . '.',
                    'reapply_on' => $reapplyOn,
                ];
            }

            try {
                $application = Application::create([
                    'app_id' => $appId,
                    'request_position_id' => $posting->request_position_id,
                    'job_posting_id' => $jobPostingId,
                    'status' => Application::APPLIED,
                    'applied_at' => now(),
                ]);
            } catch (QueryException $e) {
                // The database's one-open-application-per-posting index rejected
                // the insert: a concurrent submission won. Same outcome as the
                // check above, so report it identically. Anything else is a real
                // failure and must surface.
                if (!self::isDuplicateEntry($e)) {
                    throw $e;
                }

                return self::alreadyApplied(self::openApplication($appId, $jobPostingId));
            }

            return ['success' => true, 'message' => 'Application submitted successfully.', 'application' => $application];
        });
    }

    /**
     * The open application to this posting, if there is one. "Open" here is
     * closed_at IS NULL — exactly what the database's unique index enforces, so
     * this check and the index can never disagree.
     */
    private static function openApplication(int $appId, int $jobPostingId): ?Application
    {
        return Application::where('app_id', $appId)
            ->where('job_posting_id', $jobPostingId)
            ->whereNull('closed_at')
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
