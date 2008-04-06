<?php
/**
$Id$

Class: Web

    Manages controllers' execution.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class Web {

    private function __construct() {}

    /**
    Function: run

        Compares route against user supplied urls and executes appropriate
        controller method.

    Parameters:

        array $urls - Associative URL array containing acceptable routes.

    Returns:

        true  - if a route was found.
        false - if a route was not found. 

    Example:

        > Web::run(array(
        >     '/' => 'IndexController',
        >     '/home' => 'IndexController::GET',
        >     '/blog/posts' => 'BlogController->viewPosts',
        >     '/blog/posts/(\d+) => 'BlogController->viewPost'
        > ));
     */
    public static function run($urls) {
        
        $matches = array();
        $matchfound = false;
        
        if (is_array($urls)) {
            
            foreach ($urls as $pattern => $controller) {
                
                $route = (isset($_GET['__route__'])) ? '/' . $_GET['__route__'] : '/';
                
                $regex = '#^' . $pattern . '$#i';
                
                if (preg_match($regex, $route, $matches) > 0) {
                    $matchfound = true;
                    break;
                }
            }
        } else if (is_string($urls)) {
            $matchfound = true;
            $matches = array();
            $controller = $urls;
        }
        
        if ($matchfound) {
            
            $arguments = null;
            
            if (count($matches) > 1) {
                $arguments = array_slice($matches, 1);
            }
            
            $method = $_SERVER['REQUEST_METHOD'];
            $static = false;
            
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
            
            if (!$static) {
                $controller = new ReflectionClass($controller);
                $controller = $controller->newInstance();
            }
            
            $method = new ReflectionMethod($controller, $method);
            
            if ($arguments !== null) {
                $method->invokeArgs($controller, $arguments);
            } else {
                $method->invoke($controller);
            }
            
            return true;
        
        } else {
            return false;
        }
    }
}