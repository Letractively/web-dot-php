<?php
class IndexController {
     function GET() {
     
        if (Browser::isMobile()) {
            View::set('message', 'Hello, Mobile World!');
        } else {
            View::set('message', 'Hello, World!');
        }

        $chart = new GoogleChart('200x125', 'lc', 'text');
        $chartUrl = $chart->addData(array(10, 40, 1, 60))->encode();

        $chart = new GoogleChart('200x225', 'bhs', 'extended');
        $chartUrl2 = $chart
            ->addData(array(10, 40, 1, 61))
            ->addColor('FF00FF')
            ->addData(array(50, 11, 40, 0))
            ->addColor('00FF00')
            ->setMaxValue('automatic')
            ->encode();

        $chart = new GoogleChart('200x225', 'bhs', 'extended');
        $chartUrl3 = $chart
            ->addData(array(10, 40, 1, 61))
            ->addColor('FF00FF')
            ->addData(array(50, 11, 40, 0))
            ->addColor('00FF00')
            ->setMaxValue('automatic')
            ->encode();

        $chart = new GoogleChart('200x225', 'lc', 'extended');
        $chartUrl4 = $chart
            ->addData(array(10, 40, 5))
            ->addColor('FF00FF')
            ->addData(array(40, 30, 0))
            ->addColor('00FF00')
            ->addData(array(15, 22, 60))
            ->addColor('0000FF')
            ->setMaxValue('automatic')
            ->encode();

        View::set('chart', $chartUrl);
        View::set('chart2', $chartUrl2);
        View::set('chart3', $chartUrl3);
        View::set('chart4', $chartUrl4);

        View::render('views/index.php', null, 'layouts/default.php');
     }
}