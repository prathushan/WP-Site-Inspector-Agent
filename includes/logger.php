<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Custom error handler for logging to site-inspector.log
 */
function wpsi_custom_error_handler($errno, $errstr, $errfile, $errline)
{
    $types = match ($errno) {
        E_ERROR             => __('ERROR', 'wp-site-inspector'),
        E_WARNING           => __('WARNING', 'wp-site-inspector'),
        E_PARSE             => __('PARSE', 'wp-site-inspector'),
        E_NOTICE            => __('NOTICE', 'wp-site-inspector'),
        E_CORE_ERROR        => __('CORE_ERROR', 'wp-site-inspector'),
        E_CORE_WARNING      => __('CORE_WARNING', 'wp-site-inspector'),
        E_COMPILE_ERROR     => __('COMPILE_ERROR', 'wp-site-inspector'),
        E_COMPILE_WARNING   => __('COMPILE_WARNING', 'wp-site-inspector'),
        E_USER_ERROR        => __('USER_ERROR', 'wp-site-inspector'),
        E_USER_WARNING      => __('USER_WARNING', 'wp-site-inspector'),
        E_USER_NOTICE       => __('USER_NOTICE', 'wp-site-inspector'),
        E_STRICT            => __('STRICT', 'wp-site-inspector'),
        E_RECOVERABLE_ERROR => __('RECOVERABLE_ERROR', 'wp-site-inspector'),
        E_DEPRECATED        => __('DEPRECATED', 'wp-site-inspector'),
        E_USER_DEPRECATED   => __('USER_DEPRECATED', 'wp-site-inspector'),
        default             => __('INFO', 'wp-site-inspector'),
    };

    $timestamp = date("Y-m-d H:i:s");

    $log_format = __('[%1$s] %2$s - %3$s (File: %4$s, Line: %5$d)', 'wp-site-inspector');
    $log_line = sprintf($log_format, $types, $timestamp, $errstr, $errfile, $errline) . PHP_EOL;

    // Log to custom file
    error_log($log_line, 3, WP_CONTENT_DIR . '/site-inspector.log');

    return true; // Continue execution for non-fatal errors
}

// Register error handler
set_error_handler('wpsi_custom_error_handler');

/**
 * Handle fatal errors on shutdown
 */
function wpsi_shutdown_handler()
{
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $timestamp = date("Y-m-d H:i:s");

        $log_format = __('[FATAL] %1$s - %2$s (File: %3$s, Line: %4$d)', 'wp-site-inspector');
        $log_line = sprintf($log_format, $timestamp, $error['message'], $error['file'], $error['line']) . PHP_EOL;

        error_log($log_line, 3, WP_CONTENT_DIR . '/site-inspector.log');
    }
}

register_shutdown_function('wpsi_shutdown_handler');