<?php
class IndexController {
    public function index() {

        $form = new Form();
        $form->email->filters = array('email', '/^aapo/');
        $form->email = 'aapo.laakkonen@gmail.com';
        
        $view = new View('views/index.phtml');
        $view->header = 'Lorem Ipsum';
        $view->body = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        $view->form = $form;

        echo $view;
    }
}