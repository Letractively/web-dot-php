<?php
class IndexController {
     function GET() {
     
        if (Request::isMobile()) {
            View::set('message', 'Hello, Mobile World!');
        } else {
            View::set('message', 'Hello, World!');
        }

        View::render('views/index.php', null, 'layouts/default.php');
     }
}