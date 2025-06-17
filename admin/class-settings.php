<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Settings {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_submenu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_footer', [$this, 'dynamic_model_dropdown_script']); // for dynamic JS
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
        register_setting('wpsi_settings_group', 'wpsi_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]);

        register_setting('wpsi_settings_group', 'wpsi_alert_emails', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_emails'],
            'default' => ''
        ]);

        register_setting('wpsi_settings_group', 'wpsi_error_threshold', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 1
        ]);

        register_setting('wpsi_settings_group', 'wpsi_ai_provider', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'openrouter'
        ]);

        register_setting('wpsi_settings_group', 'wpsi_ai_model', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'deepseek/deepseek-chat-v3-0324:free'
        ]);

        add_settings_section(
            'wpsi_settings_section',
            'API Integration & Notifications',
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

        add_settings_field(
            'wpsi_alert_emails',
            'Alert Emails',
            [$this, 'alert_emails_field_html'],
            'wpsi-settings',
            'wpsi_settings_section'
        );

        add_settings_field(
            'wpsi_error_threshold',
            'Error Threshold',
            [$this, 'error_threshold_field_html'],
            'wpsi-settings',
            'wpsi_settings_section'
        );

        add_settings_field(
            'wpsi_ai_provider',
            'AI Provider',
            [$this, 'ai_provider_field_html'],
            'wpsi-settings',
            'wpsi_settings_section'
        );

        add_settings_field(
            'wpsi_ai_model',
            'AI Model',
            [$this, 'ai_model_field_html'],
            'wpsi-settings',
            'wpsi_settings_section'
        );
    }

    public function alert_emails_field_html() {
        $emails = get_option('wpsi_alert_emails', '');
        echo "<input type='text' name='wpsi_alert_emails' value='" . esc_attr($emails) . "' style='width: 400px;' autocomplete='off'>";
        echo "<p class='description'>Enter multiple emails separated by commas (e.g. owner@example.com,dev@example.com)</p>";
    }

    public function error_threshold_field_html() {
        $value = get_option('wpsi_error_threshold', 1);
        echo "<input type='number' name='wpsi_error_threshold' value='" . esc_attr($value) . "' min='1' max='100' class='small-text'>";
        echo "<p class='description'>Number of errors required before sending an email notification.</p>";
    }

    public function api_key_field_html() {
        $option = get_option('wpsi_api_key', '');
        echo "<input type='text' name='wpsi_api_key' value='" . esc_attr($option) . "' style='width: 400px;' autocomplete='off'>";
        echo "<p class='description'>Enter your API key for the selected provider.</p>";
    }

    public function ai_provider_field_html() {
        $value = get_option('wpsi_ai_provider', 'openrouter');
        $options = [
            'openrouter' => 'OpenRouter',
            'openai'     => 'OpenAI',
            'deepseek'   => 'DeepSeek',
            'anthropic'  => 'Anthropic',
            'google'     => 'Google',
            'mistral'    => 'Mistral',
        ];
        echo "<select name='wpsi_ai_provider' id='wpsi_ai_provider'>";
        foreach ($options as $key => $label) {
            $selected = selected($value, $key, false);
            echo "<option value='" . esc_attr($key) . "' $selected>$label</option>";
        }
        echo "</select>";
        echo "<p class='description'>Select the provider for AI model.</p>";
    }

    public function ai_model_field_html() {
        $saved_provider = get_option('wpsi_ai_provider', 'openrouter');
        $saved_model = get_option('wpsi_ai_model', '');

        $models = $this->get_models_for_provider($saved_provider);

        echo "<select name='wpsi_ai_model' id='wpsi_ai_model'>";
        foreach ($models as $model_value => $model_label) {
            $selected = selected($saved_model, $model_value, false);
            echo "<option value='" . esc_attr($model_value) . "' $selected>$model_label</option>";
        }
        echo "</select>";
        echo "<p class='description'>Choose a model from the selected provider.</p>";
    }

    private function get_models_for_provider($provider) {
        switch ($provider) {
            case 'openai':
                return [
                    'gpt-4' => 'GPT-4',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                ];
            case 'deepseek':
                return [
                    'deepseek/deepseek-chat' => 'DeepSeek Chat',
                    'deepseek/deepseek-coder' => 'DeepSeek Coder',
                    'deepseek/deepseek-chat-v3-0324:free' => 'DeepSeek Chat v3 (Free)',
                ];
            case 'anthropic':
                return [
                    'claude-3-opus-20240229' => 'Claude 3 Opus',
                    'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                    'claude-3-haiku-20240307' => 'Claude 3 Haiku',
                ];
            case 'google':
                return [
                    'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                    'gemini-1.0-pro' => 'Gemini 1.0 Pro',
                ];
            case 'mistral':
                return [
                    'mistral-small' => 'Mistral Small',
                    'mistral-medium' => 'Mistral Medium',
                    'mistral-large' => 'Mistral Large',
                ];
            case 'openrouter':
            default:
                return [
                    'openai/gpt-3.5-turbo' => 'GPT-3.5 Turbo (OpenRouter)',
                    'openai/gpt-4' => 'GPT-4 (OpenRouter)',
                    'deepseek/deepseek-chat-v3-0324:free' => 'DeepSeek Chat v3 (Free)',
                ];
        }
    }

    public function sanitize_emails($value) {
        $emails = array_filter(array_map('trim', explode(',', $value)));
        $valid_emails = [];
        foreach ($emails as $email) {
            if (is_email($email)) {
                $valid_emails[] = sanitize_email($email);
            }
        }
        return implode(',', $valid_emails);
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
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

    public function dynamic_model_dropdown_script() {
        $provider_models = [
            'openai' => [
                'gpt-4' => 'GPT-4',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            ],
            'deepseek' => [
                'deepseek/deepseek-chat' => 'DeepSeek Chat',
                'deepseek/deepseek-coder' => 'DeepSeek Coder',
                'deepseek/deepseek-chat-v3-0324:free' => 'DeepSeek Chat v3 (Free)',
            ],
            'openrouter' => [
                'openai/gpt-3.5-turbo' => 'GPT-3.5 Turbo (OpenRouter)',
                'openai/gpt-4' => 'GPT-4 (OpenRouter)',
                'deepseek/deepseek-chat-v3-0324:free' => 'DeepSeek Chat v3 (Free)',
            ],
            'anthropic' => [
                'claude-3-opus-20240229' => 'Claude 3 Opus',
                'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                'claude-3-haiku-20240307' => 'Claude 3 Haiku',
            ],
            'google' => [
                'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                'gemini-1.0-pro' => 'Gemini 1.0 Pro',
            ],
            'mistral' => [
                'mistral-small' => 'Mistral Small',
                'mistral-medium' => 'Mistral Medium',
                'mistral-large' => 'Mistral Large',
            ],
        ];
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const providerSelect = document.getElementById('wpsi_ai_provider');
                const modelSelect = document.getElementById('wpsi_ai_model');
                const providerModels = <?php echo json_encode($provider_models); ?>;

                providerSelect.addEventListener('change', function () {
                    const selectedProvider = this.value;
                    const models = providerModels[selectedProvider] || {};
                    modelSelect.innerHTML = '';

                    for (const [value, label] of Object.entries(models)) {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = label;
                        modelSelect.appendChild(option);
                    }
                });
            });
        </script>
        <?php
    }
}
