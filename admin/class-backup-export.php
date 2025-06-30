<?php
if (!defined('ABSPATH')) exit;

class WPSI_Backup_Export
{

    public static function init()
    {
        add_action('admin_post_wpsi_export_backup', [__CLASS__, 'handle_backup']);
    }

    public static function handle_backup()
    {
        // Verify permissions and nonce
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('wpsi_backup_export');

        // Setup environment for large backups
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');
        @ignore_user_abort(true);

        // Clear any existing output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Create backup directory if needed
        $backup_dir = WP_CONTENT_DIR . '/wpsi-backups/';
        if (!file_exists($backup_dir)) {
            if (!wp_mkdir_p($backup_dir)) {
                wp_die(__('Could not create backup directory.'));
            }
        }

        $timestamp = current_time('Ymd-His');
        $zip_file = $backup_dir . 'full-backup-' . $timestamp . '.zip';
        $temp_dir = $backup_dir . 'temp-' . $timestamp . '/';

        // Create temporary directory for database dump
        if (!wp_mkdir_p($temp_dir)) {
            wp_die(__('Could not create temporary directory.'));
        }

        // Step 1: Export database to temporary file
        $db_file = $temp_dir . 'database.sql';
        if (!self::export_database($db_file)) {
            self::cleanup_temp($temp_dir);
            wp_die(__('Database export failed.'));
        }

        // Step 2: Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            self::cleanup_temp($temp_dir);
            wp_die(__('Could not create ZIP archive.'));
        }

        // Add database to ZIP
        $zip->addFile($db_file, 'database.sql');

        // Add files to ZIP
        $root_path = rtrim(ABSPATH, '/') . '/';
        $excluded = ['wpsi-backups', '.git', 'node_modules', 'vendor'];

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            // Skip excluded directories
            foreach ($excluded as $exclude) {
                if (strpos($file->getPathname(), $exclude) !== false) {
                    continue 2;
                }
            }

            $file_path = $file->getRealPath();
            $relative_path = substr($file_path, strlen($root_path));

            if ($file->isDir()) {
                $zip->addEmptyDir($relative_path);
            } else {
                $zip->addFile($file_path, $relative_path);
            }
        }

        $zip->close();
        self::cleanup_temp($temp_dir);

        // Step 3: Stream the file to browser
        if (!file_exists($zip_file)) {
            wp_die(__('Backup file creation failed.'));
        }

        // Set proper headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zip_file) . '"');
        header('Content-Length: ' . filesize($zip_file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // Clear any remaining output
        flush();

        // Stream the file
        $chunk_size = 1024 * 1024; // 1MB chunks
        $handle = fopen($zip_file, 'rb');

        if ($handle === false) {
            wp_die(__('Could not open backup file for reading.'));
        }

        while (!feof($handle)) {
            echo fread($handle, $chunk_size);
            flush();
        }

        fclose($handle);

        // Clean up
        unlink($zip_file);
        exit;
    }

    private static function export_database($file_path)
    {
        global $wpdb;

        $handle = fopen($file_path, 'w');
        if (!$handle) return false;

        fwrite($handle, "-- WordPress Database Backup\n");
        fwrite($handle, "-- Generated: " . current_time('mysql') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

        $tables = $wpdb->get_col("SHOW TABLES");

        foreach ($tables as $table) {
            // Skip tables not from this WordPress installation
            if (strpos($table, $wpdb->prefix) !== 0) continue;

            // Get table structure
            $create = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($handle, $create[1] . ";\n\n");

            // Get table data in chunks
            $offset = 0;
            $chunk_size = 500;

            while (true) {
                $rows = $wpdb->get_results("SELECT * FROM `$table` LIMIT $offset, $chunk_size", ARRAY_A);
                if (empty($rows)) break;

                $offset += $chunk_size;

                $columns = array_keys($rows[0]);
                fwrite($handle, "INSERT INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES\n");

                $inserts = array();
                foreach ($rows as $row) {
                    $values = array_map(function ($v) use ($wpdb) {
                        if (is_null($v)) return "NULL";
                        return "'" . $wpdb->_real_escape($v) . "'";
                    }, array_values($row));
                    $inserts[] = '(' . implode(',', $values) . ')';
                }

                fwrite($handle, implode(",\n", $inserts) . ";\n\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($handle);
        return true;
    }

    private static function cleanup_temp($dir)
    {
        if (!file_exists($dir)) return;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }
}

WPSI_Backup_Export::init();
