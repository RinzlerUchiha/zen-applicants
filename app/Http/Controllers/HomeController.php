<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\ApplicantDocumentStatus;
use App\Services\ApplicationCompletenessService;

/**
 * The applicant's home — the page that did not exist before.
 *
 * Previously "/" redirected a signed-in applicant straight to the job
 * listings, so nothing ever told them where they stood or what to do next.
 * This answers both in the first screenful.
 */
class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $completeness = ApplicationCompletenessService::for($user);

        $docSlots = ApplicantDocumentStatus::slots($user->app_id);
        $requiredDocs = $docSlots->where('required', true);

        // A required document HR has asked to be replaced does not count as
        // sent — to the applicant it is still something left to do.
        $docsSubmitted = $requiredDocs
            ->filter(fn ($slot) => $slot['document'] && $slot['document']->review_status !== 'rejected')
            ->count();

        $docsAttention = $docSlots->filter(fn ($slot) => $slot['needs_action'])->values();

        $applications = Application::where('app_id', $user->app_id)
            ->orderByDesc('applied_at')
            ->get()
            ->map(function ($application) {
                $posting = $application->jobPosting();
                $position = $application->requestPosition();

                $application->job_title = $posting->posting_title ?? '—';
                $application->mr_no = $position->mr_no ?? '—';

                return $application;
            });

        return view('pages.home', [
            'completeness'  => $completeness,
            'sections'      => $completeness->sections(),
            'percent'       => $completeness->percentage(),
            'counts'        => $completeness->blockingCounts(),
            'nextSection'   => $completeness->nextSection(),
            'docsSubmitted' => $docsSubmitted,
            'docsRequired'  => $requiredDocs->count(),
            'docsAttention' => $docsAttention,
            'applications'  => $applications,
            // Only open applications are "being considered". Closed ones are
            // still listed, with what happened to them.
            'openApplications' => $applications->reject(fn ($application) => $application->is_closed)->values(),
        ]);
    }
}
