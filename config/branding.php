<?php

return [
    'party_name' => env('BRAND_PARTY_NAME', 'ZANU-PF'),

    // Shown in the dashboard header. Confirm the exact wording with the Department of Information & Publicity.
    'motto' => env('BRAND_MOTTO', 'Unity, Peace and Development'),

    // Relative to /public. Match the file name exactly, including capitals (Linux servers are case-sensitive).
    'logo' => env('BRAND_LOGO', 'images/Zanu-PF-logo.jpg'),
];
