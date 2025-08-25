<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap wpsi-premium-page">

    <h1 class="wp-heading-inline">🚀 WP Site Importer</h1>
    <hr class="wp-header-end">

    <!-- Nav Tabs -->
    <h2 class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active">Export Backup</a>
        <a href="#" class="nav-tab">Import Backup</a>
        <a href="#" class="nav-tab">Manage Backups</a>
        <a href="#" class="nav-tab">Performance</a>
    </h2>

    <!-- Server Information -->
    <div class="wpsi-card">
        <h2 class="wpsi-card-title">💻 Server Information</h2>
        <table class="widefat striped">
            <tbody>
                <tr>
                    <td>PHP Version</td>
                    <td><span class="wpsi-value">8.2.28</span></td>
                </tr>
                <tr>
                    <td>Memory Limit</td>
                    <td><span class="wpsi-value">256M</span></td>
                </tr>
                <tr>
                    <td>Max Execution Time</td>
                    <td><span class="wpsi-value">120 seconds</span></td>
                </tr>
                <tr>
                    <td>Upload Max Filesize</td>
                    <td><span class="wpsi-value">512M</span></td>
                </tr>
                <tr>
                    <td>Post Max Size</td>
                    <td><span class="wpsi-value">512M</span></td>
                </tr>
                <tr>
                    <td>Available Disk Space</td>
                    <td><span class="wpsi-value">1.26 TB</span></td>
                </tr>
            </tbody>
        </table>
        <button class="button button-primary">Refresh</button>
    </div>

    <!-- Performance Tips -->
    <div class="wpsi-card">
        <h2 class="wpsi-card-title">⚡ Performance Tips</h2>

        <div class="wpsi-grid">
            <div class="wpsi-tip">
                <h3>🧠 Memory Optimization</h3>
                <p>Increase memory limit to <code>512M</code> or higher in your <code>php.ini</code> for better backup performance.</p>
            </div>

            <div class="wpsi-tip">
                <h3>⏱ Execution Time</h3>
                <p>Increase max execution time to <code>300</code> seconds or higher for large backups.</p>
            </div>

            <div class="wpsi-tip">
                <h3>💾 Storage Optimization</h3>
                <p>Use SSD storage and exclude unnecessary directories like cache, logs, and temporary files.</p>
            </div>
        </div>
    </div>

    <!-- Premium Overlay -->
    <div class="wpsi-premium-overlay">
        <div class="wpsi-premium-modal">
            <h2>✨ Upgrade to Premium</h2>
            <p>Unlock backup,import,Manage and advanced features by upgrading to 
            <strong>WP Site Inspector Pro</strong>.</p>
            <a href="https://www.wpsia.com/" target="_blank" class="button button-primary button-hero">Upgrade Now</a>
        </div>
    </div>
</div>

<style>
.wpsi-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 6px;
    padding: 20px;
    margin-top: 20px;
}

.wpsi-card-title {
    margin: 0 0 15px;
    font-size: 16px;
    font-weight: 600;
}

/* Server Info Values */
.wpsi-value {
    color: #2271b1;
    font-weight: 600;
}

/* Tips Grid */
.wpsi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(250px,1fr));
    gap: 15px;
    margin-top: 15px;
}

.wpsi-tip {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
}

/* === Premium Overlay === */
.wpsi-premium-overlay {
    position: fixed;
    top: 32px; /* WP admin bar height */
    left: 160px; /* WP admin menu width */
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
