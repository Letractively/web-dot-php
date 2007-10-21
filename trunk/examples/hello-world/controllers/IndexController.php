<?php
class IndexController {
     function GET() {
        View::set('title', 'Example - Hello, World');
        View::render('views/index.php', array('message' => 'Hello, World!'), 'layouts/default.php');        
     }

     function redirect() {
        //Web::redirect('http://www.fchaps.com/', 'IndexController->redirected');
        Web::redirect('redirected', 'IndexController->redirected');
     }

     function redirectWithData() {
        Web::redirect('redirected', 'IndexController->redirected', 'Redirected with data');
     }

     function redirected($data = false) {
        if ($data === false) {
            echo 'No data passed during redirect';
        } else {
            echo $data;
        }


     }
}