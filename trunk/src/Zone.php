<?php
/*
    Class: Zone

        Zone class is used to define placeholders in views and layouts 

    About: License

        This file is licensed under the MIT.
*/
class Zone {

    private function __construct() {}
    private static $zones = array();
    private static $zone = null;

    /*
    Function: write

        Opens Zone for Writing.

    Parameters:

        string $zone - Name of the Zone to open write buffer for.
    */
    public static function write($zone) {

        self::$zone = $zone;

        if (!isset(self::$zones[$zone])) {
            self::$zones[$zone] = array();
        }

        array_push(self::$zones[$zone], null);
        ob_start();
    }

    /*
    Function: flush

        Flushes write buffer and stores Zone data.

    */
    public static function flush() {

        if (self::$zone !== null) {
            array_pop(self::$zones[self::$zone]);
            array_push(self::$zones[self::$zone], ob_get_clean());
            self::$zone = null;
        }
        
    }

    /*
    Function: render

        Renders Zone.

    Parameters:

        string $zone - Name of the Zone to render.

    Returns:

        true - if there was data written on $zone.
        false - if there is nothing to render.
    */
    public static function render($zone) {

        if (isset(self::$zones[$zone]) && count(self::$zones[$zone]) > 0) {

            while (($data = array_shift(self::$zones[$zone])) != null) {
                echo $data;
            }

            return true;

        } else {
            return false;
        }
    }
}
