<?php

get('/', function() {
    $view = new view('views/login.phtml');
    echo $view;
});

post('/', function() {
    $form = new form($_POST);
    $form->username->filter('trim', length(2, 15), '/^[a-z0-9åäö_-]+$/ui', 'encode');
    $form->password->filter(length(6, 20), 'password');
    if ($form->validate() && $db->users->login($form->username, $form->password)) {
        redirect('~/news');
    }
    $view = new view('views/login.phtml');
    $view->invalid = true;
    echo $view;
});

get('/registration', function() {
    session();
    $view = new view('views/registration.phtml');
    if (isset($_SESSION['google-not-registered'])) $view->google_not_registered = true;
    $view->form = new form;
    echo $view;
});

post('/registration', function() {
    $form = new form($_POST);
    $form->username->filter('trim', length(2, 15), '/^[a-z0-9åäö_-]+$/ui', 'encode');
    $form->password1->filter(length(6, 20), equal($form->password2->value));
    $form->password2->filter(length(6, 20), equal($form->password1->value));
    $form->email->filter('trim', 'email', 'encode');
    $view = new view('views/registration.phtml');
    $db = new db;
    try {
        if ($form->validate()) {
            $db->users->register($form->username, password($form->password1), $form->email);
            redirect('~/news');
        }
    } catch (PDOException $e) {
        if ($e->getCode() != 23000) throw $e;
        if ($db->users->username_taken($form->username)) $view->username_taken = true;
        if ($db->users->email_taken($form->email)) $view->email_taken = true;
    }
    
    $view->form = $form;
    echo $view;
});

post('/registration/google', function() {
    session();
    $_SESSION['login-google'] = 'registration';
    $xrds = openid_discover('https://www.google.com/accounts/o8/id');
    openid_authenticate($xrds->getUrl(), array(
        'openid.return_to' => url('~/login/google', true),
        'openid.ns.ui' => 'http://specs.openid.net/extensions/ui/1.0',
        'openid.ui.icon' => 'true',
        'openid.ns.ax' => 'http://openid.net/srv/ax/1.0',
        'openid.ax.mode' => 'fetch_request',
        'openid.ax.required' => 'firstname,email',
        'openid.ax.type.email' => 'http://axschema.org/contact/email',
        'openid.ax.type.firstname' => 'http://axschema.org/namePerson/first'
    ));
});

get('/registration/google/confirm', function() {
    session();
    if (isset($_SESSION['google-claim']) && isset($_SESSION['google-fname']) && isset($_SESSION['google-email'])) {
        $form = new form($_GET);
        $form->username = $_SESSION['google-fname'];
        $form->email = $_SESSION['google-email'];
        unset($_SESSION['google-fname']);
        $view = new view('views/registration.google.phtml');
        $view->form = $form;
        echo $view;
    } else {
        redirect('~/');
    }
});

post('/registration/google/confirm', function() {
    session();
    if (!isset($_SESSION['google-claim']) || !isset($_SESSION['google-email'])) redirect('~/');
    $claim = $_SESSION['google-claim'];
    $email = $_SESSION['google-email'];
    $form = new form($_POST);
    $form->username->filter('trim', '/^[a-z0-9åäö_-]+$/ui', 'encode');
    $form->email($email);
    $view = new view('views/registration.google.phtml');
    $view->form = $form;
    if ($form->validate()) {
        try {
            $db = new db;
            $db->users->claim($form->username, $claim, $form->email);
            unset($_SESSION['google-claim'], $_SESSION['google-email']);
            redirect('~/news');
        } catch (PDOException $e) {
            if ($e->getCode() != 23000) throw $e;
            $view->username_taken = true;
        }
    }
    echo $view;
});

post('/login/google', function() {
    session();
    $_SESSION['login-google'] = 'login';
    $xrds = openid_discover('https://www.google.com/accounts/o8/id');
    openid_authenticate($xrds->getUrl(), array(
        'openid.return_to' => url('~/login/google', true),
        'openid.ns.ui' => 'http://specs.openid.net/extensions/ui/1.0',
        'openid.ui.icon' => 'true'
    ));
});

get('/login/google', function() {
    session();
    if (isset($_SESSION['login-google'])) {
        $login = $_SESSION['login-google'];
        unset($_SESSION['login-google']);
        $form = new form($_GET);
        $form->openid_claimed_id->filter('url');
        $form->openid_op_endpoint->filter('url');
        if ($form->validate() && openid_check($form->openid_op_endpoint)) {
            $claim = password($form->openid_claimed_id);
            $db = new db;
            if ($db->users->claimed($claim)) {
                $db = null;
                redirect('~/news');
            }
            $db = null;
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
        } else {
            if ($login == 'registration') {
                redirect('~/registration');
            }
        }
    }
    redirect('~/');
});