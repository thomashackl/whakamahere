<?php

/**
 * Adds a database table for storing room assignments to a course time.
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

class StoreRoomWithCourseTime extends Migration {

    public function description()
    {
        return 'Adds a database table for storing room assignments to a course time.';
    }

    public function up()
    {
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_time_bookings`
        (
            `id` INT NOT NULL AUTO_INCREMENT,
            `time_id` INT NOT NULL REFERENCES `whakamahere_course_times`,
            `booking_id` VARCHAR(32) COLLATE latin1_bin NOT NULL REFERENCES `resource_bookings`.`id`,
            `mkdate` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX time_id (`time_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_time_bookings`");
    }

}
