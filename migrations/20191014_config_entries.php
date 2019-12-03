<?php

/**
 * Creates several entries in global config for semester planning.
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

class ConfigEntries extends Migration {

    public function description()
    {
        return 'Creates several entries in global config for semester planning.';
    }

    /**
     * Migration UP: We have just installed the plugin
     * and need to prepare all necessary data.
     */
    public function up()
    {
        // Provide config options for weekdays and times for planning and statistics
        Config::get()->create('WHAKAMAHERE_PLANNING_SHOW_WEEKENDS', [
            'value' => '0',
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Sollen Wochenenden in der Planungsansicht angezeigt werden?'
        ]);
        Config::get()->create('WHAKAMAHERE_PLANNING_START_HOUR', [
            'value' => '08:00',
            'type' => 'string',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Mit welcher Uhrzeit beginnt die Planungsansicht?'
        ]);
        Config::get()->create('WHAKAMAHERE_PLANNING_END_HOUR', [
            'value' => '22:00',
            'type' => 'string',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Mit welcher Uhrzeit endet die Planungsansicht?'
        ]);
        Config::get()->create('WHAKAMAHERE_OCCUPATION_DAYS', [
            'value' => '[1,2,3,4,5]',
            'type' => 'array',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Welche Wochentage werden bei der Raumauslastungsstatistik berücksichtigt?'
        ]);
        Config::get()->create('WHAKAMAHERE_OCCUPATION_START_HOUR', [
            'value' => '08:00',
            'type' => 'string',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'Ab welcher Uhrzeit sollen Belegungen in der Raumauslastungsstatistik berücksichtigt werden?'
        ]);
        Config::get()->create('WHAKAMAHERE_OCCUPATION_END_HOUR', [
            'value' => '22:00',
            'type' => 'string',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'Bis zu welcher Uhrzeit sollen Belegungen in der Raumauslastungsstatistik berücksichtigt werden?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entries.
        foreach ([
                     'WHAKAMAHERE_PLANNING_SHOW_WEEKENDS',
                     'WHAKAMAHERE_PLANNING_START_HOUR',
                     'WHAKAMAHERE_PLANNING_END_HOUR',
                     'WHAKAMAHERE_OCCUPATION_DAYS',
                     'WHAKAMAHERE_OCCUPATION_START_HOUR',
                     'WHAKAMAHERE_OCCUPATION_END_HOUR'
                 ] as $field) {

            Config::get()->delete($field);

        }
    }

}
