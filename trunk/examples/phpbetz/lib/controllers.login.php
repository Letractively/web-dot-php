<?php
get('/', function() {
    if (authenticated) {
        $db = new db;
        $view = new view('views/main.phtml');
        $view->news = $db->news->all();
        $db->close();
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
    $db = new db;
    if ($form->validate() && $db->users->login($form->username, $form->password)) {
        $db->close();
        login($form->username->value, isset($_POST['remember']));
        redirect('~/');
    }
    $db->close();
    $view = new view('views/login.phtml');
    $view->invalid = true;
    echo $view;
});

get('/registration', function() {
    $view = new view('views/registration.phtml');
    if (isset($_SESSION['google-not-registered'])) $view->google_not_registered = true;
    $view->form = new form;
    echo $view;
});

post('/registration', function() {
    $form = new form($_POST);
    $form->username->filter('trim', length(2, 15), '/^[a-z0-9åäö_-]+$/ui', specialchars());
    $form->password1->filter(length(6, 20), equal($form->password2->value));
    $form->password2->filter(length(6, 20), equal($form->password1->value));
    $form->email->filter('trim', 'email', specialchars());
    $view = new view('views/registration.phtml');
    if ($form->validate()) {
        $db = new db;
        $db->users->register($form->username, password($form->password1), $form->email);
        $changes = $db->changes();
        if ($changes === 0) {
            if ($db->users->username_taken($form->username)) $view->username_taken = true;
            if ($db->users->email_taken($form->email)) $view->email_taken = true;
            $db->close();
        } else {
            $db->close();
            login($form->username->value, true);
            redirect('~/');
        }
    }
    $view->form = $form;
    echo $view;
});

post('/registration/google', function() {
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
    if (isset($_SESSION['google-claim']) && isset($_SESSION['google-fname']) && isset($_SESSION['google-email'])) {
        $form = new form($_GET);
        $form->username = $_SESSION['google-fname'];
        $form->email = $_SESSION['google-email'];
        unset($_SESSION['google-fname']);
        $db = new db;
        $view = new view('views/registration.google.phtml');
        $view->form = $form;
        if ($db->users->email_taken($form->email)) $view->email_taken = true;
        $db->close();
        echo $view;
    } else {
        redirect('~/');
    }
});

post('/registration/google/confirm', function() {
    if (!isset($_SESSION['google-claim']) || !isset($_SESSION['google-email'])) redirect('~/');
    $claim = $_SESSION['google-claim'];
    $email = $_SESSION['google-email'];
    $form = new form($_POST);
    $form->username->filter('trim', '/^[a-z0-9åäö_-]+$/ui', specialchars());
    $form->email($email);
    $view = new view('views/registration.google.phtml');
    $view->form = $form;
    if ($form->validate()) {
        $db = new db;
        $db->users->claim($form->username, $claim, $form->email);
        $changes = $db->changes();
        if ($changes === 0) {
            if ($db->users->username_taken($form->username)) $view->username_taken = true;
            if ($db->users->email_taken($form->email)) $view->email_taken = true;
            $db->close();
        } else {
            $db->close();
            unset($_SESSION['google-claim'], $_SESSION['google-email']);
            login($form->username->value, true);
            redirect('~/');
        }
    }
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
            $db = new db;
            $username = $db->users->claimed($claim);
            if ($username !== false) {
                $db->close();
                login($username, true);
                redirect('~/');
            }
            $db->close();
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