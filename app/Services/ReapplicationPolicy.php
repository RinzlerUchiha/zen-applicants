<?php

namespace App\Services;

use App\Models\Application;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Whether an applicant may apply to a job posting again, after an earlier
 * application to that SAME posting closed.
 *
 * The rule lives in config/applications.php under 'cooldowns': a closing status
 * and how long it blocks a new application. Today only "Not Selected" does, for
 * 6 months. Withdrawn and Non-Responsive start no cooldown.
 *
 * A cooldown is per applicant AND job posting. Being Not Selected for posting A
 * never affects postings B or C, and nothing here is applicant-wide.
 *
 * To change the rule, edit the config: add or remove a status, change the
 * months. To change what counts as "the same job" (for example matching a
 * re-posted role by its manpower-request position), change sameJob() only.
 *
 * The closed applications themselves are never touched — they are the history
 * this decision is read from.
 */
class ReapplicationPolicy
{
    /**
     * The first day a new application to this posting is allowed, or null if
     * nothing blocks it now.
     */
    public static function blockedUntil(int $appId, int $jobPostingId, ?CarbonInterface $asOf = null): ?Carbon
    {
        $asOf ??= now();
        $until = null;

        $closed = self::sameJob($appId, $jobPostingId)
            ->whereNotNull('closed_at')
            ->whereIn('status', array_keys(self::cooldowns()))
            ->get(['id', 'status', 'closed_at']);

        foreach ($closed as $application) {
            $ends = self::availableFrom($application);

            if ($ends && (!$until || $ends->greaterThan($until))) {
                $until = $ends;
            }
        }

        return $until && $asOf->lessThan($until) ? $until : null;
    }

    /**
     * For one closed application: the first day the applicant may apply to the
     * same posting again, or null if its outcome starts no cooldown.
     *
     * Counted in whole calendar days: a cooldown ends at the start of the day,
     * so the date shown to the applicant is the day they can apply. Months are
     * added without overflow (31 August + 6 months is 28 February, not 3 March).
     */
    public static function availableFrom(Application $application): ?Carbon
    {
        if ($application->closed_at === null) {
            return null;
        }

        $rule = self::ruleFor($application->status);

        if (!$rule) {
            return null;
        }

        return Carbon::parse($application->closed_at)
            ->addMonthsNoOverflow((int) $rule['months'])
            ->startOfDay();
    }

    /** The cooldown rules, keyed by the status that starts them. */
    public static function cooldowns(): array
    {
        return array_filter(
            config('applications.cooldowns', []),
            fn ($rule) => (int) ($rule['months'] ?? 0) > 0
        );
    }

    /** Matched case-insensitively, the way the database compares the column. */
    private static function ruleFor(?string $status): ?array
    {
        foreach (self::cooldowns() as $key => $rule) {
            if (strcasecmp($key, trim((string) $status)) === 0) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * What counts as "the same job" for a cooldown: today, the same job
     * posting. The one place to change if that definition is ever refined.
     */
    private static function sameJob(int $appId, int $jobPostingId)
    {
        return Application::where('app_id', $appId)->where('job_posting_id', $jobPostingId);
    }
}
