<?php

get('/news', function() {
    if (!authenticated) redirect('~/unauthorized');
    $db = new db;
    $view = new view('views/news.phtml');
    $view->news = $db->news->all();
    $db->close();
    echo $view;
});

get('/stats', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/stats.phtml');
    echo $view;
});

get('/rules', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/rules.phtml');
    echo $view;
});

get('/chat', function() {
    if (!authenticated) redirect('~/unauthorized');
    session();
    $last = 0;
    $db = new db;
    $messages = $db->chat->latest(50, $last);
    $db->close();
    $view = new view('views/chat.phtml');
    if (count($messages) > 0) {
        $_SESSION['last-chat-message-id'] = $last;
        $chat = new view('views/chat.messages.phtml');
        $chat->messages = $messages;
        $view->chat = $chat;
    }
    echo $view;
});

post('/chat', function() {
    if (!authenticated) redirect('~/unauthorized');
    $form = new form($_POST);
    $form->chatmessage->filter('trim', minlength(1), 'encode', 'links', 'smileys');
    if ($form->validate()) {
        $db = new db;
        $db->chat->post(username, $form->chatmessage);
        $db->close();
    }
});

get('/chat/poll', function() {
    if (!authenticated) return;
    session();
    $last = $_SESSION['last-chat-message-id'];
    $db = new db;
    $messages = $db->chat->poll($last);
    $db->close();
    if (count($messages) === 0) return;
    $_SESSION['last-chat-message-id'] = $last;
    $view = new view('views/chat.messages.phtml');
    $view->messages = $messages;
    echo $view;
});