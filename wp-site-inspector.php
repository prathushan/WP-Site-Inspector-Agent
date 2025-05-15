<?php
/**
 * Plugin Name: WP Site Inspector
 * Description: Inspect active themes, post types, shortcodes, APIs, CDNs, templates, and more—visually.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'admin/class-admin-ui.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-analyzer.php';

// Instantiate the Admin UI class to add the admin menu & enqueue assets
new WP_Site_Inspector_Admin_UI();
