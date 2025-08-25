<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap wpsi-accessibility-page">

    <h1 class="wp-heading-inline">🔎 WPSIA Accessibility</h1>
    <hr class="wp-header-end">

    <!-- Scan Selector -->
    <div class="wpsi-scan-box">
        <label for="wpsi-page-select"><strong>Select Page/Post to Scan:</strong></label>
        <select id="wpsi-page-select">
            <option>Hello world! (Post)</option>
        </select>
        <button class="button button-primary">Scan Page</button>
    </div>

    <!-- Scan Results -->
    <h2>Scanning: Hello world!</h2>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Issue</th>
                <th>Impact</th>
                <th>Affected Elements</th>
                <th>Help</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><strong>Banner landmark should not be contained in another landmark</strong><br><em>Ensure the banner landmark is at top level</em></td>
                <td><span class="wpsi-impact moderate">MODERATE</span></td>
                <td><code>header</code></td>
                <td><button class="button">Guide</button></td>
            </tr>
            <tr>
                <td>2</td>
                <td><strong>Contentinfo landmark should not be contained in another landmark</strong><br><em>Ensure the contentinfo landmark is at top level</em></td>
                <td><span class="wpsi-impact moderate">MODERATE</span></td>
                <td><code>footer</code></td>
                <td><button class="button">Guide</button></td>
            </tr>
            <tr>
                <td>3</td>
                <td><strong>Main landmark should not be contained in another landmark</strong><br><em>Ensure the main landmark is at top level</em></td>
                <td><span class="wpsi-impact moderate">MODERATE</span></td>
                <td><code>#wp--skip-link--target</code></td>
                <td><button class="button">Guide</button></td>
            </tr>
            <tr>
                <td>4</td>
                <td><strong>Document should not have more than one main landmark</strong></td>
                <td><span class="wpsi-impact moderate">MODERATE</span></td>
                <td><code>&lt;main&gt;</code></td>
                <td><button class="button">Guide</button></td>
            </tr>
        </tbody>
    </table>

    <!-- Premium Overlay -->
    <div class="wpsi-premium-overlay">
        <div class="wpsi-premium-modal">
            <h2>✨ Upgrade to Premium</h2>
            <p>Get detailed accessibility reports and advanced guidance with <strong>Accessibility Checker Premium</strong>.</p>
            <a href="https://www.wpsia.com/" target="_blank" class="button button-primary button-hero">Upgrade Now</a>
        </div>
    </div>
</div>

<style>
/* Scan Box */
.wpsi-scan-box {
    margin: 15px 0 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.wpsi-scan-box select {
    min-width: 200px;
    padding: 4px;
}

/* Impact Labels */
.wpsi-impact {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    color: #fff;
}

.wpsi-impact.moderate {
    background: #ffb100;
    color: #000;
}

/* === Premium Overlay === */
.wpsi-premium-overlay {
    position: fixed;
    top: 32px; /* WP admin bar */
    left: 160px; /* WP admin menu */
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}

.wpsi-premium-modal {
    background: #fff;
    padding: 40px;
    border-radius: 10px;
    max-width: 500px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.wpsi-premium-modal h2 {
    margin: 0 0 15px;
    font-size: 24px;
    color: #333;
}

.wpsi-premium-modal p {
    font-size: 15px;
    color: #555;
    margin-bottom: 20px;
}

.button-hero{
    background: #6D7AE2 !important;
    border:1px solid #6D7AE2 !important;
    border-radius:20px !important;
    animation: pulse 1.5s infinite;
}
.pro-premium-tag {
    background: #6D7AE2;
    color: #fff;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 4px;
    animation: pulse 1.5s infinite;
}

/* Pulse keyframes */
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
