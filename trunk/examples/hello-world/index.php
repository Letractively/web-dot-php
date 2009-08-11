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

require 'Web.php';
require 'View.php';
require 'Form.php';

/* =======================================================================
 * Enable Error Handling
 * ======================================================================= */

error_reporting(E_ALL | E_STRICT);

/* =======================================================================
 * Dispatch Request
 * ======================================================================= */

get('footer.html');

get('/:action/:param', function ($action, $param) {
    
    $form = new Form();
    $form->email->filters = array('email', '/^aapo/');
    $form->email = 'aapo.laakkonen@gmail.com';

    $view = new View('views/index.phtml');
    $view->header = 'Lorem Ipsum ' . $action . ' ' . $param;
    $view->body = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
    $view->form = $form;

    echo $view;
});

route('IndexController->notfound');
//run();

//web\run('404.php');