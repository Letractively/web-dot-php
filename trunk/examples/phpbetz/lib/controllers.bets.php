<?php
get('/bets/games', function() {
    $db = new db;
    $view = new view('views/bets.games.phtml');
    $view->games = $db->bets->games('bungle');
    $db->close();
    echo $view;
});

post('/bets/games/#game', function($game) {
    echo json_encode(array('game' => $game, 'score' => $_POST['score']));
});
