<?php
/**
$Id$

Class: EchoHelper

    Echo helper for views.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class EchoHelper {

    private function __construct() {}

    /**
    Function: echo

		Echo helper for views using htmlspecialchars.

    Parameters:

        string $string    - String to echo with htmlspecialchars

    Examples:

		> <?php $e('<html> <- with entities'); ?>
     */
    public static function e($string) {
        echo htmlspecialchars($string);
    }
}