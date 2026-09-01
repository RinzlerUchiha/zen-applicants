<?php

namespace App\Http\Controllers;

use App\Services\JobApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobListingController extends Controller
{
    public function index()
    {
        $postings = DB::connection('zen')->table('tbl_job_posting')
            ->where('status', 'Published')
            ->orderByDesc('posted_at')
            ->get();

            return view('careers.index', compact('postings'));
    }

    public function show($id)
    {
        $posting = DB::connection('zen')->table('tbl_job_posting')
            ->where('id', $id)
            ->where('status', 'Published')
            ->first();

        if (!$posting) {
            abort(404, 'This job posting is not available.');
        }

        return view('careers.show', compact('posting'));
    }

    public function apply(Request $request, $id)
    {
        $result = JobApplicationService::apply(auth()->user()->app_id, $id);

        return redirect()
            ->route('applications.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}