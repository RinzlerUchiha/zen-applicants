<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\ApplicationWithdrawal;
use App\Services\ReapplicationPolicy;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::where('app_id', auth()->user()->app_id)
            ->orderByDesc('applied_at')
            ->get()
            ->map(function ($application) {
                $posting = $application->jobPosting();
                $position = $application->requestPosition();

                $application->job_title = $posting->posting_title ?? '—';
                $application->mr_no = $position->mr_no ?? '—';

                // When a cooldown on this posting ends, if this application's
                // outcome started one and it has not ended yet.
                $availableFrom = ReapplicationPolicy::availableFrom($application);
                $application->reapply_on = $availableFrom && now()->lessThan($availableFrom) ? $availableFrom : null;

                return $application;
            });

        return view('applications.index', compact('applications'));
    }

    /**
     * Withdraws ONE of the signed-in applicant's applications. Their other
     * applications, their profile and their documents are not affected.
     */
    public function withdraw(Request $request, $application)
    {
        $validated = $request->validate([
            'confirm' => 'accepted',
            'note' => 'nullable|string|max:500',
        ], [
            'confirm.accepted' => 'Please confirm which application you are withdrawing.',
        ]);

        $withdrawn = ApplicationWithdrawal::withdraw(
            auth()->user()->app_id,
            (int) $application,
            $validated['note'] ?? null
        );

        return redirect()->route('applications.index')
            ->with('success', 'Your application for ' . ($withdrawn->jobPosting()?->posting_title ?? 'this position') . ' has been withdrawn.');
    }
}
