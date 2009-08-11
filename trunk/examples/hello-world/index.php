<?php

$starttime = microtime(true);

/* =======================================================================
 * Setup Default Timezone
 * ======================================================================= */

date_default_timezone_set('Europe/Helsinki');

/* =======================================================================
 * Setup Additional Include Paths and Register Class Auto Loader
 * ======================================================================= */

set_include_path('..' . DIRECTORY_SEPARATOR .
                 '..' . DIRECTORY_SEPARATOR .
                 'src' . PATH_SEPARATOR . get_include_path());

/* =======================================================================
 * Include Required Files
 * ======================================================================= */

require 'Router.php';
require 'Web.php';
require 'View.php';
require 'Form.php';

/* =======================================================================
 * Enable Error Handling
 * ======================================================================= */

error_reporting(E_ALL | E_STRICT);

//set_error_handler('Error::handleError');
//set_exception_handler('Error::handleException');

/* =======================================================================
 * Include Controller Files (you can also use spl_autoload)
 * ======================================================================= */

require 'controllers/IndexController.php';

/* =======================================================================
 * Dispatch Request
 * ======================================================================= */

$router = new Router();
$router->add('/', 'IndexController->index');
$router->add(array('/d' => 'IndexController->isndex'));

//print_r($router->routes); die;
Web::run($router->route());