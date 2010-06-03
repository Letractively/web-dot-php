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
    echo $view;
});

