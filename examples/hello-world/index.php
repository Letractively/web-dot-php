<?php

/* =======================================================================
 * Setup Default Timezone
 * ======================================================================= */

date_default_timezone_set('Europe/Helsinki');

/* =======================================================================
 * Include Required Files
 * ======================================================================= */

require_once 'Zend/Loader.php';

/* =======================================================================
 * Setup Additional Include Paths and Register Class Auto Loader
 * ======================================================================= */

set_include_path('..' . DIRECTORY_SEPARATOR .
                 '..' . DIRECTORY_SEPARATOR . 'src' . PATH_SEPARATOR .
                 'controllers' . PATH_SEPARATOR . get_include_path());
                 
Zend_Loader::registerAutoload();

/* =======================================================================
 * Dispatch Request
 * ======================================================================= */

try {

    Web::run(array('/' => 'IndexController'));

} catch (Exception $e) {

    if ($e->getCode() == 404) {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        View::render('views/404.php');
    } else {
        throw $e;
    }

}