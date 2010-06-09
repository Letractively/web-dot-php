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
    $view->online = db\users\visited(username, 'Pistetilanne');
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