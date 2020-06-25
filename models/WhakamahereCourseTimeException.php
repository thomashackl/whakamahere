<?php

/**
 * WhakamahereCourseTimeException.php
 * model class for courses which only have a day and time, but no room assignment.
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
 * @property int exception_id database column
 * @property int time_id database column
 * @property datetime start database column
 * @property datetime end database column
 * @property string booking_id database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamahereCourseTimeException extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_course_time_exceptionss';
        $config['belongs_to']['time'] = [
            'class_name' => 'WhakamahereCourseTime',
            'foreign_key' => 'time_id',
            'assoc_foreign_key' => 'time_id'
        ];
        $config['has_one']['booking'] = [
            'class_name' => 'ResourceBooking',
            'foreign_key' => 'booking_id',
            'assoc_foreign_key' => 'booking_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];

        parent::configure($config);
    }

    public static function findByTimeAndWeek($time_id, $week)
    {
        return self::findBySQL("`time_id` = :time AND `week` = :week", ['time' => $time_id, 'week' => $week]);
    }

    /**
     * Provides an array structure usable for JSON encoding.
     *
     * @return array
     */
    public function formatForSchedule()
    {
        $seatsId = WhakamaherePropertyRequest::getSeatsPropertyId();

        $result = [
            'id' => $this->exception_id,
            'time_id' => (int) $this->id,
            'start' => $this->start,
            'end' => $this->end
        ];

        return $result;
    }

}
