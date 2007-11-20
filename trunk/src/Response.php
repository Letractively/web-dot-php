<?php
/*
Class: Response

    HTTP response related functionality.

About: Version

    $Id$

About: License

    This file is licensed under the MIT.
*/
class Response {

    private function __construct() {}

    /*
    Function: redirect

        Makes a bit more sophisticated header("Location: url");

    Parameters:

        string $url          - The redirect location, it can be an absolute url, server relative url or application relative url.
        boolean $permanently - Specifies whether the redirect is permanent (301) or temporary. Defaults to false.

    Examples:

        > // permanent absolute url redirect
        > Response::redirect('http://www.example.com', true);
        > // nonpermanent server relative url redirect
        > Response::redirect('/');
        > // nonpermanent application relative url redirect
        > Response::redirect('posts');
    */
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
