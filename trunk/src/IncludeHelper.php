<?php

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

    public function javascript($src) {
        $this->javascripts[$src] = sprintf('<script type="text/javascript" src="%s"></script>', $src);
        return $this->javascripts[$src];
    }

    public function javascripts() {
        foreach ($this->javascripts as $javascript) {
            echo $javascript, "\n";
        }
    }

    public function stylesheet($src, $media = 'screen, projection') {
        $this->stylesheets[$src] = sprintf('<link rel="stylesheet" href="%s" type="text/css" media="%s" />', $src, $media);
        return $this->javascripts[$src];
    }

    public function stylesheets() {
        foreach ($this->stylesheets as $stylesheet) {
            echo $stylesheet, "\n";
        }
    }

}