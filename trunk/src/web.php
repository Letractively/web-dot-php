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
function get($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'GET') ? route($path, $func) : false; }
function post($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'POST') ? route($path, $func) : false; }
function put($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'PUT' || (isset($_POST['_method']) && strcasecmp($_POST['_method'], 'PUT') == 0)) ? route($path, $func) : false; }
function delete($path, $func = null) { return ($_SERVER['REQUEST_METHOD'] == 'DELETE' || (isset($_POST['_method']) && strcasecmp($_POST['_method'], 'DELETE') == 0)) ? route($path, $func) : false; }
function route($path, $func = null) {
    static $matched = false;
    if ($matched) return false;
    $matched = $func == null;
    if ($matched) return run($path);
    $subject = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9))), '/');
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
    if (is_callable($ctrl)) {
        return $ctrl($params);
    }
    trigger_error("Invalid function or method $func.", E_USER_WARNING);
}
function redirect($url = null, $code = 302) {
    if (headers_sent($file, $line)) return trigger_error("Headers already sent in $file on line $line.", E_USER_ERROR);
    if ($code == 301) header($_SERVER['SERVER_PROTOCOL'] ?: 'HTTP/1.0' . ' 301 Moved Permanently');
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
function session_ensure($regenerate = false) {
    static $started;
    if ($started) return;
    if (headers_sent($file, $line) && (!defined('SID') || $regenerate)) return trigger_error("Headers already sent in $file on line $line.", E_USER_ERROR);
    if (!defined('SID')) session_start();
    if (isset($_SESSION['web.php:session'])) {
        if ($_SESSION['web.php:session'] !== crc32($_SERVER['HTTP_USER_AGENT'])) {
            return trigger_error('Possible Session Hijacking Attempt.', E_USER_ERROR);
        }
    } else {
        $_SESSION['web.php:session'] = crc32($_SERVER['HTTP_USER_AGENT']);
    }
    if ($regenerate) session_regenerate_id(true);
}
function flash($name, $value, $hops = 1) {
    session_ensure();
    $_SESSION[$name] = $value;
    $_SESSION["web.php:flash:$name"] = $hops;
}
function block($name, $operation = 'render') {
    static $block;
    static $blocks = array();
    if ($name !== null) $block = $name;
    switch ($operation) {
        case 'start': ob_start(); break;
        case 'end': $blocks[$block] = ob_get_clean(); break;
        case 'render': echo $blocks[$block];
    }
}
function block_start($name) {
    block($name, 'start');
}
function block_end() {
    block(null, 'end');
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
class form extends ArrayObject {
    private $filters;
    private $value;
    function __construct($args = null) {
        if ($args == null) return;
        foreach ($args as $name => $value) {
            if (is_array($value) && !isset($value[0][0])) {
                $this[$name] = new self($value);
            } else {
                $this[$name] = new self;
                $this[$name]->value = $value;
            }
        }
    }
    function __set($name, $value) {
        if ($name == 'filters') {
            $this->filters =  is_string($value)? explode(',', $value) : (array) $value;
            return;
        }
        if (!isset($this[$name])) $this[$name] = new self;
        $this[$name]['value'] = $value;
    }
    function __get($name) {
        if (!isset($this[$name])) $this[$name] = new self;
        $field = $this[$name];
        return $field;
    }
    function __call($name, $args) {
        if (!isset($args[0])) return print $this->value ?: '';
        return print $this->value ?: $args[0];
    }
    function __toString() {
        return $this->value ?: '';
    }
    function validate() {
        if ($this->filters == null) return true;
        $validates = true;
        foreach ($this->filters as $filter) {
            switch (trim($filter)) {
                // filters
                case 'trim':  $this->value = trim($this->value); break;
                case 'ltrim': $this->value = ltrim($this->value); break;
                case 'rtrim':
                case 'chop':  $this->value = rtrim($this->value); break;
                // validators
                case 'req':   $validates = strlen($this->value) > 0; break;
                case 'bool':  $validates = false !== filter_var($this->value, FILTER_VALIDATE_BOOLEAN); break;
                case 'int':   $validates = false !== filter_var($this->value, FILTER_VALIDATE_INT); break;
                case 'float': $validates = false !== filter_var($this->value, FILTER_VALIDATE_FLOAT); break;
                case 'ip':    $validates = false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6); break;
                case 'ipv4':  $validates = false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4); break;
                case 'ipv6':  $validates = false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6); break;
                case 'email': $validates = false !== filter_var($this->value, FILTER_VALIDATE_EMAIL); break;
                case 'url':   $validates = false !== filter_var($this->value, FILTER_VALIDATE_URL); break;
                default:
                    // regex validator
                    if ((strpos($filter, '/')) === 0) {
                        $validates = preg_match($filter, $this->value);
                    }
                    break;
            }
            if (!$validates) return false;
        }
        return true;
    }
}
register_shutdown_function(function() {
    if (!defined('SID')) return;
    $keys = array_keys($_SESSION);
    foreach($keys as $key) {
        if (strpos($key, 'web.php:flash:') === 0) {
            if ($_SESSION[$key] === 0)
                unset($_SESSION[$key], $_SESSION[substr($key, 14)]);
            else
                $_SESSION[$key]--;
        }
    }
});