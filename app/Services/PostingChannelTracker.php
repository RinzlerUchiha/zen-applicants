<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Credits a Careers visit, and the application that may follow, to the place
 * the visitor came from.
 *
 * HR posts a job by hand on LinkedIn, JobStreet and the rest, using a link
 * HireFlow tagged for that place:
 *
 *     https://…/careers/15?src=linkedin
 *
 * Landing here through that link is what this records. It says nothing about
 * how many people saw the post on LinkedIn — we have no API to ask, and this
 * number must never be presented as if we did. It is a floor, too: an
 * untagged link, or a site that strips the query string, arrives as `direct`.
 *
 * The channel is remembered for the session because applying is not the next
 * click. A visitor usually registers or signs in first, and those pages carry
 * no tag; without remembering it, every application would look like `direct`.
 *
 * Counters live in portal_db (the `zen` connection) beside the posting, and
 * are written with an atomic increment so simultaneous visits cannot lose one.
 */
class PostingChannelTracker
{
    private const TABLE = 'tbl_job_posting_channel';

    /**
     * Where the remembered channel for one posting is kept.
     *
     * Colons, not dots: Laravel reads a dot in a session key as a path into
     * a nested array, so 'hf_src.15' and 'hf_src.15.seen' would be the same
     * branch and each write would clobber the other.
     */
    private static function sessionKey(int $postingId): string
    {
        return 'hf_src:' . $postingId;
    }

    /** Marks that this session has already been counted for one channel. */
    private static function seenKey(int $postingId, string $channel): string
    {
        return 'hf_seen:' . $postingId . ':' . $channel;
    }

    private static function parameter(): string
    {
        return (string) config('posting_channels.parameter', 'src');
    }

    private static function defaultKey(): string
    {
        return (string) config('posting_channels.default', 'direct');
    }

    /**
     * Record that someone opened this posting, and from where.
     *
     * The tag is only honoured if the posting actually has a row for it —
     * a channel HR set up. Anything else (a typo, a guess, someone editing
     * the URL) is ignored and falls back to the default bucket, so the
     * parameter cannot be used to invent channels or inflate a rival's
     * numbers from the address bar.
     *
     * Counted once per session per posting per channel. A refresh or a
     * second look is the same visitor, and counting those would flatter
     * every channel equally while telling HR nothing.
     */
    public static function recordVisit(Request $request, int $postingId): void
    {
        $tag = trim((string) $request->query(self::parameter(), ''));
        $channel = self::resolve($postingId, $tag);

        // Remember it for the apply step, which happens on a later request
        // after registering or signing in.
        $request->session()->put(self::sessionKey($postingId), $channel);

        $seen = self::seenKey($postingId, $channel);

        if ($request->session()->get($seen)) {
            return;
        }

        $request->session()->put($seen, true);

        DB::connection('zen')->table(self::TABLE)
            ->where('job_posting_id', $postingId)
            ->where('channel_key', $channel)
            ->increment('visits');
    }

    /**
     * The channel this visitor arrived through, for an application about to
     * be created. Falls back to the default when nothing was remembered —
     * someone who reached the apply form another way.
     */
    public static function channelFor(int $postingId): string
    {
        return (string) session(self::sessionKey($postingId), self::defaultKey());
    }

    /**
     * Credit an application to its channel.
     *
     * Called inside the same transaction that creates the application, so an
     * application that rolls back cannot leave its count behind.
     */
    public static function recordApplication(int $postingId, string $channel): void
    {
        DB::connection('zen')->table(self::TABLE)
            ->where('job_posting_id', $postingId)
            ->where('channel_key', $channel)
            ->increment('applications');
    }

    /**
     * A tag is honoured only when the posting has that channel; otherwise the
     * default. The default row is created if it is missing, because untagged
     * traffic arrives whether or not anyone set the posting up for tracking.
     */
    private static function resolve(int $postingId, string $tag): string
    {
        $default = self::defaultKey();

        if ($tag !== '' && $tag !== $default) {
            $exists = DB::connection('zen')->table(self::TABLE)
                ->where('job_posting_id', $postingId)
                ->where('channel_key', $tag)
                ->exists();

            if ($exists) {
                return $tag;
            }
        }

        self::ensureDefaultRow($postingId, $default);

        return $default;
    }

    private static function ensureDefaultRow(int $postingId, string $default): void
    {
        $table = DB::connection('zen')->table(self::TABLE);

        if ($table->where('job_posting_id', $postingId)->where('channel_key', $default)->exists()) {
            return;
        }

        // insertOrIgnore, not insert: two first-time visitors can arrive at
        // once, and the unique index would turn the loser into a 500.
        $table->insertOrIgnore([
            'job_posting_id' => $postingId,
            'channel_key' => $default,
            'state' => 'Not Posted',
            'visits' => 0,
            'applications' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
