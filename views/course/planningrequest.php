<?php if ($form) : ?>
<form class="default" action="<?php echo $disabled ? '' : $controller->link_for('course/store_request') ?>"
      method="post">
<?php endif ?>
<fieldset>
    <legend>
        <?php echo dgettext('whakamahere', 'Angaben zur Semesterplanung') ?>
    </legend>
    <div id="whakamahere-planning-request">
        <planning-request regular="<?= $regular ?>" :properties='<?= studip_json_encode($properties) ?>'
                          seats-id="<?= $seats ?>"
                          :lecturers='<?= studip_json_encode($lecturers) ?>'
                          :rooms='<?= studip_json_encode($rooms) ?>'
                          :start-weeks='<?= studip_json_encode($start_weeks) ?>'
                          :end-weeks='<?= studip_json_encode($end_weeks) ?>'
                          :request='<?= studip_json_encode($request) ?>'
                          :disabled="<?php echo $disabled ? 'true' : 'false' ?>"></planning-request>
    </div>

    <script>
        new Vue({
            el: '#whakamahere-planning-request'
        });
    </script>
</fieldset>

<?php if ($form) : ?>
    <?php echo CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?php echo Studip\Button::createAccept(dgettext('whakamahere', 'Speichern'),
            'submit', $disabled ? ['disabled' => true] : null) ?>
    </footer>
</form>
<?php endif;
