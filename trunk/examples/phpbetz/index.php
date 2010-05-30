<?php
$starttime = microtime(true);
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Helsinki');

require 'lib/config.php';
require 'lib/utils.php';
require 'lib/web.php';
require 'lib/db.php';
require 'lib/controllers.php';

authenticate();
dispatch();