<?php

class Session {

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function start() {

        static $started;

        if (!$started) {

            if (!defined('SID') && !headers_sent()) {
                session_start();
            }

            register_shutdown_function('Flash::shutdown');

            $started = true;
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return (self::has($key)) ? $_SESSION[$key] : null;
    }

    public static function has($key) {
        return (isset($_SESSION[$key]));
    }

    public static function remove($key) {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }
}