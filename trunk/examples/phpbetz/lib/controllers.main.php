<?php

get('/news', function() {
    $db = new db;
    $view = new view('views/news.phtml');
    $view->news = $db->news->all();
    $db->close();
    echo $view;
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
    $db = new db;
    $chat = new view('views/chat.messages.phtml');
    $chat->messages = $db->chat->poll(50);
    $db->close();
    $view = new view('views/chat.phtml');
    $view->chat = $chat;
    echo $view;
});

post('/chat', function() {
    $form = new form($_POST);
    $form->chatmessage->filter('trim', minlength(1), 'encode', 'links', 'smileys');
    if ($form->validate()) {
        $db = new db;
        $db->chat->post('bungle', $form->chatmessage);
        $db->close();
    }
});

get('/chat/poll', function() {
    $db = new db;
    $view = new view('views/chat.messages.phtml');
    $view->messages = $db->chat->poll(50);
    $db->close();
    echo $view;
});