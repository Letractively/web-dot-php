<?php
get('/bets/games', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/bets.games.phtml');
    $view->title = 'Otteluveikkaus';
    $view->menu = 'bets/games';
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
    $view->title = 'Kolmen kärki &trade;';
    $view->menu = 'bets/teams';
    $view->hide_teams = true;
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

get('/bets/scorer', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/bets.scorer.phtml');
    if (isset($_SESSION['saved'])) {
        $view->saved = true;
    }
    $view->title = 'Maalikuninkuus';
    $view->form = new form();
    $view->menu = 'bets/scorer';
    echo $view;
});

post('/bets/scorer', function() {
    if (!authenticated) redirect('~/unauthorized');
    $form = new form($_POST);
    $form->scorer->filter('trim', specialchars(), minlength(3));
    $view = new view('views/bets.scorer.phtml');
    if ($form->validate()) {
        db\bets\scorer(username, $form->scorer);
        flash('saved', true);
        redirect('~/bets/scorer');
    } else {
        $view->title = 'Maalikuninkuus';
        $view->menu = 'bets/scorer';
        $view->form = $form;
        $view->error = true;
        echo $view;

    }
});