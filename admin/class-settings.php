<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Settings {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_submenu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_submenu() {
        add_submenu_page(
            'wp-site-inspector',
            'Site Inspector Settings',
            'Settings',
            'manage_options',
            'wpsi-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('wpsi_settings_group', 'wpsi_api_key');

        add_settings_section(
            'wpsi_settings_section',
            'API Integration',
            null,
            'wpsi-settings'
        );

        add_settings_field(
            'wpsi_api_key',
            'API Key',
            [$this, 'api_key_field_html'],
            'wpsi-settings',
            'wpsi_settings_section'
        );
    }

    public function api_key_field_html() {
        $value = esc_attr(get_option('wpsi_api_key', ''));
        echo "<input type='text' name='wpsi_api_key' value='$value' style='width: 400px;'>";
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Site Inspector - Settings</h1>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
                <div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('wpsi_settings_group');
                do_settings_sections('wpsi-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
