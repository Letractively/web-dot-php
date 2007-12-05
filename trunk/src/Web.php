<?php
/*
$Id$

Class: Web

    Manages controllers' execution.

About: Version

    $Revision$ ($Date$)

About: Author

    $Author$

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

        true  - if a route was found.
        false - if a route was not found. 

    See also:
        <execute>,
        <forward>

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

        foreach ($urls as $pattern => $controller) {

            $route = (isset($_GET['__route__'])) ? '/'. $_GET['__route__'] : '/';

            $regex = '#^' . $pattern . '$#i';

            if (preg_match($regex, $route, $matches) > 0) {

                $matchfound = true;
                break;
            }
        }

        if ($matchfound) {

            if (count($matches) > 1) {
                self::execute($controller, array_slice($matches, 1));
            } else {
                self::execute($controller);
            }

            return true;
            
        } else {
            return false;
        }
    }

    /*
    Function: execute

        Executes a controller method using reflection.

    Parameters:

        string $controller - Controller and (optionally) a method.
         mixed $arguments  - Method arguments.

    Returns:

        No value is returned.

    See also:
        <forward>,
        <Browser::redirect>

    Examples:

        > Web::execute('IndexController');
        > Web::execute('IndexController::index');
        > Web::execute('IndexController->echoArgs', array('Hello, ', 'World!'));
        > Web::execute('BlogController->viewPost', 1);
        >
        > // Normally you should use controllers directly:
        >
        > IndexController::index();
        >
        > $ctl = new IndexController();
        > $ctl->echoArgs('Hello, ', 'World!');
    */
    public static function execute($controller, $arguments = null) {

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
            if (is_array($arguments)) {
                $method->invokeArgs($controller, $arguments);
            } else {
                $method->invokeArgs($controller, array($arguments));            
            }
        } else {
            $method->invoke($controller);
        }
    }

    /*
    Function: forward

        Forwards to a controller method and terminates.

    Parameters:

        string $controller - Controller and (optionally) a method.
         mixed $arguments  - Method arguments.

    Returns:

        No value is returned.

    See also:
        <execute>,
        <Browser::redirect>

    Examples:

        See <execute> examples.
    */
    public static function forward($controller, $arguments = null) {
        self::execute($controller, $arguments);
        exit;
    }
}