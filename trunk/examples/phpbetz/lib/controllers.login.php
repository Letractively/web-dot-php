<?php

get('/', function() {
    $view = new view('views/login.phtml');
    echo $view;
});

get('/registration', function() {
    $view = new view('views/registration.phtml');
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