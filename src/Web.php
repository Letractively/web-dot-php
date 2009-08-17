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
function redirect($url = null, $statuscode = 302) {
    while (ob_get_level()) @ob_end_clean();

    switch ($statuscode) {
        case 301: header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
    }

    header('Location: ' . url($location));
}
function url($url = null) {
    if ($url != null) extract(parse_url($url));
    if (!isset($port) && isset($scheme)) $port = $scheme == 'http' ? 80 : 443;
    if (!isset($scheme)) $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    if (!isset($host)) $host = $_SERVER['HTTP_HOST'];
    if (!isset($port)) $port = $_SERVER['SERVER_PORT'];
    if (!isset($path)) $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($path, '@') === 0) $path = substr($_SERVER['SCRIPT_NAME'], 0, -9) . substr($path, 1);
    if (strpos($path, '/') !== 0) $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . $path;
    if (!isset($query) && $url == null) $query = $_SERVER['QUERY_STRING'];
    $url = $scheme . '://' . $host;
    if (($scheme == 'http' && $port != 80) || ($scheme == 'https' && $port != 443)) $url .= ':' . $port;
    $url .= $path;
    if (isset($query)) $url .= '?' . $query;
    if (isset($fragment)) $url .= '#' . $fragment;
    return $url;
}