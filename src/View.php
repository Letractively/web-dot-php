<?php

class View {

    /* =======================================================================
     * View Private Constructor, Prevent an Object from Being Constructed
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * View Constants
     * ======================================================================= */

    const NO_LAYOUT = '__VIEW_NO_LAYOUT__';
    const DATA = '__VIEW_DATA__';
    const TARGET_VIEW = 1;
    const TARGET_LAYOUT = 2;
    const TARGET_ALL = 3;

    /* =======================================================================
     * View Variables
     * ======================================================================= */

    // Public Variables
    public static $base_url;
    public static $base_path;
    public static $base_root;

    // Private Variables
    private static $layout = self::NO_LAYOUT;
    private static $zones = array();
    private static $zones_current = false;
    private static $data_view = array();
    private static $data_layout = array();
    private static $view_data = false;

    /* =======================================================================
     * View Variable Setting Method
     * ======================================================================= */

    public static function set($key, $data, $target = self::TARGET_VIEW) {
        if ($target == self::TARGET_ALL) {
            self::$data_view[$key] = $data;
            self::$data_layout[$key] = $data;
        } else if ($target == self::TARGET_VIEW) {
            self::$data_view[$key] = $data;
        } else if ($target == self::TARGET_LAYOUT) {
            self::$data_layout[$key] = $data;
        }
    }

    /* =======================================================================
     * View Initialization Method
     * ======================================================================= */

    public static function initialize() {

        $scheme = 'http://';

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $scheme = 'https://';
        }

        self::$base_root = $scheme .
            preg_replace('/[^a-z0-9-:._]/i', '', $_SERVER['HTTP_HOST']);

        if ($path = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
            self::$base_url = self::$base_root . '/' . $path;
            self::$base_path = '/' . $path . '/';
        }
        else {
            self::$base_url = self::$base_root;
            self::$base_path = '/';
        }
        
    }

    /* =======================================================================
     * View Configuration Method
     * ======================================================================= */

    public static function configure($config) {

        if (is_array($config)) {
            // TODO: Automatic Layout Selection Code 
        }
    }

    /* =======================================================================
     * View  Rendering Method
     * ======================================================================= */

    public static function render($view, $data = false, $layout = false) {

        if ($layout) {
            self::$layout = $layout;
        }

        if (self::$view_data === false) {
            extract(self::$data_view);
        }

        if ($data) {
            extract($data);
        }

        if (self::$layout != self::NO_LAYOUT) {

            ob_start();
            // Render View

            require($view);
            $view = self::$layout;
            self::$layout = self::NO_LAYOUT;

            if (isset(self::$zones[self::DATA])) {

                ob_clean();

                self::$view_data = '';

                while (($zone_data = array_shift(self::$zones[self::DATA])) != null) {
                    self::$view_data .= $zone_data;
                }

                // Render Layout
                self::render($view , self::$data_layout);

            } else {

                self::$view_data = ob_get_clean(); 

                // Render Layout
                self::render($view , self::$data_layout);
            }

        } else {
            // Render layout Less View or Layout
            require($view);
        }
    }

    /* =======================================================================
     * View Data Method for Layouts
     * ======================================================================= */

    public static function getData() {
        return self::$view_data;
    }

    /* =======================================================================
     * View Zone Methods
     * ======================================================================= */

    public static function writeZone($zone) {

        if (self::$layout != self::NO_LAYOUT) {

            self::$zones_current = $zone;

            if (!isset(self::$zones[$zone])) {
                self::$zones[$zone] = array();
            }

            array_push(self::$zones[$zone], '');
            
            ob_start();
        }
    }

    public static function flushZone() {
        if (self::$layout) {
            if (self::$zones_current) {
                array_pop(self::$zones[self::$zones_current]);
                $data = ob_get_clean();
                array_push(self::$zones[self::$zones_current], $data);
                self::$zones_current = false;
            }
        }
    }

    public static function renderZone($zone) {

        if (isset(self::$zones[$zone]) && count(self::$zones[$zone]) > 0) {

            while (($zone_data = array_shift(self::$zones[$zone])) != null) {
                echo $zone_data;
            }

            return true;

        } else {
            return false;
        }
    }

    /* =======================================================================
     * Include Resource Method
     * ======================================================================= */

    public static function includeResource($resource) {
        // Todo: implement this
    }
}

// Initialization
View::initialize();
