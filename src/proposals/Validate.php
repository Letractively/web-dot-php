<?php
/**
$Id: Validate.php 237 2008-04-07 07:28:55Z aapo.laakkonen $

Class: Validate

    Validation functionality.

About: Version

    $Revision: 237 $

About: Author

    $Author: aapo.laakkonen $

About: License

    This file is licensed under the MIT.

    Regular expression used in email validation function is
    copyrighted by Simon Slick and used here with permission:

    http://simonslick.com/VEAF/ValidateEmailAddressFormat.html.
 */
class Validate {

    private function __construct() {}

    private static function normalizeNumber($value) {

        $locale = localeconv();

        $value = (string) $value;

        $value = str_replace($locale['decimal_point'], '.', $value);
        $value = str_replace($locale['thousands_sep'], '', $value);

        return $value;
    }

    /**
    Function: presence
     */
    public static function presence($value) {
        $value = (string) $value;
        return (!empty($value));
    }

    /**
    Function: format
     */
    public static function format($value, $pattern) {
        return preg_match($pattern, $value);
    }

    /**
    Function: numericality
     */
    public static function numericality($value) {
        return (self::int($value) || self::float($value));
    }

    /**
    Function: length
     */
    public static function length() {}

    /**
    Function: inclusion
     */

    public static function inclusion() {}

    /**
    Function: exclusion
     */
    public static function exclusion() {}

    /**
    Function: acceptance
     */
    public static function acceptance() {}

    /**
    Function: confirmation
     */
    public static function confirmation() {

    }

    /**
    Function: int
     */
    public static function int($value) {
        $value = self::normalizeNumber($value);
        return (strval(intval($value)) == $value);
    }

    /**
    Function: float
     */
    public static function float($value) {
        $value = self::normalizeNumber($value);
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
    Function: email
     */
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
    Function: ip
     */
    public static function ip($value) {
        $value = (string) $value;
        return (ip2long($value) !== false);
    }
}
