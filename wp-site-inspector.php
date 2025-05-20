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



// Instantiate Admin UI
new WP_Site_Inspector_Admin_UI();
