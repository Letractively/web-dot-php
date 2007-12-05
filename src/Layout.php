<?php
/*
$Id$

Class: Layout

    View layout/decoration functionality.

About: Version

    $Revision$ ($Date$)

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
*/
class Layout {

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Properties
     * ======================================================================= */

    private static $layout = null;
    private static $data = array();

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function set($key, $value = null) {
        if ($value === null) {
            self::$layout = $key;
        } else {
            self::$data[$key] = $value;
        }
    }

    public static function get($key = null) {
        if ($key === null) {
            return self::$layout;
        } else {
            return (isset(self::$data[$key])) ? self::$data[$key] : null;
        }
    }

    public static function has($key) {
        return (isset(self::$data[$key]));
    }

    public static function decorate($view, $layout = null) {

        if ($layout === null) {
            $layout = self::get();
        }

        if ($layout !== null) {
            extract(array_merge(self::$data, array('view' => $view)));
            require $layout;
        } else if ($view->body !== null) {
            echo $view->body;
        }

        self::reset();
    }

    private static function reset() {
        self::$layout = null;
        self::$data = array();
    }
}