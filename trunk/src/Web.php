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

        mixed $urls - Associative URL array containing acceptable routes or
                      a string that defines function, method or php file.


    Returns:

        mixed - whatever your function, method or php file returns.

    Example:

        > // Uses require to include 404.php (if exists):
        > Web::run('404.php');
     */
    public static function run($route, $args = null) {

        if (is_array($route) && $args == null) list($route, $args) = $route;

        if (file_exists($route)) {
            return require $route;
        }

        if (strpos($route, '->') !== false) {
            list($clazz, $method) = explode('->', $route, 2);
            return Web::invokeMethod($clazz, $method, $args);
        }

        if (strpos($route, '::') !== false) {
            list($clazz, $method) = explode('::', $route, 2);
            return Web::invokeMethod($clazz, $method, $args, true);
        }

        return Web::invokeFunction($route, $args);
    }

    public static function invokeMethod($clazz, $method, $args = array(), $static = false) {
        
        if (!is_array($args)) $args = array();

        $argscount = count($args);

        if ($static) {
            $rmethod = new ReflectionMethod($clazz, $method);
            return ($argscount == 0) ? $rmethod->invoke(null) : $rmethod->invokeArgs(null, $args);
        }

        $obj = new $clazz;
        if ($argscount == 0) return $obj->$method();
        $rmethod = new ReflectionMethod($clazz, $method);
        return $rmethod->invokeArgs($obj, $args);
    }

    public static function invokeFunction($func, $args = array()) {
        if (!is_array($args)) $args = array();
        if (count($args) == 0) return $func();
        $rfunc = new ReflectionFunction($func);
        return $rfunc->invokeArgs($args);
    }
}