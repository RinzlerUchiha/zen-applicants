<?php

namespace App\Services;

use App\Models\Application;
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

        $existing = Application::where('app_id', $appId)
            ->where('job_posting_id', $jobPostingId)
            ->first();

        if ($existing) {
            return ['success' => true, 'message' => 'You have already applied to this position.', 'application' => $existing];
        }

        $application = Application::create([
            'app_id' => $appId,
            'request_position_id' => $posting->request_position_id,
            'job_posting_id' => $jobPostingId,
            'status' => 'Applied',
            'applied_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Application submitted successfully.', 'application' => $application];
    }
}