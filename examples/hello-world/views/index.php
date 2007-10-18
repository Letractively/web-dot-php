<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php View::writeZone('title'); echo $title; View::flushZone(); ?></title>
</head>
<body>
    <?php View::writeZone(View::DATA); ?>
        <h1><?php echo $message; ?></h1>
    <?php View::flushZone(); ?>
</body>
</html>