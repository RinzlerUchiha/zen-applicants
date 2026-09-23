<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\ApplicantDocumentStatus;
use App\Services\ApplicationCompletenessService;
use App\Services\ApplicationMaterials;
use App\Services\JobApplicationService;
use App\Services\ReapplicationPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobListingController extends Controller
{
    /**
     * The public landing page — the first thing a visitor sees.
     *
     * Previously "/" redirected straight to the job listings, dropping a
     * first-time visitor into an internal page with no account of who we are
     * or what applying involves. A signed-in applicant skips it: they have
     * already landed, so their own application is the more useful destination.
     */
    public function landing()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        $postings = DB::connection('zen')->table('tbl_job_posting')
            ->where('status', 'Published')
            ->orderByDesc('posted_at')
            ->get();

        return view('pages.landing', compact('postings'));
    }

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

        // A signed-in applicant still in a cooldown for THIS posting is told when
        // they can apply again, instead of being offered a button that would be
        // refused. Other postings are unaffected.
        $reapplyOn = auth()->check()
            ? ReapplicationPolicy::blockedUntil(auth()->user()->app_id, (int) $posting->id)
            : null;

        $photos = $this->photos($posting);

        return view('careers.show', compact('posting', 'reapplyOn', 'photos'));
    }

    /**
     * One of a published posting's photos — its Job Specification's, which HR
     * adds in zen-admin. Nothing else on that disk is reachable: the name must
     * be one of that posting's own photos.
     */
    public function photo($id, string $name)
    {
        $posting = DB::connection('zen')->table('tbl_job_posting')
            ->where('id', $id)
            ->where('status', 'Published')
            ->first();

        abort_unless($posting && in_array($name, $this->photos($posting), true), 404);

        $disk = Storage::disk('job_photos');
        $path = self::PHOTO_FOLDER . '/' . (int) $posting->jobspec_id . '/' . $name;

        $stream = $disk->readStream($path);
        abort_unless($stream, 404);

        return response()->stream(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Must match zen-admin's App\Services\Recruitment\JobSpecPhotos. */
    private const PHOTO_FOLDER = 'jobspec-photos';
    private const PHOTO_NAME = '/^[A-Za-z0-9]{24}\.(webp|jpe?g|png)$/';
    private const PHOTO_MAX = 4;

    /** The posting's photo names, oldest first; none if the disk cannot be read. */
    private function photos(object $posting): array
    {
        if (!$posting->jobspec_id) {
            return [];
        }

        try {
            $disk = Storage::disk('job_photos');
            $folder = self::PHOTO_FOLDER . '/' . (int) $posting->jobspec_id;

            return collect($disk->files($folder))
                ->map(fn ($path) => basename($path))
                ->filter(fn ($name) => preg_match(self::PHOTO_NAME, $name))
                ->sortBy(fn ($name) => $disk->lastModified($folder . '/' . $name))
                ->take(self::PHOTO_MAX)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * One posting for the careers page's detail pane — the same partial the
     * posting's own page renders, without the page around it.
     */
    public function panel($id)
    {
        $posting = DB::connection('zen')->table('tbl_job_posting')
            ->where('id', $id)
            ->where('status', 'Published')
            ->first();

        abort_unless($posting, 404);

        return view('careers.partials.posting', [
            'posting' => $posting,
            'photos' => $this->photos($posting),
            'reapplyOn' => auth()->check()
                ? ReapplicationPolicy::blockedUntil(auth()->user()->app_id, (int) $posting->id)
                : null,
        ]);
    }

    /**
     * The apply step: what this application is sent with. The CV/résumé and
     * 2x2 picture are required and the cover letter optional
     * (config/documents.php); they are the applicant's own documents, so ones
     * already on file are simply shown. Later-stage documents are never asked
     * for here.
     */
    public function applyForm($id)
    {
        $posting = DB::connection('zen')->table('tbl_job_posting')
            ->where('id', $id)
            ->where('status', 'Published')
            ->first();

        if (!$posting) {
            return redirect()->route('careers.index')->with('error', 'This job offer is no longer available.');
        }

        $user = auth()->user();

        $applied = Application::where('app_id', $user->app_id)
            ->where('job_posting_id', $posting->id)
            ->whereNull('closed_at')
            ->exists();
        if ($applied) {
            return redirect()->route('applications.index')->with('success', 'You have already applied to this position.');
        }

        if ($reapplyOn = ReapplicationPolicy::blockedUntil($user->app_id, (int) $posting->id)) {
            return redirect()->route('careers.show', $posting->id)
                ->with('error', 'You can apply for this position again on ' . $reapplyOn->format('F j, Y') . '.');
        }

        $slots = ApplicantDocumentStatus::slots($user->app_id);
        $completeness = ApplicationCompletenessService::for($user);

        return view('careers.apply', [
            'posting' => $posting,
            'required' => $slots->where('required', true),
            'optional' => $slots->where('required', false),
            'missing' => ApplicationMaterials::missing($user->app_id),
            'percent' => $completeness->percentage(),
            'nextSection' => $completeness->nextSection(),
            'maxSizeKb' => config('documents.max_size_kb'),
            'extensions' => config('documents.extensions'),
        ]);
    }

    public function apply(Request $request, $id)
    {
        // An application is sent with its CV and 2x2 picture — the apply step
        // asks for them, and this holds it even if that page is bypassed.
        $missing = ApplicationMaterials::missing(auth()->user()->app_id);
        if ($missing) {
            return redirect()->route('careers.apply.form', $id)
                ->with('error', 'Add your ' . strtolower(implode(' and ', $missing)) . ' before submitting your application.');
        }

        $result = JobApplicationService::apply(auth()->user()->app_id, $id);

        return redirect()
            ->route('applications.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}