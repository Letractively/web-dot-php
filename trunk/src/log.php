<?php
// Logging
namespace log {
    function debug($message) { append($message, LOG_DEBUG); }
    function info($message) { append($message, LOG_INFO); }
    function warn($message) { append($message, LOG_WARNING); }
    function error($message) { append($message, LOG_ERR); }
    function write($message, $level) { append($message, $level); }
    function level($level) {
        if ($level > LOG_INFO) return 'DEBUG';
        if ($level > LOG_WARNING) return 'INFO';
        return $level > LOG_ERR ? 'WARNING' : 'ERROR';
    }
    function append($message, $level) {
        defined('LOG_PATH')  or define('LOG_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/data');
        defined('LOG_LEVEL') or define('LOG_LEVEL', LOG_DEBUG);
        defined('LOG_FILE')  or define('LOG_FILE', 'Y-m-d.\l\o\g');
        if (LOG_LEVEL < $level) return;
        static $messages = null;
        if ($messages == null) {
            register_shutdown_function(function() use (&$messages) {
                file_put_contents(rtrim(LOG_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date_create()->format(LOG_FILE), $messages, FILE_APPEND | LOCK_EX);
            });
        }
        $trace = debug_backtrace();
        list($usec, $sec) = explode(' ', microtime());
        $messages .= sprintf('%s %7s %-20s %s', date('Y-m-d H:i:s.', $sec) . substr($usec, 2, 3) , level($level), basename($trace[1]['file']) . ':' . $trace[1]['line'], trim($message) . PHP_EOL);
    }
}