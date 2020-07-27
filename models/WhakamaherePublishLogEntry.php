<?php

/**
 * WhakamaherePublishLogEntry.php
 * model class for log entries used in publishing.
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
 * @property int entry_id database column
 * @property string semester_id database column
 * @property string course_id database column
 * @property string time_id database column
 * @property string exception_id database column
 * @property string date_id database column
 * @property string booking_id database column
 * @property string user_id database column
 * @property string state database column
 * @property string note database column
 * @property string mkdate database column
 */

class WhakamaherePublishLogEntry extends SimpleORMap
{

    public static $SUCCESS = 0;
    public static $ERROR_BOOKING_STORE = 1;
    public static $ERROR_BOOKING_NOT_FOUND = 2;
    public static $ERROR_NO_DATES_GENERATED = 3;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_publish_log';
        $config['belongs_to']['semester'] = [
            'class_name' => 'Semester',
            'foreign_key' => 'semester_id',
            'assoc_foreign_key' => 'semester_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
            'assoc_foreign_key' => 'course_id'
        ];
        $config['belongs_to']['time'] = [
            'class_name' => 'WhakamahereCourseTime',
            'foreign_key' => 'time_id',
            'assoc_foreign_key' => 'time_id'
        ];
        $config['belongs_to']['exception'] = [
            'class_name' => 'WhakamahereCourseTimeException',
            'foreign_key' => 'exception_id',
            'assoc_foreign_key' => 'exception_id'
        ];
        $config['belongs_to']['date'] = [
            'class_name' => 'CourseDate',
            'foreign_key' => 'date_id',
            'assoc_foreign_key' => 'termin_id'
        ];
        $config['belongs_to']['booking'] = [
            'class_name' => 'ResourceBooking',
            'foreign_key' => 'booking_id',
            'assoc_foreign_key' => 'id'
        ];
        $config['belongs_to']['creator'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }

}
