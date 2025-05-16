<?php
/**
 * Plugin Name: WP Site Inspector
 * Description: Inspect active themes, post types, shortcodes, APIs, CDNs, templates, and more—visually.
 * Version: 1.0
 * Author: Prathusha, Prem, Vinay
 * Author URI: https://example.com/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-site-inspector
 * Domain Path: /languages
 */
if (!defined('ABSPATH')) exit;

// Wait until all plugins are loaded before initializing
add_action('plugins_loaded', 'wpsi_initialize_plugin');

function wpsi_initialize_plugin() {
    // Check if the class already exists (possibly from another similar plugin)
    if (!class_exists('WP_Site_Inspector_Admin_UI')) {
           // Load required classes safely
    require_once plugin_dir_path(__FILE__) . 'admin/class-admin-ui.php';
    require_once plugin_dir_path(__FILE__) . 'admin/class-analyzer.php';

    // Initialize your plugin
    new WP_Site_Inspector_Admin_UI();
    }else{
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>WP Site Inspector:</strong> Another plugin with similar functionality is already active. Please deactivate the other version to use the Current one.</p>';
            echo '</div>';
        });
        return;
    } 
}