<?php
class Router extends ArrayIterator {

    public function __construct($routes = null) { $this->routes = (is_array($routes)) ? $routes : array(); }

    public function add($pattern, $route = null) {
        if (is_array($pattern)) {
            $this[] = array_merge($this, $pattern);
        } else {
            $this[$pattern] = $route;
        }
    }

    public function route() {
        $url = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9))), '/');
        foreach ($this as $pattern => $route) {
            if (!isset($route)) return array($pattern, null);
            $regex = '#^' . $pattern . '$#i';
            if (!preg_match($regex, $url, $args) > 0) continue;
            $args = (count($args) > 1) ? array_slice($args, 1) : null;
            return array($route, $args);
        }
        return false;
    }
}