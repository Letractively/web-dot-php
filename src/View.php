<?php
/**
$Id$

Class: View

    View functionality. Views are templates that are rendered to a browser.
    
About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class View {

    function __construct($file) {
        $this->file = $file;
    }

    function __toString() {
        extract((array)$this);
        $blocks = new Blocks();
        do {
            ob_start();
            require $file;
            if (!isset($layout)) return ob_get_clean();
            $view = ob_get_clean();
            $file = $layout;
            unset($layout);
        } while (true);
    }

    //public static function partial($route) { Web::run($route); }
    //public static function url($src) { echo self::src($src); }
    //public static function src($src) { return ((parse_url($src, PHP_URL_SCHEME) == null) && (strpos($src, '/') !== 0)) ? substr($_SERVER['SCRIPT_NAME'], 0, -9) . $src : $src; }
}

class Blocks {
    function __call($name, $args) {
        echo isset($this->$name) ? $this->$name : '';
    }
    function __get($name) {
        if (!isset($this->$name)) $this->$name = new Block();
        return $this->$name;
    }
}

class Block implements IteratorAggregate, Countable {
    function __construct() { $this->output = array(); $this->mode = -1; }
    function getIterator() { return new ArrayIterator($this->output); }
    function count() { return count($this->output); }
    function start() { $this->mode = 0; ob_start(); }
    function append() { $this->mode = 1; ob_start(); }
    function prepend() { $this->mode = 2; ob_start(); }
    function insert($offset) { $this->mode = 3; $this->offset = $offset; ob_start(); }
    function flush() {
        if ($this->mode == -1) trigger_error('Flush-method can only be called after calling start, append, prepend, or insert.', E_USER_WARNING);
        switch ($this->mode) {
            case 0: $this->output = array(ob_get_clean()); break;
            case 1: $this->output[] = ob_get_clean(); break;
            case 2: array_unshift($this->output, ob_get_clean()); break;
            case 3: array_splice($this->output, $this->offset, 0, ob_get_clean()); break;
        }
        $this->mode = -1;
    }
    function __toString() { return implode($this->output); }
}
