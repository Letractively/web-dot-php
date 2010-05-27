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
require 'db.php';
require 'proposals/web.openid.php';

get('/', function() {
    $view = new view('views/login.phtml');
    echo $view;
});

get('/registration', function() {
    $view = new view('views/registration.phtml');
    echo $view;
});


get('/admin/news', function() {
    $view = new view('views/admin.news.phtml');
    echo $view;
});

get('/news', function() {
    $db = new db();
    $view = new view('views/news.phtml');
    $view->news = $db->news->all();
    echo $view;
});

post('/admin/news', function() {
    $form = new form($_POST);
    $form->slug($form->title)->filter('slug');
    $form->content->filter('trim', minlength(1), 'encode', 'links', 'smileys');
    if($form->validate()) {
        $db = new db();
        $db->news->add($form->title, $form->content, $form->level, 'bungle', $form->slug);
        redirect('~/news');
    }
});

get('/stats', function() {
    $view = new view('views/oldstats.phtml');
    echo $view;
});

get('/rules', function() {
    $view = new view('views/rules.phtml');
    echo $view;
});

get('/bets/games', function() {
    $db = new db();
    $view = new view('views/bets.games.phtml');
    $view->games = $db->bets->games('bungle');
    echo $view;
});

post('/bets/games/#game', function($game) {
    echo json_encode(array('game' => $game, 'score' => $_POST['score']));
});

get('/chat', function() {
    $db = new db();
    $msgs = new view('views/chat.messages.phtml');
    $msgs->messages = $db->chat->poll(17);
    $view = new view('views/chat.phtml');
    $view->messages = $msgs;
    echo $view;
});

post('/chat', function() {
    $form = new form($_POST);
    $form->chatmessage->filter('trim', minlength(1), 'encode', 'links', 'smileys');
    if ($form->validate()) {
        $db = new db();
        $db->chat->post('bungle', $form->chatmessage);
    }
});

get('/chat/poll', function() {
    $db = new db();
    $view = new view('views/chat.messages.phtml');
    $view->messages = $db->chat->poll(17);
    echo $view;
});

get('/admin/teams', function() {
    $db = new db();
    $view = new view('views/admin.teams.phtml');
    $view->teams = $db->teams->all();
    echo $view;
});

post('/teams', function() {
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

get('/install', function() {
    $db = new db();
    $db->install->tables();
    $db->install->teams();
    $db->install->games();
});

dispatch();