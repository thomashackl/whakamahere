<?php if (count($timelines) < 1) : ?>

    <?= MessageBox::info(dgettext('whakamahere', 'Es wurden noch keine Zeitpläne definiert.')) ?>

<?php else : ?>


    <?php foreach ($timelines as $data) : ?>
        <table class="default">
            <caption><?= htmlReady($data['semester']) ?></caption>
            <colgroup>
                <col width="40">
                <col>
                <col width="100">
                <col width="100">
                <col width="200">
                <col width="60">
            </colgroup>
            <thead>
                <tr>
                    <th><?= dgettext('whakamahere', 'Farbe') ?></th>
                    <th><?= dgettext('whakamahere', 'Name') ?></th>
                    <th><?= dgettext('whakamahere', 'Beginn') ?></th>
                    <th><?= dgettext('whakamahere', 'Ende') ?></th>
                    <th><?= dgettext('whakamahere', 'Automatischer Semesterstatus?') ?></th>
                    <th><?= dgettext('whakamahere', 'Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($data['phases'] as $phase) :
                ?>
                <tr>
                    <td style="background-color: <?= $phase->color ?>"></td>
                    <td><?= htmlReady($phase->name) ?></td>
                    <td><?= $phase->start->format('d.m.Y') ?></td>
                    <td><?= $phase->end->format('d.m.Y') ?></td>
                    <td><?= $phase->auto_status ? $status[$phase->auto_status] : '-' ?></td>
                    <td>
                        <a href="<?= $controller->link_for('timelines/edit', $phase->phase_id) ?>"
                           title="<?= dgettext('whakamahere', 'Phase bearbeiten') ?>"
                           data-dialog="size=auto">
                            <?= Icon::create('edit') ?></a>
                        <a href="<?= $controller->link_for('timelines/delete', $phase->phase_id) ?>"
                           title="<?= dgettext('whakamahere', 'Phase bearbeiten') ?>"
                           data-confirm="<?= dgettext('whakamahere', 'Soll die Phase wirklich gelöscht werden?') ?>">
                            <?= Icon::create('trash') ?></a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endforeach ?>
<?php endif;
