<?php
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
        $changes = db\users\register($form->username, password($form->password1), $form->email);
        if ($changes === 0) {
            if (db\users\username_taken($form->username)) $view->username_taken = true;
            if (db\users\email_taken($form->email)) $view->email_taken = true;
        } else {
            cache_delete('worldcup2010:points');
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
        $view = new view('views/registration.google.phtml');
        $view->form = $form;
        if (db\users\email_taken($form->email)) $view->email_taken = true;
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
        $changes = db\users\claim($form->username->value, $claim, $form->email->value);
        if ($changes === 0) {
            if (db\users\username_taken($form->username->value)) $view->username_taken = true;
            if (db\users\email_taken($form->email->value)) $view->email_taken = true;
        } else {
            unset($_SESSION['google-claim'], $_SESSION['google-email']);
            cache_delete('worldcup2010:points');
            login($form->username->value, true);
            redirect('~/');
        }
    }
    echo $view;
});