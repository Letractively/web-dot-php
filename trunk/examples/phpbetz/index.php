<?php
error_reporting(-1);

define('starttime', microtime(true));
define('database', 'data/worldcup2010.sq3');
define('secret', 'Replace this on a production server.');
define('ajax', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('install', true);
define('DATE_SQLITE', 'Y-m-d\TH:i:s');
define('LOG_PATH', 'data');
define('LOG_LEVEL', LOG_WARNING);

date_default_timezone_set('Europe/Helsinki');
setlocale(LC_ALL, 'fi_FI.utf8');

session_start();
require 'lib/utils.php';
require 'lib/web.php';
require 'lib/db.php';

set_exception_handler(function(Exception $ex) {
    @error(sprintf('%s [%s:%s]', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
    while(@ob_end_clean());
    if (ajax) {
        status(500);
        exit;
    } else {
        @forward('/error');
    }
});

set_error_handler(function($code, $message, $file, $line, $context) {
    @error(sprintf('%s [%s:%s]', $message, $file, $line));
    while(@ob_end_clean());
    if (ajax) {
        status(500);
        exit;
    } else {
        @forward('/error');
    }
});

require 'lib/controllers.login.php';

authenticate();

if (authenticated) {
    require 'lib/controllers.main.php';
    require 'lib/controllers.bets.php';
    require 'lib/controllers.stats.php';
}

if (admin) {
    require 'lib/controllers.admin.php';
}

if (install) {
    require 'lib/db.install.php';
    require 'lib/controllers.install.php';
}

portlets();
dispatch();