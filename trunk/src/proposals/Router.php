<?php
class Router implements ArrayAccess, Countable {

    function __construct($routes = null) {
        $this->routes = array();
        if (isset($routes)) $this->add($routes);
    }

    function offsetSet($offset, $value) { $this->routes[$offset] = $value; }
    function offsetExists($offset) { return isset($this->routes[$offset]); }
    function offsetUnset($offset) { unset($this->routes[$offset]); }
    function offsetGet($offset) { return isset($this->routes[$offset]) ? $this->routes[$offset] : null; }
    function count() { count($this->routes); }

    function add($pattern, $route = null) {
        if (is_array($pattern)) {
            $this->routes += $pattern;
        } else {
            $this[$pattern] = $route;
        }
    }

    function route() {
        $url = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9))), '/');
        foreach ($this->routes as $pattern => $route) {
            if (!isset($route)) return array($pattern);
            $regex = '#^' . trim($pattern, '/') . '$#i';
            if (!preg_match($regex, $url, $args) > 0) continue;
            if (count($args) > 1) return array($route, array_slice($args, 1));
            return array($route);
        }
        return false;
    }
}