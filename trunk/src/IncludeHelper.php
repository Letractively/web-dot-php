<?php
/*
$Id$

Class: IncludeHelper

    Include helper for external view resources (eg. javascripts and stylesheets)

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
*/
class IncludeHelper {

    /* =======================================================================
     * Properties
     * ======================================================================= */

    protected $javascripts;
    protected $stylesheets;

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    public function __construct() {
        $this->javascripts = array();
        $this->stylesheets = array();
    }

    /* =======================================================================
     * Methods
     * ======================================================================= */

    private function location($src) {

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

    public function javascript($src, $before = '', $after = '') {
        $this->javascripts[$src] = sprintf('%s<script type="text/javascript" src="%s"></script>%s', $before, self::location($src), $after);
        return $this->javascripts[$src];
    }

    public function javascripts() {

        $space = '';

        foreach ($this->javascripts as $javascript) {
            echo $space, $javascript, "\n";

            if (strlen($space) === 0) {
                $space = '    ';
            }
        }
    }

    public function stylesheet($src, $media = 'screen, projection', $before = '', $after = '') {
        $this->stylesheets[$src] = sprintf('%s<link rel="stylesheet" href="%s" type="text/css" media="%s" />%s', $before, self::location($src), $media, $after);
        return $this->stylesheets[$src];
    }

    public function stylesheets() {

        $space = '';

        foreach ($this->stylesheets as $stylesheet) {
            echo $space, $stylesheet, "\n";

            if (strlen($space) === 0) {
                $space = '    ';
            }
        }
    }

}