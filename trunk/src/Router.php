<?php
class Router implements ArrayAccess {

    private $routes;

    public function __construct($routes = null) { $this->routes = (is_array($routes)) ? $routes : array(); }
    public function offsetSet($pattern, $route) { $this->routes[$pattern] = $route; }
    public function offsetExists($pattern) { return isset($this->routes[$pattern]); }
    public function offsetUnset($pattern) { unset($this->routes[$pattern]); }
    public function offsetGet($pattern) { return isset($this->routes[$pattern]) ? $this->routes[$pattern] : null; }

    public function add($pattern, $route = null) {
        if (is_array($pattern)) {
            $this->routes = array_merge($this->routes, $pattern);
        } else {
            $this->routes[$pattern] = $route;
        }
    }

    public function route() {
        $url = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9))), '/');
        foreach ($this->routes as $pattern => $route) {
            if (!isset($route)) return array($pattern, null);
            $regex = '#^' . $pattern . '$#i';
            if (!preg_match($regex, $url, $args) > 0) continue;
            $args = (count($args) > 1) ? array_slice($args, 1) : null;
            return array($route, $args);
        }
        return false;
    }
}