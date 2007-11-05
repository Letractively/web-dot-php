<?php

class UrlHelper {

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() { }

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function url($src) {

        $location = $src;
        $urlarray = parse_url($src);

        if ((!isset($urlarray['scheme'])) && (strpos($src, '/') !== 0)) {
            if ($path = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
                $path = '/' . $path . '/';
            } else {
                $path = '/';
            }

            $location = $path . $src;
        }

        return $location;
    }
}