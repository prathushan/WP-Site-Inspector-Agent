<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Fix_Agent
{

    public function __construct()
    {
        add_action('wp_ajax_wpsi_fix_with_ai', [$this, 'handle_fix']);
    }

    public function handle_fix()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['error' => 'Unauthorized']);
        }

        $message = sanitize_text_field($_POST['message'] ?? '');
        $file = '';
        $line = 0;

        if (preg_match('/\(File:\s*(.+?)[,\s]+Line:\s*(\d+)\)/i', $message, $matches)) {
            $file = trim($matches[1]);
            $line = intval($matches[2]);
        }

        if (!$message || !$file || !$line) {
            wp_send_json_error(['error' => 'Invalid or incomplete error message']);
        }

        $relative_file = str_replace(site_url(), '', $file);
        $relative_file = ltrim(str_replace(ABSPATH, '', $relative_file), '/');
        $file_path = ABSPATH . $relative_file;

        if (!file_exists($file_path)) {
            wp_send_json_error(['error' => 'File not found: ' . $file_path]);
        }

        $lines = file($file_path);
        $original_line = $lines[$line - 1] ?? '';
        $context = implode("", array_slice($lines, max(0, $line - 3), 5));

        $provider = get_option('wpsi_ai_provider', 'openrouter');
        $model    = get_option('wpsi_ai_model', 'deepseek/deepseek-chat-v3-0324:free');
        $api_key  = get_option('wpsi_api_key', '');

        if (empty($api_key) || empty($model)) {
            wp_send_json_error(['error' => 'Missing AI API key or model in settings.']);
        }

        $cleaned_message = trim(preg_replace('/\(File:.+\)/', '', $message));
        $prompt = "PHP Error: $cleaned_message\n\nFile Path: $file\nLine Number: $line\n\nCode Snippet:\n$context\n\nFix the broken line of code at line $line. Return ONLY the corrected line, and include semicolon or closing bracket if required. Do not return explanations.";

        $endpoint = '';
        $headers = [];
        $body = [];

        switch ($provider) {
            case 'wp-site-inspector':
                $endpoint = 'https://a20e533b-cd6d-48d5-a013-fd822a0e1324-00-1v48q3mlrue66.pike.replit.dev/handle-message';
                $headers = [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ];
                $body = json_encode([
                    'user_id' => get_current_user_id(),
                    'message' => $prompt
                ]);
                break;

            case 'openai':
                $endpoint = 'https://api.openai.com/v1/chat/completions';
                $headers = [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ];
                $body = json_encode([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a PHP code fixer bot. Return only the corrected line.'],
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]);
                break;

            case 'anthropic':
                $endpoint = 'https://api.anthropic.com/v1/messages';
                $headers = [
                    'x-api-key' => $api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ];
                $body = json_encode([
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens' => 1000
                ]);
                break;

            case 'google':
                $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;
                $headers = ['Content-Type' => 'application/json'];
                $body = json_encode([
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);
                break;

            case 'mistral':
                $endpoint = 'https://api.mistral.ai/v1/chat/completions';
                $headers = [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ];
                $body = json_encode([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a PHP fixer bot. Return only the corrected line.'],
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]);
                break;

            case 'openrouter':
            default:
                $endpoint = 'https://openrouter.ai/api/v1/chat/completions';
                $headers = [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                    'HTTP-Referer'  => site_url(),
                    'X-Title'       => get_bloginfo('name')
                ];
                $body = json_encode([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful PHP code fixer bot. Return only the corrected line.'],
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]);
                break;
        }

        $response = wp_remote_post($endpoint, [
            'headers' => $headers,
            'body'    => $body,
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['error' => $response->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $fixed = '';

        switch ($provider) {
            case 'wp-site-inspector':
                $fixed = $body['result'] ?? '';
                break;
            case 'anthropic':
                $fixed = $body['content'][0]['text'] ?? '';
                break;
            case 'google':
                $fixed = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
                break;
            default:
                $fixed = $body['choices'][0]['message']['content'] ?? '';
                break;
        }

        $fixed = trim($fixed);

        // Validate unsafe or empty fix
        if (!$fixed || stripos($fixed, '<?php') !== false) {
            wp_send_json_error(['error' => 'Invalid AI response or unsafe fix.']);
        }

        // Incomplete block validation
        if (
            preg_match('/\b(if|for(each)?|while|switch|function|else)\b/i', $fixed) &&
            strpos($fixed, '{') !== false &&
            strpos($fixed, '}') === false
        ) {
            wp_send_json_error([
                'error' => 'Fix appears to contain an incomplete code block (e.g., missing closing brace). Rollback triggered.',
                'rollback' => true,
                'message' => "Fix created an unsafe code block. Rollback was applied. Please manually inspect: $file at line $line.",
                'file' => $file,
                'line' => $line
            ]);
        }

        // Backup and apply fix
        $backup_path = $file_path . '.bak';
        copy($file_path, $backup_path);
        $lines[$line - 1] = rtrim($fixed, " \t\n") . "\n";
        file_put_contents($file_path, implode("", $lines));

        // PHP lint check
        $cmd = "php -l " . escapeshellarg($file_path);
        exec($cmd, $output, $return_var);

        if ($return_var !== 0) {
            // Restore backup
            copy($backup_path, $file_path);
            wp_send_json_error([
                'error' => 'Fatal error occurred after applying fix. Rollback applied.',
                'rollback' => true,
                'message' => "Created fatal while clearing issue. Don't worry, rollback was applied. You can manually inspect the file: $file at line $line.",
                'file' => $file,
                'line' => $line
            ]);
        }

        wp_send_json_success([
            'status'      => 'success',
            'fixed_line'  => $fixed,
            'file'        => $file,
            'line'        => $line,
            'backup'      => $backup_path
        ]);
    }
}
