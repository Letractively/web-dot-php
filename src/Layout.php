<?php
/**
$Id$

Class: Layout

    View layout/decoration functionality.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class Layout {

    private function __construct() {}
    
    private static $layout = null;
    private static $data = array();
    
    private static function reset() {
        self::$layout = null;
        self::$data = array();
    }
    
    /**
    Function set
     */
    public static function set($key, $value = null) {
        if ($value === null) {
            self::$layout = $key;
        } else {
            self::$data[$key] = $value;
        }
    }

    /**
    Function get
     */
    public static function get($key = null) {
        if ($key === null) {
            return self::$layout;
        } else {
            return (isset(self::$data[$key])) ? self::$data[$key] : null;
        }
    }

    /**
    Function has
     */
    public static function has($key) {
        return (isset(self::$data[$key]));
    }

    /**
    Function decorate
     */
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
}