<?php
define('starttime', microtime(true));
define('DATE_SQLITE', 'Y-m-d\TH:i:s');
error_reporting(-1);
date_default_timezone_set('Europe/Helsinki');
session_start();
require 'lib/config.php';
require 'lib/utils.php';
require 'lib/web.php';
require 'lib/db.php';
require 'lib/controllers.php';
authenticate();
dispatch();