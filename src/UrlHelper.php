<?php
/**
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

    private function __construct() {}

    /**
    Function: url
    
		Url helper for views.

    Parameters:

        string $src    - Url of the link, path to the image, etc..

    Returns:

        No value is returned.

    Examples:

        >     
		> <button type='submit'><img src='<?php $url('images/tick.png'); ?>' />Send!</img></button>
     */
    public static function url($src) {
        
        $location = $src;
        $urlarray = parse_url($src);
        
        if ((!isset($urlarray['scheme'])) && (strpos($src, '/') !== 0)) {
            
            $path = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/');
            
            if (strlen($path) > 0) {
                $path = '/' . $path . '/';
            } else {
                $path = '/';
            }
            
            $location = $path . $src;
        }
        
        echo $location;
    }
}