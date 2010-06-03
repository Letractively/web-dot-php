<?php
error_reporting(-1);

define('starttime', microtime(true));
define('database', 'data/phpbetz.sq3');
define('secret', 'Replace this on a production server.');
define('DATE_SQLITE', 'Y-m-d\TH:i:s');

date_default_timezone_set('Europe/Helsinki');
setlocale(LC_ALL, 'fi_FI.utf8');

session_start();
require 'lib/utils.php';
require 'lib/web.php';
require 'lib/db.php';
require 'lib/controllers.php';
authenticate();
dispatch();