<?php
class IndexController {
     function GET() {

        View::set('title', 'Example - Hello, World');

        View::render('views/index.php', array('message' => 'Hello, World!'), 'layouts/default.php');        
     }
}