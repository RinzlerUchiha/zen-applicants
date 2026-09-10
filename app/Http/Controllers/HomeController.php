<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
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

        $documents = Document::where('app_id', $user->app_id)->get();
        $requiredDocs = collect(config('documents.required', []));

        $docsSubmitted = $requiredDocs
            ->filter(fn ($type) => $documents->contains('doc_type', $type))
            ->count();

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
            'documents'     => $documents,
            'docsSubmitted' => $docsSubmitted,
            'docsRequired'  => $requiredDocs->count(),
            'applications'  => $applications,
        ]);
    }
}
