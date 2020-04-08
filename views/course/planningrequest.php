<?php if ($form) : ?>
<form class="default" action="<?php echo $controller->link_for('course/store_request') ?>" method="post">
<?php endif ?>
<div id="whakamahere-planning-request">
    <planning-request regular="<?= $regular ?>" :properties='<?= studip_json_encode($properties) ?>'
                      seats-id="<?= $seats ?>"
                      :lecturers='<?= studip_json_encode($lecturers) ?>'
                      :rooms='<?= studip_json_encode($rooms) ?>'
                      :weeks='<?= studip_json_encode($weeks) ?>'
                      :request='<?= studip_json_encode($request) ?>'></planning-request>
</div>

<script>
    new Vue({
        el: '#whakamahere-planning-request'
    });
</script>

<?php if ($form) : ?>
    <?php echo CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?php echo Studip\Button::createAccept(dgettext('whakamahere', 'Speichern'),
            'submit') ?>
    </footer>
</form>
<?php endif;
