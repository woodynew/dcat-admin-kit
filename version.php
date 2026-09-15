<?php

return [
    '0.1.0' => [
        'Initial release.',
    ],
    '0.2.0' => [
        'Add an opt-in locale switcher with session preferences, authenticated CSRF-protected switching, and same-origin iframe shell reloads.',
        'Ship Simplified Chinese, Traditional Chinese and English language packs for Kit components, including standalone tools.',
        'Add an accessible QR-code close button with outside-click and Escape dismissal, single-popover behavior, and PJAX cleanup.',
        'Keep the QR-code header and close button legible in both light and dark themes.',
    ],
    '0.2.1' => [
        'Stop the QR-code popover from scrolling the page back to the top when it moves focus to its close button.',
    ],
    '0.2.2' => [
        'Keep grid header labels on one line so a translated or long header no longer wraps into a taller header row.',
        'Document the header behavior alongside the other global feature switches.',
    ],
];
