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
class View implements Iterator {

    private $_file;
    private $_view;
    private $_data;
    private $_output;
    private $_parsed;
    private $_iterator;
    
    public function __construct($file, $view = null) {
        $this->_file = $file;
        $this->_data = array();
        $this->_output = '';
        $this->_parsed = false;
        $this->_view = $view;
    }

    public function rewind() {
        $this->_iterator = $this;
    }

    public function current() {
        return $this->_iterator;
    }

    public function key() {
        return $this->_iterator->_file;
    }

    public function next() {
        $this->_iterator = $this->_iterator->_view;
    }

    public function valid() {
        return isset($this->_iterator);
    }

    public function title($separator = ' &lt; ') {
        $titles = array();
        foreach ($this as $view) {
            if (!isset($view->title)) continue;
            array_unshift($titles, $view->title);
        }
        echo implode($separator, $titles);
    }

    public function content() {
        echo $this->_view;
    }

    public static function url($src) {
        echo View::src($src);
    }

    public static function src($src) {
        return ((parse_url($src, PHP_URL_SCHEME) == null) && (strpos($src, '/') !== 0)) ? substr($_SERVER['SCRIPT_NAME'], 0, -9) . $src : $src;
    }

    public static function javascript($src) {
        printf("<script type=\"text/javascript\" src=\"%s\"></script>%s", View::src($src), PHP_EOL);
    }

    public function javascripts() {

        $javascripts = array();

        foreach ($this as $view) {

            if (!isset($view->javascripts)) continue;

            $js = $view->javascripts;

            if (!is_array($js)) $js = array($js);

            foreach ($js as $javascript) {
                $url = $this->src($javascript);
                if (in_array($url, $javascripts)) continue;
                $javascripts[] = $url;
            }
        }

        array_walk($javascripts, 'View::javascript');
    }

    public static function stylesheet($src) {
        printf("<link rel=\"stylesheet\" href=\"%s\" type=\"text/css\" />%s", View::src($src), PHP_EOL);
    }

    public function stylesheets() {

        $stylesheets = array();

        foreach ($this as $view) {

            if (!isset($view->stylesheets)) continue;

            $css = $view->stylesheets;

            if (!is_array($css)) $css = array($css);

            foreach ($css as $stylesheet) {
                $url = $this->src($stylesheet);
                if (in_array($url, $stylesheets)) continue;
                $stylesheets[] = $url;
            }
        }

        array_walk($stylesheets, 'View::stylesheet');
    }

    public function embeds($section = null) {

        static $currentSection = null;

        if ($section != null) {
            foreach($this as $view) {
                $hide = $view->hide;
                if (!isset($hide)) continue;
                $hidden = (is_array($hide)) ? in_array($section, $hide) : $section == $hide;
                if ($hidden) return false;
            }
            $currentSection = $section;
            return true;
        }

        if ($currentSection == null) return;

        $embedded = array();
        
        foreach($this as $view) {
            $embeds = $view->embeds;
            if (!is_array($embeds) || !array_key_exists($currentSection, $embeds)) continue;
            $file = $embeds[$currentSection];
            if (in_array($file, $embedded)) continue;
            $embedded[] = $file;
            Web::run($file);
        }

        $currentSection = null;
    }

    public function __set($key, $value) {
        $this->_data[$key] = $value;
    }

    public function __get($key) {
        return (array_key_exists($key, $this->_data)) ? $this->_data[$key] : null;
    }

    public function __isset($key) {
        return isset($this->_data[$key]);
    }

    public function __unset($key) {
        unset($this->_data[$key]);
    }

    public function __toString() {
        if ($this->_parsed) return $this->_output;
        ob_start();
        extract($this->_data);
        require $this->_file;
        $this->_parsed = true;
        return $this->_output = ob_get_clean();
    }

    public function render() {
        $output = $this->__toString();
        if ($this->layout == null) {
            echo $output;
        } else {
            $view = new View($this->layout, $this);
            $view->render();
        }
    }

    public function parse() {
        ob_start();
        $this->render();
        return ob_get_clean();
    }
}
