<?php
get('/points', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/points.phtml');
    $view->title = 'Pistetilanne';
    $view->menu = 'points';
    $view->points = db\stats\points();
    echo $view;
});

get('/stats', function() {
    if (!authenticated) redirect('~/unauthorized');
    $view = new view('views/stats.phtml');
    $view->title = 'Tilastot';
    $view->menu = 'stats';
    echo $view;
});