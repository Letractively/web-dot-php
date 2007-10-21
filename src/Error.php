<?php

class Error {

    /* =======================================================================
     * Error Private Constructor, Prevent an Object from Being Constructed
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Error Handling Function
     * ======================================================================= */

    public static function handleError($errno, $errstr, $errfile, $errline, $context) {

        restore_error_handler();
        restore_exception_handler();

        while (ob_get_level()) @ob_end_clean();

        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');

        $error = array();

        $error['file'] = $errfile;
        $error['line'] = $errline;
        $error['number'] = $errno;
        $error['message'] = $errstr;
        $error['code'] = '';
        $error['context'] = $context;

        switch ($errno)
        {
            case E_ERROR:
                $error['type'] = 'Error';
                $error['class'] = 'error';
                break;
            case E_WARNING:
                $error['type'] = 'Warning';
                $error['class'] = 'notice';
                break;
            case E_PARSE:
                $error['type'] = 'Parsing Error';
                $error['class'] = 'error';
                break;
            case E_NOTICE:
                $error['type'] = 'Notice';
                $error['class'] = 'notice';
                break;
            case E_CORE_ERROR:
                $error['type'] = 'Core Error';
                $error['class'] = 'error';
                break;
            case E_CORE_WARNING:
                $error['type'] = 'Core Warning';
                $error['class'] = 'notice';
                break;
            case E_COMPILE_ERROR:
                $error['type'] = 'Compile Error';
                $error['class'] = 'error';
                break;
            case E_COMPILE_WARNING:
                $error['type'] = 'Compile Warning';
                $error['class'] = 'notice';
                break;
            case E_USER_ERROR:
                $error['type'] = 'User Error';
                $error['class'] = 'error';
                break;
            case E_USER_WARNING:
                $error['type'] = 'User Warning';
                $error['class'] = 'notice';
                break;
            case E_USER_NOTICE:
                $error['type'] = 'User Notice';
                $error['class'] = 'notice';
                break;
            case E_STRICT:
                $error['type'] = 'Runtime Notice';
                $error['class'] = 'notice';
                break;
            default:
                $error['type'] = 'Unknown Error';
                $error['class'] = 'error';
                break;
        }

        // TODO: Shitty Code, Cleanup Needed
        if (is_readable($errfile)) {
            $lines = file($errfile);
            $startline = (($errline - 5) < 0) ? 0 : ($errline - 5);
            $startline = ((count($lines) - $startline) < 9)
                ? $startline + 1 - (count($lines) - $startline)
                : $startline;
            $startline = ($startline < 1) ? 0 : $startline;
            $lines = array_slice($lines, $startline, 9);
            $error['startline'] = $startline + 1;

            foreach($lines as $line) {
                $error['code'] .= htmlentities(rtrim($line));
                $error['code'] .= "&nbsp;\n";
            }
        }

        $error['backtrace'] = array();

        $backtraces = array_slice(debug_backtrace(), 1);

        $j = 0;

        foreach ($backtraces as $backtrace) {

            $func = (isset($backtrace['function'])) ? $backtrace['function'] : '';
            $file = (isset($backtrace['file'])) ? $backtrace['file'] : null;
            $line = (isset($backtrace['line'])) ? $backtrace['line'] : null;
            $args = (isset($backtrace['args'])) ? $backtrace['args'] : array();
            $code = '';

            $error['backtrace'][$j]['func'] = $func;
            $error['backtrace'][$j]['line'] = $line;
            $error['backtrace'][$j]['file'] = $file;
            $error['backtrace'][$j]['args'] = $args;
            $error['backtrace'][$j]['code'] = '';

            // TODO: Shitty Code, Cleanup Needed
            if (is_readable($file)) {
                $lines = file($file);
                $startline = (($line - 5) < 0) ? 0 : ($line - 5);
                $startline = ((count($lines) - $startline) < 9)
                    ? $startline + 1 - (count($lines) - $startline)
                    : $startline;
                $startline = ($startline < 1) ? 0 : $startline;
                $lines = array_slice($lines, $startline, 9);

                $error['backtrace'][$j]['startline'] = $startline + 1;

                foreach($lines as $line) {
                    $code .= htmlentities(rtrim($line));
                    $code .= "&nbsp;\n";
                }

                $error['backtrace'][$j]['code'] = $code;
            }

            $j++;
        }

        View::render('views/error.php', $error);

        die;
    }

    /* =======================================================================
     * Exception Handling Function
     * ======================================================================= */

    public static function handleException($exception) {

        restore_error_handler();
        restore_exception_handler();

        while (ob_get_level()) @ob_end_clean();

        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');

        $error = array();

        $error['file'] = $exception->getFile();
        $error['line'] = $exception->getLine();
        $error['number'] = $exception->getCode();
        $error['message'] = $exception->getMessage();
        $error['type'] = get_class($exception);
        $error['class'] = 'error';
        $error['code'] = '';
        $error['context'] = '';

        // TODO: Shitty Code, Cleanup Needed
        if (is_readable($exception->getFile())) {
            $lines = file($exception->getFile());
            $startline = (($exception->getLine() - 5) < 0) ? 0 : ($exception->getLine() - 5);
            $startline = ((count($lines) - $startline) < 9)
                ? $startline + 1 - (count($lines) - $startline)
                : $startline;
            $startline = ($startline < 1) ? 0 : $startline;
            $lines = array_slice($lines, $startline, 9);
            $error['startline'] = $startline + 1;

            foreach($lines as $line) {
                $error['code'] .= htmlentities(rtrim($line));
                $error['code'] .= "&nbsp;\n";
            }
        }

        $error['backtrace'] = array();

        $backtraces = $exception->getTrace();

        $j = 0;

        foreach ($backtraces as $backtrace) {

            $func = (isset($backtrace['function'])) ? $backtrace['function'] : '';
            $line = (isset($backtrace['line'])) ? $backtrace['line'] : null;
            $file = (isset($backtrace['file'])) ? $backtrace['file'] : null;
            $args = (isset($backtrace['args'])) ? $backtrace['args'] : array();
            $code = '';

            $error['backtrace'][$j]['func'] = $func;
            $error['backtrace'][$j]['line'] = $line;
            $error['backtrace'][$j]['file'] = $file;
            $error['backtrace'][$j]['args'] = $args;
            $error['backtrace'][$j]['code'] = '';

            // TODO: Shitty Code, Cleanup Needed
            if (is_readable($file)) {
                $lines = file($file);
                $startline = (($line - 5) < 0) ? 0 : ($line - 5);
                $startline = ((count($lines) - $startline) < 9)
                    ? $startline + 1 - (count($lines) - $startline)
                    : $startline;
                $startline = ($startline < 1) ? 0 : $startline;
                $lines = array_slice($lines, $startline, 9);

                $error['backtrace'][$j]['startline'] = $startline + 1;

                foreach($lines as $line) {
                    $code .= htmlentities(rtrim($line));
                    $code .= "&nbsp;\n";
                }
            }

            $error['backtrace'][$j]['code'] = $code;

            $j++;
        }

        View::render('views/error.php', $error);

        die;
    }
}