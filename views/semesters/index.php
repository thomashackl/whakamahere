<span id="update-status"></span>
<table class="default" id="semesters" data-update-url="<?= $controller->link_for('semesters/update') ?>">
    <caption><?= dgettext('whakamahere', 'Semester') ?></caption>
    <thead>
        <tr>
            <th><?= dgettext('whakamahere', 'Semester') ?></th>
            <th><?= dgettext('whakamahere', 'Beginn (Vorlesungsbeginn)') ?></th>
            <th><?= dgettext('whakamahere', 'Ende (Vorlesungsende)') ?></th>
            <th><?= dgettext('whakamahere', 'Anzahl Veranstaltungen') ?></th>
            <th><?= dgettext('whakamahere', 'Status') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($semesters as $one) : ?>
        <tr>
            <td><?= htmlReady($one->semester->name) ?></td>
            <td>
                <?= htmlReady(date('d.m.Y', $one->semester->beginn)) ?>
                (<?= htmlReady(date('d.m.Y', $one->semester->vorles_beginn)) ?>)
            </td>
            <td>
                <?= htmlReady(date('d.m.Y', $one->semester->ende)) ?>
                (<?= htmlReady(date('d.m.Y', $one->semester->vorles_ende)) ?>)
            </td>
            <td><?= htmlReady($one->semester->absolute_seminars_count) ?></td>
            <td>
                <select name="status" data-semester-id="<?= htmlReady($one->semester_id) ?>">
                    <?php foreach ($one->statusvalues as $value => $name) : ?>
                        <option value="<?= htmlReady($value) ?>"<?= $one->status === $value ? ' selected' : '' ?>>
                            <?= htmlReady($name) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= CSRFProtection::tokenTag() ?>