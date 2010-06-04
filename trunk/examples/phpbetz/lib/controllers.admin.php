<?php
get('/admin/news', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.news.phtml');
    $view->title = 'Uutisten ylläpito';
    $view->menu = 'admin/news';
    echo $view;
});

post('/admin/news', function() {
    if (!admin) redirect('~/unauthorized');
    $form = new form($_POST);
    $form->slug($form->title)->filter('slug');
    $form->content->filter('trim', minlength(1), 'links', 'smileys');
    $form->level->filter('int');
    if($form->validate()) {
        db\news\add($form->title->value, $form->content->value, $form->level->value, username, $form->slug->value);
        redirect('~/');
    }
});

get('/admin/teams', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.teams.phtml');
    $view->title = 'Joukkueiden ylläpito';
    $view->menu = 'admin/news';
    $view->teams = db\teams\all();
    echo $view;
});

get('/admin/games', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.games.phtml');
    $view->title = 'Otteluiden ylläpito';
    $view->menu = 'admin/games';
    echo $view;
});

get('/admin/scorers', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.games.phtml');
    $view->title = 'Maalintekijöiden ylläpito';
    $view->menu = 'admin/scorers';
    echo $view;
});

get('/admin/users', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.games.phtml');
    $view->title = 'Käyttäjien ylläpito';
    $view->menu = 'admin/users';
    echo $view;
});
