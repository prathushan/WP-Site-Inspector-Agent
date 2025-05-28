<?php
if (!defined('ABSPATH')) exit;

// AJAX handler for Ask AI
function wpsi_handle_ask_ai() {
    header('Content-Type: application/json');

    if (empty($_POST['message'])) {
        wp_send_json_error([
            'error' => __('Missing message parameter.', 'wp-site-inspector')
        ]);
    }

    $message = sanitize_text_field($_POST['message']);
    $api_key = get_option('wpsi_api_key', '');

    // Validate API key
    if (empty($api_key)) {
        wp_send_json_error([
            'error' => __('API key is not configured. Please go to Site Inspector → Settings and add your API key.', 'wp-site-inspector')
        ]);
    }

    $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => get_site_url(),
            'X-Title'       => get_bloginfo('name'),
        ],
        'body' => json_encode([
            'model' => 'deepseek/deepseek-chat-v3-0324:free',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => __('You are a helpful assistant skilled in analyzing and fixing WordPress and PHP errors.', 'wp-site-inspector'),
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
        wp_send_json_error([
            'error' => $response->get_error_message()
        ]);
    }

    $body = wp_remote_retrieve_body($response);
    $decoded = json_decode($body, true);

    // Log raw response for debugging
    error_log("OpenRouter raw response: " . print_r($body, true));

    if (!empty($decoded['choices'][0]['message']['content'])) {
        wp_send_json_success([
            'response' => $decoded['choices'][0]['message']['content']
        ]);
    } elseif (!empty($decoded['error']['message'])) {
        wp_send_json_error([
            'error' => $decoded['error']['message']
        ]);
    } else {
        wp_send_json_error([
            'error' => __('No valid AI response or error message received.', 'wp-site-inspector'),
            'raw'   => $body
        ]);
    }
}

// Register AJAX actions
add_action('wp_ajax_wpsi_ask_ai', 'wpsi_handle_ask_ai');
add_action('wp_ajax_nopriv_wpsi_ask_ai', 'wpsi_handle_ask_ai');

class WP_Site_Inspector_Ajax_Handler {
    
    private $items_per_page = 10;
    
    public function __construct() {
        add_action('wp_ajax_wpsi_load_tab_content', [$this, 'handle_tab_content_load']);
        add_action('wp_ajax_wpsi_load_page', [$this, 'handle_page_load']);
    }
    
    public function handle_page_load() {
        check_ajax_referer('wpsi_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        $tab = sanitize_text_field($_POST['tab']);
        $page = intval($_POST['page']);
        $analyzer = new WP_Site_Inspector_Analyzer();
        
        $data = $analyzer->analyze_tab($tab);
        $paginated_data = $this->paginate_data($data, $page);
        
        ob_start();
        $this->render_table_rows($tab, $paginated_data['items'], $page);
        $html = ob_get_clean();
        
        wp_send_json_success([
            'html' => $html,
            'total_pages' => $paginated_data['total_pages']
        ]);
    }
    
    private function paginate_data($data, $page = 1) {
        if (!is_array($data)) {
            return ['items' => [], 'total_pages' => 0];
        }
        
        $total_items = count($data);
        $total_pages = ceil($total_items / $this->items_per_page);
        
        $offset = ($page - 1) * $this->items_per_page;
        $items = array_slice($data, $offset, $this->items_per_page);
        
        return [
            'items' => $items,
            'total_pages' => $total_pages
        ];
    }
    
    public function handle_tab_content_load() {
        check_ajax_referer('wpsi_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : '';
        
        if (empty($tab)) {
            wp_send_json_error('Tab parameter is required');
        }
        
        $analyzer = new WP_Site_Inspector_Analyzer();
        
        // Get data for this tab
        $data = $analyzer->analyze_tab($tab);
        
        if ($data === false) {
            wp_send_json_error('Invalid tab or error analyzing data');
        }
        
        // Paginate the data
        $paginated_data = $this->paginate_data($data);
        
        // Generate HTML for the tab
        ob_start();
        $this->render_tab_content($tab, $paginated_data['items'], $paginated_data['total_pages']);
        $html = ob_get_clean();
        
        // Get chart data if needed
        $chart_data = $this->get_chart_data($tab, $data);
        
        wp_send_json_success([
            'html' => $html,
            'chartData' => $chart_data
        ]);
    }
    
    private function render_tab_content($tab, $data, $total_pages, $custom_title = null) {
        $headers = $this->get_headers_for_tab($tab);
        
        echo "<div id='$tab' class='tab-content'>";
        echo "<h2>" . ($custom_title ? wp_kses_post($custom_title) : esc_html($this->get_tab_title($tab))) . "</h2>";
        echo "<div class='wpsi-table-wrap'>";
        
        // Table start
        echo "<table data-title='" . esc_attr($this->get_tab_title($tab)) . "'><thead><tr>";
        echo "<th>" . esc_html__('S.No', 'wp-site-inspector') . "</th>";
        
        foreach ($headers as $th) {
            echo "<th>" . esc_html($th) . "</th>";
        }
        
        echo "</tr></thead><tbody id='{$tab}-tbody'>";
        
        $this->render_table_rows($tab, $data);
        
        echo "</tbody></table></div>";
        
        // Pagination controls
        if ($total_pages > 1) {
            $this->render_pagination($tab, $total_pages);
        }
        
        echo "</div>";
    }
    
    private function render_table_rows($tab, $data, $page = 1) {
        if (empty($data) || !is_array($data)) {
            $colspan = count($this->get_headers_for_tab($tab)) + 1;
            echo "<tr><td colspan='" . esc_attr($colspan) . "'>" . esc_html__('No data available', 'wp-site-inspector') . "</td></tr>";
            return;
        }

        // Calculate starting index based on current page
        $page = max(1, intval($page)); // Ensure page is at least 1
        $items_per_page = intval($this->items_per_page);
        $starting_index = intval(($page - 1) * $items_per_page); // Ensure integer type
        
        foreach ($data as $index => $row) {
            echo "<tr>";
            // Calculate S.No independently of the data structure, ensuring integer type
            $sno = intval($starting_index) + intval($index) + 1; // Ensure all operands are integers
            echo "<td>" . esc_html($sno) . "</td>";
            
            if (is_array($row)) {
                foreach ($row as $col) {
                    $display_value = is_array($col) ? implode(', ', $col) : $col;
                    echo "<td>" . wp_kses_post($display_value) . "</td>";
                }
            } else {
                echo "<td>" . wp_kses_post($row) . "</td>";
            }
            
            echo "</tr>";
        }
    }
    
    private function render_pagination($tab, $total_pages) {
        echo "<div class='wpsi-pagination' data-tab='$tab' data-total-pages='$total_pages'>";
        
        // Previous button
        echo "<button class='pagination-btn prev-page' " . (1 <= 1 ? 'disabled' : '') . ">" . esc_html__('Previous', 'wp-site-inspector') . "</button>";
        
        // First page
        echo "<button class='pagination-btn page-number active' data-page='1'>1</button>";
        
        if ($total_pages > 7) {
            // If current page is 1, show first 5 pages
            echo "<button class='pagination-btn page-number' data-page='2'>2</button>";
            echo "<button class='pagination-btn page-number' data-page='3'>3</button>";
            echo "<button class='pagination-btn page-number' data-page='4'>4</button>";
            echo "<button class='pagination-btn page-number' data-page='5'>5</button>";
            
            if ($total_pages > 8) {
                echo "<span class='pagination-ellipsis'>...</span>";
            }
            
            // Last two pages
            if ($total_pages > 7) {
                if ($total_pages > 7) {
                    echo "<button class='pagination-btn page-number' data-page='" . ($total_pages - 1) . "'>" . ($total_pages - 1) . "</button>";
                }
                echo "<button class='pagination-btn page-number' data-page='$total_pages'>$total_pages</button>";
            }
        } else {
            // If less than 8 pages, show all numbers
            for ($i = 2; $i <= $total_pages; $i++) {
                echo "<button class='pagination-btn page-number' data-page='$i'>$i</button>";
            }
        }
        
        // Next button
        echo "<button class='pagination-btn next-page' " . ($total_pages <= 1 ? 'disabled' : '') . ">" . esc_html__('Next', 'wp-site-inspector') . "</button>";
        echo "</div>";
    }
    
    private function get_headers_for_tab($tab) {
        switch ($tab) {
            case 'theme':
                return ['Property', 'Value'];
            case 'builders':
                return ['Builder', 'Status'];
            case 'plugins':
                return ['Plugin Name', 'Status', 'Update Status', 'Last Updated', 'Installed On'];
            case 'pages':
                return ['Title', 'Status', 'Published At'];
            case 'posts':
                return ['Title', 'Status', 'Published At'];
            case 'post-types':
                return ['Type', 'Label', 'Location', 'Used Count', 'Last Used'];
            case 'templates':
                return ['Template Title', 'File'];
            case 'shortcodes':
                return ['Shortcode', 'File', 'Used In'];
            case 'hooks':
                return ['Type', 'Hook', 'Registered In'];
            case 'apis':
                return ['Endpoint', 'Location', 'Used In'];
            case 'cdn':
                return ['Library', 'File'];
            case 'logs':
                return ['Date', 'Type', 'Message', 'AI'];
            default:
                return [];
        }
    }
    
    private function get_tab_title($tab) {
        $titles = [
            'theme' => __('Theme Info', 'wp-site-inspector'),
            'builders' => __('Theme Builders', 'wp-site-inspector'),
            'plugins' => __('Plugins', 'wp-site-inspector'),
            'pages' => __('Pages', 'wp-site-inspector'),
            'posts' => __('Posts', 'wp-site-inspector'),
            'post-types' => __('Post Types', 'wp-site-inspector'),
            'templates' => __('Templates', 'wp-site-inspector'),
            'shortcodes' => __('Shortcodes', 'wp-site-inspector'),
            'hooks' => __('Hooks', 'wp-site-inspector'),
            'apis' => __('REST API Endpoints', 'wp-site-inspector'),
            'cdn' => __('CDN / JS Usage', 'wp-site-inspector'),
            'logs' => __('Error Logs', 'wp-site-inspector')
        ];
        
        return $titles[$tab] ?? ucfirst($tab);
    }
    
    private function get_chart_data($tab, $data) {
        $chart_data = [];

        // Get plugins data for charts
        if ($tab === 'plugins' || $tab === 'theme') {
            $analyzer = new WP_Site_Inspector_Analyzer();
            $plugins_data = $analyzer->analyze_tab('plugins');
            $active = count(array_filter($plugins_data, fn($p) => $p['status'] === 'Active'));
            $inactive = count($plugins_data) - $active;
            
            $chart_data['plugins'] = [
                'active_inactive' => [$active, $inactive]
            ];
        }

        // Get pages data for charts
        if ($tab === 'pages' || $tab === 'theme') {
            $analyzer = new WP_Site_Inspector_Analyzer();
            $pages_data = $analyzer->analyze_tab('pages');
            $published = count(array_filter($pages_data, fn($p) => $p['status'] === 'Publish'));
            $draft = count($pages_data) - $published;
            
            $chart_data['pages'] = [
                'published_draft' => [$published, $draft]
            ];
        }

        // Get overview data for combined chart
        if ($tab === 'theme') {
            $analyzer = new WP_Site_Inspector_Analyzer();
            
            $overview_data = [
                'labels' => [
                    'Posts',
                    'Plugins',
                    'Pages',
                    'Post Types',
                    'Templates',
                    'Shortcodes',
                    'REST APIs'
                ],
                'data' => [
                    count($analyzer->analyze_tab('posts')),
                    count($analyzer->analyze_tab('plugins')),
                    count($analyzer->analyze_tab('pages')),
                    count($analyzer->analyze_tab('post-types')),
                    count($analyzer->analyze_tab('templates')),
                    count($analyzer->analyze_tab('shortcodes')),
                    count($analyzer->analyze_tab('apis'))
                ]
            ];
            
            $chart_data['overview'] = $overview_data;
        }

        return $chart_data;
    }
}

// Initialize the AJAX handler
new WP_Site_Inspector_Ajax_Handler();
