<?php
get('/', function() {
    if (authenticated) {
        $view = new view('views/main.phtml');
        $view->title = 'Etusivu';
        $view->menu = 'main';
        $view->news = db\news\all();
        $view->online = db\users\visited(username, 'Etusivu');
        echo $view;
    } else {
        $view = new view('views/login.phtml');
        echo $view;
    }
});
post('/', function() {
    $form = new form($_POST);
    $form->username->filter('trim', length(2, 15), '/^[a-z0-9åäö_-]+$/ui', specialchars());
    $form->password->filter(length(6, 20), 'password');
    if ($form->validate() && db\users\login($form->username, $form->password)) {
        login($form->username->value, isset($_POST['remember']));
        redirect('~/');
    }
    $view = new view('views/login.phtml');
    $view->invalid = true;
    echo $view;
});
post('/login/google', function() {
    $_SESSION['login-google'] = 'login';
    $xrds = openid_discover('https://www.google.com/accounts/o8/id');
    openid_authenticate($xrds->getUrl(), array(
        'openid.return_to' => url('~/login/google', true),
        'openid.ns.ui' => 'http://specs.openid.net/extensions/ui/1.0',
        'openid.ui.icon' => 'true'
    ));
});
get('/login/google', function() {
    if (isset($_SESSION['login-google'])) {
        $login = $_SESSION['login-google'];
        unset($_SESSION['login-google']);
        $form = new form($_GET);
        $form->openid_claimed_id->filter('url');
        $form->openid_op_endpoint->filter('url');
        if ($form->validate() && openid_check($form->openid_op_endpoint)) {
            $claim = password($form->openid_claimed_id);
            $username = db\users\claimed($claim);
            if ($username !== false) {
                login($username, true);
                redirect('~/');
            }
            if ($login == 'login') {
                flash('google-not-registered', true);
                redirect('~/registration');
            } elseif ($login == 'registration') {
                $form->openid_ext1_value_email->filter('email');
                $form->openid_ext1_value_firstname->filter('trim', preglace('/[^a-z0-9åäö_-]/ui', ''));
                if ($form->validate()) {
                    $_SESSION['google-claim'] = $claim;
                    $_SESSION['google-fname'] = $form->openid_ext1_value_firstname->value;
                    $_SESSION['google-email'] = $form->openid_ext1_value_email->value;
                    redirect('~/registration/google/confirm');
                }
            }
        } elseif ($login == 'registration') {
            redirect('~/registration');
        }
    }
    redirect('~/');
});
get('/logoff', function() {
    logoff();
    redirect('~/');
});
get('/unauthorized', function() {
    status(401);
    logoff();
    $view = new view('views/unauthorized.phtml');
    echo $view;
   
});
route('/error', function() {
    status(500);
    if (defined('authenticated') && authenticated) {
        $view = new view('views/error.main.phtml');
        $view->title = 'Sivulla tapahtui virhe';
        $view->menu = 'error';
        echo $view;
    } else {
        $view = new view('views/error.login.phtml');
        echo $view;
    }
});