class Zone {

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Properties
     * ======================================================================= */

    private static $zones = array();
    private static $zone = null;

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function write($zone) {

        self::$zone = $zone;

        if (!isset(self::$zones[$zone])) {
            self::$zones[$zone] = array();
        }

        array_push(self::$zones[$zone], null);
        ob_start();
    }

    public static function flush() {

        if (self::$zone !== null) {
            array_pop(self::$zones[self::$zone]);
            array_push(self::$zones[self::$zone], ob_get_clean(););
            self::$zone = null;
        }
        
    }

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
