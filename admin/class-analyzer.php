<?php
if (!defined('ABSPATH')) exit;

class WP_Site_Inspector_Analyzer {
    public function analyze() {
        $dir = ABSPATH;
        $theme = wp_get_theme();
        $theme_dir = get_theme_root() . '/' . $theme->get_stylesheet();
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        $theme_info = [
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'type' => file_exists($theme_dir . '/theme.json') ? 'Block (FSE)' : 'Classic'
        ];

        // === Plugins ===
        $all_plugins = get_plugins();
        $plugins = [];
        foreach ($all_plugins as $slug => $info) {
            $plugin_path = WP_PLUGIN_DIR . '/' . $slug;
            $install_time = file_exists($plugin_path) ? gmdate('Y-m-d H:i:s', filectime($plugin_path)) : 'N/A';
            $update_time  = file_exists($plugin_path) ? gmdate('Y-m-d H:i:s', filemtime($plugin_path)) : 'N/A';
        
            $plugins[] = [
                'name' => $info['Name'],
                'status' => is_plugin_active($slug) ? 'Active' : 'Inactive',
                'update'       => isset($update_plugins->response[$slug]) ? 'Update available' : 'Up to date',
                'installed_on' => $install_time,
                'last_update'  => $update_time,
            ];
        }

        // === Theme Builders ===
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
                    'name' => $label,
                    'status' => is_plugin_active($slug) ? 'Active' : 'Inactive'
                ];
            }
        }

        // === Pages ===
        $pages = [];
        foreach (get_pages(['post_status' => ['publish', 'draft']]) as $page) {
            $pages[] = [
                'title' => $page->post_title,
                'status' => ucfirst($page->post_status),
                'date'   => $page->post_status === 'publish'
                ? gmdate('Y-m-d H:i:s', strtotime($page->post_date))
                : 'Not Published',
            ];
        }

        // === Posts ===
        $posts = [];
        foreach (get_posts(['numberposts' => -1, 'post_status' => ['publish', 'draft', 'pending']]) as $post) {
            $posts[] = [
                'title' => $post->post_title,
                'status' => ucfirst($post->post_status),
                'date'   => $post->post_status === 'publish'
                    ? gmdate('Y-m-d H:i:s', strtotime($post->post_date))
                    : 'Not Published',
            ];
        }
        // === Post Types ===
    $post_types = [];
    foreach (get_post_types([], 'objects') as $post_type => $obj) {
        // Try to guess registration file from _builtin and description
        $file = 'Built in';
        if (!$obj->_builtin) {
            if (!empty($obj->description) && stripos($obj->description, 'plugin') !== false) {
                $file = 'Plugin (guessed)';
            } else {
                $file = 'functions.php or plugin';
            }
        }

    // Get published post count
    $count = wp_count_posts($post_type);
    $published = isset($count->publish) ? $count->publish : 0;

    // Get last post date (published)
    $last = get_posts([
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'posts_per_page' => 1,
        'fields'         => 'ids'
    ]);

    $last_used = !empty($last)
    ? gmdate('Y-m-d H:i:s', strtotime(get_post_field('post_date', $last[0])))
    : 'N/A';

    $post_types[$post_type] = [
        'label'      => $obj->label,
        'file'       => $file,
        'used_count' => $published,
        'last_used'  => $last_used,
    ];
}

        // === Templates ===
        $templates = [];
        foreach ($rii as $file) {
            if ($file->isDir() || pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
            $path = $file->getPathname();
            $relative = str_replace($dir, '', $path);
            $base = basename($path);
             // Only scan inside themes
             if (strpos($relative, '/themes/') === false) continue;

            if (preg_match('/^(page|single|archive|category|tag|index|home|404|search|author|taxonomy).*\.php$/', $base)) {
                $templates[] = ['title' => $base, 'path' => $relative];
            } elseif (preg_match('/Template Name\s*:\s*(.+)/i', file_get_contents($path), $match)) {
                $templates[] = ['title' => $match[1], 'path' => $relative];
            }
        }

        // === Shortcodes, Hooks, REST APIs, CDN ===
        $shortcodes = [];
        $hooks = [];
        $rest_apis = [];
        $cdn_links = [];
        $cdn_patterns = ['swiper','jquery','bootstrap','fontawesome','gsap','chart.js','lodash','moment','anime','three'];

        $files = iterator_to_array($rii);
        foreach ($files as $file) {
            if ($file->isDir()) continue;
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, ['php', 'js'])) continue;

            $path = $file->getPathname();
            $relative = str_replace($dir, '', $path);
            $lines = file($path);

            foreach ($lines as $i => $line) {
                // Shortcodes
                if (preg_match_all('/add_shortcode\s*\(\s*[\'\"]([^\'"]+)[\'\"]/', $line, $m)) {
                    foreach ($m[1] as $tag) {
                        if (!isset($shortcodes[$tag])) {
                            $shortcodes[$tag] = ['file' => $relative, 'line' => $i + 1, 'used_in' => []];
                        }
                    }
                }

                // Hooks
                if (strpos($relative, '/themes/' . $theme->get_stylesheet() . '/') !== false) {
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

                // REST APIs
                if (strpos($line, 'register_rest_route') !== false) {
                    if (preg_match("/register_rest_route\s*\(\s*['\"]([^'\"]+)['\"],\s*['\"]([^'\"]+)['\"]/", $line, $match)) {
                        $namespace = $match[1];
                        $route = $match[2];
                        $rest_apis["$namespace$route"] = [
                            'namespace' => $namespace,
                            'route' => $route,
                            'file' => $relative,
                            'line' => $i + 1,
                            'used_in' => []
                        ];
                    }
                }

                // CDN/JS
                foreach ($cdn_patterns as $lib) {
                    if (stripos($line, $lib) !== false && strpos($relative, '/themes/' . $theme->get_stylesheet() . '/') !== false)
                        $cdn_links[] = [
                            'lib' => $lib,
                            'file' => $relative,
                            'url' => ''
                        ];
                    }
                }
            }
        }

        // === Cross-check for shortcode & API usage ===
        global $wpdb;
        $contents = $wpdb->get_results("SELECT post_title, post_content FROM {$wpdb->posts} WHERE post_status IN ('publish', 'draft')", ARRAY_A);
        foreach ($contents as $entry) {
            foreach ($shortcodes as $tag => &$info) {
                if (strpos($entry['post_content'], "[$tag") !== false) {
                    $info['used_in'][] = $entry['post_title'];
                }
            }
            foreach ($rest_apis as &$api) {
                if (strpos($entry['post_content'], $api['route']) !== false) {
                    $api['used_in'][] = $entry['post_title'];
                }
            }
        }

        return [
            'theme' => $theme_info,
            'builders' => $builders,
            'plugins' => $plugins,
            'pages' => $pages,
            'post_types' => $post_types,
            'posts' => $posts,
            'templates' => $templates,
            'shortcodes' => $shortcodes,
            'hooks' => $hooks,
            'apis' => $rest_apis,
            'cdn_links' => $cdn_links,
        ];
    }
}
