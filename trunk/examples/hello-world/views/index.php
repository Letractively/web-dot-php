<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php $include = Helper::import('IncludeHelper'); ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hello, World from a View!</title>
    <?php echo $include->javascript('scripts/alert.js'); ?>
</head>
<body>
    <?php echo $message ?>

    <img src="<?php echo $chart ?>" />
    <img src="<?php echo $chart2 ?>" />
    
</body>
</html>