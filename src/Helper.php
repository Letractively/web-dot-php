<?php

class Helper {

    /* =======================================================================
     * Properties
     * ======================================================================= */

    private static $helpers = array();

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function register($key, $helper) {
        self::$helpers[$key] = array('helper' => $helper, 'loaded' => false);
    }

    public static function import($key) {

        if (isset(self::$helpers[$key])) {

            if (!self::$helpers[$key]['loaded']) {

                $class = self::$helpers[$key]['helper'];
                $method = null;

                if (strpos($class, '::') !== false) {
                    $class = explode('::', $class, 2);
                    $method = $class[1];
                    $class = $class[0];
                    $helper = create_function(null, '$args = func_get_args(); return call_user_func_array(array("' . $class . '", "' . $method . '"), $args);');
                } else {
                    $class = new ReflectionClass($class);
                    $helper = $class->newInstance();
                }

                self::$helpers[$key]['helper'] = $helper;
                self::$helpers[$key]['loaded'] = true;
            }

            return self::$helpers[$key]['helper'];

        } else {
            throw new Exception(sprintf('Helper with key \'%s\' has not been registered.', $key));
        }

    }
}
