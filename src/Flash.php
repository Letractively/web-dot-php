<?php

class Flash {

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Methods
     * ======================================================================= */

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