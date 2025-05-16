<?php
/**
 * Plugin Name: WP Site Inspector
 * Description: Inspect active themes, post types, shortcodes, APIs, CDNs, templates, and more—visually.
 * Version: 1.0
 * Author: Prathusha Prem Vinay
 * Author URI: https://example.com/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-site-inspector
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load translations
function wp_site_inspector_load_textdomain() {
    load_plugin_textdomain('wp-site-inspector', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'wp_site_inspector_load_textdomain');

// Load plugin files after all plugins are loaded
function wp_site_inspector_init_plugin() {
    require_once plugin_dir_path(__FILE__) . 'admin/class-admin-ui.php';
    require_once plugin_dir_path(__FILE__) . 'admin/class-analyzer.php';

    new WP_Site_Inspector_Admin_UI();
}
add_action('plugins_loaded', 'wp_site_inspector_init_plugin');
