<?php
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
    $last = isset($_SESSION['last-chat-message-id']) ? $_SESSION['last-chat-message-id'] : 0;
    $db = new db;
    $messages = $db->chat->poll($last);
    $db->close();
    if (count($messages) === 0) return;
    $_SESSION['last-chat-message-id'] = $last;
    $view = new view('views/chat.messages.phtml');
    $view->messages = $messages;
    echo $view;
});