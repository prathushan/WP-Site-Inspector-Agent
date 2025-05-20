<?php
if (!defined('ABSPATH')) exit;

// AJAX handler function
function wpsi_handle_ask_ai() {
    header('Content-Type: application/json');

    if (empty($_POST['message'])) {
        wp_send_json_error(['error' => 'Missing message parameter.']);
    }

    $message = sanitize_text_field($_POST['message']);

    // Define your API keys
    $api_keys = [
        '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', // Primary
        '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'  // Fallback
    ];

    $success = false;
    $error = '';
    $raw_body = '';

    foreach ($api_keys as $key) {
        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => get_site_url(),
                'X-Title'       => get_bloginfo('name'),
            ],
            'body' => json_encode([
                'model' => 'deepseek/deepseek-chat-v3-0324:free',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant skilled in analyzing and fixing WordPress and PHP errors.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ]
            ]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            $error = $response->get_error_message();
            continue;
        }

        $raw_body = wp_remote_retrieve_body($response);
        $decoded = json_decode($raw_body, true);

        error_log("OpenRouter response: " . print_r($raw_body, true));

        if (!empty($decoded['choices'][0]['message']['content'])) {
            wp_send_json_success(['response' => $decoded['choices'][0]['message']['content']]);
            $success = true;
            break;
        } elseif (!empty($decoded['error']['message'])) {
            // If it's a rate limit or credit error, try next key
            if (strpos($decoded['error']['message'], 'Rate limit') !== false || strpos($decoded['error']['message'], 'credit') !== false) {
                $error = $decoded['error']['message'];
                continue;
            } else {
                // Other errors shouldn't trigger fallback
                wp_send_json_error(['error' => $decoded['error']['message']]);
            }
        }
    }

    // If both failed
    if (!$success) {
        wp_send_json_error([
            'error' => 'All API keys failed. Last error: ' . $error,
            'raw' => $raw_body
        ]);
    }
}

// Register AJAX actions
add_action('wp_ajax_wpsi_ask_ai', 'wpsi_handle_ask_ai');
add_action('wp_ajax_nopriv_wpsi_ask_ai', 'wpsi_handle_ask_ai');
