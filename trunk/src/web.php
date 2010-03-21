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
namespace web {
    function init() {
        $base = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base = substr($base, 0, strrpos($base, '/')) . '/';
        $exec = trim(substr($path, strlen($base)), '/');
        $path = substr($path, 0, strrpos($path, '/')) . '/';
        $full = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $full .= $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        if (($full[4] === 's' &&  $port !== '443') || $port !== '80') $full .= ":$port";
        define('WEB_URL_ROOT', $full . '/');
        define('WEB_URL_PATH', $path);
        define('WEB_URL_BASE', $base);
        define('WEB_URL_EXEC', $exec);
        define('WEB_METHOD', isset($_POST['_method']) ? $_POST['_method'] : $_SERVER['REQUEST_METHOD']);
        register_shutdown_function(function() {
            if (!defined('SID') || !isset($_SESSION['web.php:flash'])) return;
            $flash =& $_SESSION['web.php:flash'];
            foreach($flash as $key => $hops) {
                if ($hops === 0) {
                    unset($_SESSION[$key], $flash[$key]);
                } else {
                    $flash[$key]--;
                }
            }
            if (count($flash) === 0) {
                unset($flash);
            }
        });
    }
}
namespace {
    function get($path, $func = null) { route($path, $func, 'GET'); }
    function post($path, $func = null) { route($path, $func, 'POST'); }
    function put($path, $func = null) { route($path, $func, 'PUT'); }
    function delete($path, $func = null) { route($path, $func, 'DELETE'); }
    function route($path = null, $func = null, $method = null) {
        static $routes = array();
        if ($path === null) return $routes;
        $subject = preg_quote(trim($path, '/'), '/');
        $subject = str_replace('\:', ':', $subject);
        $subject = str_replace('\|', '|', $subject);
        $subject = str_replace('\*', '(.+)', $subject);
        $pattern = sprintf('/^%s$/i', preg_replace(
            array('/:[\w-]+/', '/#[\w-]+/', '/([\w-]+(\|[\w-]+)+)/'),
            array('([\w-]+)', '(\d+)', '($1)'),
            $subject));
        $routes[] = array($pattern, $func, $method);
        unset($subject, $pattern);
    }
    function forward($url, $method = null) {
        return dispatch(trim($url, '/'), $method, true);
    }
    function dispatch(
            $url = WEB_URL_EXEC,
            $method = WEB_METHOD,
            $forwarded = false) {
        $routes = route();
        foreach($routes as $route) {
            list($pattern, $func, $type) = $route;
            if ($method !== null && $method !== $type) continue;
            if ($func === null && $forwarded) continue;
            if ($func === null) return run($pattern);
            $params = array();
            $matched = (bool) preg_match($pattern, $url, $params);
            if ($matched) return run($func, array_slice($params, 1));
        }
    }
    function run($func, array $params = array()) {
        $ctrl = $func;
        if (is_string($ctrl)) {
            if (file_exists($ctrl)) return require $ctrl;
            if (strpos($ctrl, '->') !== false) {
                list($clazz, $method) = explode('->', $ctrl, 2);
                $ctrl = array(new $clazz, $method);
            }
        }
        return call_user_func_array($ctrl, $params);
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
        header('Location: ' . url($url, true), true, $code);
        exit;
    }
    function session() {
        static $called = false;
        if ($called) return;
        $called = true;
        if (!defined('SID')) session_start();
        $session = crc32($_SERVER['HTTP_USER_AGENT']);
        if (!isset($_SESSION['web.php:session'])) {
            $_SESSION['web.php:session'] = $session;
        } elseif ($_SESSION['web.php:session'] !== $session) {
            trigger_error('Possible Session Hijacking Attempt.', E_USER_ERROR);
        }
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
    function slug($title, $delimiter = '-') {
        $title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $title = preg_replace('#[^a-z0-9/_|+\s-]#i', '', $title);
        $title = strtolower(trim($title, '/_|+ -'));
        $title = preg_replace('#[/_|+\s-]+#', $delimiter, $title);
        return $title;
    }
    function title() {
        echo htmlspecialchars(
                implode(' - ', func_get_args()), ENT_QUOTES, 'UTF-8');
    }
    function block(&$block = false) {
        if ($block === false) {
            ob_end_clean();
        } else {
            ob_start(function($buffer) use (&$block) {
                return $block = $buffer;
            });
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
    web\init();
}

