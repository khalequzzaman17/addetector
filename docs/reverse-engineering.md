# Reconstruction record

## Target

CHP Ads Block Detector, version 4.0.3 as listed on WordPress.org. The public ZIP was downloaded into the local research directory and opened with REA as a ZIP artifact.

## Evidence-backed observations

| Area | Observed behavior | Reconstruction |
| --- | --- | --- |
| Known marker | Looks for `stndz-style` before other checks | `DetectorConfig::$knownMarkers` |
| Network probe | Loads Google Ads script and inspects load/error and `window.adsbygoogle` | `googleBlocked()` |
| XHR probe | Performs an asynchronous request to the same ad URL | Simplified into bounded script-load probe with timeout |
| Bait probe | Adds ad-like classes and checks `offsetHeight` | `baitBlocked()` |
| Lifecycle | Runs on DOM ready | `checkOnDomReady` |
| Presentation | Shows a modal and toggles body/page state | Configurable overlay and page lock |
| Fallback | Emits a `<noscript>` modal | `showNoscriptFallback` |
| Anti-allowlisting | Generates random CSS/JS names | Per-render random root ID; no source obfuscation |
| AakList profile | Generic selectors and script path fragments such as `#adblock-message`, `adblockchecker`, `blockadblock.js` | `AakListProfile` and `DetectorConfig` signatures |

## Limitations

The original browser runtime was not executed in a controlled browser with a specific extension installed, so extension-by-extension detection claims remain static-source observations. Network filtering, CSP, privacy extensions, offline mode, and browser behavior can produce false positives or false negatives. No detector can guarantee enforcement against a hostile browser.

The AakList source was fetched on 2026-10-13 and hashed as `b8b0e58261fda95d1eb7529d11ab700b9cb8b0c313be890e40c8cdf4246a730f`. REA was attempted against the raw text target, but the available REA provider accepts executable/archive/browser targets and rejected a plain `.txt` artifact as unsupported. The rule-family analysis therefore uses the fetched source as external text evidence plus bounded local parsing; it does not claim REA static-analysis coverage for the text file itself.
