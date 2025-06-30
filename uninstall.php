<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
// delete_option('wpsi_api_key');
delete_option('wpsi_alert_emails');
// delete_option('wpsi_error_threshold');
// delete_option('wpsi_ai_provider');
// delete_option('wpsi_ai_model');
delete_option('wpsi_last_sent_log_index');

// Delete the log file
$log_file = WP_CONTENT_DIR . '/site-inspector.log';
if (file_exists($log_file)) {
    @unlink($log_file);
}