# Reverse Engineering & Source Analysis Record

## 1. Upstream Target Analysis: CHP Ads Block Detector

**Target:** Public CHP Ads Block Detector plugin (WordPress.org archive).

### Inspected Files & Observed Flow

1. **`view/main_scripts.php`**:
   - **Extension Marker Check (`fairAdblock`)**: Checks `document.getElementById('stndz-style') !== null` (Fair AdBlocker injection marker).
   - **Network Probe (`adsBlocked`)**: Creates a `<script>` tag pointing to `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js`. Checks `onload`, `onerror`, and whether `window.adsbygoogle` exists. Concurrently sends an XMLHttpRequest probe inspecting for `ublock`, `height:1px`, or status 0 / redirected URLs.
   - **Bait Element Probe (`checkMultiple`)**: Injects an off-screen `<div>` with ad-related class names (`adsbygoogle Ad-Container sidebar-ad ad-slot ad ads doubleclick ad-placement ad-placeholder adbadge BannerAd adsbox`). Checks whether `offsetHeight == 0` or if the element is removed.
   - **Connection Check (`is_connected`)**: Queries `window.navigator.onLine` to avoid false positives when the browser has no internet access.
   - **UI Trigger (`show_modal` / `hide_model`)**: Toggles visibility classes on modal and adds locking classes to the body element.

2. **`view/footer_part.php` & `view/noscript.php`**:
   - Renders the modal DOM tree, title, subtitle, refresh button, optional close button, and icon.
   - `view/noscript.php` provides a static fallback notice for users browsing with JavaScript disabled.
   - Bundles third-party branding links (`chpadblock.com`, `toolkitspro.com`).
   - Uses an `Obfuscator` eval-wrapper (`new \CHPADB\Includes\Obfuscator($content)`).

3. **`includes/scripts.php`**:
   - Manages WordPress action hooks (`wp_head`, `wp_footer`, `admin_enqueue_scripts`).
   - Handles CSS and JavaScript injection into the page output.
   - Calls `randomClass` to replace class/id tokens.

4. **`includes/randomClass.php`**:
   - Generates randomized class names using AES-256-CBC hash formatting, splitting alphanumerics with an underscore (`substr(..., 0, $mid) . '_' . substr(..., $mid)`). This avoids ad-blocker heuristics that blanket-hide DOM nodes with long uninterrupted hex/alphanumeric IDs.

5. **`includes/functions.php`**:
   - Manages default configuration options, basic regex HTML minification, and branding injections.

### Clean-Room Reconstruction Principles

- **No WordPress Dependencies**: Pure PHP 8.1+ classes (`AdblockDetector`, `DetectorConfig`, `NoticeConfig`) with zero CMS coupling.
- **No Bundled Assets or Branding**: All third-party links, logos, and branding from the CHP plugin are completely eliminated.
- **No Unsafe Obfuscation**: Replaced eval-based string obfuscation with readable, auditable vanilla JavaScript and safe JSON serialization.
- **Enhanced Reliability**: Structured multi-signal heuristic evaluation requiring multiple independent strong signals or strong + weak signals, preventing single-point false positives.

---

## 2. Anti-Adblock Killer (AakList) Analysis

**Source:** [reek/anti-adblock-killer](https://raw.githubusercontent.com/reek/anti-adblock-killer/master/anti-adblock-killer-filters.txt)  
**Author:** Reek  
**License:** Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)  
**SHA-256:** `b8b0e58261fda95d1eb7529d11ab700b9cb8b0c313be890e40c8cdf4246a730f`  

### Nature of AakList
AakList is an Adblock Plus (ABP) filter list designed to **neutralize and circumvent** anti-adblock scripts and detection overlays. It contains 3,860 rules comprising domain-specific exceptions, script redirection rules, and element-hiding filters.

**Crucial Technical Distinction:** AakList is **not** an "is adblock installed?" detection API. Executing all 3,860 ABP rules directly in a PHP application would introduce severe performance overhead, domain collision, and rampant false positives.

### Curated Extraction for Detection Heuristics
Instead of blind rule execution, we extracted generic, domain-independent signatures to serve as **secondary/weak contextual signals**:

1. **Generic Cosmetic Warning Selectors**:
   - `#adblock-message`, `.adblock-message`, `.adblock-warning`, `#adblock_layer`, `.deadblocker-header-bar`, `.afs_ads`, `.no-ad`, `.box_error`, `.box-error`, `.adblock-notice`, `#adblockplus`, `.adblock-msg`, `#adbuddy-no-adb-container`, `#adbuddy-overlay`.
   - *Inference*: The presence of these DOM elements indicates anti-adblock countermeasures or blocked placeholders on the page.

2. **Generic Script Path Patterns**:
   - `adblock-detector`, `adblockchecker`, `adblockbait`, `antiadblock`, `blockadblock`, `fuckadblock`, `blockcake.js`, `anti-ad-buster.js`, `jquery.adblock.js`, `jquery.antiadsblock.js`, `adblockpopup.js`, `adblock_notify.js`, `adblock_detection`, `adblockdetect`, `deadblocker`, `no-adblock`.
   - *Inference*: Identifying loaded scripts matching these patterns provides context about whether ad-blocking defense tools are active in the browser session.

3. **Ad-Like Bait Class Names**:
   - `adsbygoogle`, `ad-container`, `sidebar-ad`, `ad-slot`, `ads`, `doubleclick`, `ad-placement`, `ad-placeholder`, `adbadge`, `bannerad`, `adsbox`, `carbon-ad`, `sponsor-container`.
   - *Inference*: Used to test whether the client browser is actively collapsing or styling known ad containers as `display: none` or zero height.

---

## 3. Practical Detection Architecture & Limitations

### Signal Classification

| Signal Type | Probe Mechanism | Confidence Level | Rationale |
| :--- | :--- | :--- | :--- |
| **Strong** | Bait element dimension collapse | High | Ad blockers inject CSS (`display: none !important`) that collapses ad containers to 0px. |
| **Strong** | Google Ads script error or timeout | High | Network-level blockers (uBlock, AdGuard, Pi-hole, Brave Shields) cancel or block network requests to ad endpoints. |
| **Strong** | Script loads but global `window.adsbygoogle` missing | High | Script replacement / stubbing extensions return empty responses or mock objects without ad properties. |
| **Strong** | Blocker extension DOM marker (e.g. `stndz-style`) | High | Definitively injected by specific extension engines (e.g. Fair AdBlocker). |
| **Weak** | AakList anti-adblock DOM selector match | Low / Contextual | Indicates page contains warning markup; not proof in isolation. |
| **Weak** | AakList anti-adblock script path match | Low / Contextual | Indicates third-party detection script presence; not proof in isolation. |

### Enforced Decision Rules
- **Weak signals NEVER trigger blocking alone.**
- Notice triggers if:
  `strongSignals >= minimumStrongSignals (default: 2)` **OR**
  `allowWeakSignalAssistance && strongSignals >= 1 && weakSignals >= 1`.
- Offline visitors (`navigator.onLine === false`) are never treated as ad-blocked due to network failures.
- MutationObserver mutations generated by the detector's own modal are ignored to prevent infinite loops.

### Technical Reality: 100% Detection Is Impossible
Client-side browser detection can never guarantee 100% mathematical accuracy:
- The visitor ultimately controls the browser, DOM execution, and user agent.
- Browser extensions can spoof APIs, stub global variables, or remove detection DOM elements in real time.
- Strict Content Security Policies (CSP) or privacy network configurations can block third-party script probes.
- Detection must therefore be treated as a **high-confidence probabilistic heuristic**, designed with sensible fallbacks and low false-positive rates.
