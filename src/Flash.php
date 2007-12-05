<?php
/*
$Id$

Class: Flash

    Handles sessionwide variables. Used for example to notify the user about form action, ie.
    was the operation executed succesfully.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
*/
class Flash {

    private function __construct() {}


    /*
    Function: set

        Sets a session variable.

    Parameters:

        string $key   - name of the variable.
        string $value - value of the variable.
        string $hops  - number of requests the variable is available.

    See also:
        <get>,
        <has>,
        <remove>

    Examples:

        > // sets a session wide variable that is valid for two requests
        > Flash::set('reminder', 'Brush your teeth twice a day!', 2);
        > // in the view
        > if (Flash::has('reminder')):
        >   echo "<div class='notice'>" . Flash::get('reminder') . "</div>";
        > endif;

    */

    public static function set($key, $value, $hops = 1) {
        if (self::has()) {
            $flashes = self::get();
        } else {
            $flashes = array();
        }

        $flashes[$key] = array();
        $flashes[$key]['value'] = $value;
        $flashes[$key]['hops'] = $hops;

        Session::set('urn:web.php:flash', $flashes);
    }

    /*
    Function: get

        Returns the specified session variable, if one is set.

    Parameters:
        
        string $key - name of the variable.

    Returns:

        The variable if it is set.

    See also:

        <set>,
        <has>,
        <remove>

    Examples:
        
        > // gets the specified variable
        > if (Flash::has('reminder')) {
        >     $reminder = Flash::get('reminder');
        > }
    */
    public static function get($key = null) {
        if ($key === null) {
            return Session::get('urn:web.php:flash');
        } else if (self::has($key)) {
            $flashes = Session::get('urn:web.php:flash');
            return $flashes[$key]['value'];
        } else {
            return null;
        }
    }

    /*
    Function: has
        
        Checks whether a session variable by this name is set.

    Parameters:

        string $key - name of the variable.

    Returns:

        true - if the variable is set.
        false - if the varaible is not set.

    See also:

        <get>,
        <set>,
        <remove>

    Examples:

        > // checks if a variable is set
        > if (Flash::has('reminder')) {
        >    echo "Thank you, I have been reminded.";
        > } else {
        >    echo "I haven't been reminded.";
        > }
    */
    public static function has($key = null) {
        if ($key === null) {
            return (Session::has('urn:web.php:flash'));
        } else if (Session::has('urn:web.php:flash')) {
            $flashes = Session::get('urn:web.php:flash');
            return (isset($flashes[$key]));
        } else {
            return false;
        }
    }

    /*
    Function: remove

        Removes the specified session variable.

    Parameters:

        string $key - name of the variable.

    See also:
        
        <get>,
        <set>,
        <has>

    Examples:

        > // removes a variable from session (let's assume the variable was set with 2 hops)
        > if (Flash::has('reminder')) {
        >    echo "Reminder: " . Flash::get('reminder');
        >    // one reminder is enough
        >    Flash::remove('reminder');
        > }
    */
    public static function remove($key = null) {
        if ($key === null) {
            Session::remove('urn:web.php:flash');
        } else {

            $flashes = self::get();

            if (isset($flashes[$key])) {
                unset($flashes[$key]);
                Session::set('urn:web.php:flash', $flashes);
            }
        }
    }

    /*
    Function: shutdown

        Counts the remaining hops?

    See also:
        
        <get>,
        <set>,
        <has>,
        <remove>

    */

    public static function shutdown() {

        if (self::has()) {

            $flashes = self::get();

            foreach ($flashes as $key => $flash) {

                --$flash['hops'];

                if ($flash['hops'] < 0) {
                    unset($flashes[$key]);
                } else {
                    $flashes[$key] = $flash;
                }

            }

            if (count($flashes) > 0) {
                Session::set('urn:web.php:flash', $flashes);
            } else {
                Session::remove('urn:web.php:flash');
            }
        }
    }
}
