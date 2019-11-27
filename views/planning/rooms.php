<table class="default room-proposals">
    <colgroup>
        <col width="200">
        <col width="50">
        <col>
        <col width="25">
    </colgroup>
    <thead>
        <tr>
            <th>Name</th>
            <th>Sitzplätze</th>
            <th>Ausstattung</th>
            <th>Übernehmen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rooms as $room) : ?>
            <?php $properties = SimpleCollection::createFromArray(ResourceProperty::findByResource_id($room->id))->orderBy('name') ?>
            <tr>
                <td><?= htmlReady($room->name) ?></td>
                <td><?= htmlReady($properties->findOneBy('property_id', '44fd30e8811d0d962582fa1a9c452bdd')->state) ?></td>
                <td>
                    <ul>
                    <?php foreach ($properties as $property) : ?>
                        <?php if ($property->id != '44fd30e8811d0d962582fa1a9c452bdd' && $property->state != '') : ?>
                            <li>
                                <?= htmlReady($property->name) ?>:
                                <?= $property->type == 'bool' ?
                                    Icon::create('accept', 'info')->asImg(12) :
                                    htmlReady($property->state) ?>
                            </li>
                        <?php endif ?>
                    <?php endforeach ?>
                    </ul>
                </td>
                <td>
                    <?= Icon::create('accept')->asImg(24) ?>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
