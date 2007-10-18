<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php if (!View::renderZone('title')): ?>Example - Default Title<?php endif; ?></title>
</head>
<body>
    <?php echo View::getData(); ?>
</body>
</html>