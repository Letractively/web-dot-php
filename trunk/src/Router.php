<?php
class Router implements ArrayAccess, Countable {

    public function __construct($routes = null) {
        $this->routes = array();
        if (isset($routes)) $this->add($routes);
    }

    public function offsetSet($offset, $value) { $this->routes[$offset] = $value; }
    public function offsetExists($offset) { return isset($this->routes[$offset]); }
    public function offsetUnset($offset) { unset($this->routes[$offset]); }
    public function offsetGet($offset) { return isset($this->routes[$offset]) ? $this->routes[$offset] : null; }
    public function count() { count($this->routes); }

    public function add($pattern, $route = null) {
        if (is_array($pattern)) {
            $this->routes += $pattern;
        } else {
            $this[$pattern] = $route;
        }
    }

    public function route() {
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