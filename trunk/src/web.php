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
class view {
    function __construct($file) {
        $this->file = $file;
    }
    function __toString() {
        extract((array)$this);
        $blocks = new blocks;
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
class blocks {
    function __call($name, $args) {
        echo isset($this->$name) ? $this->$name : '';
    }
    function __get($name) {
        if (!isset($this->$name)) $this->$name = new Block();
        return $this->$name;
    }
}
class block implements IteratorAggregate, Countable {
    function __construct() { $this->output = array(); $this->mode = -1; }
    function getIterator() { return new ArrayIterator($this->output); }
    function count() { return count($this->output); }
    function start() { $this->mode = 0; ob_start(); }
    function append() { $this->mode = 1; ob_start(); }
    function prepend() { $this->mode = 2; ob_start(); }
    function insert($offset) { $this->mode = 3; $this->offset = $offset; ob_start(); }
    function flush() {
        if ($this->mode == -1) trigger_error('Flush-method can only be called after calling start, append, prepend, or insert.', E_USER_WARNING);
        switch ($this->mode) {
            case 0: $this->output = array(ob_get_clean()); break;
            case 1: $this->output[] = ob_get_clean(); break;
            case 2: array_unshift($this->output, ob_get_clean()); break;
            case 3: array_splice($this->output, $this->offset, 0, ob_get_clean()); break;
        }
        $this->mode = -1;
    }
    function __toString() { return implode($this->output); }
}
class form extends ArrayObject {
    private $filters;
    private $value;
    function __construct($args = null) {
        if ($args == null) return;
        foreach ($args as $name => $value) {
            if (is_array($value) && !isset($value[0][0])) {
                $this[$name] = new form($value);
            } else {
                $this[$name] = new form;
                $this[$name]->value = $value;
            }
        }
    }
    function __set($name, $value) {
        if ($name == 'filters') {
            $this->filters =  is_string($value)? explode(',', $value) : (array) $value;
            return;
        }
        if (!isset($this[$name])) $this[$name] = new form;
        $this[$name]['value'] = $value;
    }
    function __get($name) {
        if (!isset($this[$name])) $this[$name] = new form;
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