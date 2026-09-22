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
    '0.2.3' => [
        'Adapt the grid header, its top and bottom separator lines, and both NiceScroll scrollbars to the dark theme.',
        'Show only the QR code in its popover: drop the title bar and close button, keep the QR code on a white card with a matching arrow in dark mode, and remove the extra body padding.',
        'Fix the QR code size options: honor width and height through the plugin size option, and add a quiet-zone option defaulting to 2 modules and configurable up to the 4-module specification.',
        'Confirm a successful copy with a short toast instead of showing the raw content in a tooltip.',
    ],
    '0.2.4' => [
        'Show a loading state on the form top submit button while the native submit request is in flight, and reset it when validation fails or the request ends.',
        'Show a loading state on the form top back button only when navigation will leave the page, and reset it when the page is restored from the back-forward cache.',
    ],
];
