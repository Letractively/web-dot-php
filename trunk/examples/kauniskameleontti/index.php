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
                 'classes' . PATH_SEPARATOR .
                 'controllers' . PATH_SEPARATOR .
                 'models' . PATH_SEPARATOR . get_include_path());
                 
Zend_Loader::registerAutoload();

/* =======================================================================
 * Enable Error Handling
 * ======================================================================= */

error_reporting(E_ALL | E_STRICT);

set_error_handler('Error::handleError');
set_exception_handler('Error::handleException');

/* =======================================================================
 * Dispatch Request
 * ======================================================================= */

//try {

    Session::start();

    Layout::set('layouts/layout.php');

    Helper::register('include', 'IncludeHelper');
    Helper::register('url', 'UrlHelper::url');

    Web::run(array(
        '/' => 'IndexController->GET',
        '/methods' => 'MethodController->index',
        '/methods/add' => 'MethodController->add'
    ));

/*
} catch (Exception $e) {

    if ($e->getCode() == 404) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        View::render('views/404.php');
    } else {
        throw $e;
    }

}
*/
