<?php

class Web {

    /* =======================================================================
     * Front Controller Method
     * ======================================================================= */

    public static function run($urls) {

        $matches = array();
        $matchfound = false;
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($urls as $pattern => $controller) {

            $route = (isset($_GET['__route__'])) ? '/'. $_GET['__route__'] : '/';

            $regex = '/^' . str_replace('/', '\/', $pattern) . '$/';

            if (preg_match($regex, $route, $matches) > 0) {

                $matchfound = true;

                if (is_array($controller)) {
                    switch (count($controller)) {
                        case 2:
                            $method = $controller[1];
                        case 1:
                            $controller = $controller[0];
                            break;
                    }
                }

                break;
            }
        }

        if ($matchfound) {

            $controller = new ReflectionClass($controller);
            $controller = $controller->newInstance();
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
