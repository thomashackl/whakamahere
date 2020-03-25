<?php

/**
 * Adds global config entries for room proposals in planning.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Whakamahere
 */

class RoomProposals extends Migration {

    public function description()
    {
        return 'Adds global config entries for room proposals in planning.';
    }

    public function up()
    {
        Config::get()->create('WHAKAMAHERE_SEATS_LOWER_LIMIT', [
            'value' => '90',
            'type' => 'integer',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Prozentuale Schranke, die ein Raum zu wenig Sitzplätze haben soll und trotzdem noch als passend vorgeschlagen wird.'
        ]);
        Config::get()->create('WHAKAMAHERE_SEATS_UPPER_LIMIT', [
            'value' => '125',
            'type' => 'integer',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Prozentuale Schranke, die ein Raum zu viele Sitzplätze haben soll und trotzdem noch als passend vorgeschlagen wird.'
        ]);
        Config::get()->create('WHAKAMAHERE_OCCUPIED_DATES_LIMIT', [
            'value' => '2',
            'type' => 'integer',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Anzahl Termine, an denen ein Raum belegt sein darf und trotzdem noch als verfügbar vorgeschlagen wird'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entries.
        foreach ([
                     'WHAKAMAHERE_SEATS_LOWER_LIMIT',
                     'WHAKAMAHERE_SEATS_UPPER_LIMIT',
                     'WHAKAMAHERE_OCCUPIED_DATES_LIMIT'
                 ] as $field) {

            Config::get()->delete($field);

        }
    }

}
