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
    $messages = db\chat\latest(50, $last);
    $view = new view('views/chat.phtml');
    $_SESSION['last-chat-message-id'] = $last;
    if (count($messages) > 0) {
        $chat = new view('views/chat.messages.phtml');
        $chat->messages = $messages;
        $view->chat = $chat;
    }
    echo $view;
});

post('/chat', function() {
    if (!authenticated) redirect('~/unauthorized');
    $form = new form($_POST);
    $form->message->filter('trim', minlength(1), specialchars(), 'links', 'smileys');
    if ($form->validate()) {
        db\chat\post(username, $form->message->value);
    }
});

get('/chat/poll', function() {
    if (!authenticated) return;
    $last = isset($_SESSION['last-chat-message-id']) ? $_SESSION['last-chat-message-id'] : 0;
    $messages = db\chat\poll($last);
    if (count($messages) === 0) return status(304);
    $_SESSION['last-chat-message-id'] = $last;
    $view = new view('views/chat.messages.phtml');
    $view->messages = $messages;
    echo $view;
});