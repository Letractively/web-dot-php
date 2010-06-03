<?php
get('/bets/games', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/bets.games.phtml');
    $view->games = db\bets\games(username);
    echo $view;
});

post('/bets/games/#game', function($game) {
    if (!authenticated) return status(401);
    $form = new form($_POST);
    $form->score->filter(choice('1', 'X', '2'));
    if ($form->validate()) {
        db\bets\game($game, username, $form->score->value);
    } else {
        status(500);
    }
});

get('/bets/teams', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/bets.teams.phtml');
    $view->teams = db\teams\all();
    $single = db\bets\single(username);
    if ($single) {
        $view->winner = $single['winner'];
        $view->winner_abbr = $single['winner_abbr'];
        $view->second = $single['second'];
        $view->second_abbr = $single['second_abbr'];
        $view->third = $single['third'];
        $view->third_abbr = $single['third_abbr'];
    } else {
        $view->winner = null;
        $view->second = null;
        $view->third = null;
    }
    echo $view;
});

post('/bets/teams/#position', function($position) {
    if (!authenticated) return status(401);
    $form = new form($_POST);
    $form->team->filter('\db\teams\exists');
    $form->position($position)->filter(choice('1', '2', '3'), 'intval');
    if ($form->validate()) {
        switch ($form->position->value) {
            case 1: db\bets\winner(username, $form->team->value); break;
            case 2: db\bets\second(username, $form->team->value); break;
            case 3: db\bets\third(username, $form->team->value); break;
        }
    } else {
        status(500);
    }
});



