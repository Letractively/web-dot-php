<?php
class IndexController {
     function GET() {
        View::set('message', 'Hello, World!');
        View::render('viesws/index.php', null, 'layouts/default.php');
     }
}