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
get('/admin/news/#id', function($id) {
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
    $view->games = db\games\all();
    echo $view;
});
post('/admin/games/#id', function($id) {
    if (!admin) redirect('~/unauthorized');
    $form = new form($_POST);
    $form->home_goals->filter('int', 'intval');
    $form->road_goals->filter('int', 'intval');
    if ($form->validate()) {
        db\games\score($id, $form->home_goals->value, $form->road_goals->value);
        cache_delete('worldcup2010:points');
    }
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
post('/admin/users', function() {
    if (!admin) redirect('~/unauthorized');
    $form = new form($_POST);
    $form->active->filter('checkbox');
    $form->paid->filter('checkbox');
    $form->admin->filter('checkbox');
    if ($form->validate()) {
        db\users\update($form->user->value, $form->active->value || username == $form->user->value, $form->paid->value, $form->admin->value || username == $form->user->value);
    }
});
get('/admin/config', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.config.phtml');
    $view->title = 'Konfiguraatio';
    $view->menu = 'admin/config';
    echo $view;
});
get('/admin/patches/view-games-1', function() {
    if (!admin) redirect('~/unauthorized');
    db\patches\view_games_1();
    echo 'Patch "view_games_1" installed.';
});