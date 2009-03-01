<?php
class Router extends ArrayIterator {

    public function __construct($routes = null) {
        $this->add($routes);
    }

    public function add($pattern, $route = null) {
        
        if ($pattern == null) return;
        
        if (is_array($pattern)) {
            foreach ($pattern as $key => $value) {
                if (isset($this[$key])) unset($this[$key]);
                $this[$key] = $value;
            }
        } else {
            $this[$pattern] = $route;
        }
    }

    public function route() {
        $url = trim(substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(substr($_SERVER['SCRIPT_NAME'], 0, -9))), '/');
        foreach ($this as $pattern => $route) {
            if (!isset($route)) return array($pattern, null);
            $regex = '#^' . trim($pattern, '/') . '$#i';
            if (!preg_match($regex, $url, $args) > 0) continue;
            $args = (count($args) > 1) ? array_slice($args, 1) : null;
            return array($route, $args);
        }
        return false;
    }
}