<?php
/*
Class: View

    View functionality. Views are templates that are rendered to a browser.
    
About: Version

    $Id$

About: License

    This file is licensed under the MIT.
*/
class View {

    private static $view = null;
    private static $data = array();

    public $title = null;
    public $body = null;

    /*
    Contruct: __contruct

        Instantiates a new view object.

    Parameters:

        string $body  - Textual body representation.
        string $title - Textual title representation, optional and defaults to null.
    */
    public function __construct($body, $title = null) {
        $this->body = $body;
        $this->title = $title;
    }

    /*
    Function: set

        Sets a view to be rendered or a view variable identified by key.

    Parameters:

        $key   - If value is omitted,  $key is a view to be rendered, otherwise
                 this is an identifier for a view variable defined by value.
        $value - Value of a view variable, optional and defaults to null.

    Returns:

        No value is returned.

    See also:

        <get>,
        <has>,
        <render>

    Examples:

        > // This is how to set a view variable ($user):
        >
        > View::set('user', new User('John', 'Doe'));
        >
        > // And this is how to set a view that is rendered with render():
        >
        > View::set('views/display-user.php');
    */
    public static function set($key, $value = null) {
        if ($value === null) {
            self::$view = $key;            
        } else {
            self::$data[$key] = $value;
        }
    }

    /*
    Function: get

        Gets a view to be rendered or a value identified by a key.

    Parameters:

        $key - Key of the value we're getting, optional and defaults to null.

    Returns:

        A view to be rendered or an associated value for a key, or null.

    See also:

        <get>,
        <has>,
        <render>

    Examples:

        > // This is how to get a view to be rendered:
        >
        > $view = View::get();
        >
        > // If you need to get a variable identified by a key, use this syntax:
        >
        > $user = View::get('user');
    */
    public static function get($key = null) {
        if ($key === null) {
            return self::$view;
        } else {
            return (isset(self::$data[$key])) ? self::$data[$key] : null; 
        }
    }

    /*
    Function: has

        Checks wheter a key has a value.

    Parameters:

        $key - Key for a value we're trying to check, optional and defaults to null.

    Returns:

        true  - if a view is defined or a value identified with key exists.
        false - if none of the above is true.

    See also:

        <get>,
        <has>,
        <render>

    Examples:

        > // Let's see if a view is defined:
        >
        > if (!View::has()) {
        >     View::set('views/display-user.php');
        > }
        >
        > // Let's see if a view variable is defined:
        >
        > if (!View::has('user')) {
        >     View::set('user', new User('John', 'Doe'));
        > }
    */
    public static function has($key = null) {
        if ($key === null) {
            return (isset(self::$view));
        } else {
            return (isset(self::$data[$key]));
        }
    }

    /*
    Function: render

        Renders the view.

    Parameters:

        $view   - Path to the view file to be rendered, eg. (views/filename.php), defaults to null.
        $data   - Data to be passed to the actual view. Type Array, eg. array('key' => 'value'), defaults to null.
        $layout - Path to the layout file that wraps the view (if wanted). Defaults to null.

    Examples:
        
        >  // Render a view with a variable and a layout
        >  View::render('views/view.php', array('variable' => $variable), 'layouts/default.php');

    */
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

            $isphp = ((stristr(substr($view, -4), '.php')) === false) ? false : true;

            extract($data);

            if ($layout !== null) {

                if ($isphp) {
                    ob_start();
                    require $view;
                    $view = ob_get_clean();
                } else {
                    $view = file_get_contents($view);
                }

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
                        "#(\w+)='([^'].*?)'#s",
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
                if ($isphp) {
                    require $view;
                } else {
                    readfile($view);
                }
            }
        }
    }

    /*
    Function: reset

        Resets the view.

    */
    private static function reset() {
        self::$view = null;
        self::$data = array();
    }
}
