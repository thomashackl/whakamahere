<?php

/**
 * Adds database tables for resource requests and preferences created via course creation wizard.
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

class Requests extends Migration {

    public function description()
    {
        return 'Adds database tables for resource/property requests and preferences.';
    }

    public function up()
    {
        // Request: one per course, preferred room, cycle data, comments
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_requests`
        (
            `request_id` INT NOT NULL AUTO_INCREMENT,
            `course_id` VARCHAR(32) NOT NULL REFERENCES `seminare`.`Seminar_id`,
            `room_id` VARCHAR(32) NULL REFERENCES `resources`.`id`,
            `twoweek` BOOLEAN NOT NULL DEFAULT false,
            `startweek` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `comment` TEXT NOT NULL DEFAULT '',
            `internal_comment` TEXT NOT NULL DEFAULT '', 
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`request_id`),
            UNIQUE INDEX course_id (`course_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Property requests:
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_property_requests`
        (
            `property_request_id` INT NOT NULL AUTO_INCREMENT,
            `request_id` VARCHAR(32) NOT NULL REFERENCES `whakamahere_requests`.`request_id`,
            `property_id` VARCHAR(32) NOT NULL REFERENCES `resource_property_definitions`.`property_id`,
            `value` VARCHAR(255) NOT NULL,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`property_request_id`),
            INDEX request_id (`request_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Course slots:
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_course_slots`
        (
            `slot_id` INT NOT NULL AUTO_INCREMENT,
            `request_id` VARCHAR(32) NOT NULL REFERENCES `whakamahere_requests`.`request_id`,
            `length` SMALLINT NOT NULL,
            `user_id` VARCHAR(32) NULL REFERENCES `auth_user_md5`.`user_id`,
            `weekday` TINYINT NOT NULL,
            `time` TIME NOT NULL,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`slot_id`),
            INDEX request_id (`request_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_requests`");
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_property_requests`");
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_course_slots`");
    }

}
