<?php

class View {

    /* =======================================================================
     * Properties
     * ======================================================================= */

    // Static Properties
    private static $view = null;
    private static $data = array();

    // Instance Properties
    public $title = null;
    public $body = null;

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    public function __construct($body, $title = null) {
        $this->body = $body;
        $this->title = $title;
    }

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function set($key, $value = null) {
        if ($value === null) {
            self::$view = $key;            
        } else {
            self::$data[$key] = $value;
        }
    }

    public static function get($key = null) {
        if ($key === null) {
            return self::$view;
        } else {
            return (isset(self::$data[$key])) ? self::$data[$key] : null; 
        }
    }

    public static function render($view = null, $data = null, $layout = null) {

        if ($view === null) {
            $view = self::get();
        }

        if ($layout === null) {
            $layout = Layout::get();
        }

        if ($data !== null && is_array($data)) {
            $data = array_merge(self::$data, $data);
        } else {
            $data = self::$data;
        }

        self::reset();

        if ($view !== null) {

            extract($data);

            if ($layout !== null) {

                ob_start();
                require $view;
                $view = ob_get_clean();
                $body = new Body();

                if (preg_match('#<body\b([^>]*)>(.*?)</body>#si', $view, $bodymatches) !== 0)
                {
                    $body[] = (isset($bodymatches[2])) ? $bodymatches[2] : '';

                    if (preg_match_all(
                        '#(\w+)="([^"].*?)"#s',
                        $bodymatches[1],
                        $attributematches,
                        PREG_PATTERN_ORDER) !== 0) {

                        $count = count($attributematches[0]);

                        for($i = 0; $i < $count; $i++) {
                            $body[$attributematches[1][$i]] = $attributematches[2][$i]; 
                        }
                    }

                    if (preg_match_all(
                        '#(\w+)=\'([^\'].*?)\'#s',
                        $bodymatches[1],
                        $attributematches,
                        PREG_PATTERN_ORDER) !== 0) {

                        $count = count($attributematches[0]);

                        for($i = 0; $i < $count; $i++) {
                            $body[$attributematches[1][$i]] = $attributematches[2][$i];
                        }
                    }

                } else {
                    $body[] = $view;
                }

                if (preg_match('#<title\b[^>]*>(.*?)</title>#si', $view, $titlematches) !== 0) {
                    $view = new View($body, $titlematches[1]);
                } else {
                    $view = new View($body);
                }
                
                Layout::decorate($view, $layout);
                
            } else {
                require $view;
            }
        }
    }

    private static function reset() {
        self::$view = null;
        self::$data = array();
    }
}