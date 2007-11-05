<?php $url = Helper::import('url'); ?>

<?php Zone::write('javascripts'); ?>
<script type="text/javascript">
<!--
$(document).ready(function() {
    $('#methods-table').tablesorter({ widgets: ['zebra'] });
    $('#message').animate({opacity: 1.0}, 2000)
    $('#message').fadeOut('slow');
});
//-->
</script>
<?php Zone::flush(); ?>

<?php if (count($methods) == 0): ?>
    <p>Tietokannasta ei löytynyt yhtään maksutapaa. Lisää uusi maksutapa klikkaamalla alla olevaa painiketta.</p>
<?php else: ?>

<?php if (Flash::has('message')): ?>
    <div id="message" class="success"><?php echo Flash::get('message'); ?></div>
<?php endif; ?>

<table id="methods-table" class="tablesorter" border="0" cellpadding="0" cellspacing="1">
    <thead>
        <tr>
            <th>Nimi</th>
            <th>Lyhenne</th>
            <th>Toiminnot</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($methods as $method): ?>
        <tr>
            <td><?php echo $method->name; ?></td>
            <td><?php echo $method->abbr; ?></td>
            <td><a href="<?php $url('methods/edit/' . $method->id); ?>">Muokkaa</a> | <a href="<?php $url('methods/delete/' . $method->id); ?>">Poista</a>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>
    
<a href="<?php $url('methods/add'); ?>">Lisää maksutapa</a>