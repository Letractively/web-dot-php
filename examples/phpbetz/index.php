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
 * Enable Error Handling
 * ======================================================================= */

error_reporting(E_ALL | E_STRICT);

/* =======================================================================
 * Dispatch Request
 * ======================================================================= */
require 'web.php';
require 'proposals/web.openid.php';

get('/', function() {
    $view = new view('views/index.phtml');
    echo $view;
});

post('/login', function() {
    $xrds = openid\discover('https://www.google.com/accounts/o8/id');
    openid\authenticate($xrds->XRD->Service->URI, array(
        'openid.return_to' => url('~/login/check', true),
        'openid.ns.ax' => 'http://openid.net/srv/ax/1.0',
        'openid.ax.mode' => 'fetch_request',
        'openid.ax.required' => 'email',
        'openid.ax.type.email' => 'http://axschema.org/contact/email'
    ));
});

get('/login/check', function() {
    if (openid\check($_GET['openid_op_endpoint'])) {
        echo 'You have been logged in!';
    }
});

get('/bets/games', function() {
   $view = new view('views/bets.games.phtml');
   echo $view;
});

post('/bets/games/#game', function($game) {
    echo json_encode(array('game' => $game, 'score' => $_POST['score']));
});

get('/admin/teams', function() { 

});

post('/admin/teams', function() {

    $form = new form();
    $form->email->filters('required');
    $form->email->validate();
    echo $form->email;
    $form->email();
    echo $form->email('default value');

    $form->validate()


    validate('name', array('required'));


    validate($_POST, array('name' => array('')));

});

get('/admin/games', function() {

});

post('/admin/games', function() {

});

get('/admin/games', function() {

});

post('/admin/games', function() {

});



dispatch();