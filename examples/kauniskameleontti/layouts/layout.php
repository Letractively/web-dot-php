<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php $url = Helper::import('url'); ?>
<?php $include = Helper::import('include'); ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kaunis Kameleontti</title>

    <?php $include->stylesheet('styles/blueprint/screen.css'); ?>
    <?php $include->stylesheet('styles/blueprint/print.css', 'print'); ?>
    <?php $include->stylesheet('styles/blueprint/lib/ie.css', 'screen, projection', '<!--[if IE]>', '<![endif]-->'); ?>
    <?php $include->stylesheet('styles/tablesorter/blue/style.css'); ?>
    <?php $include->stylesheet('styles/core.css'); ?>

    <?php $include->javascript('scripts/yav/yav.js'); ?>
    <?php $include->javascript('scripts/jquery/jquery.js'); ?>
    <?php $include->javascript('scripts/jquery/jquery.tablesorter.js'); ?>
    <?php $include->javascript('scripts/jquery/jquery.yav.js'); ?>

    <?php $include->stylesheets(); ?>
    <?php $include->javascripts(); ?>

    <?php Zone::render('javascripts'); ?>
</head>
<body>

<div class="container">

    <div class="column span-24">
        <h1 class="alt">Tmi Kaunis Kameleontti</h1>
        <hr />
    </div>

    <div class="column span-24">

        <ul>
            <li><a href="<?php $url('receipts'); ?>"><span>Kuitit</span></a></li>
            <li><a href="<?php $url('works'); ?>"><span>Työtehtävät</span></a></li>
            <li><a href="<?php $url('methods'); ?>"><span>Maksutavat</span></a></li>
        </ul>

    </div>

    <div class="column span-24">
        <?php echo $view->body; ?>
    </div>

</div>

</body>
</html>