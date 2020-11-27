<form class="default" action="<?= $controller->link_for('settings/store') ?>" method="post">
    <fieldset>
        <legend>
            <?= dgettext('whakamahere', 'Planungsansicht') ?>
        </legend>
        <section class="col-2">
            <label for="plan-start-time">
                <?= dgettext('whakamahere', 'Angezeigter Zeitraum von') ?>
            </label>
            <select id="plan-start-time" class="col-2" name="planning_start_time">
                <?php for ($i = 1 ; $i < 24 ; $i++) : $current = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                <option value="<?= $current ?>"
                        <?= $current == Config::get()->WHAKAMAHERE_PLANNING_START_HOUR ? ' selected' : '' ?>>
                    <?= $current ?>
                </option>
                <?php endfor ?>
            </select>
        </section>
        <section class="col-2">
            <label for="plan-start-time">
                <?= dgettext('whakamahere', 'bis') ?>
            </label>
            <select id="plan-end-time" class="col-2" name="planning_end_time">
                <?php for ($i = 1 ; $i < 24 ; $i++) : $current = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                    <option value="<?= $current ?>"
                            <?= $current == Config::get()->WHAKAMAHERE_PLANNING_END_HOUR ? ' selected' : '' ?>>
                        <?= $current ?>
                    </option>
                <?php endfor ?>
            </select>
        </section>
        <section>
            <input type="checkbox" name="show_weekends" value="1" id="plan-show-weekends"
                   <?= Config::get()->WHAKAMAHERE_PLANNING_SHOW_WEEKENDS ? ' checked' : '' ?>>
            <label for="plan-show-weekends" class="col-2">
                <?= dgettext('whakamahere', 'Wochenendtage anzeigen?') ?>
            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('whakamahere', 'In welchen Phasen sind Angaben zur Semesterplanung erlaubt?') ?>
        </legend>
        <section>
            <label for="create">
                <?= dgettext('whakamahere', 'Angaben neu anlegen und bearbeiten') ?>:
            </label>
            <select name="create[]" id="create" class="nested-select" multiple>
                <?php foreach ($semesterstatus as $status => $name) : ?>
                    <option value="<?php echo htmlReady($status) ?>"
                        <?php echo in_array($status, $create) ? 'selected' : '' ?>>
                        <?php echo htmlReady($name) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </section>
        <section>
            <label for="edit-only">
                <?= dgettext('whakamahere',
                    'Bereits vorhandene Angaben bearbeiten, aber keine neuen anlegen') ?>:
            </label>
            <select name="edit[]" id="edit-only" class="nested-select" multiple>
                <?php foreach ($semesterstatus as $status => $name) : ?>
                    <option value="<?php echo htmlReady($status) ?>"
                        <?php echo in_array($status, $edit) ? 'selected' : '' ?>>
                        <?php echo htmlReady($name) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </section>
        <section>
            <label for="read-only">
                <?= dgettext('whakamahere', 'Nur Lesezugriff') ?>:
            </label>
            <select name="read[]" id="read-only" class="nested-select" multiple>
                <?php foreach ($semesterstatus as $status => $name) : ?>
                    <option value="<?php echo htmlReady($status) ?>"
                        <?php echo in_array($status, $readonly) ? 'selected' : '' ?>>
                        <?php echo htmlReady($name) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('whakamahere', 'In welchen Phasen kann die Planung veröffentlicht werden?') ?>
        </legend>
        <section>
            <label for="publish">
                <?= dgettext('whakamahere', 'Veröffentlichung der Planung möglich in') ?>:
            </label>
            <select name="publish[]" id="publish" class="nested-select" multiple>
                <?php foreach ($semesterstatus as $status => $name) : ?>
                    <option value="<?php echo htmlReady($status) ?>"
                        <?php echo in_array($status, $publish) ? 'selected' : '' ?>>
                        <?php echo htmlReady($name) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('whakamahere', 'Dashboard-Statistik') ?>
        </legend>
        <section class="col-2">
            <label for="plan-start-time">
                <?= dgettext('whakamahere', 'Berücksichtige Belegungszeiten von') ?>
            </label>
            <select id="occupation-start-time" class="col-2" name="occupation_start_time">
                <?php for ($i = 1 ; $i < 24 ; $i++) : $current = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                    <option value="<?=  $current ?>"
                            <?= $current == Config::get()->WHAKAMAHERE_OCCUPATION_START_HOUR ? ' selected' : '' ?>>
                        <?= $current ?>
                    </option>
                <?php endfor ?>
            </select>
        </section>
        <section class="col-2">
            <label for="plan-start-time">
                <?= dgettext('whakamahere', 'bis') ?>
            </label>
            <select id="occupation-end-time" class="col-2" name="occupation_end_time">
                <?php for ($i = 1 ; $i < 24 ; $i++) : $current = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                    <option value="<?=  $current ?>"
                            <?= $current == Config::get()->WHAKAMAHERE_OCCUPATION_END_HOUR ? ' selected' : '' ?>>
                        <?= $current ?>
                    </option>
                <?php endfor ?>
            </select>
        </section>
        <section>
            <label for="occupation-days">
                <?= dgettext('whakamahere', 'Berücksichtige Belegungen an folgenden Tagen') ?>
            </label>
            <select id="occupation-days" name="occupation_days[]" class="nested-select" multiple>
                <?php foreach ($days as $number => $name) : ?>
                    <option value="<?= $number ?>"<?= in_array($number, Config::get()->WHAKAMAHERE_OCCUPATION_DAYS) ? ' selected' : '' ?>>
                        <?= htmlReady($name) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </section>
        <section>
            <label for="institutes">
                <?= dgettext('whakamahere', 'Welche Einrichtungen (inklusive Untereinrichtungen) ' .
                    'sollen in der Dashboard-Statistik berücksichtigt werden?') ?>
            </label>
            <select id="institutes" name="statistics_institutes[]" class="nested-select" multiple>
                <?php foreach ($institutes as $one) : ?>
                    <option value="<?= $one['id'] ?>"<?= in_array($one['id'], $selectedInstitutes) ? ' selected' : '' ?>>
                        <?= $one['name'] ?>
                    </option>
                <?php endforeach ?>
            </select>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('whakamahere', 'Mailbenachrichtigungen') ?>
        </legend>
        <section>
            <header>
                <h2><?= dgettext('whakamahere', 'An welche E-Mailadressen sollen ' .
                    'Benachrichtigungen geschickt werden?') ?></h2>
            </header>
            <label for="mailto">
                <?= dgettext('whakamahere', 'E-Mailadresse hinzufügen') ?>
            </label>
            <input type="email" name="mailto[]" id="mailto">
            <div>
                <?php foreach ($mailto as $one) : ?>
                    <ul class="list-entry">
                        <li>
                            <?= htmlReady($one) ?>
                            <input type="hidden" name="mailto[]" value="<?= htmlReady($one) ?>">
                            <?= Icon::create('trash', 'clickable', ['onclick' => '$(this).parent().remove()']) ?>
                        </li>
                    </ul>
                <?php endforeach ?>
            </div>
        </section>
        <section>
            <header>
                <h2><?= dgettext('whakamahere', 'Die Aktionen welcher Kennungen ' .
                    'sollen Benachrichtigungen auslösen?') ?></h2>
            </header>
            <label for="user">
                <?= dgettext('whakamahere', 'Kennung hinzufügen') ?>
            </label>
            <?= $user_search->render() ?>
            <div>
                <?php foreach ($follow_users as $one) : ?>
                    <ul class="list-entry">
                        <li>
                            <?= htmlReady($one) ?>
                            <input type="hidden" name="users[]" value="<?= htmlReady($one) ?>">
                            <?= Icon::create('trash', 'clickable', ['onclick' => '$(this).parent().remove()']) ?>
                        </li>
                    </ul>
                <?php endforeach ?>
            </div>
        </section>
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('whakamahere', 'Speichern'), 'submit') ?>
    </footer>
</form>
