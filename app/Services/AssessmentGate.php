<?php

namespace App\Services;

use App\Models\AssessmentAccess;
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
