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

            if (!defined('SID')) {
                session_start();
            }

            if (self::get('urn:web.php:session-initiated') !== true) {
                session_regenerate_id();
                self::set('urn:web.php:session-initiated', true);
            }

            if (self::has('urn:web.php:session-http-user-agent')) {
                if (self::get('urn:web.php:session-http-user-agent') !== sha1($_SERVER['HTTP_USER_AGENT'] . 'zaS2uZQRE9GIQHVxRGV1')) {
                    // Possible Session Hijacking Attempt                    
                    throw new Exception('404 Not Found', 404);
                }
            } else {
                self::set('urn:web.php:session-http-user-agent', sha1($_SERVER['HTTP_USER_AGENT'] . 'zaS2uZQRE9GIQHVxRGV1')); 
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