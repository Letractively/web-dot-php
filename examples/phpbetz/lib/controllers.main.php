<?php
get('/euro2008', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/euro2008.phtml');
    $view->title = 'Euro2008 lopputulokset';
    $view->menu = 'euro2008';
    $view->online = db\users\visited(username, 'Euro2008');
    echo $view;
});
get('/rules', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/rules.phtml');
    $view->title = 'Säännöt';
    $view->menu = 'rules';
    $view->online = db\users\visited(username, 'Säännöt');
    echo $view;
});
get('/chat', function() {
    if (!authenticated) redirect('~/unauthorized');
    $last = 0;
    $messages = db\chat\latest(50, $last);
    $view = new view('views/chat.phtml');
    $view->title = 'Kisachat';
    $view->menu = 'chat';
    $view->smileys = smileys_array();
    $view->online = db\users\visited(username, 'Kisachat');
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
        db\users\visited(username, 'Kisachat');
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
get('/program', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/program.phtml');
    $view->title = 'Otteluohjelma';
    $view->menu = 'program';
    $view->online = db\users\visited(username, 'Otteluohjelma');
    echo $view;
});