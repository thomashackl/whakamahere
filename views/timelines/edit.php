<form class="default" action="<?= $controller->link_for('timelines/store', $phase->id) ?>" method="post">
    <section>
        <label for="phase-name">
            <?= dgettext('whakamahere', 'Name') ?>
        </label>
        <input type="text" size="40" maxlength="75" name="name" value="<?= htmlReady($phase->name) ?>" id="phase-name">
    </section>
    <section class="col-3">
        <label for="phase-start">
            <?= dgettext('whakamahere', 'Beginn') ?>
        </label>
        <input type="text" size="5" name="start" value="<?= $phase->start->format('d.m.Y') ?>"
               id="phase-start" data-date-picker>
    </section>
    <section class="col-3">
        <label for="phase-end">
            <?= dgettext('whakamahere', 'Ende') ?>
        </label>
        <input type="text" size="5" name="end" value="<?= $phase->end->format('d.m.Y') ?>"
               id="phase-end" data-date-picker='{">=":"#phase-start"}'>
    </section>
    <section class="col-3">
        <label for="phase-color">
            <?= dgettext('whakamahere', 'Farbkennzeichnung') ?>
        </label>
        <input type="color" name="color" value="<?= $phase->color ?>" id="phase-color">
    </section>
    <section class="col-3">
        <label for="phase-auto-status">
            <?= dgettext('whakamahere', 'Automatisch Semesterstatus setzen?') ?>
        </label>
        <select name="auto_status">
            <option value=""><?= dgettext('whakamahere', 'keinen Status setzen') ?></option>
            <?php foreach ($status as $value => $name) : ?>
                <option value="<?= htmlReady($value) ?>"><?= htmlReady($name) ?></option>
            <?php endforeach ?>
        </select>
    </section>
    <?php if ($phase->isNew()) : ?>
        <section>
            <label for="phase-semester">
                <?= dgettext('whakamahere', 'Semester') ?>
            </label>
            <select name="semester" id="phase-semester">
                <?php foreach ($semesters as $semester) : ?>
                    <option value="<?= htmlReady($semester->id) ?>"<?= $semester->id === $selectedSemester->id ? ' selected' : '' ?>>
                        <?= htmlReady($semester->name) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </section>
    <?php else: ?>
    <section>
        <?= dgettext('whakamahere', 'Semester') ?>: <?= htmlReady($phase->semester->name) ?>
    </section>
    <?php endif ?>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('whakamahere', 'Speichern'),
            'submit') ?>
        <?= Studip\Button::createCancel(dgettext('whakamahere', 'Abbrechen'),
            'cancel', ['data-dialog-close' => true]) ?>
    </footer>
</form>