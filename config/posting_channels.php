<?php

/*
| The Careers portal's half of HireFlow's posting channels.
|
| HR chooses where a job is advertised in zen-admin, which owns the full
| catalogue (zen-admin/config/posting_channels.php). This side only needs to
| know two things: what the tag on a link is called, and what to credit a
| visit to when there is no tag.
|
| The names and descriptions are deliberately NOT mirrored here. Which
| channels a posting has is read from tbl_job_posting_channel — the rows HR
| created — so a channel added in zen-admin works on the Careers page with
| no change here, and a tag naming a channel the posting does not have is
| ignored rather than trusted.
|
| Both values must match zen-admin's. `parameter` in particular is baked into
| links already posted on external sites that nobody can go back and edit.
|
| @see \App\Services\PostingChannelTracker
*/

return [

    /* The query parameter carrying the channel: /careers/15?src=linkedin */
    'parameter' => 'src',

    /* Credited when a visit arrives with no tag, or one this posting lacks. */
    'default' => 'direct',
];
