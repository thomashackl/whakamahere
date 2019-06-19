<table class="default">
    <colgroup>
        <col width="200">
        <col>
        <col width="100">
    </colgroup>
    <thead>
        <tr>
            <th><?= dgettext('whakamahere', 'Zeitraum') ?></th>
            <th><?= dgettext('whakamahere', 'Phase') ?></th>
            <th><?= dgettext('whakamahere', 'Semester') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($phases as $phase) : ?>
        <tr>
            <td>
                <?= $phase->start->format('d.m.Y') ?> - <?= $phase->end->format('d.m.Y') ?>
            </td>
            <td>
                <?= htmlReady($phase->name) ?>
            </td>
            <td>
                <?= htmlReady($phase->semester->name) ?>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
