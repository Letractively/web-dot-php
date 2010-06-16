<?php
get('/points', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/points.phtml');
    $view->title = 'Pistetilanne';
    $view->menu = 'points';
    $points = cache_fetch('worldcup2010:points');
    if ($points === false) {
        $points = db\stats\points();
        cache_store('worldcup2010:points', $points);
    }
    $view->points = $points;
    $scorers = cache_fetch('worldcup2010:scorers');
    if ($scorers === false) {
        $scorers = db\stats\scorers();
        cache_store('worldcup2010:scorers', $scorers);
    }
    $view->scorers = $scorers;
    $view->online = db\users\visited(username, 'Pistetilanne');
    echo $view;
});
get('/points/:username', function($username) {
    if (!authenticated) redirect('~/unauthorized');
    $username = urldecode($username);
    $view = new view('views/points.user.phtml');
    $view->title = username === $username ? 'Omat pisteet' : "Pistetilanne ($username)";
    $view->title_games = username === $username ? 'Omat otteluveikkaukset' : "Otteluveikkaukset";
    $view->menu = 'points';
    $points = cache_fetch('worldcup2010:points');
    if ($points === false) {
        $points = db\stats\points();
        cache_store('worldcup2010:points', $points);
    }
    $points = array_filter($points, function($point) use ($username) {
        return $point['username'] === $username;
    });
    $view->points = $points;
    $scorers = cache_fetch('worldcup2010:scorers');
    if ($scorers === false) {
        $scorers = db\stats\scorers();
        cache_store('worldcup2010:scorers', $scorers);
    }
    $view->scorers = $scorers;
    $view->pointsuser = $username;
    $view->games = db\stats\games($username);
    $view->online = db\users\visited(username, $view->title);
    echo $view;
});
get('/stats', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/stats.phtml');
    $view->title = 'Tilastot';
    $view->menu = 'stats';
    $view->online = db\users\visited(username, 'Tilastot');
    echo $view;
});