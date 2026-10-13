<?php

declare(strict_types=1);

namespace AdblockDetector;

final class DetectorConfig
{
    public function __construct(
        public readonly bool $enabled = true,
        public readonly bool $checkOnDomReady = true,
        public readonly bool $checkGoogleAds = true,
        public readonly bool $checkBaitElement = true,
        public readonly bool $checkKnownMarker = true,
        public readonly bool $periodicCheck = false,
        public readonly int $periodicIntervalMs = 30000,
        public readonly int $requestTimeoutMs = 4000,
        public readonly string $googleAdsUrl = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
        public readonly array $knownMarkers = ['stndz-style'],
        public readonly array $antiAdblockSelectors = [
            '#adblock-message', '.adblock-message', '.adblock-warning', '#adblock_layer',
            '.deadblocker-header-bar', '.afs_ads', '.no-ad', '.box_error', '.box-error',
            '.adblock-notice', '#adblockplus', '.adblock-msg',
        ],
        public readonly array $antiAdblockScriptPatterns = [
            'adblock-detector', 'adblockchecker', 'adblockbait', 'antiadblock',
            'blockadblock', 'fuckadblock', 'blockcake.js', 'anti-ad-buster.js',
            'jquery.adblock.js', 'jquery.antiadsblock.js', 'adblockpopup.js',
            'adblock_notify.js', 'adblock_detection', 'adblockdetect',
        ],
        public readonly array $baitClasses = [
            'adsbygoogle', 'ad-container', 'sidebar-ad', 'ad-slot', 'ads',
            'doubleclick', 'ad-placement', 'ad-placeholder', 'adbadge', 'bannerad', 'adsbox',
        ],
    ) {
        if ($this->periodicIntervalMs < 1000) {
            throw new \InvalidArgumentException('periodicIntervalMs must be at least 1000.');
        }
        if ($this->requestTimeoutMs < 250) {
            throw new \InvalidArgumentException('requestTimeoutMs must be at least 250.');
        }
    }

    public function with(array $values): self
    {
        return new self(...array_merge([
            'enabled' => $this->enabled,
            'checkOnDomReady' => $this->checkOnDomReady,
            'checkGoogleAds' => $this->checkGoogleAds,
            'checkBaitElement' => $this->checkBaitElement,
            'checkKnownMarker' => $this->checkKnownMarker,
            'periodicCheck' => $this->periodicCheck,
            'periodicIntervalMs' => $this->periodicIntervalMs,
            'requestTimeoutMs' => $this->requestTimeoutMs,
            'googleAdsUrl' => $this->googleAdsUrl,
            'knownMarkers' => $this->knownMarkers,
            'antiAdblockSelectors' => $this->antiAdblockSelectors,
            'antiAdblockScriptPatterns' => $this->antiAdblockScriptPatterns,
            'baitClasses' => $this->baitClasses,
        ], $values));
    }
}
