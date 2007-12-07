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

    protected $javascripts;
    protected $stylesheets;

    public function __construct() {
        $this->javascripts = array();
        $this->stylesheets = array();
    }

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
    
    /*
    Function: javascript
    
		Registers a javascript-file include.

    Parameters:

        string $src    - Path to the file.
        string $before - Content before the actual <script>-tag. 
        string $after  - Content after the actual </script>-tag.

    Examples:
    
		> // We're assuming that you've imported the helper to $include
		> echo $include->javascript('scripts/javascript.js');
		> echo $include->javascript('scripts/pngfix.js', '<!--[if lt IE 7.]>', '<![endif]-->');
    */
    public function javascript($src, $before = '', $after = '') {
        $this->javascripts[$src] = sprintf('%s<script type="text/javascript" src="%s"></script>%s', $before, self::location($src), $after);
        return $this->javascripts[$src];
    }

    /*
    Function: javascripts
    
		Goes through all registred javascript-files.

    Examples:
    
		> // We're assuming that you've imported the helper to $include
		> $include->javascript('scripts/javascript.js');
		> $include->javascript('scripts/pngfix.js', '<!--[if lt IE 7.]>', '<![endif]-->');
		> echo $include->javascripts();
    */
    public function javascripts() {

        $space = '';

        foreach ($this->javascripts as $javascript) {
            echo $space, $javascript, "\n";

            if (strlen($space) === 0) {
                $space = '    ';
            }
        }
    }

    /*
    Function: stylesheet
    
		Registers a css-file include.

    Parameters:

        string $src    - Path to the file.
        string $media  - Media parameter of the <link .. />-tag. Defaults to 'screen, projection'
        string $before - Content before the actual <link rel.. />-tag. 
        string $after  - Content after the actual <link rel.. />-tag.

    Examples:
    
		> // We're assuming that you've imported the helper to $include
		> echo $include->stylesheet('css/screen.css');
		> echo $include->stylesheet('css/ie.js', 'screen, projection', '<!--[if IE]>', '<![endif]-->');
		> echo $include->stylesheet('css/print.css', 'print');
    */
    public function stylesheet($src, $media = 'screen, projection', $before = '', $after = '') {
        $this->stylesheets[$src] = sprintf('%s<link rel="stylesheet" href="%s" type="text/css" media="%s" />%s', $before, self::location($src), $media, $after);
        return $this->stylesheets[$src];
    }

    /*
    Function: stylesheets
    
		Goes through all registred css-files.

    Examples:
    
		> // We're assuming that you've imported the helper to $include
		> $include->stylesheet('css/screen.css');
		> $include->stylesheet('css/ie.js', 'screen, projection', '<!--[if IE]>', '<![endif]-->');
		> $include->stylesheet('css/print.css', 'print');
		> echo $include->stylesheets();
    */
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