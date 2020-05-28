<?php

/**
 * WhakamahereTimeBooking.php
 * model class for storing which room bookings are assigned to which course time.
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
 * @property int id database column
 * @property int time_id database column
 * @property string booking_id database column
 * @property string mkdate database column
 */

class WhakamahereTimeBooking extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_time_bookings';
        $config['belongs_to']['time'] = [
            'class_name' => 'WhakamahereCourseTime',
            'foreign_key' => 'time_id',
            'assoc_foreign_key' => 'time_id'
        ];
        $config['belongs_to']['booking'] = [
            'class_name' => 'ResourceBooking',
            'foreign_key' => 'booking_id',
            'assoc_foreign_key' => 'id'
        ];

        parent::configure($config);
    }

}
