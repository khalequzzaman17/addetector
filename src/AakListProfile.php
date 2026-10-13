<?php

declare(strict_types=1);

namespace AdblockDetector;

/**
 * Curated profile derived from the generic, domain-independent rules in
 * Reek's Anti-Adblock Killer filter list (AakList).
 *
 * Attribution:
 * - Author: Reek (https://github.com/reek/anti-adblock-killer)
 * - License: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)
 *   https://creativecommons.org/licenses/by-sa/4.0/
 * - Upstream Source: https://raw.githubusercontent.com/reek/anti-adblock-killer/master/anti-adblock-killer-filters.txt
 *
 * Note: AakList is an Adblock Plus filter list created to neutralize anti-adblock
 * scripts. It is not an "is adblock installed?" API. This profile extracts only
 * generic, documented signatures used as secondary/weak contextual signals.
 */
final class AakListProfile
{
    public const AUTHOR = 'Reek';
    public const SOURCE = 'https://raw.githubusercontent.com/reek/anti-adblock-killer/master/anti-adblock-killer-filters.txt';
    public const SOURCE_LICENSE = 'CC BY-SA 4.0';
    public const SOURCE_SHA256 = 'b8b0e58261fda95d1eb7529d11ab700b9cb8b0c313be890e40c8cdf4246a730f';

    /**
     * Generic cosmetic selectors matching anti-adblock warnings or containers.
     *
     * @return list<string>
     */
    public static function selectors(): array
    {
        return [
            '#adblock-message',
            '.adblock-message',
            '.adblock-warning',
            '#adblock_layer',
            '.deadblocker-header-bar',
            '.afs_ads',
            '.no-ad',
            '.box_error',
            '.box-error',
            '.adblock-notice',
            '#adblockplus',
            '.adblock-msg',
            '#adbuddy-no-adb-container',
            '#adbuddy-overlay',
            '.message.adblock-message',
            '.ab-container',
            '#adchecker_btn',
        ];
    }

    /**
     * Generic script path fragments matching known anti-adblock solutions.
     *
     * @return list<string>
     */
    public static function scriptPatterns(): array
    {
        return [
            'adblock-detector',
            'adblockchecker',
            'adblockbait',
            'antiadblock',
            'blockadblock',
            'fuckadblock',
            'blockcake.js',
            'anti-ad-buster.js',
            'jquery.adblock.js',
            'jquery.antiadsblock.js',
            'adblockpopup.js',
            'adblock_notify.js',
            'adblock_detection',
            'adblockdetect',
            'deadblocker',
            'no-adblock',
        ];
    }

    /**
     * Commonly blocked ad-like class names used for client-side bait elements.
     *
     * @return list<string>
     */
    public static function baitClasses(): array
    {
        return [
            'adsbygoogle',
            'ad-container',
            'sidebar-ad',
            'ad-slot',
            'ads',
            'doubleclick',
            'ad-placement',
            'ad-placeholder',
            'adbadge',
            'bannerad',
            'adsbox',
            'carbon-ad',
            'sponsor-container',
        ];
    }
}
