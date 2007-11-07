<?php
/*
    Class: Web

        Implements Front Controller Logic in web.php Framework

    About: License

        This file is licensed under the MIT.
*/
class Web {

    private function __construct() {}

    /*
    Function: run

        Compares route against user supplied urls and executes appropriate
        controller method.

    Parameters:

        array $urls - Associative URL array containing acceptable routes.

    Returns:

        Throws an exception if a route doesn't exists.
    */
    public static function run($urls) {

        $matches = array();
        $static = false;
        $matchfound = false;
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($urls as $pattern => $controller) {

            $route = (isset($_GET['__route__'])) ? '/'. $_GET['__route__'] : '/';

            $regex = '#^' . $pattern . '$#i';

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