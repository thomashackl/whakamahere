<?php

/**
 * Adds a database table for storing the publication process of the planned times and rooms.
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

class PublishPlanningLog extends Migration {

    public function description()
    {
        return 'Adds a database table for storing the publication process of the planned times and rooms.';
    }

    public function up()
    {
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_publish_log`
        (
            `entry_id` INT NOT NULL AUTO_INCREMENT,
            `semester_id` VARCHAR(32) NOT NULL REFERENCES `semester_data`.`semester_id`,
            `course_id` VARCHAR(32) NOT NULL REFERENCES `seminare`.`Seminar_id`,
            `time_id` INT NOT NULL REFERENCES `whakamahere_course_times`.`time_id`,
            `exception_id` INT NULL REFERENCES `whakamahere_course_time_exceptions`. `exception_id`, 
            `date_id` VARCHAR(32) NULL REFERENCES `termine`.`termin_id`,
            `booking_id` VARCHAR(32) NULL REFERENCES `resource_bookings`.`id`,
            `user_id` VARCHAR(32) NOT NULL REFERENCES `auth_user_md5`.`user_id`,
            `state` ENUM('success', 'warning', 'error') NOT NULL DEFAULT 'success',
            `note` VARCHAR(255) NULL DEFAULT NULL,
            `mkdate` DATETIME NOT NULL,
            PRIMARY KEY (`entry_id`),
            INDEX semester_id (`semester_id`),
            INDEX course_id (`course_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_publish_log`");
    }

}
