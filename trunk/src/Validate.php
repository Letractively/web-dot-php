<?php
/**
$Id$

Class: Validate

    Validation functionality.

About: Version

    $Revision$

About: Author

    $Author$

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
        
        if (strval(intval($value)) != $value) {
            return false;
        }
        
        return true;
    }

    /**
    Function: float
     */
    public static function float($value) {
        
        $value = self::normalizeNumber($value);
        
        if (strval(intval($value)) != $value) {
            return false;
        }
        
        return true;
    }

    /**
    Function: email
     */
    public static function email($value) {
        
        $value = (string) $value;
        
        if (preg_match(';^(?=.{6,256}$)(([a-z0-9!#$%&\'*+\-/=?^_`{|}~]|(?<!^)\.(?!\.|@)){1,64}|^"([\x01-\x08\x0b\x0c\x0e-\x1f\x20\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f]){1,64}")@(?=.{1,255}$)(?!.{1,252}\.([0-9]{2,64}|.)$)([a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?\.){1,126}([a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])$)|\[((((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))|([a-z0-9\-]*[a-z0-9]:(([0-9a-f]{1,4}(:[0-9a-f]{1,4}){7})|([0-9a-f]{1,4}(:[0-9a-f]{1,4}){5}:((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))|(\[((::)|(([0-9a-f]{1,4}:){1,6}:)|((:(:[0-9a-f]{1,4}){1,6})|(([0-9a-f]{1,4}:){1}(:[0-9a-f]{1,4}){1,5})|(([0-9a-f]{1,4}:){2}(:[0-9a-f]{1,4}){1,4})|(([0-9a-f]{1,4}:){3}(:[0-9a-f]{1,4}){1,3})|(([0-9a-f]{1,4}:){4}(:[0-9a-f]{1,4}){1,2})|(([0-9a-f]{1,4}:){5}(:[0-9a-f]{1,4}){1,1})))\])|(\[((::)|(([0-9a-f]{1,4}:){1,4}:)|((:(:[0-9a-f]{1,4}){1,4})|(([0-9a-f]{1,4}:){1}(:[0-9a-f]{1,4}){1,3})|(([0-9a-f]{1,4}:){2}(:[0-9a-f]{1,4}){1,2})|(([0-9a-f]{1,4}:){3}(:[0-9a-f]{1,4}){1,1})):)\]((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)))))\]$;iu', $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    Function: ip
     */
    public static function ip($value) {
        $value = (string) $value;
        return (ip2long($value) !== false);
    }
}
