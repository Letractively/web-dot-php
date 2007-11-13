<?php
/*
Class: Flash

    Handles sessionwide variables. Used for example to notify the user about form action, ie.
    was operation executed succesfully.

About: Version

    $Id$

About: License

    This file is licensed under the MIT.
*/
class Flash {

    private function __construct() {}


    /*
    Function: set

        Sets a session variable.

    Parameters:

        string $key - name of the variable.
        string $value - value of the variable.
        string $hops - number of requests the variable is available.
    */
    public static function set($key, $value, $hops = 1) {
        if (self::has()) {
            $flashes = self::get();
        } else {
            $flashes = array();
        }

        $flashes[$key] = array();
        $flashes[$key]['value'] = $value;
        $flashes[$key]['hops'] = $hops;

        Session::set('urn:web.php:flash', $flashes);
    }

    /*
    Function: get

        Returns the specified session variable, if one is set.

    Parameters:
        
        string $key - name of the variable.

    Returns:

        The variable if it is set.
    */
    public static function get($key = null) {
        if ($key === null) {
            return Session::get('urn:web.php:flash');
        } else if (self::has($key)) {
            $flashes = Session::get('urn:web.php:flash');
            return $flashes[$key]['value'];
        } else {
            return null;
        }
    }

    /*
    Function: has
        
        Checks whether a session variable by this name is set.

    Parameters:

        string $key - name of the variable.

    Returns:

        true - if the variable is set.
        false - if the varaible is not set.
    */
    public static function has($key = null) {
        if ($key === null) {
            return (Session::has('urn:web.php:flash'));
        } else if (Session::has('urn:web.php:flash')) {
            $flashes = Session::get('urn:web.php:flash');
            return (isset($flashes[$key]));
        } else {
            return false;
        }
    }

    /*
    Function: remove

        Removes the specified session variable.

    Parameters:

        string $key - name of the variable.
    */
    public static function remove($key = null) {
        if ($key === null) {
            Session::remove('urn:web.php:flash');
        } else {

            $flashes = self::get();

            if (isset($flashes[$key])) {
                unset($flashes[$key]);
                Session::set('urn:web.php:flash', $flashes);
            }
        }
    }

    public static function shutdown() {

        if (self::has()) {

            $flashes = self::get();

            foreach ($flashes as $key => $flash) {

                --$flash['hops'];

                if ($flash['hops'] < 0) {
                    unset($flashes[$key]);
                } else {
                    $flashes[$key] = $flash;
                }

            }

            if (count($flashes) > 0) {
                Session::set('urn:web.php:flash', $flashes);
            } else {
                Session::remove('urn:web.php:flash');
            }
        }
    }
}
