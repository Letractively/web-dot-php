<?php
/**
$Id$

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
*/
function __web_php_init() {
    $base = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
    $base = substr($base, 0, strrpos($base, '/')) . '/';
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = substr($path, 0, strrpos($path, '/')) . '/';
    $full = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $full .= $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];
    if (($full[4] === 's' &&  $port !== '443') || $port !== '80') $full .= ":$port";
    define('WEB_URL_ROOT', $full . '/');
    define('WEB_URL_PATH', $path);
    define('WEB_URL_BASE', $base);
    register_shutdown_function(function() {
        if (!defined('SID') || !isset($_SESSION['web.php:flash'])) return;
        $flash =& $_SESSION['web.php:flash'];
        foreach($flash as $key => $hops) {
            if ($hops === 0) unset($_SESSION[$key], $flash[$key]); else $flash[$key]--;
        }
        if (count($flash) === 0) unset($flash);
    });
}
function get($path, $func = null) { return $_SERVER['REQUEST_METHOD'] == 'GET' ? route($path, $func) : false; }
function post($path, $func = null) { return $_SERVER['REQUEST_METHOD'] == 'POST' ? route($path, $func) : false; }
function put($path, $func = null) { return $_SERVER['REQUEST_METHOD'] == 'PUT' || (isset($_POST['_method']) && strcasecmp($_POST['_method'], 'PUT') === 0) ? route($path, $func) : false; }
function delete($path, $func = null) { return $_SERVER['REQUEST_METHOD'] == 'DELETE' || (isset($_POST['_method']) && strcasecmp($_POST['_method'], 'DELETE') === 0) ? route($path, $func) : false; }
function route($path, $func = null) {
    static $matched = false;
    if ($matched) return false;
    $matched = $func == null;
    if ($matched) return run($path);
    $subject = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(WEB_URL_BASE)), '/');
    $params = array();
    if (stripos($path, 'r/') === 0) {
        $pattern = substr($path, 1);
        $matched = (bool) preg_match($pattern, $subject, $params);
        if (!$matched) return false;
    } else {
        $pattern = '/^' . preg_replace(array('/@([a-z_\d+-]+)/', '/#([a-z_\d+-]+)/'), array('(?<$1>[a-z_\d+-]+)', '(?<$1>\d+)'), preg_quote(trim($path, '/'), '/')) . '$/i';
        $matched = (bool) preg_match($pattern, $subject, $params);
        if (!$matched) return false;
    }
    return run($func, $params);
}
function run($func, $params = array()) {
    $ctrl = $func;
    if (is_string($ctrl)) {
        if (file_exists($ctrl)) return require $ctrl;
        if (strpos($ctrl, '->') !== false) {
            list($clazz, $method) = explode('->', $ctrl, 2);
            $ctrl = array(new $clazz, $method);
        }
    }
    if (is_callable($ctrl)) return $ctrl($params);
    trigger_error("Invalid function or method $func.", E_USER_WARNING);
}
function url($url, $abs = false) {
    if (parse_url($url, PHP_URL_SCHEME) !== null) return $url;
    if (strpos($url, '~/') === 0) {
        $url = WEB_URL_BASE . substr($url, 2);
    } elseif (strpos($url, '/') !== 0) {
        $url = WEB_URL_PATH . $url;
    }
    $parts = explode('/', $url);
    $url = array();
    for ($i=0; $i < count($parts); $i++) {
        if ($parts[$i] === '' || $parts[$i] === '.') continue;
        if ($parts[$i] === '..') {
            array_pop($url);
            continue;
        }
        array_push($url, $parts[$i]);
    }
    $url = implode('/', $url);
    return ($abs) ? WEB_URL_ROOT . $url : '/' . $url;
}
function redirect($url = null, $code = 301) {
    if (headers_sent($file, $line)) {
        trigger_error("Headers already sent in $file on line $line.", E_USER_ERROR);
    } else {
        header('Location: ' . url($url, true), true, $code);
    }
    die;
}
function session($regenerate = false, $delete = false) {
    if (headers_sent($file, $line) && (!defined('SID') || $regenerate)) {
        trigger_error("Headers already sent in $file on line $line.", E_USER_ERROR);
        return;
    }
    if (!defined('SID')) session_start();
    if (isset($_SESSION['web.php:session'])) {
        if ($_SESSION['web.php:session'] !== crc32($_SERVER['HTTP_USER_AGENT'])) {
            trigger_error('Possible Session Hijacking Attempt.', E_USER_ERROR);
            return;
        }
    } else {
        $_SESSION['web.php:session'] = crc32($_SERVER['HTTP_USER_AGENT']);
    }
    if ($regenerate) session_regenerate_id($delete);
}
function flash($name, $value, $hops = 1) {
    session();
    $_SESSION[$name] = $value;
    if (!isset($_SESSION['web.php:flash'])) {
        $_SESSION['web.php:flash'] = array($name => $hops);
    } else {
        $_SESSION['web.php:flash'][$name] = $hops;
    }
}
function slug($title, $replace = null, $delimiter = '-') {
	if ($replace !== null) {
            $title = strtr($title, $replace, str_repeat(' ', strlen($title)));
	}
	$title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
	$title = preg_replace('#[^a-z0-9/_|+\s-]#i', '', $title);
	$title = strtolower(trim($title, '/_|+ -'));
	$title = preg_replace('#[/_|+\s-]+#', $delimiter, $title);
	return $title;
}
function block(&$block = false) {
    if ($block === false) {
        ob_end_clean();
    } else {
        ob_start(function($buffer) use (&$block) { return $block = $buffer; });
    }
}
class view {
    function __construct($file) {
        $this->file = $file;
    }
    function __toString() {
        extract((array)$this);
        do {
            ob_start();
            require $file;
            if (!isset($layout)) return ob_get_clean();
            $view = ob_get_clean();
            $file = $layout;
            unset($layout);
        } while (true);
    }
}
__web_php_init();