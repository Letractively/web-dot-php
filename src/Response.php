<?php

class Response {

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function redirect($url, $permanently = false) {

        while (ob_get_level()) @ob_end_clean();

        $location = $url;
        $urlarray = parse_url($url);

        if (!isset($urlarray['scheme'])) {

            $location = 'http://';

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $location = 'https://';
            }

            $location .= preg_replace('/[^a-z0-9-:._]/i', '', $_SERVER['HTTP_HOST']);

            if (strpos($url, '/') === 0) {
                $location .= $url;
            } else {

                if ($path = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
                    $path = '/' . $path . '/';
                } else {
                    $path = '/';
                }

                $location .= $path . $url;
            }
        }

        if ($permanently) {
            header($_SERVER['SERVER_PROTOCOL'] .  ' 301 Moved Permanently');
        }

        header('Location: ' . $location);
        exit;
    }
}