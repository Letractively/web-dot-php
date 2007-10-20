<?php

class Web {

    /* =======================================================================
     * Front Controller Method
     * ======================================================================= */

    public static function run($urls) {

        $matches = array();
        $static = false;
        $matchfound = false;
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($urls as $pattern => $controller) {

            $route = (isset($_GET['__route__'])) ? '/'. $_GET['__route__'] : '/';

            $regex = '/^' . str_replace('/', '\/', $pattern) . '$/i';

            if (preg_match($regex, $route, $matches) > 0) {

                $matchfound = true;

                if (strpos($controller, '::') !== false) {
                    $static = true;
                    $controller = explode('::', $controller, 2);
                    $method = $controller[1];
                    $controller = $controller[0];
                } else if (strpos($controller, '->') !== false) {
                    $controller = explode('->', $controller, 2);
                    $method = $controller[1];
                    $controller = $controller[0];
                }

                break;
            }
        }

        if ($matchfound) {

            if (!$static) {
                $controller = new ReflectionClass($controller);
                $controller = $controller->newInstance();
            }
            
            $method = new ReflectionMethod($controller, $method);

            if (count($matches) > 1) {
                $method->invokeArgs($controller, array_slice($matches, 1));
            } else {
                $method->invoke($controller);
            }

        } else {
            throw new Exception('404 Not Found', 404);
        }
    }

}
