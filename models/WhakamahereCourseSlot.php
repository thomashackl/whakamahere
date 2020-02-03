<?php

/**
 * WhakamahereCourseSlot.php
 * model class for course slots. Courses may occur several times a week, e.g. in two two-hour slots.
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
 * @property int slot_id database column
 * @property int request_id database column
 * @property int length database column
 * @property string user_id database column
 * @property int weekday database column
 * @property string time database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamahereCourseSlot extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_course_slots';
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['belongs_to']['request'] = [
            'class_name' => 'WhakamaherePlanningRequest',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id'
        ];

        parent::configure($config);
    }

}
