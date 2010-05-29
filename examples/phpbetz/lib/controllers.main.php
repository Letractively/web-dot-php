<?php

get('/news', function() {
    $db = new db();
    $view = new view('views/news.phtml');
    $view->news = $db->news->all();
    echo $view;
    $db = null;
});

get('/stats', function() {
    $view = new view('views/stats.phtml');
    echo $view;
});

get('/rules', function() {
    $view = new view('views/rules.phtml');
    echo $view;
});

get('/chat', function() {
    $db = new db();
    $msgs = new view('views/chat.messages.phtml');
    $msgs->messages = $db->chat->poll(50);
    $view = new view('views/chat.phtml');
    $view->messages = $msgs;
    echo $view;
    $db = null;
});

post('/chat', function() {
    $form = new form($_POST);
    $form->chatmessage->filter('trim', minlength(1), 'encode', 'links', 'smileys');
    if ($form->validate()) {
        $db = new db();
        $db->chat->post('bungle', $form->chatmessage);
        $db = null;
    }
});

get('/chat/poll', function() {
    $db = new db();
    $view = new view('views/chat.messages.phtml');
    $view->messages = $db->chat->poll(50);
    echo $view;
    $db = null;
});