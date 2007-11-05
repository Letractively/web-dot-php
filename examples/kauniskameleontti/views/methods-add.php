<?php $url = Helper::import('url'); ?>

<?php Zone::write('javascripts'); ?>
    <script type="text/javascript">
    <!--
    $(document).ready(function() {
        $("#methods-add-form").yav({
            errorDiv: "errors",
            errorMessage: "Lomakkeen tietoja ei voitu lähettää, koska täyttämäsi lomake sisältää virheitä. Ole hyvä ja korjaa havaitut virheet.",
            errorTag: "div",
            onError: function(){
                $("#notice").hide();
                return false;
            }
        }, {
            inputclasserror: "error",
        });
    });
    //-->
    </script>
<?php Zone::flush(); ?>

<form id="methods-add-form" action="<?php $url('methods/add'); ?>" method="post">

    <fieldset>

        <legend>Maksutavan lisääminen</legend>

        <div id="errors"></div>

        <?php if (isset($errors)): ?>
            <div id="errors" class="error">
            <?php foreach($errors as $error) echo $error . '<br />'; ?>
            </div>
        <?php else: ?>
            <div id="notice" class="notice">Tähdellä merkityt kentät ovat pakollisia.</div>
        <?php endif; ?>

        <div class="field">
            <label for="name">Nimi: <span class="required">*</span></label><br />
            <input id="name" name="name" value="<?php echo $method->name; ?>" type="text" maxlength="50" class="text required" title="Nimi on pakollinen kenttä ja se voi sisältää ainoastaan kirjaimia ja numeroita sekä välilyöntejä."/>
        </div>

        <div class="field">
            <label for="abbr">Lyhenne: <span class="required">*</span></label><br />
            <input id="abbr" name="abbr" value="<?php echo $method->abbr; ?>" type="text" maxlength="6" class="text required" title="Lyhenne on pakollinen kenttä ja se voi sisältää ainoastaan kirjaimia ja numeroita sekä välilyöntejä."/>
        </div>

        <hr class="space" />
        <hr />

        <div>
            <button type="submit" class="button normal positive">
                <img src="/styles/blueprint/plugins/buttons/icons/tick.png" alt=""/> Lähetä
            </button>

            <a class="button normal" href="<?php $url('methods') ?>">
                <img src="/styles/blueprint/plugins/buttons/icons/cross.png" alt=""/> Takaisin
            </a>
        </div>

    </fieldset>

</form>