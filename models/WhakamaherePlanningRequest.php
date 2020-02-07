<?php

/**
 * WhakamaherePlanningRequest.php
 * model class for requests: all data that is needed for course planning,
 * like time preferences, property requests etc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Whakamahere
 *
 * @property int request_id database column
 * @property string course_id database column
 * @property string room_id database column
 * @property int cycle database column
 * @property int startweek database column
 * @property string comment database column
 * @property string internal_comment database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamaherePlanningRequest extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_requests';
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
            'assoc_foreign_key' => 'seminar_id'
        ];
        $config['has_many']['property_requests'] = [
            'class_name' => 'WhakamaherePropertyRequest',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];
        $config['has_many']['slots'] = [
            'class_name' => 'WhakamahereCourseSlot',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];

        parent::configure($config);
    }

}
