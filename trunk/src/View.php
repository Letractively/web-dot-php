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

    private $_file;

    public function __construct($file) {
        $this->_file = $file;
        $this->blocks = new Blocks();
    }

    public function __call($name, $args) {
        if (isset($this->$name)) return print $this->$name;
        trigger_error('Undefined method "' . $name . '".');
    }

    public function __toString() {
        ob_start();
        require $this->_file;

        while (isset($this->layout)) {
            $this->view = ob_get_clean();
            ob_start();
            $this->_file = $this->layout;
            unset($this->layout);
            require $this->_file;
        }

        return ob_get_clean();
    }

    public function render() { echo strval($this); }
    public function partial($route) { Web::run($route); }
    public static function url($src) { echo View::src($src); }
    public static function src($src) { return ((parse_url($src, PHP_URL_SCHEME) == null) && (strpos($src, '/') !== 0)) ? substr($_SERVER['SCRIPT_NAME'], 0, -9) . $src : $src; }
}

class Blocks {
    public function __call($name, $args) {
        if (isset($this->$name)) return print $this->$name;
        trigger_error('Undefined method "' . $name . '".');
    }
    public function __get($name) {
        if (!isset($this->$name)) $this->$name = new Block();
        return $this->$name;
    }
}

class Block implements ArrayAccess, Countable {
    private $_output, $_mode, $_offset;
    public function __construct() { $this->_output = array(); $this->_mode = -1; }
    public function offsetSet($offset, $value) { $this->_output[$offset] = $value; }
    public function offsetExists($offset) { return isset($this->_output[$offset]); }
    public function offsetUnset($offset) { unset($this->_output[$offset]); }
    public function offsetGet($offset) { return isset($this->_output[$offset]) ? $this->_output[$offset] : null; }
    public function count() { count($this->_output); }
    public function start() { $this->_mode = 0; ob_start(); }
    public function append() { $this->_mode = 1; ob_start(); }
    public function prepend() { $this->_mode = 2; ob_start(); }
    public function insert($offset) { $this->_mode = 3; $this->_offset = $offset; ob_start(); }
    public function flush() {
        if ($this->_mode == -1) trigger_error('Flush-method can only be called after calling start, append, prepend, or insert.', E_USER_WARNING);
        switch ($this->_mode) {
            case 0: $this->_output = array(ob_get_clean()); break;
            case 1: $this->_output[] = ob_get_clean(); break;
            case 2: array_unshift($this->_output, ob_get_clean()); break;
            case 3: array_splice($this->_output, $this->_offset, 0, ob_get_clean()); break;
        }
        $this->_mode = -1;
    }
    public function __toString() { return implode($this->_output); }
}
