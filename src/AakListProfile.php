<?php

declare(strict_types=1);

namespace AdblockDetector;

/**
 * Small runtime profile derived from the generic, domain-independent rules in
 * Reek's AakList. The full ABP list is intentionally not executed as CSS or
 * JavaScript: it contains domain-specific exceptions and network rules.
 */
final class AakListProfile
{
    public const SOURCE = 'https://raw.githubusercontent.com/reek/anti-adblock-killer/master/anti-adblock-killer-filters.txt';
    public const SOURCE_LICENSE = 'CC BY-SA 4.0';
    public const SOURCE_SHA256 = 'b8b0e58261fda95d1eb7529d11ab700b9cb8b0c313be890e40c8cdf4246a730f';

    public static function selectors(): array
    {
        return [
            '#adblock-message', '.adblock-message', '.adblock-warning', '#adblock_layer',
            '.deadblocker-header-bar', '.afs_ads', '.no-ad', '.box_error', '.box-error',
            '.adblock-notice', '.adblock-warning', '#adblockplus', '.adblock-msg',
            '.message.adblock-message', '.redtube-adblock-msg',
        ];
    }

    public static function scriptPatterns(): array
    {
        return [
            'adblock-detector', 'adblockchecker', 'adblockbait', 'antiadblock',
            'blockadblock', 'fuckadblock', 'blockcake.js', 'anti-ad-buster.js',
            'jquery.adblock.js', 'jquery.antiadsblock.js', 'adblockpopup.js',
            'adblock_notify.js', 'adblock_detection', 'adblockdetect',
        ];
    }
}
