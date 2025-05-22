
<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Custom error handler for logging to site-inspector.log
 */
function wpsi_custom_error_handler($errno, $errstr, $errfile, $errline) {
    $types = match ($errno) {
         E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
        default                     => 'INFO',
    };

    $timestamp = date("Y-m-d H:i:s");
    $log_line = "[$types] $timestamp - $errstr (File: $errfile, Line: $errline)" . PHP_EOL;

    // Log to custom file
    error_log($log_line, 3, WP_CONTENT_DIR . '/site-inspector.log');

    return true; // Continue execution for non-fatal errors
}

// Register error handler
set_error_handler('wpsi_custom_error_handler');

/**
 * Handle fatal errors on shutdown
 */
function wpsi_shutdown_handler() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $timestamp = date("Y-m-d H:i:s");
        $log_line = "[FATAL] $timestamp - {$error['message']} (File: {$error['file']}, Line: {$error['line']})" . PHP_EOL;
        error_log($log_line, 3, WP_CONTENT_DIR . '/site-inspector.log');
    }
}

register_shutdown_function('wpsi_shutdown_handler');