<?php

namespace App\Services;

use App\Models\AssessmentAccess;
use App\Models\AssessmentAccessRequest;
use App\Models\AssessmentAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * The assessment gate: HR's one-time access code.
 *
 * The assessments are provided by HR after the initial interview. HR issues a
 * code from the applicant's profile in zen-admin; the applicant enters it and
 * the assessments open — in that browser session only, for a limited time.
 *
 *   - Only the most recently issued code counts; issuing a new one retires
 *     the old one (zen-admin does that, and this class ignores older rows too).
 *   - A code is single-use and must be entered before it expires.
 *   - Too many wrong entries revoke it, so it cannot be guessed.
 *   - The unlock is tied to the session it was entered in: it opens the
 *     assessments on the device HR saw, not on any device the applicant owns.
 *
 * The unlock is needed to START an assessment, to RESUME an interrupted one,
 * and to move a running one to another browser. A running attempt in its own
 * browser carries on to its time limit even if the unlock window closes.
 */
class AssessmentGate
{
    public static function sessionHash(): string
    {
        return hash('sha256', (string) session()->getId());
    }

    /** The unlock in force for this applicant in this browser session, if any. */
    public function current(User $user): ?AssessmentAccess
    {
        return AssessmentAccess::query()
            ->where('app_id', $user->app_id)
            ->whereNotNull('redeemed_at')
            ->whereNull('revoked_at')
            ->where('session_hash', self::sessionHash())
            ->where('unlocked_until', '>', now())
            ->orderByDesc('id')
            ->first();
    }

    public function isUnlocked(User $user): bool
    {
        return $this->current($user) !== null;
    }

    /**
     * Unlocked by a code entered AFTER $moment — what resuming an interrupted
     * attempt needs, so an old unlock cannot restart a stopped clock.
     */
    public function unlockedSince(User $user, Carbon $moment): bool
    {
        $access = $this->current($user);

        return $access !== null && $access->redeemed_at->greaterThan($moment);
    }

    /**
     * The most recent code this applicant entered, in any browser and whether
     * or not it is still open — to tell them their access ENDED (or is open
     * elsewhere) rather than that they never had any.
     */
    public function lastUnlock(User $user): ?AssessmentAccess
    {
        return AssessmentAccess::where('app_id', $user->app_id)
            ->whereNotNull('redeemed_at')
            ->orderByDesc('id')
            ->first();
    }

    /* ------------------------------------------------------------------
     * Asking HR for a new code
     *
     * Access is authorization to START (or resume, or move) assessments; it
     * ends after unlock_hours. An assessment already running is not affected
     * by that — it carries on to its own time limit. Afterwards, the next
     * assessment needs a new code, and the applicant can ask HR for one here.
     *
     * One open request per applicant: asking again refreshes it. HR sees open
     * requests in zen-admin, and issuing a code resolves them. No message is
     * sent anywhere.
     * ------------------------------------------------------------------ */

    /** The applicant's open request, if any. */
    public function openRequest(User $user): ?AssessmentAccessRequest
    {
        return AssessmentAccessRequest::where('app_id', $user->app_id)->open()->orderByDesc('id')->first();
    }

    /**
     * May ask: HR has given them a code before (the assessments are provided
     * after the initial interview — a first code comes from HR, not a request),
     * and they need one now: access is not open in this browser, or it is but
     * a paused assessment needs a code entered after it stopped.
     */
    public function canRequest(User $user): bool
    {
        if (!AssessmentAccess::where('app_id', $user->app_id)->exists()) {
            return false;
        }

        $current = $this->current($user);

        return $current === null
            || AssessmentAttempt::where('app_id', $user->app_id)
                ->where('status', AssessmentAttempt::INTERRUPTED)
                ->where('interrupted_at', '>=', $current->redeemed_at)
                ->exists();
    }

    /**
     * Record (or refresh) the request. $assessment is the one they were on;
     * the reason is worked out here from their situation, not taken on trust.
     */
    public function requestAccess(User $user, ?string $assessment): AssessmentAccessRequest
    {
        if (!$this->canRequest($user)) {
            throw ValidationException::withMessages(['access_request' => [
                $this->isUnlocked($user)
                    ? 'Your assessments are already open in this browser.'
                    : 'The assessments are provided by HR after your initial interview.',
            ]]);
        }

        $reason = $this->requestReason($user, $assessment);

        return DB::transaction(function () use ($user, $assessment, $reason) {
            User::whereKey($user->app_id)->lockForUpdate()->first();

            $open = $this->openRequest($user);

            if ($open) {
                $open->update([
                    'reason' => $reason,
                    'assessment' => $assessment,
                    'requested_at' => now(),
                    'times_asked' => $open->times_asked + 1,
                ]);

                return $open;
            }

            return AssessmentAccessRequest::create([
                'app_id' => $user->app_id,
                'reason' => $reason,
                'assessment' => $assessment,
                'requested_at' => now(),
            ]);
        });
    }

    private function requestReason(User $user, ?string $assessment): string
    {
        $attempt = $assessment
            ? AssessmentAttempt::where('app_id', $user->app_id)->where('assessment', $assessment)->first()
            : null;

        if ($attempt?->isInterrupted()) {
            return 'paused';
        }
        if ($attempt?->isActive() && $attempt->session_hash !== self::sessionHash()) {
            return 'other_browser';
        }

        return 'expired';
    }

    /**
     * Enter a code. Returns the unlock, or throws a ValidationException on the
     * "code" field with a message the applicant can act on.
     */
    public function redeem(User $user, string $code): AssessmentAccess
    {
        $code = preg_replace('/\s+/', '', $code);
        $settings = config('application_form.assessments.access');

        $outcome = DB::transaction(function () use ($user, $code, $settings) {
            // Same serialization point as every other applicant write.
            User::whereKey($user->app_id)->lockForUpdate()->first();

            $latest = AssessmentAccess::where('app_id', $user->app_id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$latest || $latest->revoked_at || $latest->redeemed_at || $latest->expires_at->isPast()) {
                return 'none';
            }

            if (!Hash::check($code, $latest->code_hash)) {
                $latest->failed_attempts++;
                if ($latest->failed_attempts >= $settings['max_failures']) {
                    $latest->revoked_at = now();
                }
                $latest->save();

                return $latest->revoked_at ? 'revoked' : 'wrong';
            }

            $latest->redeemed_at = now();
            $latest->session_hash = self::sessionHash();
            $latest->unlocked_until = now()->addHours($settings['unlock_hours']);
            $latest->save();

            return $latest;
        });

        if ($outcome instanceof AssessmentAccess) {
            return $outcome;
        }

        throw ValidationException::withMessages(['code' => [match ($outcome) {
            'wrong' => 'That code is not correct. Check it with HR and try again.',
            'revoked' => 'Too many incorrect tries — that code no longer works. Ask HR for a new one.',
            default => 'There is no active code for you. Ask HR for one — codes expire '
                . $settings['code_minutes'] . ' minutes after they are issued.',
        }]]);
    }
}
