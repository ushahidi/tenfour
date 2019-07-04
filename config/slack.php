<?php

/*
 * Slack Integration
 */
return [
    'from' => env('SLACK_FROM', 'TenFour'),
    'from_emoji' => env('SLACK_FROM_EMOJI', ':loudspeaker:'),
    'image_url' => env('SLACK_IMAGE_URL', 'http://github.ushahidi.org/rollcall-pattern-library/assets/img/avatar-rollcall.png'),
    'thumb_url' => env('SLACK_THUMB_URL', 'http://github.ushahidi.org/rollcall-pattern-library/assets/img/avatar-rollcall.png')
];
