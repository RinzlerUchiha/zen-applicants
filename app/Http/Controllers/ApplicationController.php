<?php

namespace App\Http\Controllers;

use App\Models\Application;

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

                return $application;
            });

        return view('applications.index', compact('applications'));
    }
}