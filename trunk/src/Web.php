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

        Dynamically executes a controller.

    Parameters:

        mixed $route - The controller to execute or include. It can be
                       a function, an instance method, a static method,
                       an anonymous method, or a file.

    Returns:

        mixed - whatever your function, method or php file returns.

    Example:
        > // Executes a function:
        > Web::run('ExecutableFunction');
        > // Executes an instance method:
        > Web::run('Controller->execute');
        > // Executes a static method:
        > Web::run('Controller::execute');
        > // Executes an anonymous method:
        > Web::run(function() { echo 'Hello from a closure.'});
        > // Uses require to include 404.php (if exists):
        > Web::run('404.php');
        > // Executes an instance method with parameters:
        > Web::run(array('Controller->execute', array('arg0', 'arg1'));
     */
    public static function run($route) {

        $ctrl = $route;
        $args = array();

        if (is_array($route)) {
            if (isset($route[0])) $ctrl = $route[0];
            if (isset($route[1])) $args = $route[1];
        }

        if (is_string($ctrl)) {
            if (file_exists($ctrl)) return require $ctrl;
            if (strpos($ctrl, '->') !== false) {
                list($clazz, $method) = explode('->', $ctrl, 2);
                $ctrl = array(new $clazz, $method);
            }
        }

        if (is_callable($ctrl)) return call_user_func_array($ctrl, (is_array($args)) ? $args : array($args));

        throw new InvalidArgumentException('Invalid route, "' . print_r($route, true) . '", was supplied.', 404);
    }
}