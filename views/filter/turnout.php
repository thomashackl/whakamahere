<form action="<?php echo $controller->link_for('filter/store_selection') ?>" method="post">
    <label for="range">
        <?php echo dgettext('whakamahere', 'von') ?>
    </label>
    <input type="number" name="min" id="min" min="0" size="5" maxlength="4" value="<?php echo $min ?>"
           placeholder="-">
    <label for="max">
        <?php echo dgettext('whakamahere', 'bis') ?>
    </label>
    <input type="number" name="max" id="max" min="0" size="5" maxlength="4" value="<?php echo $max ?>"
           placeholder="-">
    <input type="hidden" name="type" value="list_turnout">
    <input type="submit" hidden>
</form>
