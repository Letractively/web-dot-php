<?php
error_reporting(-1);

define('database', '../data/phpbetz.sq3');
define('secret', 'Replace this on a production server.');
define('DATE_SQLITE', 'Y-m-d\TH:i:s');

date_default_timezone_set('Europe/Helsinki');
setlocale(LC_ALL, 'fi_FI.utf8');

require 'db.install.php';

db\install\tables();
db\install\views();
db\install\teams();
db\install\games();

//db\install\views();
db\install\admins();
//db\install\add_draw_to_games();
//db\install\add_paid_to_users();
