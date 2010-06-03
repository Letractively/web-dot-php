<?php
get('/admin/news', function() {
    if (!admin) redirect('~/unauthorized');
    $view = new view('views/admin.news.phtml');
    echo $view;
});

post('/admin/news', function() {
    if (!admin) redirect('~/unauthorized');
    $form = new form($_POST);
    $form->slug($form->title)->filter('slug');
    $form->content->filter('trim', minlength(1), 'links', 'smileys');
    $form->level->filter('int');
    if($form->validate()) {
        $db = new db;
        $db->news->add($form->title->value, $form->content->value, $form->level->value, username, $form->slug->value);
        $db->close();
        redirect('~/');
    }
});

get('/admin/teams', function() {
    if (!admin) redirect('~/unauthorized');
    $db = new db;
    $view = new view('views/admin.teams.phtml');
    $view->teams = $db->teams->all();
    $db->close();
    echo $view;
});