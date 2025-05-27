<?php
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Analyzer
{


    public function analyze_tab($tab) {
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

    // private function analyze_theme() {
    //     $theme = wp_get_theme();
    //     $name = $theme->get('Name');
    //     $version = $theme->get('Version');
        
    //     return [
    //         'name' => $name . ' v' . $version,
    //         'type' => file_exists(get_theme_root() . '/' . $theme->get_stylesheet() . '/theme.json') 
    //             ? __('Block (FSE)', 'wp-site-inspector') 
    //             : __('Classic', 'wp-site-inspector')
    //     ];
    // }

    private function analyze_theme() {
        $theme = wp_get_theme();
        $name = $theme->get('Name');
        $version = $theme->get('Version');
        $type=file_exists(get_theme_root() . '/' . $theme->get_stylesheet() . '/theme.json') 
                    ? __('Block (FSE)', 'wp-site-inspector') 
                    : __('Classic', 'wp-site-inspector');
        
        return [
            ['Active Theme', esc_html($name) . ' v' . esc_html($version)],
            ['Theme Type', esc_html($type)]
          ];
    }




    // private function analyze_theme() {
    //     $theme = wp_get_theme();
    //     $name=$theme->get('Name');
    //     $version = $theme->get('Version');
    //     return [
    //         'name' => $theme->get('Name'),
    //         'version' => $theme->get('Version'),
    //         'type' => file_exists(get_theme_root() . '/' . $theme->get_stylesheet() . '/theme.json') 
    //             ? __('Block (FSE)', 'wp-site-inspector') 
    //             : __('Classic', 'wp-site-inspector')
    //     ];
    // }

    // private function analyze_theme() {
    //     $theme = wp_get_theme();
    //     $theme_dir = get_theme_root() . '/' . $theme->get_stylesheet();
    
    //     $theme_type = file_exists($theme_dir . '/theme.json') 
    //         ? 'Block (FSE)' 
    //         : 'Classic';
    
    //     $theme_info = [
    //         'name'    => $theme->get('Name'),
    //         'version' => $theme->get('Version'),
    //         'type'    => $theme_type
    //     ];
    
    //     wpsi_render_tab_content(
    //         'theme',
    //         'Theme Info',
    //         ['Property', 'Value'],
    //         [
    //             ['Active Theme', esc_html($theme_info['name']) . ' v' . esc_html($theme_info['version'])],
    //             ['Theme Type', esc_html($theme_info['type'])]
    //         ]
    //     );
    
    //     return $theme_info; // Optional return if needed elsewhere
    // }

    private function analyze_builders() {
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

    private function analyze_plugins() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all_plugins = get_plugins();
        $update_plugins = get_site_transient('update_plugins');
        $plugins = [];
        
        foreach ($all_plugins as $slug => $info) {
            $has_update = isset($update_plugins->response[$slug]);
            $plugin_path = WP_PLUGIN_DIR . '/' . $slug;
            $install_time = file_exists($plugin_path) ? date('Y-m-d H:i:s', filectime($plugin_path)) : __('N/A', 'wp-site-inspector');
            $update_time = file_exists($plugin_path) ? date('Y-m-d H:i:s', filemtime($plugin_path)) : __('N/A', 'wp-site-inspector');
            
            $plugins[] = [
                'name' => $info['Name'],
                'status' => is_plugin_active($slug) ? __('Active', 'wp-site-inspector') : __('Inactive', 'wp-site-inspector'),
                'update' => $has_update ? __('Update available', 'wp-site-inspector') : __('Up to date', 'wp-site-inspector'),
                'installed_on' => $install_time,
                'last_update' => $update_time,
            ];
        }
        
        return $plugins;
    }

    private function analyze_pages() {
        $pages = [];
        foreach (get_pages(['post_status' => ['publish', 'draft']]) as $page) {
            $formatted_date = $page->post_status === 'publish' 
                ? date('m/d/y, h:ia', strtotime($page->post_date)) 
                : __('Not Published', 'wp-site-inspector');
            
            $pages[] = [
                'title' => $page->post_title,
                'status' => ucfirst(__($page->post_status, 'wp-site-inspector')),
                'date' => $formatted_date
            ];
        }
        return $pages;
    }

    private function analyze_posts() {
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

    private function analyze_post_types() {
        $post_types = [];
        foreach (get_post_types([], 'objects') as $post_type => $obj) {
            $file = $obj->_builtin ? 'Built in' : (!empty($obj->description) && stripos($obj->description, 'plugin') !== false ? 'Plugin (guessed)' : 'functions.php or plugin');
            $count = wp_count_posts($post_type);
            $published = isset($count->publish) ? $count->publish : 0;

            $last = get_posts([
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                'posts_per_page' => 1,
                'fields'         => 'ids'
            ]);
            $last_used = !empty($last) ? get_the_date('Y-m-d H:i:s', $last[0]) : '—';
            $label = isset($obj->label) ? $obj->label : (isset($obj->labels->name) ? $obj->labels->name : ucfirst($post_type));
            error_log("Post type '{$post_type}' label: {$label}");
            $post_types[$post_type] = [
                'post_type'  => $post_type,
                'label'      => $label,
                'file'       => $file,
                'used_count' => $published,
                'last_used'  => $last_used,
            ];
        }
        return $post_types;
    }

    private function analyze_templates() {
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

    private function analyze_shortcodes() {
        global $wpdb;
        $shortcodes = [];
    
        // 1. Find all shortcodes in theme/plugin files
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
                        if (!isset($shortcodes[$tag])) {
                            $shortcodes[$tag] = [
                                'name'     => $tag,
                                'file'     => $relative,
                                // 'line'     => $i + 1,
                                'used_in'  => [],
                            ];
                        }
                    }
                }
            }
        }
    
        // 2. Search posts/pages to find where these shortcodes are used
        $contents = $wpdb->get_results("
            SELECT post_title, post_content 
            FROM {$wpdb->posts} 
            WHERE post_status IN ('publish', 'draft')
        ", ARRAY_A);
    
        foreach ($contents as $entry) {
            foreach ($shortcodes as $tag => &$info) {
                if (strpos($entry['post_content'], "[$tag") !== false) {
                    $info['used_in'][] = $entry['post_title'];
                }
            }
        }
    
        return $shortcodes;
    }
    

    // private function analyze_shortcodes() {
    //     global $wpdb;
    
    //     $shortcodes = [];
    
    //     // 1. Find all shortcodes in PHP/JS files
    //     $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
    //     $files = iterator_to_array($rii);
    
    //     foreach ($files as $file) {
    //         if ($file->isDir()) continue;
    //         $ext = pathinfo($file, PATHINFO_EXTENSION);
    //         if (!in_array($ext, ['php', 'js'])) continue;
    
    //         $path = $file->getPathname();
    //         $relative = str_replace(ABSPATH, '', $path);
    //         $lines = @file($path); // @ to suppress warnings if unreadable
    
    //         if ($lines === false) continue;
    
    //         foreach ($lines as $i => $line) {
    //             if (preg_match_all('/add_shortcode\s*\(\s*[\'\"]([^\'"]+)[\'\"]/', $line, $m)) {
    //                 foreach ($m[1] as $tag) {
    //                     if (!isset($shortcodes[$tag])) {
    //                         $shortcodes[$tag] = [
    //                             'shortcodes' => esc_html('[' . $tag . ']'),
    //                             'file'     => $relative,
    //                             'line'     => $i + 1,
    //                             'used_in'  => [],
    //                         ];
    //                     }
    //                 }
    //             }
    //         }
    //     }
    
    //     // 2. Query post content safely
    //     $query = $wpdb->prepare(
    //         "SELECT post_title, post_content FROM {$wpdb->posts} WHERE post_status IN (%s, %s)",
    //         'publish',
    //         'draft'
    //     );
    //     $contents = $wpdb->get_results($query, ARRAY_A);
    
    //     // 3. Search for shortcode usage in post content
    //     foreach ($contents as $entry) {
    //         foreach ($shortcodes as $tag => &$info) {
    //             // Simple strpos is okay since you're not evaluating or executing input
    //             if (strpos($entry['post_content'], "[$tag") !== false) {
    //                 $info['used_in'][] = $entry['post_title'];
    //             }
    //         }
    //     }
    
    //     return $shortcodes;
    // }
    

    private function analyze_hooks() {
        $hooks = [];
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
        $files = iterator_to_array($rii);
        foreach ($files as $file) {
            if ($file->isDir()) continue;
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, ['php', 'js'])) continue;

            $path = $file->getPathname();
            $relative = str_replace(ABSPATH, '', $path);
            $lines = file($path);

            foreach ($lines as $i => $line) {
                if (strpos($relative, '/themes/' . wp_get_theme()->get_stylesheet() . '/') !== false) {
                    if (preg_match_all('/add_(action|filter)\s*\(\s*[\'\"]([^\'"]+)[\'\"]/', $line, $m, PREG_SET_ORDER)) {
                        foreach ($m as $match) {
                            $hooks[] = [
                                'type' => ucfirst($match[1]),
                                'hook' => $match[2],
                                'file' => $relative . ' (line ' . ($i + 1) . ')'
                            ];
                        }
                    }
                }
            }
        }
        return $hooks;
    }

    // private function analyze_apis() {
    //     $rest_apis = [];
    //     $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
    //     $files = iterator_to_array($rii);
    //     foreach ($files as $file) {
    //         if ($file->isDir()) continue;
    //         $ext = pathinfo($file, PATHINFO_EXTENSION);
    //         if (!in_array($ext, ['php', 'js'])) continue;

    //         $path = $file->getPathname();
    //         $relative = str_replace(ABSPATH, '', $path);
    //         $lines = file($path);

    //         foreach ($lines as $i => $line) {
    //             if (strpos($line, 'register_rest_route') !== false) {
    //                 if (preg_match("/register_rest_route\s*\(\s*['\"]([^'\"]+)['\"],\s*['\"]([^'\"]+)['\"]/", $line, $match)) {
    //                     $namespace = $match[1];
    //                     $route = $match[2];
    //                     $rest_apis["$namespace$route"] = [
    //                         'namespace' => $namespace,
    //                         'route' => $route,
    //                         'file' => $relative,
    //                         'line' => $i + 1,
    //                         'used_in' => []
    //                     ];
    //                 }
    //             }
    //         }
    //     }
    //     return $rest_apis;
    // }


    private function analyze_apis() {
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

    private function analyze_cdn() {
        $cdn_links = [];
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
        $files = iterator_to_array($rii);
        $theme = wp_get_theme();
        $cdn_patterns = ['swiper', 'jquery', 'bootstrap', 'fontawesome', 'gsap', 'chart.js', 'lodash', 'moment', 'anime', 'three'];

        foreach ($files as $file) {
            if ($file->isDir()) continue;
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, ['php', 'js'])) continue;

            $path = $file->getPathname();
            $relative = str_replace(ABSPATH, '', $path);
            $lines = file($path);

            foreach ($lines as $i => $line) {
                foreach ($cdn_patterns as $lib) {
                    if (stripos($line, $lib) !== false && strpos($relative, '/themes/' . $theme->get_stylesheet() . '/') !== false) {
                        $cdn_links[] = [
                            'lib' => $lib,
                            'file' => $relative,
                            'url' => ''
                        ];
                    }
                }
            }
        }
        return $cdn_links;
    }

    private function analyze_logs() {
        $log = "Analysis performed at " . current_time('mysql') . " by user ID: " . get_current_user_id();
        $log .= "\nTheme: " . $this->analyze_theme()['name'] . " (" . $this->analyze_theme()['version'] . ")";
        $log .= "\nPlugins: " . count($this->analyze_plugins()) . " installed";
        $log .= "\nPages: " . count($this->analyze_pages()) . ", Posts: " . count($this->analyze_posts());
        $log .= "\nTemplates: " . count($this->analyze_templates()) . ", Shortcodes: " . count($this->analyze_shortcodes());

        // Example of logging an error (in real usage, catch these from WordPress/PHP)
        $log .= "\n[ERROR] " . date('Y-m-d H:i:s') . " - Failed to load template: single-post.php";
        $log .= "\n[WARNING] " . date('Y-m-d H:i:s') . " - Undefined variable in footer.php";

        file_put_contents(WP_CONTENT_DIR . '/site-inspector.log', $log . "\n\n", FILE_APPEND);
        return true;
    }
}
