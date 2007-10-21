<?php

class Web {

    /* =======================================================================
     * Web Private Constructor, Prevent an Object from Being Constructed
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Web Variables
     * ======================================================================= */

    // Public Variables
    public static $base_url;
    public static $base_path;
    public static $base_root;

    /* =======================================================================
     * Web Initialization Method
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
     * Front Controller Method
     * ======================================================================= */

    public static function run($urls) {

        $matches = array();
        $static = false;
        $matchfound = false;
        $method = $_SERVER['REQUEST_METHOD'];
        $session = new Zend_Session_Namespace('Web');

        if (isset($session->url)) {
            $urls = array_merge($session->url, $urls);
        }

        foreach ($urls as $pattern => $controller) {

            $route = (isset($_GET['__route__'])) ? '/'. $_GET['__route__'] : '/';

            $regex = '/^' . str_replace('/', '\/', $pattern) . '$/i';

            if (preg_match($regex, $route, $matches) > 0) {

                $matchfound = true;

                if (strpos($controller, '::') !== false) {
                    $static = true;
                    $controller = explode('::', $controller, 2);
                    $method = $controller[1];
                    $controller = $controller[0];
                } else if (strpos($controller, '->') !== false) {
                    $controller = explode('->', $controller, 2);
                    $method = $controller[1];
                    $controller = $controller[0];
                }

                break;
            }
        }

        if ($matchfound) {

            if (!$static) {
                $controller = new ReflectionClass($controller);
                $controller = $controller->newInstance();
            }
            
            $method = new ReflectionMethod($controller, $method);

            if (isset($session->data)) {
                $method->invokeArgs($controller, array($session->data));
            } else if (count($matches) > 1) {
                $method->invokeArgs($controller, array_slice($matches, 1));
            } else {
                $method->invoke($controller);
            }

        } else {
            throw new Exception('404 Not Found', 404);
        }
    }

    /* =======================================================================
     * Redirection Method
     * ======================================================================= */

    public static function redirect($url, $controller = false, $data = false) {

        $location = $url; 

        if (strrpos($url, '/') === 0) {
            $location = self::$base_root . $url;
        } else if ((strrpos($url, 'http://') !== 0) &&
                   (strrpos($url, 'https://') !== 0)) {
            $location = self::$base_root . self::$base_path . $url;
            $url = '/' . $url;
        } 

        if ($controller !== false || $data !== false) {

            $session = new Zend_Session_Namespace('Web');

            if ($controller !== false) {
                $session->url = array($url => $controller);
            }

            if ($data !== false) {
                $session->data = $data;
            }

            $session->setExpirationHops(1, array('url', 'data'));
        }

        header('Location: ' . $location);
        die;
    }
   
}

// Initialization
Web::initialize();
