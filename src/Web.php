<?php
/**
$Id$

Class: Web

    Manages controllers' execution.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
function get($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'GET') ? route($path, $func) : false; }
function post($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'POST') ? route($path, $func) : false; }
function put($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'PUT') ? route($path, $func) : false; }
function delete($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'DELETE') ? route($path, $func) : false; }
function route($path, $func = null) {
    static $matched = false;
    if ($matched) return false;
    $matched = $func == null;
    if ($matched) return run($path);
    $subject = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9))), '/');
    $pattern = '#^' . preg_replace('/:([\w-]+)/', '(?<$1>[\w-]+)', trim($path, '/')) . '#i';
    return ($matched = (bool) preg_match($pattern, $subject, $matches)) ? run($func,  array_slice($matches, 1)) : false;
}
function run($func, $args = array()) {
    $ctrl = $func;
    if (is_string($ctrl)) {
        if (file_exists($ctrl)) return require $ctrl;
        if (strpos($ctrl, '->') !== false) {
            list($clazz, $method) = explode('->', $ctrl, 2);
            $ctrl = array(new $clazz, $method);
        }
    }
    if (is_callable($ctrl)) return call_user_func_array($ctrl, $args);
    trigger_error("Invalid function or method '" . $func .  "'.", E_USER_WARNING);
    return false;
}
function status($code) {
    if (headers_sent()) return false;
    switch ($code) {
        case 100: $msg = 'Continue'; break;
        case 200: $msg = 'OK'; break;
        case 201: $msg = 'Created'; break;
        case 101: $msg = 'Switching Protocols'; break;
        case 200: $msg = 'OK'; break;
        case 201: $msg = 'Created'; break;
        case 202: $msg = 'Accepted'; break;
        case 203: $msg = 'Non-Authoritative Information'; break;
        case 204: $msg = 'No Content'; break;
        case 205: $msg = 'Reset Content'; break;
        case 206: $msg = 'Partial Content'; break;
        case 300: $msg = 'Multiple Choices'; break;
        case 301: $msg = 'Moved Permanently'; break;
        case 302: $msg = 'Found'; break;
        case 303: $msg = 'See Other'; break;
        case 304: $msg = 'Not Modified'; break;
        case 305: $msg = 'Use Proxy'; break;
        case 306: $msg = '(Unused)'; break;
        case 307: $msg = 'Temporary Redirect'; break;
        case 400: $msg = 'Bad Request'; break;
        case 401: $msg = 'Unauthorized'; break;
        case 402: $msg = 'Payment Required'; break;
        case 403: $msg = 'Forbidden'; break;
        case 404: $msg = 'Not Found'; break;
        case 405: $msg = 'Method Not Allowed'; break;
        case 406: $msg = 'Not Acceptable'; break;
        case 407: $msg = 'Proxy Authentication Required'; break;
        case 408: $msg = 'Request Timeout'; break;
        case 409: $msg = 'Conflict'; break;
        case 410: $msg = 'Gone'; break;
        case 411: $msg = 'Length Required'; break;
        case 412: $msg = 'Precondition Failed'; break;
        case 413: $msg = 'Request Entity Too Large'; break;
        case 414: $msg = 'Request-URI Too Long'; break;
        case 415: $msg = 'Unsupported Media Type'; break;
        case 416: $msg = 'Requested Range Not Satisfiable'; break;
        case 417: $msg = 'Expectation Failed'; break;
        case 500: $msg = 'Internal Server Error'; break;
        case 501: $msg = 'Not Implemented'; break;
        case 502: $msg = 'Bad Gateway'; break;
        case 503: $msg = 'Service Unavailable'; break;
        case 504: $msg = 'Gateway Timeout'; break;
        case 505: $msg = 'HTTP Version Not Supported'; break;
        default: $msg = '';
    }
    header(trim(sprintf('%s %u &s', $_SERVER['SERVER_PROTOCOL'], $code, $msg)));
    return true;
}
function redirect($url = null, $code = 302) {
    if (headers_sent()) return false;
    while (ob_get_level()) @ob_end_clean();
    switch ($code) {
        case 301:
        case 303:
        case 305:
        case 307:
            status($code);
        default:
            header('Location: ' . url($url));
    }
    return true;
}
function url($url = null) {
    if ($url != null) extract(parse_url($url));
    if (!isset($port) && isset($scheme)) $port = $scheme == 'http' ? 80 : 443;
    if (!isset($scheme)) $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    if (!isset($host)) $host = $_SERVER['HTTP_HOST'];
    if (!isset($port)) $port = $_SERVER['SERVER_PORT'];
    if (!isset($path)) $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($path, '~') === 0) $path = substr($_SERVER['SCRIPT_NAME'], 0, -9) . ltrim(substr($path, 1), '/');
    if (strpos($path, '/') !== 0) $path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') . '/' . ltrim($path, '/');
    if (!isset($query) && $url == null) $query = $_SERVER['QUERY_STRING'];
    $url = $scheme . '://' . $host;
    if (($scheme == 'http' && $port != 80) || ($scheme == 'https' && $port != 443)) $url .= ':' . $port;
    $url .= $path;
    if (isset($query)) $url .= '?' . $query;
    if (isset($fragment)) $url .= '#' . $fragment;
    return $url;
}