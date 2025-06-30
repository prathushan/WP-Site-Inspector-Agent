<?php
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Export_Handler
{

    public function __construct()
    {
        add_action('wp_ajax_wpsi_export_excel', [$this, 'handle_export_excel']);
    }

    public function handle_export_excel()
    {
        // Verify nonce and permissions
        check_ajax_referer('wpsi_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['error' => 'Unauthorized access']);
        }

        // Initialize analyzer
        $analyzer = new WP_Site_Inspector_Analyzer();

        // Get data for all tabs
        $export_data = [
            'plugins' => $this->format_data($analyzer->analyze_tab('plugins')),
            'pages' => $this->format_data($analyzer->analyze_tab('pages')),
            'posts' => $this->format_data($analyzer->analyze_tab('posts')),
            'post-types' => $this->format_data($analyzer->analyze_tab('post-types')),
            'templates' => $this->format_data($analyzer->analyze_tab('templates')),
            'shortcodes' => $this->format_data($analyzer->analyze_tab('shortcodes')),
            'apis' => $this->format_data($analyzer->analyze_tab('apis')),
            'hooks' => $this->format_data($analyzer->analyze_tab('hooks')),
            'cdn' => $this->format_data($analyzer->analyze_tab('cdn'))
        ];

        wp_send_json_success($export_data);
    }

    private function format_data($data)
    {
        if (empty($data) || !is_array($data)) {
            return [];
        }

        $formatted = [];
        foreach ($data as $row) {
            if (is_array($row)) {
                // Convert arrays to strings for Excel compatibility
                $formatted_row = [];
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        $formatted_row[$key] = implode(', ', $value);
                    } else {
                        $formatted_row[$key] = $value;
                    }
                }
                $formatted[] = $formatted_row;
            } else {
                $formatted[] = ['value' => $row];
            }
        }

        return $formatted;
    }
}

// Initialize the export handler
new WP_Site_Inspector_Export_Handler();
