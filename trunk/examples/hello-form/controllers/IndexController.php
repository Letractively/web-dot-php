<?php
class IndexController {
    public function index() {
        $view = new View('views/form.phtml');
        $view->render();
    }

    public function post() {
        
        $form = new Form($_POST);
        $form->email = array('required', 'email', '2..3', 'minlength ', '/^bungle@fchaps.com$/', '#^bungle@fchaps.com$#');

        if ($form->validate()) {
            echo 'jee';
        } else {
            $view = new View('views/form.phtml');
            $view->invalid = 'invalid';
            $view->form = $form;
            $view->render();
        }
    }
    
    public function notfound() {
        header("HTTP/1.0 404 Not Found");
        $view = new View('views/404.phtml');
        $view->text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        $view->render();
    }
}