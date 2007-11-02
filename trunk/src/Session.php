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
        if (!self::started()) session_start();
    }

    public static function started() {
        return defined('SID');
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return (self::has($key)) ? $_SESSION[$key] : null;
    }

    public static function has($key) {
        self::start();
        return (isset($_SESSION[$key]));
    }

    public static function remove($key) {

        self::start();

        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}