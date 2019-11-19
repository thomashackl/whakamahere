<form class="default">
    <label for="semester">
        <?= dgettext('whakamahere', 'Semester') ?>
    </label>
    <select name="semester" id="semester" data-select-url="<?= $controller->link_for('filter/select_semester') ?>">
        <option value=""><?= dgettext('whakamahere', 'alle') ?></option>
        <?php foreach ($semesters as $semester) : ?>
            <option value="<?= htmlReady($semester->id) ?>"
                    <?= $semester->id === $selectedSemester ? 'selected' : '' ?>>
                <?= htmlReady($semester->semester->name) ?>
            </option>
        <?php endforeach ?>
    </select>
    <label for="semester">
        <?= dgettext('whakamahere', 'Einrichtung') ?>
    </label>
    <select name="institute" id="institute" class="nested-select"
            data-select-url="<?= $controller->link_for('filter/select_institute') ?>">
        <option value=""><?= dgettext('whakamahere', 'alle') ?></option>
        <?php foreach ($institutes as $institute) : ?>
            <option value="<?= htmlReady($institute['Institut_id']) ?>"
                <?= $institute['Institut_id'] === UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_INSTITUTE ?
                    'selected' : '' ?>>
                <?= $institute['is_fak'] ? '' : '&nbsp;&nbsp;' ?><?= htmlReady($institute['Name']) ?>
            </option>
        <?php endforeach ?>
    </select>
    <label for="semester">
        <?= dgettext('whakamahere', 'Raum') ?>
    </label>
    <select name="room" id="room" class="nested-select"
            data-select-url="<?= $controller->link_for('filter/select_room') ?>">
        <option value=""><?= dgettext('whakamahere', 'alle') ?></option>
        <?php foreach ($locations as $location) : ?>
            <option value="<?= htmlReady($location->id) ?>" disabled>
                <?= htmlReady($location->name) ?>
            </option>
            <?php foreach ($location->buildings as $building) : ?>
                <option value="<?= htmlReady($building->id) ?>" disabled>
                    &nbsp;&nbsp;<?= htmlReady($building->name) ?>
                </option>
                <?php foreach ($building->rooms as $room) : ?>
                    <option value="<?= htmlReady($room->id) ?>"
                        <?= $room->id === UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_RESOURCE ?
                            'selected' : '' ?>>
                        &nbsp;&nbsp;&nbsp;&nbsp;<?= htmlReady($room->name) ?>
                    </option>
                <?php endforeach ?>
            <?php endforeach ?>
        <?php endforeach ?>
    </select>
</form>
