<?php
/*
$Id$

Class: UrlHelper

    Url helper for views.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
*/
class UrlHelper {

    private function __construct() { }

    /*
    Function: url
    
		Url helper for views.

    Parameters:

        string $src    - Url of the link, path to the image, etc..

    Examples:
    
		> <button type='submit'><img src='<?php echo $url('images/tick.png'); ?>' />Send!</img></button>
    */
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