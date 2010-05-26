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
    init();
    function init() {
        $base = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
        $base = substr($base, 0, strrpos($base, '/')) . '/';
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = substr($path, 0, strrpos($path, '/')) . '/';
        $full = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $full .= $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        if (($full[4] === 's' && $port !== '443') || $port !== '80') $full .= ":$port";
        define('WEB_URL_ROOT', $full . '/');
        define('WEB_URL_PATH', $path);
        define('WEB_URL_BASE', $base);
        register_shutdown_function(function() {
            if (!defined('SID') || !isset($_SESSION['web.php:flash'])) return;
            $flash =& $_SESSION['web.php:flash'];
            foreach($flash as $key => $hops) {
                if ($hops === 0)  unset($_SESSION[$key], $flash[$key]);
                else $flash[$key]--;
            }
            if (count($flash) === 0) unset($flash);
        });
    }
    function routes($path = null, $func = null) {
        static $routes = array();
        if ($path == null) return $routes;
        $pattern = sprintf('/^%s$/i', preg_replace(
            array('/\\\\\*/', '/\\\\\:[\w-]+/', '/#[\w-]+/', '/([\w-]+)(\\\\(\|[\w-]+)+)/'),
            array('(.+)', '([\w-]+)', '(\d+)', '($1$3)'),
            preg_quote(trim($path, '/'), '/')));
        $routes[] = array($pattern, $func, $path);
    }
    function params($path = null, $url = null) {
        static $params = array();
        if ($path == null && $url == null) return $params;
        $pattern = sprintf('/^%s$/i', preg_replace(
            array('/\\\\\*/', '/\\\\\:([\w-]+)/', '/#([\w-]+)/', '/([\w-]+)(\\\\(\|[\w-]+)+)/'),
            array('.+', '(?<$1>[\w-]+)', '(?<$1>\d+)', '$1$3'),
            preg_quote(trim($path, '/'), '/')));
        if ((bool) preg_match($pattern, $url, $params)) {
            $params = array_slice($params, 1);
        }
    }
    function splats($path = null, $url = null) {
        static $splats = array();
        if ($path == null && $url == null) return $splats;
        $pattern = sprintf('/^%s$/i', preg_replace(
            array('/\\\\\*/', '/\\\\\:[\w-]+/', '/#[\w-]+/', '/([\w-]+)(\\\\(\|[\w-]+)+)/'),
            array('(.+)', '[\w-]+', '\d+', '($1$3)'),
            preg_quote(trim($path, '/'), '/')));
        if ((bool) preg_match($pattern, $url, $splats)) {
            $splats = array_slice($splats, 1);
        }
    }
    function dispatch($url = null, $exit = false, $pass = false) {
        static $i = 0, $lastUrl;
        if ($url != null) $lastUrl = trim($url, '/');
        $routes = routes();
        $count = count($routes);
        for ($i = $pass ? $i + 1 : 0; $i < $count; $i++) {
            list($pattern, $func, $path) = $routes[$i];
            $params = array();
            $matched = (bool) preg_match($pattern, $lastUrl, $params);
            if ($matched) {
                splats($path, $lastUrl);
                params($path, $lastUrl);
                return run($func, array_slice($params, 1));
            }
        }
        if ($exit) exit;
    }
}
namespace {
    function get($path, $func) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') route($path, $func);
    }
    function post($path, $func) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') route($path, $func);
    }
    function put($path, $func) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' || !isset($_POST['_method'])) return;
        if (strcasecmp($_POST['_method'], 'PUT') === 0) route($path, $func);
    }
    function delete($path, $func) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' || !isset($_POST['_method'])) return;
        if (strcasecmp($_POST['_method'], 'DELETE') === 0) route($path, $func);
    }
    function route($path, $func) {
        web\routes($path, $func);
    }
    function dispatch() {
        web\dispatch(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(WEB_URL_BASE)));
    }
    function pass($url = null, $exit = true) {
        web\dispatch($url, $exit, true);
    }
    function forward($url, $exit = true) {
        web\dispatch($url, $exit);
    }
    function redirect($url, $code = 301, $exit = true) {
        header('Location: ' . url($url, true), true, $code);
        if ($exit) exit;
    }
    function splats() {
        return web\splats();
    }
    function splat($position) {
        $splats = splats();
        return $splats[$position];
    }
    function params() {
        return web\params();
    }
    function param($name) {
        $params = params();
        return $params[$name];
    }
    function run($func, array $params = array()) {
        if (is_string($func)) {
            if (file_exists($func)) return require $func;
            if (strpos($func, '->') !== false) {
                list($clazz, $method) = explode('->', $func, 2);
                $func = array(new $clazz, $method);
            }
        }
        return call_user_func_array($func, $params);
    }
    function url($url, $abs = false) {
        if (parse_url($url, PHP_URL_SCHEME) !== null) return $url;
        if (strpos($url, '~/') === 0) $url = WEB_URL_BASE . substr($url, 2);
        elseif (strpos($url, '/') !== 0) $url = WEB_URL_PATH . $url;
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
    function session() {
        static $called = false;
        if ($called) return;
        $called = true;
        if (!defined('SID')) session_start();
        $session = crc32($_SERVER['HTTP_USER_AGENT']);
        if (!isset($_SESSION['web.php:session']))
            $_SESSION['web.php:session'] = $session;
        elseif ($_SESSION['web.php:session'] !== $session)
            trigger_error('Possible Session Hijacking Attempt.', E_USER_ERROR);
    }
    function flash($name, $value, $hops = 1) {
        session();
        $_SESSION[$name] = $value;
        if (!isset($_SESSION['web.php:flash']))
            $_SESSION['web.php:flash'] = array($name => $hops);
        else
            $_SESSION['web.php:flash'][$name] = $hops;
    }
    function filter(&$value, array $filters) {
        foreach ($filters as $filter) {
            $valid = true;
            switch ($filter) {
                case 'bool':   $valid = false !== filter_var($value, FILTER_VALIDATE_BOOLEAN); break;
                case 'int':    $valid = false !== filter_var($value, FILTER_VALIDATE_INT); break;
                case 'float':  $valid = false !== filter_var($value, FILTER_VALIDATE_FLOAT); break;
                case 'ip':     $valid = false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6); break;
                case 'ipv4':   $valid = false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4); break;
                case 'ipv6':   $valid = false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6); break;
                case 'email':  $valid = false !== filter_var($value, FILTER_VALIDATE_EMAIL); break;
                case 'url':    $valid = false !== filter_var($value, FILTER_VALIDATE_URL); break;
                case 'encode': $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); break;
                default:
                    if (is_callable($filter)) {
                        $filtered = $filter($value);
                        if ($filtered !== null) {
                            if (is_bool($filtered)) {
                                $valid = $filtered;
                            } else {
                                $value = $filtered;
                            }
                        }
                    } elseif ((strpos($filter, '/')) === 0) {
                        $valid = preg_match($filter, $value);
                    } else {
                        trigger_error(sprintf('Invalid filter: %s', $filter), E_USER_WARNING);
                    }
            }
            if (!$valid) return false;
        }
        return true;
    }
    function equal($exact) {
        return function($value) use ($exact) { return $value === $exact; };
    }
    function length($min, $max) {
        return function($value) use ($min, $max) {
            $len = strlen($value);
            return $len >= $min && $len <= $max;
        };
    }
    function minlength($min) {
        return function($value) use ($min) {
            $len = strlen($value);
            return $len >= $min;
        };
    }
    function maxlength($max) {
        return function($value) use ($max) {
            $len = strlen($value);
            return $len <= $max;
        };
    }
    function between($min, $max) {
        return function($value) use ($min, $max) {
            return $value >= $min && $value <= $max;
        };
    }
    function choice() {
        $choices = func_get_args();
        return function($value) use ($choices) {
            return in_array($value, $choices);
        };
    }
    function links($value) {
		$words = explode(" ", $value);
		$content = "";
		foreach($words as $word) {
			$word = trim($word);
			switch(substr($word, 0, 7)) {
				case "http://":
				$word = "<a href='$word'>$word</a> ";
				break;
			default:
				$word .= " ";
			}
			$content .= $word;
		}
        return trim($content);
    }
    function smileys($value) {
		$words = explode(" ", $value);
		$content = "";
		foreach($words as $word) {
			$word = trim($word);
			switch(substr($word, 0, 3)) {
				case ":)":
					$word = "<img src='" . url('~/img/smileys/smile.gif') . "' alt=':)' /> ";
					break;
				case ":D":
					$word = "<img src='" . url('~/img/smileys/lol.gif') . "' alt=':D' /> ";
					break;
				case "(y)":
					$word = "<img src='" . url('~/img/smileys/peukku.gif') . "' alt='(y)' /> ";
					break;
				case "(b)":
					$word = "<img src='" . url('~/img/smileys/beer.gif') . "' alt='(b)' /> ";
					break;
				default:
					$word .= " ";
			}
			$content .= $word;
		}
        return trim($content);
    }
	
    function slug($title, $delimiter = '-') {
        $title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $title = preg_replace('#[^a-z0-9/_|+\s-]#i', '', $title);
        $title = strtolower(trim($title, '/_|+ -'));
        $title = preg_replace('#[/_|+\s-]+#', $delimiter, $title);
        return $title;
    }
    function title() {
        return htmlspecialchars(implode(' - ', func_get_args()), ENT_QUOTES, 'UTF-8');
    }
    function block(&$block = false) {
        if ($block === false) return ob_end_clean();
        ob_start(function($buffer) use (&$block) { $block = $buffer; });
    }
    class view {
        function __construct($file) { $this->file = $file; }
        function __toString() {
            extract((array)$this);
            start:
            ob_start();
            require $file;
            if (!isset($layout)) return ob_get_clean();
            $view = ob_get_clean();
            $file = $layout;
            unset($layout);
            goto start;
        }
    }
    class form {
        function __construct($args = null) {
            if ($args == null) return;
            foreach ($args as $name => $value) $this->$name = $value;
        }
        function __get($name) {
            if (!isset($this->$name)) $this->$name = new field();
            return $this->$name;
        }
        function __set($name, $field) {
            $this->$name = ($field instanceof field) ? $field : new field($field);
        }
        function __call($name, $args) {
            $field = $this->$name;
            return $field($args[0]);
        }
        function validate() {
            foreach($this as $field) if (!$field->valid) return false;
            return true;
        }
    }
    class field {
        public $original, $value, $valid;
        function  __construct($value = null) {
            $this->original = $value;
            $this->value = $value;
            $this->valid = true;
        }
        function filter() {
            return $this->valid = filter($this->value, func_get_args());
        }
        function __invoke($value) {
            $this->value = $value;
            return $this;
        }
        function __toString() {
            return strval($this->value);
        }
    }
}