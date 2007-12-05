<?php
/*
$Id$

Class: Zone

    Zone class is used to define and render placeholders in views
    and layouts.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
*/
class Zone {

    private function __construct() {}
    private static $zones = array();
    private static $zone = null;

    /*
    Function: write

        Opens a zone for writing.

    Parameters:

        string $zone - Name of the zone to open a write buffer for.

    Returns:

        true  - If write buffer was successfully opened.
        false - If there was an error opening write buffer.

    See also:

        <flush>,
        <render>,
        <View::render>,
        <Layout::decorate>

    Example:

        > Zone::write('left-zone');
    */
    public static function write($zone) {

        self::$zone = $zone;

        if (!isset(self::$zones[$zone])) {
            self::$zones[$zone] = array();
        }

        array_push(self::$zones[$zone], null);
        return ob_start();
    }

    /*
    Function: flush

        Flushes previous write buffer and stores it's data,
        that can later be rendered with <render> method.

    Parameters:

        None.

    Returns:

        No value is returned.

    See also:

        <write>,
        <render>,
        <View::render>,
        <Layout::decorate>

    Example:

        > Zone::flush();
    */
    public static function flush() {

        if (self::$zone !== null) {
            array_pop(self::$zones[self::$zone]);
            array_push(self::$zones[self::$zone], ob_get_clean());
            self::$zone = null;
        }
        
    }

    /*
    Function: render

        Renders a zone.

    Parameters:

        string $zone - Name of the zone to render.

    Returns:

        true  - if there was data written on zone.
        false - if there was nothing to render on zone.

    See also:

        <write>,
        <flush>,
        <View::render>,
        <Layout::decorate>

    Example:

        > <?php if (!Zone::render('left-zone')): ?>
        > Default content.
        > <?php endif; ?>
    */
    public static function render($zone) {

        if (isset(self::$zones[$zone]) && count(self::$zones[$zone]) > 0) {

            while (($data = array_shift(self::$zones[$zone])) != null) {
                echo $data;
            }

            return true;

        } else {
            return false;
        }
    }
}
