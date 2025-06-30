<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Settings
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_settings_submenu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function add_settings_submenu()
    {
        add_submenu_page(
            'wp-site-inspector',
            'Site Inspector Settings',
            'Settings',
            'manage_options',
            'wpsi-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings()
    {
        // Register settings with proper permissions check
        if (!current_user_can('manage_options')) {
            return;
        }

        register_setting('wpsi_settings_group', 'wpsi_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
            'show_in_rest' => false
        ]);

        register_setting('wpsi_settings_group', 'wpsi_enable_log_email', [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        ]);

        register_setting('wpsi_settings_group', 'wpsi_alert_emails', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_emails'],
            'default' => '',
            'show_in_rest' => false
        ]);

        register_setting('wpsi_settings_group', 'wpsi_error_threshold', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 1,
            'show_in_rest' => false
        ]);

        register_setting('wpsi_settings_group', 'wpsi_ai_provider', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'wp-site-inspector',
            'show_in_rest' => false
        ]);

        register_setting('wpsi_settings_group', 'wpsi_ai_model', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'wpsi-01',
            'show_in_rest' => false
        ]);

        // Settings section
        add_settings_section(
            'wpsi_settings_section',
            esc_html__('API Integration & Notifications', 'wp-site-inspector'),
            null,
            'wpsi-settings'
        );

        // Settings fields
        add_settings_field(
            'wpsi_api_key',
            esc_html__('API Key', 'wp-site-inspector'),
            [$this, 'api_key_field_html'],
            'wpsi-settings',
            'wpsi_settings_section',
            ['label_for' => 'wpsi_api_key']
        );

        add_settings_field(
            'wpsi_enable_log_email',
            esc_html__('Enable Log Email Notifications', 'wp-site-inspector'),
            [$this, 'enable_log_email_field_html'],
            'wpsi-settings',
            'wpsi_settings_section'
        );

        add_settings_field(
            'wpsi_alert_emails',
            esc_html__('Alert Emails', 'wp-site-inspector'),
            [$this, 'alert_emails_field_html'],
            'wpsi-settings',
            'wpsi_settings_section',
            ['label_for' => 'wpsi_alert_emails']
        );

        add_settings_field(
            'wpsi_error_threshold',
            esc_html__('Error Threshold', 'wp-site-inspector'),
            [$this, 'error_threshold_field_html'],
            'wpsi-settings',
            'wpsi_settings_section',
            ['label_for' => 'wpsi_error_threshold']
        );

        add_settings_field(
            'wpsi_ai_provider',
            esc_html__('AI Provider', 'wp-site-inspector'),
            [$this, 'ai_provider_field_html'],
            'wpsi-settings',
            'wpsi_settings_section',
            ['label_for' => 'wpsi_ai_provider']
        );

        add_settings_field(
            'wpsi_ai_model',
            esc_html__('AI Model', 'wp-site-inspector'),
            [$this, 'ai_model_field_html'],
            'wpsi-settings',
            'wpsi_settings_section',
            ['label_for' => 'wpsi_ai_model']
        );
    }
    public function alert_emails_field_html()
    {
        $emails = get_option('wpsi_alert_emails', '');
        echo '<input type="text" id="wpsi_alert_emails" name="wpsi_alert_emails" value="' . esc_attr($emails) . '" class="regular-text" autocomplete="off">';
        echo '<p class="description">' . esc_html__('Enter multiple emails separated by commas (e.g. owner@example.com, dev@example.com)', 'wp-site-inspector') . '</p>';
    }

    public function enable_log_email_field_html()
    {
        $enabled = get_option('wpsi_enable_log_email', false);
        echo "<label style='display:inline-flex;align-items:center;gap:8px;'>
        <input type='checkbox' name='wpsi_enable_log_email' value='1'" . checked($enabled, true, false) . '> ' .
            esc_html__('Enable log email notifications', 'wp-site-inspector') . '
    </label>';
        echo '<p class="description">' . esc_html__('If enabled, error log email notifications will be sent when the threshold is reached.', 'wp-site-inspector') . '</p>';
    }

    public function error_threshold_field_html()
    {
        $value = get_option('wpsi_error_threshold', 1);
        echo '<input type="number" id="wpsi_error_threshold" name="wpsi_error_threshold" value="' . esc_attr($value) . '" min="1" max="100" class="small-text">';
        echo '<p class="description">' . esc_html__('Number of errors required before sending an email notification.', 'wp-site-inspector') . '</p>';
    }

    public function api_key_field_html()
    {
        $option = get_option('wpsi_api_key', '');
        echo '<input type="text" id="wpsi_api_key" name="wpsi_api_key" value="' . esc_attr($option) . '" class="regular-text" autocomplete="off">';
        echo '<p class="description">' . esc_html__('Enter your API key for the selected provider.', 'wp-site-inspector') . '</p>';
    }

    public function ai_provider_field_html()
    {
        $value = get_option('wpsi_ai_provider', 'wp-site-inspector');
        $options = [
            'wp-site-inspector' => 'WP Site Inspector',
            'openrouter' => 'OpenRouter',
            'openai'     => 'OpenAI',
            'deepseek'   => 'DeepSeek',
            'anthropic'  => 'Anthropic',
            'google'     => 'Google',
            'mistral'    => 'Mistral',
        ];
        echo '<select id="wpsi_ai_provider" name="wpsi_ai_provider" class="regular-text">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the provider for AI model.', 'wp-site-inspector') . '</p>';
    }

    public function ai_model_field_html()
    {
        $saved_provider = get_option('wpsi_ai_provider', 'wp-site-inspector');
        $saved_model = get_option('wpsi_ai_model', 'wpsi-01');

        $models = $this->get_models_for_provider($saved_provider);

        echo '<select id="wpsi_ai_model" name="wpsi_ai_model" class="regular-text">';
        foreach ($models as $model_value => $model_label) {
            echo '<option value="' . esc_attr($model_value) . '" ' . selected($saved_model, $model_value, false) . '>' . esc_html($model_label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Choose a model from the selected provider', 'wp-site-inspector') . '</p>';
    }

    private function get_models_for_provider($provider)
    {
        $models = [
            'wp-site-inspector' => [
                'wpsi-01' => 'WPSI-01 (Default)'
            ],
            'openai' => [
                'gpt-4' => 'GPT-4',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            ],
            'deepseek' => [
                'deepseek/deepseek-chat' => 'DeepSeek Chat',
                'deepseek/deepseek-coder' => 'DeepSeek Coder',
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
            'openrouter' => [
                'openai/gpt-3.5-turbo' => 'GPT-3.5 Turbo (OpenRouter)',
                'openai/gpt-4' => 'GPT-4 (OpenRouter)',
                'deepseek/deepseek-chat-v3-0324:free' => 'DeepSeek Chat v3 (Free)',

            ],
        ];

        return $models[$provider] ?? $models['wp-site-inspector'];
    }

    public function sanitize_emails($value)
    {
        $emails = array_filter(array_map('trim', explode(',', $value)));
        $valid_emails = [];
        foreach ($emails as $email) {
            if (is_email($email)) {
                $valid_emails[] = sanitize_email($email);
            }
        }
        return implode(',', $valid_emails);
    }

    public function render_settings_page()
    {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Add error/update messages
        settings_errors('wpsi_messages');
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wpsi_settings_group');
                do_settings_sections('wpsi-settings');
                submit_button(esc_html__('Save Settings', 'wp-site-inspector'));
                ?>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const providerSelect = document.getElementById('wpsi_ai_provider');
                const modelSelect = document.getElementById('wpsi_ai_model');

                // Define all available models
                const providerModels = {
                    'wp-site-inspector': {
                        'wpsi-01': 'WPSI-01 (Default)'
                    },
                    'openai': {
                        'gpt-4': 'GPT-4',
                        'gpt-3.5-turbo': 'GPT-3.5 Turbo'
                    },
                    'deepseek': {
                        'deepseek/deepseek-chat': 'DeepSeek Chat',
                        'deepseek/deepseek-coder': 'DeepSeek Coder',
                        'deepseek/deepseek-chat-v3-0324:free': 'DeepSeek Chat v3 (Free)'
                    },
                    'anthropic': {
                        'claude-3-opus-20240229': 'Claude 3 Opus',
                        'claude-3-sonnet-20240229': 'Claude 3 Sonnet',
                        'claude-3-haiku-20240307': 'Claude 3 Haiku'
                    },
                    'google': {
                        'gemini-1.5-pro': 'Gemini 1.5 Pro',
                        'gemini-1.0-pro': 'Gemini 1.0 Pro'
                    },
                    'mistral': {
                        'mistral-small': 'Mistral Small',
                        'mistral-medium': 'Mistral Medium',
                        'mistral-large': 'Mistral Large'
                    },
                    'openrouter': {
                        'openai/gpt-3.5-turbo': 'GPT-3.5 Turbo (OpenRouter)',
                        'openai/gpt-4': 'GPT-4 (OpenRouter)',
                        'deepseek/deepseek-chat-v3-0324:free': 'DeepSeek Chat v3 (Free)'

                    }
                };

                // Function to update model dropdown
                function updateModels(provider) {
                    // Clear current options
                    modelSelect.innerHTML = '';

                    // Get models for selected provider
                    const models = providerModels[provider] || {};

                    // Add new options
                    for (const [value, label] of Object.entries(models)) {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = label;
                        modelSelect.appendChild(option);
                    }

                    // Try to maintain the selected model if it exists in the new provider
                    const currentModel = '<?php echo esc_js(get_option('wpsi_ai_model', 'wpsi-01')); ?>';
                    if (models[currentModel]) {
                        modelSelect.value = currentModel;
                    }
                }

                // Initialize with current provider's models
                updateModels(providerSelect.value);

                // Add event listener for provider changes
                providerSelect.addEventListener('change', function() {
                    updateModels(this.value);
                });
            });
        </script>
<?php
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'wp-site-inspector_page_wpsi-settings') {
            return;
        }

        // No need for separate JS file since we're using inline JS
    }
}
