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
class View extends ArrayIterator {

    public function __construct($file, $view = null) {
        $this->file = $file;
        $this->view = $view;
    }

    public function __set($name, $value) {
        $this[$name] = $value;
    }

    public function __get($name) {
        if (isset($this[$name])) {
            return $this[$name];
        }

        $trace = debug_backtrace();
        trigger_error('Undefined property "' . $name . '" in "' . $trace[0]['file'] . '" on line ' . $trace[0]['line'], E_USER_NOTICE);
        return null;
    }

    public function __isset($name) {
        return isset($this[$name]);
    }

    public function __unset($name) {
        unset($this[$name]);
    }

    public function __call($name, $args) {
        if (isset($this[$name])) {
            echo $this[$name];
            return;
        }

        $trace = debug_backtrace();
        trigger_error('Undefined method "' . $name . '" in "' . $trace[0]['file'] . '" on line ' . $trace[0]['line'], E_USER_NOTICE);
    }

    public function __toString() {
        if (!isset($this['output'])) {
            ob_start();
            require $this['file'];
            $this['output'] = ob_get_clean();
        }
        return $this['output'];
    }

    public function render() {
        $output = strval($this);
        if (isset($this['layout'])) {
            $view = new View($this['layout'], $this);
            $view->render();
        } else {
            echo $output;
        }
    }

    public function parse() {
        ob_start();
        $this->render();
        return ob_get_clean();
    }

    public function title($separator = ' &lt; ') {
        $titles = array();
        $view = $this;
        do {
            if (isset($view['title'])) array_unshift($titles, $view['title']);
            $view = $view->view;
        } while($view != null);

        echo implode($separator, $titles);
    }

    public static function javascript($src) {
        printf('<script type="text/javascript" src="%s"></script>%s', View::src($src), PHP_EOL);
    }

    public function javascripts() {
        $javascripts = array();
        $view = $this;
        do {
            if (isset($view['javascripts'])) {
                $js = $view['javascripts'];
                if (!is_array($js)) $js = array($js);
                foreach ($js as $javascript) {
                    $url = $this->src($javascript);
                    if (in_array($url, $javascripts)) continue;
                    $javascripts[] = $url;
                }
            }
            $view = $view->view;
        } while($view != null);
        array_walk($javascripts, 'View::javascript');
    }

    public static function stylesheet($src) {
        printf('<link rel="stylesheet" href="%s" type="text/css" />%s', View::src($src), PHP_EOL);
    }

    public function stylesheets() {
        $stylesheets = array();
        $view = $this;
        do {
            if (isset($view['stylesheets'])) {
                $css = $view['stylesheets'];
                if (!is_array($css)) $css = array($css);
                foreach ($css as $stylesheet) {
                    $url = $this->src($stylesheet);
                    if (in_array($url, $stylesheets)) continue;
                    $stylesheets[] = $url;
                }
            }
            $view = $view->view;
        } while($view != null);
        array_walk($stylesheets, 'View::stylesheet');
    }

    public function embeds($section = null) {
        static $currentSection = null;
        if ($section != null) {
            $view = $this;
            do {
                if (isset($view['hide'])) {
                    $hide = $view['hide'];
                    $hidden = (is_array($hide)) ? in_array($section, $hide) : $section == $hide;
                    if ($hidden) return false;
                }
                $view = $view->view;
            } while($view != null);
            $currentSection = $section;
            return true;
        }

        if ($currentSection == null) return;

        $embedded = array();
        $view = $this;
        do {
            if (isset($view['embeds']) && isset($view['embeds'][$currentSection])) {
                $file = $view['embeds'][$currentSection];
                if (!in_array($file, $embedded)) {
                    $embedded[] = $file;
                    ob_start();
                    $retval = Web::run($file);
                    $output = ob_get_clean();
                    echo (isset($output[1])) ? $output : $retval;
                }
            }
            $view = $view->view;
        } while($view != null);
        $currentSection = null;
    }

    public static function url($src) {
        echo View::src($src);
    }

    public static function src($src) {
        return ((parse_url($src, PHP_URL_SCHEME) == null) && (strpos($src, '/') !== 0)) ? substr($_SERVER['SCRIPT_NAME'], 0, -9) . $src : $src;
    }
}
