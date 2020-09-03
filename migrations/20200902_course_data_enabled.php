<?php

/**
 * Adds config entries for setting when planning data can be
 * entered and edited in courses or when a planning can be published.
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

class CourseDataEnabled extends Migration {

    public function description()
    {
        return 'Adds config entries for setting when planning data can be entered and edited ' .
            'in courses or when a planning can be published.';
    }

    public function up()
    {
        // Which semester status values allow creating, editing or viewing planning data in courses?
        Config::get()->create('WHAKAMAHERE_ENABLED_IN_COURSES', [
            'value' => '{"create":["input"],"edit":["prepare"],"readonly":["planning","review","finished"]}',
            'type' => 'array',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'In welchen Semesterphasen können Planungsdaten in Veranstaltungen angegeben und geändert werden?'
        ]);

        // Which semester status values allow creating, editing or viewing planning data in courses?
        Config::get()->create('WHAKAMAHERE_PUBLISHING_ALLOWED', [
            'value' => '["planning","review"]',
            'type' => 'array',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'In welchen Semesterphasen darf die Planung veröffentlicht werden?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entry.
        Config::get()->delete('WHAKAMAHERE_ENABLED_IN_COURSES');
        Config::get()->delete('WHAKAMAHERE_PLANNING_ALLOWED');
    }

}
