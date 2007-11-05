<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php $include = Helper::import('include'); ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Error Occurred</title>

    <?php $include->stylesheet('styles/blueprint/screen.css'); ?>
    <?php $include->stylesheet('styles/blueprint/print.css', 'print'); ?>
    <?php $include->stylesheet('styles/blueprint/lib/ie.css', 'screen, projection', '<!--[if IE]>', '<![endif]-->'); ?>
    <?php $include->stylesheet('styles/syntax-highlighter/syntax-highlighter.css'); ?>

    <?php $include->javascript('scripts/syntax-highlighter/shCore.js'); ?>
    <?php $include->javascript('scripts/syntax-highlighter/shBrushPhp.js'); ?>

    <?php Zone::write('javascripts'); ?>
    <script type="text/javascript">
        <!--
        window.onload = function () {
            dp.SyntaxHighlighter.HighlightAll('code');
        }
        //-->
    </script>
    <?php Zone::flush(); ?>

</head>
<body>
    <div class="container">

        <div class="column span-24">

            <h2><?php echo $type . ' on ' . $file . ', line ' . $line . ':'; ?></h2>

            <div class="<?php echo $class; ?>"><?php echo $message; ?></div>

            <h4>Source:</h4>
            <pre name="code" class="php:nocontrols:firstline[<?php echo $startline; ?>]"><?php echo $code; ?></pre>

            <hr class="space" />

            <h3>Backtrace</h3>

            <?php foreach($backtrace as $trace): ?>
                <?php if (isset($trace['file'])): ?>
                    <div class="success"><?php echo $trace['func'] . '() was called from ' . $trace['file'] . ', line: ' . $trace['line']; ?>.</div>

                    <?php if(count($trace['args']) > 0): ?>
                    <h4>Arguments:</h4>
                    <pre name="code" class="php:nocontrols:nogutter"><?php print_r($trace['args']); ?></pre>
                    <?php endif; ?>

                    <?php if(isset($trace['startline'])): ?>
                    <h4>Source:</h4>
                    <pre name="code" class="php:nocontrols:firstline[<?php echo $trace['startline']; ?>]"><?php echo $trace['code']; ?></pre>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>

        </div>
       
    </div>

</body>
</html>