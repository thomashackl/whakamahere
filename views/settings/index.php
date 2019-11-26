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
            <?= dgettext('whakamahere', 'Raumauslastungsstatistik') ?>
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
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('whakamahere', 'Speichern'), 'submit') ?>
    </footer>
</form>
