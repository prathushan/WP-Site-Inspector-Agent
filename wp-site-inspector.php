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

function wp_site_inspector_textDomain() {
    load_plugin_textdomain('wp-site-inspector', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'wp_site_inspector_textDomain');


// Instantiate Admin UI
new WP_Site_Inspector_Admin_UI();
new WP_Site_Inspector_Settings();

// Register AJAX handlers
add_action('wp_ajax_wpsi_load_tab_content', 'wpsi_load_tab_content_callback');
add_action('wp_ajax_wpsi_load_page', 'wpsi_load_page_callback');

function wpsi_load_tab_content_callback() {
    check_ajax_referer('wpsi_ajax_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized access']);
    }
    
    $ajax_handler = new WP_Site_Inspector_Ajax_Handler();
    $ajax_handler->handle_tab_content_load();
}

function wpsi_load_page_callback() {
    check_ajax_referer('wpsi_ajax_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized access']);
    }
    
    $ajax_handler = new WP_Site_Inspector_Ajax_Handler();
    $ajax_handler->handle_page_load();
}