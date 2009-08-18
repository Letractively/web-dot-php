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
    $subject = trim(substr($_SERVER['SCRIPT_NAME'], 0, -9), '/');
    $pattern = '#^' . preg_replace('/:(\w+)/', '(\w+)', trim($path, '/')) . '#i';
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
    trigger_error("Invalid route '" . $func .  "'.", E_USER_WARNING);
}
function status($statuscode) {
    switch ($statuscode) {
        case 100: $statusmsg = 'Continue'; break;
        case 200: $statusmsg = 'OK'; break;
        case 201: $statusmsg = 'Created'; break;
        case 101: $statusmsg = 'Switching Protocols'; break;
        case 200: $statusmsg = 'OK'; break;
        case 201: $statusmsg = 'Created'; break;
        case 202: $statusmsg = 'Accepted'; break;
        case 203: $statusmsg = 'Non-Authoritative Information'; break;
        case 204: $statusmsg = 'No Content'; break;
        case 205: $statusmsg = 'Reset Content'; break;
        case 206: $statusmsg = 'Partial Content'; break;
        case 300: $statusmsg = 'Multiple Choices'; break;
        case 301: $statusmsg = 'Moved Permanently'; break;
        case 302: $statusmsg = 'Found'; break;
        case 303: $statusmsg = 'See Other'; break;
        case 304: $statusmsg = 'Not Modified'; break;
        case 305: $statusmsg = 'Use Proxy'; break;
        case 306: $statusmsg = '(Unused)'; break;
        case 307: $statusmsg = 'Temporary Redirect'; break;
        case 400: $statusmsg = 'Bad Request'; break;
        case 401: $statusmsg = 'Unauthorized'; break;
        case 402: $statusmsg = 'Payment Required'; break;
        case 403: $statusmsg = 'Forbidden'; break;
        case 404: $statusmsg = 'Not Found'; break;
        case 405: $statusmsg = 'Method Not Allowed'; break;
        case 406: $statusmsg = 'Not Acceptable'; break;
        case 407: $statusmsg = 'Proxy Authentication Required'; break;
        case 408: $statusmsg = 'Request Timeout'; break;
        case 409: $statusmsg = 'Conflict'; break;
        case 410: $statusmsg = 'Gone'; break;
        case 411: $statusmsg = 'Length Required'; break;
        case 412: $statusmsg = 'Precondition Failed'; break;
        case 413: $statusmsg = 'Request Entity Too Large'; break;
        case 414: $statusmsg = 'Request-URI Too Long'; break;
        case 415: $statusmsg = 'Unsupported Media Type'; break;
        case 416: $statusmsg = 'Requested Range Not Satisfiable'; break;
        case 417: $statusmsg = 'Expectation Failed'; break;
        case 500: $statusmsg = 'Internal Server Error'; break;
        case 501: $statusmsg = 'Not Implemented'; break;
        case 502: $statusmsg = 'Bad Gateway'; break;
        case 503: $statusmsg = 'Service Unavailable'; break;
        case 504: $statusmsg = 'Gateway Timeout'; break;
        case 505: $statusmsg = 'HTTP Version Not Supported'; break;
        default: $statusmsg = '';
    }
    header(trim(sprintf('%s %u &s', $_SERVER['SERVER_PROTOCOL'], $statuscode, $statusmsg)));
}
function redirect($url = null, $statuscode = 302) {
    while (ob_get_level()) @ob_end_clean();
    if ($statuscode != 302) status($statuscode);
    header('Location: ' . url($url));
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