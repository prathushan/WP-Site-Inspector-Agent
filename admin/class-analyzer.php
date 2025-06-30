<?php
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Analyzer
{
    public function analyze_tab($tab)
    {
        switch ($tab) {
            case 'theme':
                return $this->analyze_theme();

            case 'builders':
                return $this->analyze_builders();

            case 'plugins':
                return $this->analyze_plugins();

            case 'pages':
                return $this->analyze_pages();

            case 'posts':
                return $this->analyze_posts();

            case 'post-types':
                return $this->analyze_post_types();

            case 'templates':
                return $this->analyze_templates();

            case 'shortcodes':
                return $this->analyze_shortcodes();

            case 'hooks':
                return $this->analyze_hooks();

            case 'apis':
                return $this->analyze_apis();

            case 'cdn':
                return $this->analyze_cdn();

            case 'logs':
                return $this->analyze_logs();

            default:
                return false;
        }
    }

    private function analyze_theme()
    {
        $theme = wp_get_theme();
        $name = $theme->get('Name');
        $version = $theme->get('Version');
        $type = file_exists(get_theme_root() . '/' . $theme->get_stylesheet() . '/theme.json')
            ? __('Block (FSE)', 'wp-site-inspector')
            : __('Classic', 'wp-site-inspector');

        return [
            ['Active Theme', esc_html($name) . ' v' . esc_html($version)],
            ['Theme Type', esc_html($type)]
        ];
    }

    private function analyze_builders()
    {
        $all_plugins = get_plugins();
        $builders = [];
        $builder_list = [
            'elementor/elementor.php' => 'Elementor',
            'wpbakery-visual-composer/wpbakery.php' => 'WPBakery',
            'siteorigin-panels/siteorigin-panels.php' => 'SiteOrigin Page Builder',
            'beaver-builder/beaver-builder.php' => 'Beaver Builder',
            'thrive-visual-editor/thrive-visual-editor.php' => 'Thrive Architect',
            'divi-builder/divi-builder.php' => 'Divi Builder',
            'fusion-builder/fusion-builder.php' => 'Avada Builder',
            'oxygen/functions.php' => 'Oxygen Builder',
            'brizy/brizy.php' => 'Brizy',
            'themify-builder/themify-builder.php' => 'Themify Builder',
            'seedprod/seedprod.php' => 'SeedProd'
        ];

        foreach ($builder_list as $slug => $label) {
            if (isset($all_plugins[$slug])) {
                $builders[] = [
                    'name' => __($label, 'wp-site-inspector'),
                    'status' => is_plugin_active($slug) ? __('Active', 'wp-site-inspector') : __('Inactive', 'wp-site-inspector')
                ];
            }
        }

        return $builders;
    }

    private function analyze_plugins()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all_plugins = get_plugins();
        $update_plugins = get_site_transient('update_plugins');
        $plugins = [];

        foreach ($all_plugins as $slug => $info) {
            $has_update = isset($update_plugins->response[$slug]);
            $plugin_path = WP_PLUGIN_DIR . '/' . $slug;
            $install_time = file_exists($plugin_path) ? date('Y-m-d H:i:s', filectime($plugin_path)) : __('N/A', 'wp-site-inspector');
            $update_time = file_exists($plugin_path) ? date('Y-m-d H:i:s', filemtime($plugin_path)) : __('N/A', 'wp-site-inspector');

            // Use consistent status values
            $status = is_plugin_active($slug) ? 'active' : 'inactive';

            $plugins[] = [
                'name' => $info['Name'],
                'status' => $status,
                'update' => $has_update ? __('Update available', 'wp-site-inspector') : __('Up to date', 'wp-site-inspector'),
                'installed_on' => $install_time,
                'last_update' => $update_time,
            ];
        }

        return $plugins;
    }

    private function analyze_pages()
    {
        $pages = [];
        foreach (get_pages(['post_status' => ['publish', 'draft']]) as $page) {
            $formatted_date = $page->post_status === 'publish'
                ? date('m/d/y, h:ia', strtotime($page->post_date))
                : __('Not Published', 'wp-site-inspector');

            // Use consistent status values
            $status = strtolower($page->post_status);

            $pages[] = [
                'title' => $page->post_title,
                'status' => $status,
                'date' => $formatted_date
            ];
        }
        return $pages;
    }

    private function analyze_posts()
    {
        $posts = [];
        foreach (get_posts(['numberposts' => -1, 'post_status' => ['publish', 'draft', 'pending']]) as $post) {
            $posts[] = [
                'title' => $post->post_title,
                'status' => ucfirst(__($post->post_status, 'wp-site-inspector')),
                'date' => ($post->post_status === 'publish')
                    ? date('d/m/y, h:iA', strtotime($post->post_date))
                    : __('Not Published', 'wp-site-inspector')
            ];
        }
        return $posts;
    }

    public function analyze_post_types()
    {
        try {
            $post_types = [];
            foreach (get_post_types([], 'objects') as $post_type => $obj) {
                // Safely get the file source
                $file = $obj->_builtin ? 'Built in' : (!empty($obj->description) && stripos($obj->description, 'plugin') !== false ? 'Plugin (guessed)' : 'functions.php or plugin');

                // Safely get post count
                $count = wp_count_posts($post_type);
                $published = isset($count->publish) ? intval($count->publish) : 0;

                // Safely get last used date
                $last = get_posts([
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'posts_per_page' => 1,
                    'fields'         => 'ids'
                ]);

                // Format the last used date
                $last_used = !empty($last) ? get_the_date('Y-m-d H:i:s', $last[0]) : '—';

                // Safely get the label
                $label = isset($obj->label) && !empty($obj->label)
                    ? $obj->label
                    : (isset($obj->labels->name) ? $obj->labels->name : ucfirst($post_type));

                // Add to results array using numeric index
                $post_types[] = [
                    'type'       => $post_type,
                    'label'      => $label,
                    'location'   => $file,
                    'used_count' => $published,
                    'last_used'  => $last_used
                ];
            }

            return $post_types;
        } catch (Exception $e) {
            error_log('WP Site Inspector - Post Types Analysis Error: ' . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    private function analyze_templates()
    {
        $templates = [];
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
        foreach ($rii as $file) {
            if ($file->isDir() || pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;

            $path = $file->getPathname();
            $relative = str_replace(ABSPATH, '', $path);
            $base = basename($path);

            if (strpos($relative, '/themes/') === false) continue;

            if (preg_match('/^(page|single|archive|category|tag|index|home|404|search|author|taxonomy).*\.php$/', $base)) {
                $templates[] = ['title' => $base, 'path' => $relative];
            }

            $contents = file_get_contents($path);
            if (preg_match('/Template Name\s*:\s*(.+)/i', $contents, $match)) {
                $templates[] = ['title' => trim($match[1]), 'path' => $relative];
            }
        }
        return $templates;
    }

    public function analyze_shortcodes()
    {
        try {
            global $wpdb;
            $shortcodes = [];

            // Find all shortcodes in theme/plugin files
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
            foreach ($rii as $file) {
                if ($file->isDir()) continue;
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (!in_array($ext, ['php', 'js'])) continue;

                $path = $file->getPathname();
                $relative = str_replace(ABSPATH, '', $path);
                $lines = file($path);

                foreach ($lines as $i => $line) {
                    if (preg_match_all('/add_shortcode\s*\(\s*[\'"]([^\'"]+)[\'"]/', $line, $matches)) {
                        foreach ($matches[1] as $tag) {
                            $shortcode_key = '[' . $tag . ']';
                            if (!isset($shortcodes[$shortcode_key])) {
                                $shortcodes[$shortcode_key] = [
                                    'shortcode' => $shortcode_key,
                                    'file' => $relative,
                                    'used_in' => []
                                ];
                            }
                        }
                    }
                }
            }

            // Search posts/pages for shortcode usage
            $contents = $wpdb->get_results("
                SELECT post_title, post_content 
                FROM {$wpdb->posts} 
                WHERE post_status IN ('publish', 'draft')
            ", ARRAY_A);

            foreach ($contents as $entry) {
                foreach ($shortcodes as $tag => &$info) {
                    if (strpos($entry['post_content'], $tag) !== false) {
                        $info['used_in'][] = $entry['post_title'];
                    }
                }
            }

            // Convert used_in arrays to comma-separated strings
            foreach ($shortcodes as &$info) {
                $info['used_in'] = empty($info['used_in']) ? 'Not used' : implode(', ', array_unique($info['used_in']));
            }

            // Convert to indexed array for consistent output
            return array_values($shortcodes);
        } catch (Exception $e) {
            error_log('WP Site Inspector - Shortcodes Analysis Error: ' . $e->getMessage());
            return []; // Return empty array on error
        }
    }
    public function analyze_hooks()
    {
        try {
            global $wp_filter;
            $hooks_data = [];
            $theme_path = '/themes/' . wp_get_theme()->get_stylesheet() . '/';

            foreach ($wp_filter as $hook_name => $hook_obj) {
                if (is_object($hook_obj)) {
                    foreach ($hook_obj->callbacks as $priority => $callbacks) {
                        foreach ($callbacks as $cb) {
                            $callback = $this->get_callback_name($cb['function']);
                            if ($callback) {
                                // Get the file path for this callback if possible
                                $reflection = null;
                                $file_path = '';

                                try {
                                    if (is_array($cb['function'])) {
                                        if (is_object($cb['function'][0])) {
                                            $reflection = new ReflectionMethod(get_class($cb['function'][0]), $cb['function'][1]);
                                        } else {
                                            $reflection = new ReflectionMethod($cb['function'][0], $cb['function'][1]);
                                        }
                                    } elseif (is_string($cb['function'])) {
                                        $reflection = new ReflectionFunction($cb['function']);
                                    }

                                    if ($reflection) {
                                        $file_path = str_replace(ABSPATH, '', $reflection->getFileName());
                                    }
                                } catch (Exception $e) {
                                    // Skip if we can't get reflection info
                                    continue;
                                }

                                // Only include hooks from the current theme
                                if (!empty($file_path) && strpos($file_path, $theme_path) !== false) {
                                    $hooks_data[] = [
                                        'type' => 'Action/Filter',
                                        'hook' => $hook_name,
                                        'registered_in' => $callback . ' (' . $file_path . ')'
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            return $hooks_data;
        } catch (Exception $e) {
            error_log('WP Site Inspector - Hooks Analysis Error: ' . $e->getMessage());
            return [];
        }
    }

    private function get_callback_name($callback)
    {
        if (is_string($callback)) {
            return $callback;
        } elseif (is_array($callback)) {
            if (is_object($callback[0])) {
                return get_class($callback[0]) . '->' . $callback[1];
            } else {
                return $callback[0] . '::' . $callback[1];
            }
        } elseif (is_object($callback)) {
            if ($callback instanceof Closure) {
                return 'Anonymous function';
            } else {
                return get_class($callback);
            }
        }
        return false;
    }

    private function analyze_apis()
    {
        $rest_apis = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(ABSPATH),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $excluded_dirs = ['vendor', 'node_modules', '.git']; // Add any directories to exclude

            foreach ($iterator as $file) {
                // Skip directories and non-PHP/JS files
                if ($file->isDir() || !in_array($file->getExtension(), ['php', 'js'])) {
                    continue;
                }

                // Skip excluded directories
                foreach ($excluded_dirs as $dir) {
                    if (strpos($file->getPathname(), $dir) !== false) {
                        continue 2;
                    }
                }

                $path = $file->getPathname();
                $relative_path = str_replace(ABSPATH, '', $path);

                // Read file line by line to save memory
                $handle = fopen($path, 'r');
                $line_number = 0;

                while (($line = fgets($handle)) !== false) {
                    $line_number++;

                    if (strpos($line, 'register_rest_route') === false) {
                        continue;
                    }

                    // More robust pattern matching
                    if (preg_match(
                        '/register_rest_route\s*\(\s*([\'"])([^\1]+?)\1\s*,\s*([\'"])([^\3]+?)\3/',
                        $line,
                        $matches
                    )) {
                        $namespace = $matches[2];
                        $route = $matches[4];
                        $endpoint = $namespace . $route;

                        if (!isset($rest_apis[$endpoint])) {
                            $rest_apis[$endpoint] = [
                                'endpoint' => $endpoint,
                                'namespace' => $namespace,
                                'route' => $route,
                                // 'file' => $relative_path,
                                // 'line' => $line_number,
                                // 'used_in' => []
                            ];
                        }
                    }
                }

                fclose($handle);
            }

            // Convert to indexed array for consistent output
            return array_values($rest_apis);
        } catch (Exception $e) {
            error_log('API analysis error: ' . $e->getMessage());
            return [
                [
                    'endpoint' => 'Error',
                    'namespace' => 'Analysis failed',
                    'route' => $e->getMessage(),
                    'file' => '',
                    'line' => 0,
                    'used_in' => []
                ]
            ];
        }
    }

    private function analyze_cdn()
    {
        try {
            $cdn_links = [];
            $theme = wp_get_theme();
            $theme_path = '/themes/' . $theme->get_stylesheet() . '/';

            // Common CDN libraries and their patterns
            $cdn_patterns = [
                'swiper' => ['swiper', 'cdn.swiper.js'],
                'jquery' => ['jquery', 'code.jquery.com'],
                'bootstrap' => ['bootstrap', 'maxcdn.bootstrapcdn.com/bootstrap'],
                'fontawesome' => ['fontawesome', 'use.fontawesome.com', 'font-awesome'],
                'gsap' => ['gsap', 'cdnjs.cloudflare.com/ajax/libs/gsap'],
                'chart.js' => ['chart.js', 'cdn.jsdelivr.net/npm/chart.js'],
                'lodash' => ['lodash', 'cdn.jsdelivr.net/npm/lodash'],
                'moment' => ['moment', 'momentjs'],
                'anime' => ['anime.js', 'animejs'],
                'three' => ['three.js', 'threejs']
            ];

            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(get_theme_root() . '/' . $theme->get_stylesheet()),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
            } catch (UnexpectedValueException $e) {
                error_log('WP Site Inspector - CDN Analysis Error: ' . $e->getMessage());
                return [['No theme files found', 'Theme directory not accessible']];
            }

            foreach ($iterator as $file) {
                // Skip directories and non-PHP/JS/HTML files
                if ($file->isDir() || !in_array(strtolower($file->getExtension()), ['php', 'js', 'html'])) {
                    continue;
                }

                $path = $file->getPathname();
                $relative_path = str_replace(ABSPATH, '', $path);

                // Skip if file is not readable
                if (!is_readable($path)) {
                    continue;
                }

                // Read file contents
                $contents = @file_get_contents($path);
                if ($contents === false) {
                    continue;
                }

                // Check for CDN patterns
                foreach ($cdn_patterns as $lib => $patterns) {
                    foreach ($patterns as $pattern) {
                        if (stripos($contents, $pattern) !== false) {
                            // Extract URL if possible
                            // preg_match('/(https?:\/\/[^\s\'"]+(?:' . preg_quote($pattern, '/') . ')[^\s\'"]+)/', $contents, $matches);
                            // $url = !empty($matches[1]) ? $matches[1] : '';

                            $cdn_links[] = [
                                $lib,
                                $relative_path
                            ];
                            break; // Break inner loop once we find a match for this library
                        }
                    }
                }
            }

            // Return default message if no CDN links found
            if (empty($cdn_links)) {
                return [['No CDN libraries', 'No external libraries detected']];
            }

            // Remove duplicates
            $cdn_links = array_map("unserialize", array_unique(array_map("serialize", $cdn_links)));

            return $cdn_links;
        } catch (Exception $e) {
            error_log('WP Site Inspector - CDN Analysis Error: ' . $e->getMessage());
            return [['Error', 'Failed to analyze CDN usage: ' . esc_html($e->getMessage()), '']];
        }
    }

    public function analyze_logs()
    {
        try {
            $log_file = WP_CONTENT_DIR . '/site-inspector.log';
            $max_entries = 100;
            $log_rows = [];
            $seen_messages = [];
            $limit_reached = false;

            if (file_exists($log_file)) {
                $log_lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $log_lines = array_reverse($log_lines); // Start from latest

                $filtered_lines = [];

                foreach ($log_lines as $line) {
                    // Skip non-error headers
                    if (preg_match('/^(Analysis|Theme|Plugins|Pages|Posts|Templates|Shortcodes)/i', $line)) {
                        continue;
                    }

                    // Match structured error format
                    if (preg_match('/^\[(ERROR|WARNING|NOTICE|DEPRECATED|FATAL)\]\s([\d\-:\s]+)\s\-\s(.+?)(?:\s\(File:\s(.+?),\sLine:\s(\d+)\))?$/', $line, $matches)) {
                        $type = strtoupper($matches[1]);
                        $timestamp = trim($matches[2]);
                        $message = trim($matches[3]);
                        $file = isset($matches[4]) ? 'File: ' . trim($matches[4]) : '';
                        $line_no = isset($matches[5]) ? 'Line: ' . trim($matches[5]) : '';

                        // Normalize message to deduplicate based on content (ignore line number)
                        $dedup_key = $message . ($file ? " ($file)" : '');

                        $full_message = $message;
                        if ($file || $line_no) {
                            $full_message .= ' (' . trim("$file $line_no") . ')';
                        }

                        if (isset($seen_messages[$dedup_key])) {
                            continue;
                        }

                        $seen_messages[$dedup_key] = true;
                        $filtered_lines[] = $line;

                        $ai_button = '<button class="button ask-ai-button" data-message="' . esc_attr($full_message) . '">' . esc_html__('Ask AI', 'wp-site-inspector') . '</button>';

                        $log_rows[] = [
                            esc_html($timestamp ? date('m/d/y, h:ia', strtotime($timestamp)) : 'N/A'),
                            esc_html($type),
                            esc_html($full_message),
                            $ai_button
                        ];

                        if (count($filtered_lines) >= $max_entries) {
                            $limit_reached = true;
                            break;
                        }
                    }
                }
            }

            // Fallback if no errors found
            if (empty($log_rows)) {
                $log_rows[] = ['—', esc_html__('INFO', 'wp-site-inspector'), esc_html__('No error logs found.', 'wp-site-inspector'), ''];
            }

            // Return just the rows for AJAX requests
            if (wp_doing_ajax()) {
                return $log_rows;
            }

            // Add notices
            $notices = '';
            if ($limit_reached) {
                $notices .= '<div class="notice notice-warning"><p>' . esc_html__('Displaying the most recent 100 unique log entries.', 'wp-site-inspector') . '</p></div>';
            }

            $clear_button = '';
            $custom_title = 'Error Logs';

            return [
                'rows' => $log_rows,
                'notices' => $notices,
                'clear_button' => $clear_button,
                'custom_title' => $custom_title
            ];
        } catch (Exception $e) {
            error_log('WP Site Inspector - Logs Analysis Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function to log errors in a structured format
     */
    public function log_error($type, $message, $file = '', $line = '')
    {
        $log_file = WP_CONTENT_DIR . '/site-inspector.log';
        $timestamp = current_time('Y-m-d H:i:s');

        $log_entry = sprintf(
            '[%s] %s - %s%s',
            strtoupper($type),
            $timestamp,
            $message,
            ($file || $line) ? sprintf(' (File: %s, Line: %s)', $file, $line) : ''
        );

        error_log($log_entry . PHP_EOL, 3, $log_file);

        // The email notification will be handled by WP_Site_Inspector_Email_Handler
        // which checks the error threshold and sends emails accordingly
    }
}
