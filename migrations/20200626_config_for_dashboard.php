<?php

/**
 * Adds config entries for displaying data in dashboard.
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

class ConfigForDashboard extends Migration {

    public function description()
    {
        return 'Adds config entries for displaying data in dashboard.';
    }

    public function up()
    {
        // Which institutes shall be processed in dashboard statistics
        Config::get()->create('WHAKAMAHERE_DASHBOARD_STATISTICS_INSTITUTES', [
            'value' => '[]',
            'type' => 'array',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Welche Einrichtungen sollen in den Dashboard-Planungsstatistiken berücksichtigt werden?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entries.
        foreach ([
                     'WHAKAMAHERE_DASHBOARD_STATISTICS_INSTITUTES'
                 ] as $field) {

            Config::get()->delete($field);

        }
    }

}
