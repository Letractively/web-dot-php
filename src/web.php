<?php
namespace web {
    // Initialization:
    $base = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
    $base = substr($base, 0, strrpos($base, '/') + 1);
    $path = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen($base)), '/');
    $root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    $port = $_SERVER['SERVER_PORT'];
    if (($root[4] === 's' && $port !== '443') || $port !== '80') $root .= ":$port";
    define('WEB_URL_ROOT', $root);
    define('WEB_URL_BASE', $base);
    define('WEB_URL_PATH', $path);
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
        $pattern = '/^' . str_replace(array('@', '#'), array('([^\/]+)' ,'(\d+)'), preg_quote(trim($path, '/'), '/')) . '$/ui';
        $params = array();
        $matched = (bool) preg_match($pattern, WEB_URL_PATH, $params);
        if (!$matched) return;
        array_pop($params);
        if (is_string($func)) {
            if (file_exists($func)) return require $func;
            if (strpos($func, '->') !== false) {
                list($clazz, $method) = explode('->', $func, 2);
                $func = array(new $clazz, $method);
            }
        }
        call_user_func_array($func, $params);
        exit(0);
    }
    function status($code) {
        switch ($code) {
            // Informational
            case 100: $msg = 'Continue'; break;
            case 101: $msg = 'Switching Protocols'; break;
            // Successfull
            case 200: $msg = 'OK'; break;
            case 201: $msg = 'Created'; break;
            case 202: $msg = 'Accepted'; break;
            case 203: $msg = 'Non-Authoritative Information'; break;
            case 204: $msg = 'No Content'; break;
            case 205: $msg = 'Reset Content'; break;
            case 206: $msg = 'Partial Content'; break;
            // Redirection
            case 300: $msg = 'Multiple Choices'; break;
            case 301: $msg = 'Moved Permanently'; break;
            case 302: $msg = 'Found'; break;
            case 303: $msg = 'See Other'; break;
            case 304: $msg = 'Not Modified'; break;
            case 305: $msg = 'Use Proxy'; break;
            case 306: $msg = '(Unused)'; break;
            case 307: $msg = 'Temporary Redirect'; break;
            // Client Error
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
            // Server Error
            case 500: $msg = 'Internal Server Error'; break;
            case 501: $msg = 'Not Implemented'; break;
            case 502: $msg = 'Bad Gateway'; break;
            case 503: $msg = 'Service Unavailable'; break;
            case 504: $msg = 'Gateway Timeout'; break;
            case 505: $msg = 'HTTP Version Not Supported'; break;
            default: return;
        }
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        header(trim(sprintf('%s %u %s', $protocol, $code, $msg)));
    }
    function redirect($url, $code = 301, $exit = true) {
        header('Location: ' . url($url, true), true, $code);
        if ($exit) exit;
    }
    function url($url, $abs = false) {
        if (parse_url($url, PHP_URL_SCHEME) !== null) return $url;
        if (strpos($url, '~/') === 0) $url = WEB_URL_BASE . '/' . substr($url, 2);
        elseif (strpos($url, '/') !== 0) $url = WEB_URL_PATH . '/' . $url;
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
        return ($abs) ? WEB_URL_ROOT . '/'. $url : '/' . $url;
    }
    function flash($name, $value, $hops = 1) {
        $_SESSION[$name] = $value;
        if (!isset($_SESSION['web.php:flash']))
            $_SESSION['web.php:flash'] = array($name => $hops);
        else
            $_SESSION['web.php:flash'][$name] = $hops;
    }
    // View:
    class view {
        static $globals = array();
        static function __callStatic($name, $args) {
            switch (count($args)) {
                case 0:  unset(self::$globals[$name]); break;
                case 1:  self::$globals[$name] = $args[0]; break;
                default: self::$globals[$name] = $args;
            }
        }
        function __construct($file) { $this->file = $file; }
        function __toString() {
            extract(self::$globals);
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
    function block(&$block = false) {
        if ($block === false) return ob_end_clean();
        ob_start(function($buffer) use (&$block) { $block = $buffer; });
    }
    function fragments($file) {
        $doc = new DOMDocument();
        $doc->loadHTMLFile($file);
        $xpath = new DOMXpath($doc);
        $elements = $xpath->query('//*[@fragment]');
        $fragments = array();
        foreach ($elements as $fragment) {
            $name = $fragment->getAttribute('fragment');
            $fragment->removeAttribute('fragment');
            $fragments[$name] = simplexml_import_dom($fragment)->asXML();;
        }

        return $fragments;
    }
    // Filters:
    function filter(&$value, array $filters) {
        foreach ($filters as $filter) {
            $valid = true;
            switch ($filter) {
                case 'bool':  $valid = false !== filter_var($value, FILTER_VALIDATE_BOOLEAN); break;
                case 'int':   $valid = false !== filter_var($value, FILTER_VALIDATE_INT); break;
                case 'float': $valid = false !== filter_var($value, FILTER_VALIDATE_FLOAT); break;
                case 'ip':    $valid = false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6); break;
                case 'ipv4':  $valid = false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4); break;
                case 'ipv6':  $valid = false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6); break;
                case 'email': $valid = false !== filter_var($value, FILTER_VALIDATE_EMAIL); break;
                case 'url':   $valid = false !== filter_var($value, FILTER_VALIDATE_URL); break;
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
    function notequal($exact) {
        return function($value) use ($exact) { return $value !== $exact; };
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
    function specialchars($quote = ENT_NOQUOTES, $charset = 'UTF-8', $double = true) {
        return function($value) use ($quote, $charset, $double) {
            return htmlspecialchars($value, $quote, $charset, $double);
        };
    }
    function slug($title, $delimiter = '-') {
        $title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $title = preg_replace('#[^a-z0-9/_|+\s-]#i', '', $title);
        $title = strtolower(trim($title, '/_|+ -'));
        $title = preg_replace('#[/_|+\s-]+#', $delimiter, $title);
        return $title;
    }
    // Form:
    class form {
        function __construct($args = null) {
            if ($args == null) return;
            foreach ($args as $name => $value) $this->$name = $value;
        }
        function __get($name) {
            if (!isset($this->$name)) $this->$name = new field;
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
// Logging (this needs to be refactored and moved elsewhere):
namespace log {
    function debug($message) {
        if (!defined('LOG_LEVEL') || !defined('LOG_PATH') || LOG_LEVEL < LOG_DEBUG) return;
        $date = date_create();
        $file = rtrim(LOG_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date_format($date, 'Y-m-d') . '.log';
        error_log(sprintf('%s %-9s %s', date_format($date, 'Y-m-d H:i:s'), '[DEBUG]', trim($message) . PHP_EOL), 3, $file);
    }
    function warn($message) {
        if (!defined('LOG_LEVEL') || !defined('LOG_PATH') || LOG_LEVEL < LOG_WARNING) return;
        $date = date_create();
        $file = rtrim(LOG_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date_format($date, 'Y-m-d') . '.log';
        error_log(sprintf('%s %-9s %s', date_format($date, 'Y-m-d H:i:s'), '[WARNING]', trim($message) . PHP_EOL), 3, $file);
    }
    function error($message) {
        if (!defined('LOG_LEVEL') || !defined('LOG_PATH') || LOG_LEVEL < LOG_ERR) return;
        $date = date_create();
        $file = rtrim(LOG_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date_format($date, 'Y-m-d') . '.log';
        error_log(sprintf('%s %-9s %s', date_format($date, 'Y-m-d H:i:s'), '[ERROR]', trim($message) . PHP_EOL), 3, $file);
    }
}