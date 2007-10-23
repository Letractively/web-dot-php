<?php

class View {

    /* =======================================================================
     * Properties
     * ======================================================================= */

    // Static Properties
    private static $view = null;
    private static $data = array();

    // Instance Properties
    public $raw = null;
    public $head = null;
    public $title = null;
    public $body = null;

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    public function __construct($head, $body, $raw = null) {
        $this->head = $head;
        $this->title = $head->title;
        $this->body = $body;
        $this->raw = $raw;
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

        if ($view !== null) {

            extract(self::$data);

            if ($data !== null && is_array($data)) {
                extract($data);
            }

            if ($layout !== null) {

                ob_start();
                require $view;
                $view = ob_get_clean();
                $head = new Head();
                $body = new Body();

                if (preg_match('#<body\b([^>]*)>(.*?)</body>#si', $view, $bodymatches) !== 0)
                {
                    $body->content = (isset($bodymatches[2])) ? $bodymatches[2] : '';

                    if (preg_match_all(
                        '#(\w+)=["\'](.*?)["\']#s',
                        $bodymatches[1],
                        $attributematches,
                        PREG_PATTERN_ORDER) !== 0) {

                        $count = count($attributematches[0]);

                        for($i = 0; $i < $count; $i++) {
                            $body->attributes[$attributematches[1][$i]] = $attributematches[2][$i]; 
                        }
                    }
                }

                if (preg_match('#<head\b[^>]*>(.*?)</head>#si', $view, $headmatches) !== 0) {

                    $head->content = $headmatches[1];

                    if (preg_match('#<title\b[^>]*>(.*?)</title>#si', $head->content, $titlematches) !== 0) {
                        $head->title = $titlematches[1];
                    }
                }

                $view = new View($head, $body, $view);
                Layout::decorate($view, $layout);
                Layout::reset();

            } else {
                require self::$view;
            }
        }

        View::reset();
    }

    public static function reset() {
        self::$view = null;
        self::$data = array();
    }
}