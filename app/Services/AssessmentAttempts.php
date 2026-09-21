<?php

namespace App\Services;

use App\Http\Controllers\BasicAbstractReasoningController;
use App\Http\Controllers\BasicMathController;
use App\Http\Controllers\CareerAnchorController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DiscController;
use App\Http\Controllers\EnneagramController;
use App\Http\Controllers\MayaController;
use App\Http\Controllers\MiqController;
use App\Http\Controllers\TaptController;
use App\Http\Controllers\VakController;
use App\Http\Controllers\WhyIWorkController;
use App\Models\AssessmentAttempt;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * The rules of an assessment attempt — the one place they live.
 *
 * The server keeps the clock. An open exam page checks in every few seconds
 * (autosaving as it goes), and time is counted between check-ins:
 *
 *   - The SAME window checking in again after any gap — a dropped connection,
 *     a laptop lid closed — simply carries on: the whole gap counts as exam
 *     time (the page's own clock kept running) and the answers it brings are
 *     kept. Nothing is lost and no time is gained.
 *   - A NEW page load within the grace period (a refresh) carries on too.
 *   - A new page load after a longer gap — the browser was closed, crashed,
 *     or the power went — is an INTERRUPTION: the clock stops where the page
 *     was last seen, the answers stay saved, and a new HR code resumes it with
 *     the time that was left. A power cut does not cost the applicant the
 *     attempt, and walking away to look up answers does not come for free.
 *   - When the time is used up the attempt ends as TIMED_OUT, and whatever was
 *     autosaved is submitted for the applicant, through the assessment's own
 *     save — so the result has exactly the format a normal submission has.
 *   - Once SUBMITTED or TIMED_OUT, nothing can be written again.
 *
 * Nothing needs a scheduler: an attempt is brought up to date ("settled")
 * whenever it is looked at, and an open page looks at it constantly.
 */
class AssessmentAttempts
{
    /** Assessment => the controller whose save() writes its result. */
    private const SAVERS = [
        'enneagram' => EnneagramController::class,
        'tapt' => TaptController::class,
        'disc' => DiscController::class,
        'miq' => MiqController::class,
        'color' => ColorController::class,
        'vak' => VakController::class,
        'why_i_work' => WhyIWorkController::class,
        'career_anchors' => CareerAnchorController::class,
        'abstract_reasoning' => BasicAbstractReasoningController::class,
        'basic_math' => BasicMathController::class,
        'maya' => MayaController::class,
    ];

    public function __construct(private AssessmentGate $gate)
    {
    }

    public static function keys(): array
    {
        return array_keys(config('application_form.assessments.list'));
    }

    public function definition(string $key): array
    {
        $definition = config("application_form.assessments.list.$key");
        abort_unless(is_array($definition), 404);

        return $definition + ['key' => $key];
    }

    /** A result row exists in the assessment's own table. */
    public function hasResult(User $user, string $key): bool
    {
        return DB::table($this->definition($key)['table'])->where('app_id', $user->app_id)->exists();
    }

    /**
     * Everything a page needs to know about one assessment for this applicant,
     * after bringing the attempt up to date.
     *
     * status: locked | ready | active | elsewhere | interrupted | submitted | timed_out
     */
    public function view(User $user, string $key, bool $claim = false): object
    {
        $definition = $this->definition($key);
        $attempt = $this->settled($user, $key);
        $hasResult = $this->hasResult($user, $key);
        $unlock = $this->gate->current($user);

        $status = match (true) {
            $attempt === null && $hasResult => AssessmentAttempt::SUBMITTED, // finished before attempts existed
            $attempt === null => $unlock ? 'ready' : 'locked',
            $attempt->isActive() && $attempt->session_hash !== AssessmentGate::sessionHash() => 'elsewhere',
            default => $attempt->status,
        };

        $token = null;
        if ($status === AssessmentAttempt::ACTIVE && $claim) {
            // This window becomes the one allowed to answer; any other open
            // window is locked out on its next check-in.
            $token = Str::random(40);
            AssessmentAttempt::whereKey($attempt->id)->update(['tab_token' => $token]);
        }

        return (object) [
            'key' => $key,
            'definition' => $definition,
            'attempt' => $attempt,
            'status' => $status,
            'hasResult' => $hasResult,
            'remaining' => $attempt ? $this->remaining($attempt) : $definition['minutes'] * 60,
            'unlockedUntil' => $unlock?->unlocked_until,
            // Resuming an interruption needs a code entered after it happened.
            'canResume' => $status === AssessmentAttempt::INTERRUPTED
                && $this->gate->unlockedSince($user, $attempt->interrupted_at),
            'canTakeOver' => $status === 'elsewhere' && $unlock !== null,
            'token' => $token,
            'showQuestions' => $status === AssessmentAttempt::ACTIVE && $token !== null
                // After the fact a questionnaire still shows the applicant's own
                // answers, as it always has. Aptitude tests do not: their
                // questions stay unseen outside a running attempt.
                || ($hasResult && $definition['kind'] === 'questionnaire'),
        ];
    }

    /** The attempt for this applicant, brought up to date (or null). */
    public function settled(User $user, string $key): ?AssessmentAttempt
    {
        return DB::transaction(function () use ($user, $key) {
            $attempt = $this->locked($user, $key);

            return $attempt ? $this->settle($attempt) : null;
        });
    }

    /**
     * Start the assessment, resume an interrupted attempt, or move a running
     * one to this browser. Returns null on success, or a message.
     */
    public function start(User $user, string $key): ?string
    {
        $definition = $this->definition($key);

        return DB::transaction(function () use ($user, $key, $definition) {
            User::whereKey($user->app_id)->lockForUpdate()->first();

            $attempt = $this->locked($user, $key);
            $attempt = $attempt ? $this->settle($attempt) : null;

            if ($attempt === null) {
                if ($this->hasResult($user, $key)) {
                    return 'You have already completed this assessment.';
                }
                if (!$this->gate->isUnlocked($user)) {
                    return 'Enter the access code from HR first.';
                }

                AssessmentAttempt::create([
                    'app_id' => $user->app_id,
                    'assessment' => $key,
                    'status' => AssessmentAttempt::ACTIVE,
                    'duration_seconds' => $definition['minutes'] * 60,
                    'time_used_seconds' => 0,
                    'started_at' => now(),
                    'last_seen_at' => now(),
                    'seed' => random_int(1, 2147483646),
                    'session_hash' => AssessmentGate::sessionHash(),
                ]);

                return null;
            }

            if ($attempt->isFinished()) {
                return 'You have already completed this assessment.';
            }

            if ($attempt->isInterrupted()) {
                if (!$this->gate->unlockedSince($user, $attempt->interrupted_at)) {
                    return 'This assessment was interrupted. Ask HR for a new access code to continue.';
                }
                $attempt->status = AssessmentAttempt::ACTIVE;
                $attempt->last_seen_at = now();
                $attempt->session_hash = AssessmentGate::sessionHash();
                $attempt->tab_token = null;
                $attempt->save();

                return null;
            }

            // Running, but in another browser session.
            if ($attempt->session_hash !== AssessmentGate::sessionHash()) {
                if (!$this->gate->isUnlocked($user)) {
                    return 'This assessment is running in another browser. Enter an access code from HR to continue it here.';
                }
                $attempt->session_hash = AssessmentGate::sessionHash();
                $attempt->tab_token = null;
                $attempt->save();
            }

            return null;
        });
    }

    /**
     * An open exam page checking in: counts the time, autosaves the answers,
     * and says whether the page may carry on.
     *
     * state: active | other_tab | interrupted | submitted | timed_out | none
     */
    public function ping(User $user, string $key, ?string $token, ?array $payload): array
    {
        $this->definition($key);

        return DB::transaction(function () use ($user, $key, $token, $payload) {
            $attempt = $this->locked($user, $key);
            if (!$attempt) {
                return ['state' => 'none'];
            }

            // The window that owns the attempt carries on after any gap (see the
            // class comment); anyone else gets the attempt as it now stands.
            if (!($attempt->isActive() && $this->ownsAttempt($attempt, $token))) {
                $attempt = $this->settle($attempt);

                return ['state' => $attempt->isActive() ? 'other_tab' : $attempt->status];
            }

            $attempt->time_used_seconds = min(
                $attempt->duration_seconds,
                $attempt->time_used_seconds + $this->gap($attempt)
            );
            $attempt->last_seen_at = now();

            if ($payload !== null) {
                $attempt->draft = $payload;
                $attempt->draft_saved_at = now();
            }

            if ($attempt->time_used_seconds >= $attempt->duration_seconds) {
                $this->finish($attempt);

                return ['state' => AssessmentAttempt::TIMED_OUT];
            }

            $attempt->save();

            return [
                'state' => AssessmentAttempt::ACTIVE,
                'remaining' => $this->remaining($attempt),
                'saved_at' => $attempt->draft_saved_at?->format('g:i:s A'),
            ];
        });
    }

    /**
     * The applicant pressing Submit (or the page submitting at time-up).
     *
     * $save is the assessment's own existing save — validation, scoring and
     * storage exactly as before. It runs only if the attempt may still be
     * written, inside the same transaction that closes the attempt, so a
     * result and a closed attempt are always written together.
     */
    public function submit(User $user, string $key, Request $request, Closure $save): JsonResponse
    {
        $this->definition($key);

        return DB::transaction(function () use ($user, $key, $request, $save) {
            $attempt = $this->locked($user, $key);

            if (!$attempt) {
                return $this->hasResult($user, $key)
                    ? $this->refuse('submitted', 'You have already completed this assessment.', 409)
                    : $this->refuse('none', 'This assessment has not been started.', 409);
            }
            if ($attempt->isFinished()) {
                return $this->refuse($attempt->status, $attempt->status === AssessmentAttempt::TIMED_OUT
                    ? 'Time ran out on this assessment, so it has already been submitted.'
                    : 'You have already completed this assessment.', 409);
            }
            if ($attempt->isInterrupted()) {
                return $this->refuse('interrupted', 'This assessment was interrupted. Ask HR for a new access code to continue.', 409);
            }
            if (!$this->ownsAttempt($attempt, $request->input('attempt_token'))) {
                return $this->refuse('other_tab', 'This assessment is open in another window. Continue there, or reload this page to use this one.', 409);
            }

            // The owning window: the whole gap since its last check-in was exam
            // time. If that took it past the limit, this is the page's own
            // time-up submission — it froze the answers at zero and has been
            // retrying until it could reach the server, so they count.
            $used = $attempt->time_used_seconds + $this->gap($attempt);

            $response = $save($request);
            $saved = $response->getStatusCode() === 200 && ($response->getData(true)['success'] ?? false);
            $timeUp = $used >= $attempt->duration_seconds;

            if (!$saved && !$timeUp) {
                // Something to correct (an unanswered item, say). The attempt
                // carries on; count the time and let the applicant fix it.
                $attempt->time_used_seconds = $used;
                $attempt->last_seen_at = now();
                $attempt->save();

                return $response;
            }

            $attempt->status = $timeUp ? AssessmentAttempt::TIMED_OUT : AssessmentAttempt::SUBMITTED;
            $attempt->time_used_seconds = min($used, $attempt->duration_seconds);
            $attempt->result_saved = $saved;
            $attempt->ended_at = now();
            $attempt->draft = null;
            $attempt->tab_token = null;
            $attempt->save();

            return $saved ? $response : $this->refuse(AssessmentAttempt::TIMED_OUT,
                'Time ran out before the assessment was complete, so no result was recorded.', 410);
        });
    }

    /** Seconds left on the clock, as the server counts them. */
    public function remaining(AssessmentAttempt $attempt): int
    {
        $running = $attempt->isActive()
            ? min($this->gap($attempt), config('application_form.assessments.attempts.grace_seconds'))
            : 0;

        return max(0, $attempt->duration_seconds - $attempt->time_used_seconds - $running);
    }

    /**
     * $list in this attempt's own random order, keys kept — so a refresh shows
     * the same order and the submitted values are the original keys.
     */
    public function order(AssessmentAttempt $attempt, array $list, string $salt = ''): array
    {
        $keys = (new Randomizer(new Mt19937($attempt->seed ^ crc32($salt))))->shuffleArray(array_keys($list));

        return array_combine($keys, array_map(fn ($k) => $list[$k], $keys));
    }

    /* ------------------------------------------------------------------ */

    private function locked(User $user, string $key): ?AssessmentAttempt
    {
        return AssessmentAttempt::where('app_id', $user->app_id)
            ->where('assessment', $key)
            ->lockForUpdate()
            ->first();
    }

    private function ownsAttempt(AssessmentAttempt $attempt, ?string $token): bool
    {
        return $attempt->session_hash === AssessmentGate::sessionHash()
            && $attempt->tab_token !== null
            && is_string($token)
            && hash_equals($attempt->tab_token, $token);
    }

    private function gap(AssessmentAttempt $attempt): int
    {
        return max(0, now()->getTimestamp() - $attempt->last_seen_at->getTimestamp());
    }

    /** Bring an active attempt up to date: time-up, or interruption. */
    private function settle(AssessmentAttempt $attempt): AssessmentAttempt
    {
        if (!$attempt->isActive()) {
            return $attempt;
        }

        $gap = $this->gap($attempt);
        $grace = config('application_form.assessments.attempts.grace_seconds');

        if ($attempt->time_used_seconds + min($gap, $grace) >= $attempt->duration_seconds) {
            // Time ran out — while the page was open, or within the grace
            // period after it went quiet (the clock runs through that).
            $attempt->time_used_seconds = $attempt->duration_seconds;
            $this->finish($attempt);
        } elseif ($gap > $grace) {
            // Stopped at the last sign of life; the gap is not charged.
            $attempt->status = AssessmentAttempt::INTERRUPTED;
            $attempt->interrupted_at = $attempt->last_seen_at;
            $attempt->tab_token = null;
            $attempt->save();
        }

        return $attempt;
    }

    /**
     * Time is up: submit whatever was autosaved through the assessment's own
     * save. A draft that is not a valid submission (an unfinished
     * questionnaire) records no result — the attempt still ends.
     */
    private function finish(AssessmentAttempt $attempt): void
    {
        $saved = false;

        // Results are always written as the applicant themselves.
        if (is_array($attempt->draft) && (int) auth()->id() === (int) $attempt->app_id) {
            $request = Request::create('/', 'POST', $attempt->draft);
            $request->setUserResolver(fn () => auth()->user());

            $response = (self::SAVERS[$attempt->assessment])::save($request);
            $saved = $response->getStatusCode() === 200 && ($response->getData(true)['success'] ?? false);
        }

        $attempt->status = AssessmentAttempt::TIMED_OUT;
        $attempt->result_saved = $saved;
        $attempt->ended_at = now();
        $attempt->draft = null;
        $attempt->tab_token = null;
        $attempt->save();
    }

    private function refuse(string $state, string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'state' => $state,
            'error' => [$message],
            'message' => $message,
        ], $status);
    }
}
