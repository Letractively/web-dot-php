<?php
get('/bets/games', function() {
    if (!authenticated) redirect('~/unauthorized');
    $db = new db;
    $view = new view('views/bets.games.phtml');
    $view->games = $db->bets->games(username);
    $db->close();
    echo $view;
});

post('/bets/games/#game', function($game) {
    if (!authenticated) redirect('~/unauthorized');
    echo json_encode(array('game' => $game, 'score' => $_POST['score']));
});
