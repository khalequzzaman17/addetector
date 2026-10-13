<?php

declare(strict_types=1);

namespace AdblockDetector\Laravel;

use AdblockDetector\AdblockDetector;
use AdblockDetector\DetectorConfig;
use AdblockDetector\NoticeConfig;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

/**
 * Laravel Service Provider for the AdblockDetector package.
 */
class AdblockDetectorServiceProvider extends ServiceProvider
{
    /**
     * Register bindings and singleton services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/adblock-detector.php', 'adblock-detector');

        $this->app->singleton(AdblockDetector::class, function ($app) {
            $config = $app['config']->get('adblock-detector', []);

            $detectorOptions = $config['detector'] ?? [];
            $detectorConfig = new DetectorConfig(
                enabled: (bool) ($detectorOptions['enabled'] ?? true),
                minimumStrongSignals: (int) ($detectorOptions['minimum_strong_signals'] ?? 2),
                allowWeakSignalAssistance: (bool) ($detectorOptions['allow_weak_signal_assistance'] ?? true),
                periodicCheck: (bool) ($detectorOptions['periodic_check'] ?? false),
                periodicIntervalMs: (int) ($detectorOptions['periodic_interval_ms'] ?? 30000),
                requestTimeoutMs: (int) ($detectorOptions['request_timeout_ms'] ?? 4000),
                observeDomChanges: (bool) ($detectorOptions['observe_dom_changes'] ?? true),
                checkOnDomReady: (bool) ($detectorOptions['check_on_dom_ready'] ?? true),
                checkGoogleAds: (bool) ($detectorOptions['check_google_ads'] ?? true),
                googleAdsUrl: (string) ($detectorOptions['google_ads_url'] ?? 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'),
                checkBaitElement: (bool) ($detectorOptions['check_bait_element'] ?? true),
                checkKnownMarker: (bool) ($detectorOptions['check_known_marker'] ?? true),
                sensitivity: (string) ($detectorOptions['sensitivity'] ?? 'balanced'),
                knownMarkers: $detectorOptions['known_markers'] ?? null,
                antiAdblockSelectors: $detectorOptions['anti_adblock_selectors'] ?? null,
                antiAdblockScriptPatterns: $detectorOptions['anti_adblock_script_patterns'] ?? null,
                baitClasses: $detectorOptions['bait_classes'] ?? null,
            );

            $noticeOptions = $config['notice'] ?? [];
            $noticeConfig = new NoticeConfig(
                title: (string) ($noticeOptions['title'] ?? 'Please disable your ad blocker'),
                message: (string) ($noticeOptions['message'] ?? 'Please disable your ad blocker and refresh the page to continue supporting our website.'),
                refreshText: (string) ($noticeOptions['refresh_text'] ?? 'Refresh'),
                closeText: $noticeOptions['close_text'] ?? null,
                theme: (string) ($noticeOptions['theme'] ?? 'light'),
                primaryColor: (string) ($noticeOptions['primary_color'] ?? '#2563eb'),
                lockPage: (bool) ($noticeOptions['lock_page'] ?? true),
                showNoscriptFallback: (bool) ($noticeOptions['show_noscript_fallback'] ?? true),
                iconUrl: (string) ($noticeOptions['icon_url'] ?? ''),
                customCss: (string) ($noticeOptions['custom_css'] ?? ''),
            );

            return new AdblockDetector($detectorConfig, $noticeConfig);
        });

        $this->app->alias(AdblockDetector::class, 'adblock.detector');
    }

    /**
     * Bootstrap package services, publishable configs, and Blade directives.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/adblock-detector.php' => $this->app->configPath('adblock-detector.php'),
            ], 'adblock-detector-config');
        }

        if (class_exists(Blade::class)) {
            Blade::directive('adblockDetector', function () {
                return '<?php echo app(\\AdblockDetector\\AdblockDetector::class)->render(); ?>';
            });
        }
    }
}
