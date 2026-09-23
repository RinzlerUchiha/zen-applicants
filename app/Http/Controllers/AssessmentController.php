<?php

namespace App\Http\Controllers;

use App\Services\AssessmentAttempts;
use App\Services\AssessmentGate;
use App\Services\FileService;
use Illuminate\Http\Request;

/**
 * The assessments page, and what every assessment shares: the HR access code,
 * starting (or resuming) an attempt, the exam page's check-in, and the question
 * images of the two picture-based tests.
 *
 * Each assessment keeps its own controller for showing and saving its
 * questions; App\Services\AssessmentAttempts decides whether it may.
 */
class AssessmentController extends Controller
{
    /** Picture-based assessments => their folder under applicant/ in storage. */
    private const IMAGE_FOLDERS = [
        'abstract_reasoning' => 'basic-abstract-reasoning',
        'maya' => 'maya-test',
    ];

    public function __construct(private AssessmentAttempts $attempts, private AssessmentGate $gate)
    {
    }

    public function index()
    {
        $user = auth()->user();

        $assessments = collect(AssessmentAttempts::keys())
            ->map(function (string $key) use ($user) {
                $exam = $this->attempts->view($user, $key);

                return $exam->definition + [
                    'key' => $key,
                    'status' => $exam->status,
                    'remaining' => $exam->remaining,
                    'done' => in_array($exam->status, ['submitted', 'timed_out'], true),
                ];
            })
            ->values()
            ->all();

        return view('pages.assessments', [
            'assessments' => $assessments,
            'completed' => collect($assessments)->where('done', true)->count(),
            'unlock' => $this->gate->current($user),
        ]);
    }

    /** The applicant enters HR's access code. */
    public function access(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20'], ['code.required' => 'Enter the access code from HR.']);

        $unlock = $this->gate->redeem(auth()->user(), $request->input('code'));

        return redirect()->to(url()->previous() ?: route('assessments.index'))
            ->with('success', 'Assessments unlocked until ' . $unlock->unlocked_until->format('g:i A') . '.');
    }

    /**
     * Ask HR for a new access code — access has ended, or a paused assessment
     * needs one to resume. Recorded for HR to see in zen-admin; nothing is sent.
     */
    public function requestAccess(Request $request)
    {
        $request->validate(['assessment' => ['nullable', 'string', \Illuminate\Validation\Rule::in(AssessmentAttempts::keys())]]);

        $this->gate->requestAccess(auth()->user(), $request->input('assessment'));

        return redirect()->to(url()->previous() ?: route('assessments.index'))
            ->with('success', 'HR has been asked for a new access code. When they give it to you, enter it here.');
    }

    /** Start the assessment, resume an interrupted one, or continue it here. */
    public function start(string $assessment)
    {
        $problem = $this->attempts->start(auth()->user(), $assessment);
        $page = route($this->attempts->definition($assessment)['route']);

        return $problem
            ? redirect()->to($page)->with('error', $problem)
            : redirect()->to($page);
    }

    /** The open exam page checking in: heartbeat and autosave in one. */
    public function ping(Request $request, string $assessment)
    {
        $request->validate([
            'attempt_token' => 'nullable|string|max:64',
            'payload' => 'nullable|array',
        ]);

        $payload = $request->input('payload');
        if ($payload !== null && strlen(json_encode($payload)) > 65535) {
            abort(413);
        }

        return response()->json($this->attempts->ping(
            auth()->user(), $assessment, $request->input('attempt_token'), $payload
        ));
    }

    /**
     * A question image — only while this applicant's attempt is running in this
     * browser, so the picture tests cannot be seen before or outside it.
     * Served from the existing applicant storage (the company bucket in
     * production) exactly as FileService serves every other applicant file.
     */
    public function image(string $assessment, string $file)
    {
        abort_unless(isset(self::IMAGE_FOLDERS[$assessment]), 404);
        abort_unless(preg_match('/^[A-Za-z0-9_-]+\.(png|jpe?g|gif|webp)$/i', $file) === 1, 404);

        $exam = $this->attempts->view(auth()->user(), $assessment);
        abort_unless($exam->status === 'active', 403);

        $response = FileService::serveFile('applicant/' . self::IMAGE_FOLDERS[$assessment] . '/' . $file);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
