<?php
class IndexController {
     function GET() {
     
        if (Browser::isMobile()) {
            View::set('message', 'Hello, Mobile World!');
        } else {
            View::set('message', 'Hello, World!');
        }

        $chart = new GoogleChart('200x125', array(10, 40, 1, 60), 'lc');

        $chart2 = new GoogleChart(
            '200x125',
            array(
                array(10, 40, 1, 60),
                array(60, 1, 40, 10)
            ),
            'lc'
        );

        View::set('chart', $chart->buildUrl());
        View::set('chart2', $chart2->buildUrl());

        View::render('views/index.php', null, 'layouts/default.php');
     }
}