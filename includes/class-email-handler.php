<?php
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Email_Handler
{

    public function __construct()
    {
        add_action('admin_init', [$this, 'check_and_send_log_emails']);
    }

    public function check_and_send_log_emails()
    {
        $log_file = WP_CONTENT_DIR . '/site-inspector.log';
        $last_sent_index = (int) get_option('wpsi_last_sent_log_index', 0);
        $new_logs_to_send = [];
        $error_threshold = get_option('wpsi_error_threshold', 1);
        $alert_emails = get_option('wpsi_alert_emails', '');

        // Check if log email notifications are enabled
        $log_email_enabled = get_option('wpsi_enable_log_email', false);
        if (!$log_email_enabled) {
            return; // Do not send emails if disabled
        }

        // If no alert emails configured, try to get admin email
        if (empty($alert_emails)) {
            $admin_email = get_option('admin_email');
            if (!empty($admin_email) && is_email($admin_email)) {
                $alert_emails = $admin_email;
            } else {
                // Try to get site URL domain as fallback
                $site_url = parse_url(get_site_url(), PHP_URL_HOST);
                $alert_emails = 'wordpress@' . $site_url;
            }
        }

        // Split emails by comma and validate
        $email_recipients = array_filter(
            array_map('trim', explode(',', $alert_emails)),
            'is_email'
        );

        if (empty($email_recipients)) {
            // Log that no valid email addresses were found
            error_log('WP Site Inspector: No valid email addresses configured for error notifications.');
            return;
        }

        if (file_exists($log_file)) {
            $log_lines_all = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $total_lines = count($log_lines_all);

            if ($total_lines > $last_sent_index) {
                $new_lines = array_slice($log_lines_all, $last_sent_index);
                $error_count = 0;

                foreach ($new_lines as $line) {
                    if (preg_match('/^\[(ERROR|WARNING|NOTICE|DEPRECATED|FATAL)\]\s([\d\-:\s]+)\s\-\s(.+?)(?:\s\(File:\s(.+?),\sLine:\s(\d+)\))?$/', $line, $matches)) {
                        $type = strtoupper($matches[1]);
                        $timestamp = trim($matches[2]);
                        $message = trim($matches[3]);
                        $file = isset($matches[4]) ? 'File: ' . trim($matches[4]) : '';
                        $line_no = isset($matches[5]) ? 'Line: ' . trim($matches[5]) : '';

                        $full_message = $message;
                        if ($file || $line_no) {
                            $full_message .= ' (' . trim("$file $line_no") . ')';
                        }

                        $new_logs_to_send[] = "*[$type]* $full_message\nTimestamp: $timestamp";

                        if (in_array($type, ['ERROR', 'FATAL'])) {
                            $error_count++;
                        }
                    }
                }

                // Send email if error threshold is reached
                if ($error_count >= $error_threshold && !empty($new_logs_to_send)) {
                    $this->send_log_notification($new_logs_to_send, $email_recipients);
                }

                // Update last sent index
                update_option('wpsi_last_sent_log_index', $total_lines);
            }
        }
    }

    private function send_log_notification($logs, $recipients)
    {
        $site_name = get_bloginfo('name');
        $admin_url = admin_url('admin.php?page=wp-site-inspector');

        $subject = sprintf('[%s] Error Log Alert - Site Inspector', $site_name);

        $message = "Hello,\n\n";
        $message .= "The following errors have been detected on your WordPress site:\n\n";
        $message .= implode("\n\n", $logs);
        $message .= "\n\nYou can view the full logs at: " . $admin_url . "\n\n";
        $message .= "Best regards,\nSite Inspector Plugin";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];

        // Send to each recipient individually
        foreach ($recipients as $recipient) {
            wp_mail($recipient, $subject, $message, $headers);
        }
    }
}

// Initialize the email handler
new WP_Site_Inspector_Email_Handler();