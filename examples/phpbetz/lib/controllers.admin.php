<?php


get('/admin/news', function() {
    $view = new view('views/admin.news.phtml');
    echo $view;
});

post('/admin/news', function() {
    $form = new form($_POST);
    $form->slug($form->title)->filter('slug');
    $form->content->filter('trim', minlength(1), 'links', 'smileys');
    if($form->validate()) {
        $db = new db();
        $db->news->add($form->title, $form->content, $form->level, 'bungle', $form->slug);
        redirect('~/news');
        $db = null;
    }
});

get('/admin/teams', function() {
    $db = new db();
    $view = new view('views/admin.teams.phtml');
    $view->teams = $db->teams->all();
    echo $view;
    $db = null;
});