<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php $include = Helper::import('IncludeHelper'); ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Layout Title :: <?php echo $view->title ?></title>
    <?php $include->javascripts(); ?>
</head>
<body>
    <?php echo $view->body; ?>
</body>
</html>