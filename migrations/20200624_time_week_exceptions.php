<?php

/**
 * Adds a database table for storing single week exceptions from regular times.
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

class TimeWeekExceptions extends Migration {

    public function description()
    {
        return 'Adds a database table for storing single week exceptions from regular times.';
    }

    public function up()
    {
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_course_time_exceptions`
        (
            `exception_id` INT NOT NULL AUTO_INCREMENT,
            `time_id` INT NOT NULL REFERENCES `whakamahere_course_times`.`time_id`,
            `week` TINYINT UNSIGNED NOT NULL,
            `start` DATETIME NOT NULL,
            `end` DATETIME NOT NULL,
            `booking_id` VARCHAR(32) NULL DEFAULT NULL REFERENCES `resource_bookings`.`booking_id`,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`exception_id`),
            INDEX time_id (`time_id`),
            UNIQUE INDEX time_week (`time_id`, `week`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_time_exceptions`");
    }

}
