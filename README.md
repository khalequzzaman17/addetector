# khalequzzaman17/addetector

Framework-agnostic, clean-room Composer package inspired by the public behavior of CHP Ads Block Detector. It detects likely ad-blocking with browser-side signals and renders a configurable notice. It also includes a small heuristic set derived from the AakList anti-adblock filter list. Browser-side detection cannot be mathematically 100% reliable because the visitor controls the browser.

## Install

```bash
composer require khalequzzaman17/addetector
```

For local development in this repository:

```bash
composer dump-autoload
composer test
```

## Generic PHP

```php
use AdblockDetector\AdblockDetector;
use AdblockDetector\DetectorConfig;
use AdblockDetector\NoticeConfig;

$detector = new AdblockDetector(
    new DetectorConfig(periodicCheck: true),
    new NoticeConfig(
        title: 'Support this site',
        message: 'Please disable your ad blocker, then refresh the page.',
        closeText: 'Continue anyway',
        iconUrl: '/images/adblock.png',
    ),
);

echo $detector->render(); // place before </body>
```

## Laravel

Register a singleton in a service provider, then render it in a Blade layout:

```php
$this->app->singleton(AdblockDetector::class, fn () => new AdblockDetector(
    new DetectorConfig(periodicCheck: true),
    new NoticeConfig(closeText: 'Continue anyway'),
));
```

```blade
{!! app(\AdblockDetector\AdblockDetector::class)->render() !!}
```

## Core behavior

1. Check known DOM markers used by some blockers.
2. Check known anti-adblock DOM and script signatures derived from AakList as weak contextual signals.
3. Optionally load the Google Ads script and treat an error/timeout or missing `window.adsbygoogle` as blocked.
4. Add a bait element with ad-like classes and inspect its computed size/display.
5. Require multiple independent strong signals by default before showing the modal.
6. Observe dynamically inserted DOM and optionally re-check periodically.
7. Include a no-JavaScript fallback when enabled.

The implementation is deliberately independent of WordPress hooks, WordPress escaping helpers, original branding, original assets, and original obfuscation code.

The AakList-derived checks are weak heuristics: seeing an anti-adblock signature means the page is participating in anti-adblock behavior, but is not definitive proof that a blocker is enabled. By default, one strong signal plus one weak signal or two strong signals are required. Tune `minimumStrongSignals` and disable the AakList checks with empty `antiAdblockSelectors` and `antiAdblockScriptPatterns` arrays if a site has false positives.

## Reverse-engineering notes

The upstream public ZIP was downloaded from WordPress.org and inspected as a shipped artifact with REA. The package archive SHA-256 was `2edef56e4b4bed5df496896cbfc44b41ca5ff3159fbec1ac3fb6149e7418b203`; REA recorded the ZIP inventory as evidence `ev_3e8c83cfbdab37ac87b75780917542f5189c0aa728c4289c97ee8e42af5e562c` and extraction as `ev_c01caec1b42def4450ef39ceef4c14aca4f0a6aebd0b18a35857864bf4650981`.

Observed source flow in the public artifact:

- `view/main_scripts.php`: checks `stndz-style`, loads `adsbygoogle.js`, checks `window.adsbygoogle`, and performs an XHR/error probe.
- `view/main_scripts.php`: creates a hidden element with ad-like classes and treats zero `offsetHeight` as blocked.
- `view/main_scripts.php`: runs on `DOMContentLoaded` and calls the modal handler once.
- `view/noscript.php`: provides a visible fallback when JavaScript is disabled.
- `includes/randomClass.php`: randomizes CSS/DOM identifiers to reduce simple selector-based allowlisting.

This repository is a clean-room reconstruction of those observable concepts, not a copy of the upstream WordPress plugin. The result intentionally replaces WordPress hooks, upstream branding, bundled assets, and source obfuscation with a small Composer API.

## AakList attribution

The anti-adblock heuristic signatures were selected from [Anti-Adblock Killer filters](https://raw.githubusercontent.com/reek/anti-adblock-killer/master/anti-adblock-killer-filters.txt), authored by Reek. The upstream list declares [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/). This package does not bundle the full list; it uses a small derived selector subset and keeps the upstream URL and attribution here.
