<?php

/**
 * Plugin Name: Site Inspector
 * Description: Inspect active themes, post types, shortcodes, APIs, CDNs, templates, and more—visually.
 * Version: 1.0
 * Author: Prathusha, Prem Kumar, Vinay
 */

if (!defined('ABSPATH')) exit;

// Dynamically enable WP_DEBUG_LOG if toggle is enabled
$debug_toggle = get_option('wpsi_enable_debug_log');
if ($debug_toggle == '1') {
    if (!defined('WP_DEBUG')) define('WP_DEBUG', true);
    if (!defined('WP_DEBUG_LOG')) define('WP_DEBUG_LOG', true);
    if (!defined('WP_DEBUG_DISPLAY')) define('WP_DEBUG_DISPLAY', false);
    @ini_set('display_errors', 0);
}

// Load core classes
require_once plugin_dir_path(__FILE__) . 'admin/class-admin-ui.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-analyzer.php';
require_once plugin_dir_path(__FILE__) . 'includes/logger.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-settings.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-backup-export.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-export-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-email-handler.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-fix-agent.php';

// Add default settings
add_action('admin_init', function () {
    if (!get_option('wpsi_error_threshold')) {
        update_option('wpsi_error_threshold', 1);
    }

    // Get admin email or use a fallback
    $admin_email = get_option('admin_email');
    if (empty($admin_email) || !is_email($admin_email)) {
        // Try to get site URL domain as fallback
        $site_url = parse_url(get_site_url(), PHP_URL_HOST);
        $admin_email = 'wordpress@' . $site_url;
    }

    if (!get_option('wpsi_alert_emails')) {
        update_option('wpsi_alert_emails', $admin_email);
    }
});

add_action('wp_ajax_wpsi_manual_backup', function () {
    include plugin_dir_path(__FILE__) . 'admin/views/backup.php';
});

function wp_site_inspector_textDomain()
{
    load_plugin_textdomain('wp-site-inspector', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'wp_site_inspector_textDomain');


// Instantiate Admin UI
new WP_Site_Inspector_Admin_UI();
new WP_Site_Inspector_Settings();
new WP_Site_Inspector_Email_Handler();
new WP_Site_Inspector_Fix_Agent();

// Register AJAX handlers
add_action('wp_ajax_wpsi_load_tab_content', 'wpsi_load_tab_content_callback');
add_action('wp_ajax_wpsi_load_page', 'wpsi_load_page_callback');
add_action('wp_ajax_wpsi_ask_ai', 'wpsi_handle_ai_chat');

// Add export handler
// add_action('admin_post_wpsi_export_excel', 'wpsi_handle_export_excel');

function wpsi_load_tab_content_callback()
{
    check_ajax_referer('wpsi_ajax_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => esc_html__('Unauthorized access', 'wp-site-inspector')]);
    }

    $ajax_handler = new WP_Site_Inspector_Ajax_Handler();
    $ajax_handler->handle_tab_content_load();
}

function wpsi_load_page_callback()
{
    check_ajax_referer('wpsi_ajax_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => esc_html__('Unauthorized access', 'wp-site-inspector')]);
    }

    $ajax_handler = new WP_Site_Inspector_Ajax_Handler();
    $ajax_handler->handle_page_load();
}

function wpsi_handle_ai_chat()
{
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpsi_ajax_nonce')) {
        wp_send_json_error(['error' => esc_html__('Invalid nonce', 'wp-site-inspector')]);
        return;
    }

    // Get the message
    $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
    if (empty($message)) {
        wp_send_json_error(['error' => esc_html__('No message provided', 'wp-site-inspector')]);
        return;
    }

    // Here you would integrate with your AI service
    // For now, we'll just echo back a simple response
    $response = sprintf(
        esc_html__('I received your message: %s', 'wp-site-inspector'),
        esc_html($message)
    );

    wp_send_json_success(['response' => $response]);
}