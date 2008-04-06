<?php
/**
$Id$

Class: Helper

    Helper retrieving and registration functionlity.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class Helper {
    
    private static $helpers = array();

    private function __construct() {}

    /**
    Function: register

        Registers a helper.

    Parameters:

        string $key    - Name of the helper.
        string $helper - This specifies which helper is to be registred.

    Examples:
    
    	> Helper::register('include', 'IncludeHelper');
		> Helper::register('url', 'UrlHelper::url');
     */
    public static function register($key, $helper = null) {
        if ($helper === null) {
            self::$helpers[$key] = array('helper' => $key, 'loaded' => false);
        } else {
            self::$helpers[$key] = array('helper' => $helper, 'loaded' => false);
        }
    
    }

    /**
    Function: import
    
		Imports a helper.

    Parameters:

        string $key - Name of the helper used when registering it.

    Examples:
    
		> $url = Helper::import('url');
     */
    public static function import($key) {
        
        if (isset(self::$helpers[$key])) {
            
            if (!self::$helpers[$key]['loaded']) {
                
                $class = self::$helpers[$key]['helper'];
                $method = null;
                
                if (strpos($class, '::') !== false) {
                    
                    $class = explode('::', $class, 2);
                    $method = $class[1];
                    $class = $class[0];
                    
                    $helper = create_function(null, '$args = func_get_args(); return call_user_func_array(array("' . $class . '", "' . $method . '"), $args);');
                
                } else {
                    $class = new ReflectionClass($class);
                    $helper = $class->newInstance();
                }
                
                self::$helpers[$key]['helper'] = $helper;
                self::$helpers[$key]['loaded'] = true;
            }
            
            return self::$helpers[$key]['helper'];
        
        } else {
            throw new Exception(sprintf('Helper with key \'%s\' has not been registered.', $key));
        }
    }
}
