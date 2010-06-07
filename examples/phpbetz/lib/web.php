<?php
/**
$Id: web.php 457 2010-05-28 22:54:30Z aapo.laakkonen $

About: Version

    $Revision: 457 $

About: Author

    $Author: aapo.laakkonen $

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
                run($func, array_slice($params, 1));
                if ($exit) exit;
                return;
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
    function status($code) {
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
            default: return;
        }
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        header(trim(sprintf('%s %u %s', $protocol, $code, $msg)));
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
    function flash($name, $value, $hops = 1) {
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
    function preglace($pattern, $replacement) {
        return function($subject) use ($pattern, $replacement) {
            return preg_replace($pattern, $replacement, $subject);
        };
    }
    function specialchars($quote = ENT_NOQUOTES, $charset = 'UTF-8', $double = true) {
        return function($value) use ($quote, $charset, $double) {
            return htmlspecialchars($value, $quote, $charset, $double);
        };
    }
    function links($value) {
        if($value == '' || !preg_match('/(http|www\.|@)/i', $value)) return $value;
        $lines = explode("\n", $value); $value = '';
        while (list($k, $l) = each($lines)) {
            $l = preg_replace("/([ \t]|^)www\./i", "\\1http://www.", $l);
            $l = preg_replace("/(http:\/\/[^ )\r\n!]+)/i", "<a href=\"\\1\">\\1</a>", $l);
            $l = preg_replace("/(https:\/\/[^ )\r\n!]+)/i", "<a href=\"\\1\">\\1</a>", $l);
            $l = preg_replace("/([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))/i", "<a href=\"mailto:\\1\">\\1</a>", $l);
            $value .= $l."\n";
        }
        return $value;
    }
    function smileys($value) {
        $template = '<img src="%s" class="smiley" alt="%s" width="19" height="19">';
        $smileys = array(
            ':-)'          => sprintf($template, url('~/img/smileys/grin.gif'), 'grin'),
            ':D'           => sprintf($template, url('~/img/smileys/lol.gif'), 'lol'),
            ':-D'          => sprintf($template, url('~/img/smileys/lol.gif'), 'lol'),
            ':lol:'        => sprintf($template, url('~/img/smileys/lol.gif'), 'lol'),
            ':cheese:'     => sprintf($template, url('~/img/smileys/cheese.gif'), 'cheese'),
            ':)'           => sprintf($template, url('~/img/smileys/smile.gif'), 'smile'),
            ';-)'          => sprintf($template, url('~/img/smileys/wink.gif'), 'wink'),
            ';)'           => sprintf($template, url('~/img/smileys/wink.gif'), 'wink'),
            ':smirk:'      => sprintf($template, url('~/img/smileys/smirk.gif'), 'smirk'),
            ':roll:'       => sprintf($template, url('~/img/smileys/rolleyes.gif'), 'rolleyes'),
            ':-S'          => sprintf($template, url('~/img/smileys/confused.gif'), 'confused'),
            ':wow:'        => sprintf($template, url('~/img/smileys/surprise.gif'), 'surprised'),
            ':bug:'        => sprintf($template, url('~/img/smileys/bigsurprise.gif'), 'big surprise'),
            ':-P'          => sprintf($template, url('~/img/smileys/tongue_laugh.gif'), 'tongue laugh'),
            '%-P'          => sprintf($template, url('~/img/smileys/tongue_rolleye.gif'), 'tongue rolleye'),
            '%P'           => sprintf($template, url('~/img/smileys/tongue_rolleye.gif'), 'tongue rolleye'),
            ';-P'          => sprintf($template, url('~/img/smileys/tongue_wink.gif'), 'tongue wink'),
            ':P'           => sprintf($template, url('~/img/smileys/rasberry.gif'), 'raspberry'),
            ':blank:'      => sprintf($template, url('~/img/smileys/blank.gif'), 'blank stare'),
            ':long:'       => sprintf($template, url('~/img/smileys/blank.gif'), 'long face'),
            ':ohh:'        => sprintf($template, url('~/img/smileys/ohh.gif'), 'ohh'),
            ':grrr:'       => sprintf($template, url('~/img/smileys/grrr.gif'), 'grrr'),
            ':gulp:'       => sprintf($template, url('~/img/smileys/gulp.gif'), 'gulp'),
            '8-/'          => sprintf($template, url('~/img/smileys/ohoh.gif'), 'oh oh'),
            ':down:'       => sprintf($template, url('~/img/smileys/downer.gif'), 'downer'),
            ':red:'        => sprintf($template, url('~/img/smileys/embarrassed.gif'), 'red face'),
            ':sick:'       => sprintf($template, url('~/img/smileys/sick.gif'), 'sick'),
            ':shut:'       => sprintf($template, url('~/img/smileys/shuteye.gif'), 'shut eye'),
            ':-/'          => sprintf($template, url('~/img/smileys/hmm.gif'), 'hmmm'),
            '>:('          => sprintf($template, url('~/img/smileys/mad.gif'), 'mad'),
            ':mad:'        => sprintf($template, url('~/img/smileys/mad.gif'), 'mad'),
            '>:-('         => sprintf($template, url('~/img/smileys/angry.gif'), 'angry'),
            ':angry:'      => sprintf($template, url('~/img/smileys/angry.gif'), 'angry'),
            ':zip:'        => sprintf($template, url('~/img/smileys/zip.gif'), 'zipper'),
            ':kiss:'       => sprintf($template, url('~/img/smileys/kiss.gif'), 'kiss'),
            ':ahhh:'       => sprintf($template, url('~/img/smileys/shock.gif'), 'shock'),
            ':shock:'      => sprintf($template, url('~/img/smileys/shock.gif'), 'shock'),
            ':coolsmile:'  => sprintf($template, url('~/img/smileys/shade_smile.gif'), 'cool smile'),
            ':coolsmirk:'  => sprintf($template, url('~/img/smileys/shade_smirk.gif'), 'cool smirk'),
            ':coolgrin:'   => sprintf($template, url('~/img/smileys/shade_grin.gif'), 'cool grin'),
            ':coolhmm:'    => sprintf($template, url('~/img/smileys/shade_hmm.gif'), 'cool hmm'),
            ':coolmad:'    => sprintf($template, url('~/img/smileys/shade_mad.gif'), 'cool mad'),
            ':coolcheese:' => sprintf($template, url('~/img/smileys/shade_cheese.gif'), 'cool cheese'),
            ':vampire:'    => sprintf($template, url('~/img/smileys/vampire.gif'), 'vampire'),
            ':snake:'      => sprintf($template, url('~/img/smileys/snake.gif'), 'snake'),
            ':exclaim:'    => sprintf($template, url('~/img/smileys/exclaim.gif'), 'exclaim'),
            ':question:'   => sprintf($template, url('~/img/smileys/question.gif'), 'question'),
            '(y)'          => sprintf($template, url('~/img/smileys/thumbs.gif'), 'thumb'),
            '(n)'          => sprintf($template, url('~/img/smileys/thumbs_down.gif'), 'thumb down'),
            '(Y)'          => sprintf($template, url('~/img/smileys/thumbs.gif'), 'thumb'),
            '(b)'          => sprintf($template, url('~/img/smileys/beer.gif'), 'beer'),
            '(B)'          => sprintf($template, url('~/img/smileys/beer.gif'), 'beer'),
            ':finger:'     => sprintf($template, url('~/img/smileys/finger.gif'), 'finger'),
            '>:('          => sprintf($template, url('~/img/smileys/mad.gif'), 'mad'),
            '(so)'         => sprintf($template, url('~/img/smileys/soccer.gif'), 'soccer ball')
        );
        return str_replace(array_keys($smileys), array_values($smileys), $value);
    }
    function slug($title, $delimiter = '-') {
        $title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $title = preg_replace('#[^a-z0-9/_|+\s-]#i', '', $title);
        $title = strtolower(trim($title, '/_|+ -'));
        $title = preg_replace('#[/_|+\s-]+#', $delimiter, $title);
        return $title;
    }
    function block(&$block = false) {
        if ($block === false) return ob_end_clean();
        ob_start(function($buffer) use (&$block) { $block = $buffer; });
    }
    class view {
        static $globals = array();
        static function register($name, $value) {
            self::$globals[$name] = $value;
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
    function openid_discover($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xrds+xml'));
        $oid = curl_exec($ch);
        curl_close($ch);
        return simplexml_load_string($oid, 'xrds');
    }
    function openid_authenticate($url, array $params = array()) {
        $needed = array(
            'openid.mode' => 'checkid_setup',
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select'
        );
        $params = array_merge($params, $needed);
        $qs = parse_url($url, PHP_URL_QUERY);
        $url .= isset($qs) ? '&' : '?';
        $url .= http_build_query($params);
        redirect($url);
    }
    function openid_check($url) {
        $data = str_replace('openid.mode=id_res', 'openid.mode=check_authentication', $_SERVER['QUERY_STRING']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $oid = curl_exec($ch);
        curl_close($ch);
        return strpos($oid, 'is_valid:true') === 0;
    }
    class xrds extends SimpleXMLElement {
        function getUrl() {
            return $this->XRD->Service->URI;
        }
    }
}