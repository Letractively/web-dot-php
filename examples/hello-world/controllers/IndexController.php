<?php
class IndexController {
     function GET() {
        View::set('message', 'Hello, World!');
        View::render('views/index.php', null, 'layouts/default.php');
     }
}