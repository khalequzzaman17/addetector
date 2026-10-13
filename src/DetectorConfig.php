<?php

declare(strict_types=1);

namespace AdblockDetector;

use InvalidArgumentException;

/**
 * Configuration for client-side ad-block detection logic and thresholds.
 */
final class DetectorConfig
{
    /** @var list<string> */
    public readonly array $knownMarkers;

    /** @var list<string> */
    public readonly array $antiAdblockSelectors;

    /** @var list<string> */
    public readonly array $antiAdblockScriptPatterns;

    /** @var list<string> */
    public readonly array $baitClasses;

    /**
     * @param bool $enabled Master switch to enable or disable ad-block detection.
     * @param int $minimumStrongSignals Number of strong signals required when no weak signal is present (1-3, default 2).
     * @param bool $allowWeakSignalAssistance Allow 1 strong signal + 1 weak signal to trigger notice.
     * @param bool $periodicCheck Whether to periodically re-evaluate signals.
     * @param int $periodicIntervalMs Frequency of periodic checks in milliseconds (minimum 1000).
     * @param int $requestTimeoutMs Bounded timeout for the ad script network probe (minimum 250).
     * @param bool $observeDomChanges Monitor DOM mutations with MutationObserver to re-evaluate on dynamic insertions.
     * @param bool $checkOnDomReady Wait for DOMContentLoaded before executing detection.
     * @param bool $checkGoogleAds Perform network probe by loading Google Ads script.
     * @param string $googleAdsUrl URL for the Google Ads network probe.
     * @param bool $checkBaitElement Inject ad-like bait element and inspect computed sizing/visibility.
     * @param bool $checkKnownMarker Check for known blocker extension DOM markers (e.g. Fair AdBlocker).
     * @param string $sensitivity Sensitivity preset ('lenient', 'balanced', 'strict', or 'custom').
     * @param array<int|string, string>|null $knownMarkers Blocker DOM marker IDs/classes.
     * @param array<int|string, string>|null $antiAdblockSelectors AakList-derived anti-adblock element selectors.
     * @param array<int|string, string>|null $antiAdblockScriptPatterns AakList-derived script path patterns.
     * @param array<int|string, string>|null $baitClasses CSS classes applied to bait probe elements.
     */
    public function __construct(
        public readonly bool $enabled = true,
        public readonly int $minimumStrongSignals = 2,
        public readonly bool $allowWeakSignalAssistance = true,
        public readonly bool $periodicCheck = false,
        public readonly int $periodicIntervalMs = 30000,
        public readonly int $requestTimeoutMs = 4000,
        public readonly bool $observeDomChanges = true,
        public readonly bool $checkOnDomReady = true,
        public readonly bool $checkGoogleAds = true,
        public readonly string $googleAdsUrl = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
        public readonly bool $checkBaitElement = true,
        public readonly bool $checkKnownMarker = true,
        public readonly string $sensitivity = 'balanced',
        ?array $knownMarkers = null,
        ?array $antiAdblockSelectors = null,
        ?array $antiAdblockScriptPatterns = null,
        ?array $baitClasses = null,
    ) {
        if ($this->periodicIntervalMs < 1000) {
            throw new InvalidArgumentException('periodicIntervalMs must be at least 1000.');
        }

        if ($this->requestTimeoutMs < 250) {
            throw new InvalidArgumentException('requestTimeoutMs must be at least 250.');
        }

        if ($this->minimumStrongSignals < 1 || $this->minimumStrongSignals > 3) {
            throw new InvalidArgumentException('minimumStrongSignals must be between 1 and 3.');
        }

        if (!in_array($this->sensitivity, ['lenient', 'balanced', 'strict', 'custom'], true)) {
            throw new InvalidArgumentException('sensitivity must be one of: lenient, balanced, strict, custom.');
        }

        $this->knownMarkers = array_values($knownMarkers ?? ['stndz-style']);
        $this->antiAdblockSelectors = array_values($antiAdblockSelectors ?? AakListProfile::selectors());
        $this->antiAdblockScriptPatterns = array_values($antiAdblockScriptPatterns ?? AakListProfile::scriptPatterns());
        $this->baitClasses = array_values($baitClasses ?? AakListProfile::baitClasses());
    }

    /**
     * Create a strict preset: triggers on 1 strong signal or 1 strong + 1 weak signal.
     */
    public static function strict(): self
    {
        return new self(
            minimumStrongSignals: 1,
            allowWeakSignalAssistance: true,
            sensitivity: 'strict',
        );
    }

    /**
     * Create a balanced preset (default): requires 2 strong signals, or 1 strong + 1 weak signal.
     */
    public static function balanced(): self
    {
        return new self(
            minimumStrongSignals: 2,
            allowWeakSignalAssistance: true,
            sensitivity: 'balanced',
        );
    }

    /**
     * Create a lenient preset: requires 2 strong signals and ignores weak signals.
     */
    public static function lenient(): self
    {
        return new self(
            minimumStrongSignals: 2,
            allowWeakSignalAssistance: false,
            sensitivity: 'lenient',
        );
    }

    /**
     * Return a cloned instance with modified options.
     *
     * @param array<string, mixed> $values
     */
    public function with(array $values): self
    {
        return new self(...array_merge([
            'enabled' => $this->enabled,
            'minimumStrongSignals' => $this->minimumStrongSignals,
            'allowWeakSignalAssistance' => $this->allowWeakSignalAssistance,
            'periodicCheck' => $this->periodicCheck,
            'periodicIntervalMs' => $this->periodicIntervalMs,
            'requestTimeoutMs' => $this->requestTimeoutMs,
            'observeDomChanges' => $this->observeDomChanges,
            'checkOnDomReady' => $this->checkOnDomReady,
            'checkGoogleAds' => $this->checkGoogleAds,
            'googleAdsUrl' => $this->googleAdsUrl,
            'checkBaitElement' => $this->checkBaitElement,
            'checkKnownMarker' => $this->checkKnownMarker,
            'sensitivity' => $this->sensitivity,
            'knownMarkers' => $this->knownMarkers,
            'antiAdblockSelectors' => $this->antiAdblockSelectors,
            'antiAdblockScriptPatterns' => $this->antiAdblockScriptPatterns,
            'baitClasses' => $this->baitClasses,
        ], $values));
    }
}
