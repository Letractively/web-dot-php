<?php
get('/admin', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.phtml');
    $view->title = 'Ylläpito';
    $view->menu = 'admin';
    echo $view;
});

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
    $form->slug($form->title->value)->filter('slug');
    $form->content->filter('trim', minlength(1), 'links', 'smileys');
    $form->level->filter('intval');
    if (!isset($form->id->value)) $form->id->value = null; 
    if($form->validate()) {
        db\news\add($form->id->value, $form->title->value, $form->content->value, $form->level->value, username, $form->slug->value);
        redirect('~/');
    }
});

get('/admin/news/edit/:id', function($id) {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.news.phtml');
    $view->title = 'Uutisten ylläpito';
    $view->menu = 'admin/news';
    $view->news = db\news\edit($id);
    echo $view;
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
    $view = new view('views/admin.users.phtml');
    $view->title = 'Käyttäjien ylläpito';
    $view->menu = 'admin/users';
    $view->users = db\users\all();
    echo $view;
});

get('/admin/config', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.config.phtml');
    $view->title = 'Konfiguraatio';
    $view->menu = 'admin/config';
    echo $view;
});