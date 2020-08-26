<?php

/**
 * Adds database tables for saving courses which have a time, but no room assignment (yet) and vice versa.
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

class CourseTimes extends Migration {

    public function description()
    {
        return 'Adds a database table for saving courses which have a time, but no room assignment (yet).';
    }

    public function up()
    {
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_course_times`
        (
            `time_id` INT NOT NULL AUTO_INCREMENT,
            `course_id` VARCHAR(32) COLLATE latin1_bin NOT NULL,
            `part_num` INT NOT NULL DEFAULT 0,
            `weekday` INT NOT NULL,
            `start` TIME NOT NULL,
            `end` TIME NOT NULL,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`time_id`),
            INDEX course_id (`course_id`),
            UNIQUE INDEX course_part (`course_id`, `part_num`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_course_times`");
    }

}
