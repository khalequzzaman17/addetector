<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Detector Configuration
    |--------------------------------------------------------------------------
    |
    | Configure client-side probe behavior and detection sensitivity thresholds.
    |
    */
    'detector' => [
        // Master toggle for client-side detection execution
        'enabled' => (bool) env('ADBLOCK_DETECTOR_ENABLED', true),

        // Sensitivity preset: 'lenient', 'balanced', 'strict', or 'custom'
        'sensitivity' => env('ADBLOCK_SENSITIVITY', 'balanced'),

        // Number of strong signals required when no weak signal is present (1-3)
        'minimum_strong_signals' => (int) env('ADBLOCK_MIN_STRONG_SIGNALS', 2),

        // Allow 1 strong signal + 1 weak signal to trigger notice
        'allow_weak_signal_assistance' => (bool) env('ADBLOCK_ALLOW_WEAK_ASSISTANCE', true),

        // Re-evaluate client-side signals periodically
        'periodic_check' => (bool) env('ADBLOCK_PERIODIC_CHECK', false),

        // Interval between periodic checks in milliseconds (minimum 1000)
        'periodic_interval_ms' => (int) env('ADBLOCK_PERIODIC_INTERVAL_MS', 30000),

        // Maximum timeout for network script probe in milliseconds
        'request_timeout_ms' => (int) env('ADBLOCK_REQUEST_TIMEOUT_MS', 4000),

        // Observe dynamic DOM changes using MutationObserver
        'observe_dom_changes' => (bool) env('ADBLOCK_OBSERVE_DOM_CHANGES', true),

        // Wait for DOMContentLoaded before triggering detector
        'check_on_dom_ready' => (bool) env('ADBLOCK_CHECK_ON_DOM_READY', true),

        // Probe ad network script loading
        'check_google_ads' => (bool) env('ADBLOCK_CHECK_GOOGLE_ADS', true),

        // Script probe endpoint URL
        'google_ads_url' => env('ADBLOCK_GOOGLE_ADS_URL', 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'),

        // Inject ad-like bait element to detect element-hiding rules
        'check_bait_element' => (bool) env('ADBLOCK_CHECK_BAIT_ELEMENT', true),

        // Check for known extension DOM markers (e.g. Fair AdBlocker)
        'check_known_marker' => (bool) env('ADBLOCK_CHECK_KNOWN_MARKER', true),

        // Blocker DOM markers (optional override)
        'known_markers' => null,

        // AakList-derived anti-adblock element selectors (optional override)
        'anti_adblock_selectors' => null,

        // AakList-derived anti-adblock script patterns (optional override)
        'anti_adblock_script_patterns' => null,

        // Ad-like bait class names (optional override)
        'bait_classes' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notice Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the dialog overlay appearance, messaging, and accessibility options.
    |
    */
    'notice' => [
        // Heading title displayed in dialog
        'title' => env('ADBLOCK_NOTICE_TITLE', 'Please disable your ad blocker'),

        // Body message text explaining why detection occurred
        'message' => env('ADBLOCK_NOTICE_MESSAGE', 'Please disable your ad blocker and refresh the page to continue supporting our website.'),

        // Reload button label
        'refresh_text' => env('ADBLOCK_NOTICE_REFRESH_TEXT', 'Refresh'),

        // Optional close/dismiss button label (null to hide close button)
        'close_text' => env('ADBLOCK_NOTICE_CLOSE_TEXT', null),

        // Visual theme: 'light' or 'dark'
        'theme' => env('ADBLOCK_NOTICE_THEME', 'light'),

        // Primary button and accent color
        'primary_color' => env('ADBLOCK_NOTICE_PRIMARY_COLOR', '#2563eb'),

        // Prevent body/page scroll while notice is visible
        'lock_page' => (bool) env('ADBLOCK_NOTICE_LOCK_PAGE', true),

        // Render styled noscript warning if client JavaScript is disabled
        'show_noscript_fallback' => (bool) env('ADBLOCK_NOTICE_NOSCRIPT_FALLBACK', true),

        // Optional image URL displayed above title
        'icon_url' => env('ADBLOCK_NOTICE_ICON_URL', ''),

        // Custom CSS injected into the detector stylesheet
        'custom_css' => '',
    ],
];
