<?php
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Admin_UI
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
    }


    public function register_menu()
    {
        add_menu_page(
            __('WP Site Inspector', 'wp-site-inspector'),
            __('Site Inspector', 'wp-site-inspector'),
            'manage_options',
            'wp-site-inspector',
            [$this, 'render_dashboard'],
            'dashicons-chart-area',
            81
        );
        add_submenu_page(
            'wp-site-inspector',
            'Code AI Assistant',
            'Code AI',
            'manage_options',
            'wpsi-code-ai',
            [$this, 'render_code_ai_page']
        );
        add_submenu_page(
            'wp-site-inspector',
            'Site Backup',
            'Backup',
            'manage_options',
            'wpsi-backup',
            [$this, 'render_backup_page']
        );
    }

    /**
     * Enqueue assets only for the plugin page
     */
    public function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_wp-site-inspector') return;

        // Enqueue styles
        wp_enqueue_style('wpsi-style', plugin_dir_url(__FILE__) . 'assets/style.css', [], '1.0.0');

        // Enqueue Chart.js from CDN
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js',
            [],
            '3.7.0',
            true
        );

        // Enqueue main plugin script
        wp_enqueue_script(
            'wpsi-main',
            plugin_dir_url(__FILE__) . 'assets/script.js',
            ['jquery', 'chartjs'],
            '1.0.0',
            true
        );

        // Localize script with necessary data
        wp_localize_script('wpsi-main', 'wpsiAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpsi_ajax_nonce')
        ]);
    }

    /**
     * Register toggle setting for enabling WP debug log
     */
    public function register_settings()
    {
        register_setting('wpsi_settings_group', 'wpsi_enable_debug_log');
    }

    /**
     * Load dashboard view
     */
    public function render_dashboard()
    {
        include_once plugin_dir_path(__FILE__) . 'views/dashboard.php';
    }

    public function render_code_ai_page()
    {
        include_once plugin_dir_path(__FILE__) . 'views/code-ai.php';
    }

    public function render_backup_page()
    {
        include plugin_dir_path(__FILE__) . 'views/backup.php';
    }
}
