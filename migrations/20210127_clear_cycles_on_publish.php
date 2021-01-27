<?php

/**
 * Adds a config entry specifying whether existing cyclic dates should be cleared on
 * publishing a semester plan.
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

class ClearCyclesOnPublish extends Migration {

    public function description()
    {
        return 'Adds a config entry specifying whether existing cyclic dates should be cleared on ' .
            'publishing a semester plan.';
    }

    public function up()
    {
        // Which email addresses shall get notifications?
        Config::get()->create('WHAKAMAHERE_CLEAR_CYCLES_ON_PUBLISH', [
            'value' => 0,
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Sollen bestehende regelmäßige Zeiten einer Veranstaltung bei der Veröffentlichung ' .
                'der Planung entfernt werden?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entry.
        Config::get()->delete('WHAKAMAHERE_CLEAR_CYCLES_ON_PUBLISH');
    }

}
