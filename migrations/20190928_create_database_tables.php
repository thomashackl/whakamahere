<?php

/**
 * Creates database tables and content necessary for semester planning.
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

class CreateDatabaseTables extends Migration {

    public function description()
    {
        return 'Creates database tables and their content for semester planning.';
    }

    /**
     * Migration UP: We have just installed the plugin
     * and need to prepare all necessary data.
     */
    public function up()
    {
        // Semester planning status
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_semester_status`
        (
            `semester_id` VARCHAR(32) COLLATE latin1_bin NOT NULL REFERENCES `semester_data`.`semester_id`,
            `status` ENUM ('closed', 'input', 'prepare', 'planning', 'review', 'finished') NOT NULL DEFAULT 'input',
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`semester_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        /*
         * Create entries for existing semesters.
         */
        $stmt = DBManager::get()->prepare("INSERT INTO `whakamahere_semester_status` VALUES (:id, :status, NOW(), NOW())");
        // Only future semesters get status "input", all others are marked as finished.
        foreach (Semester::getAll() as $semester) {
            if ($semester->getpast() || $semester->getcurrent()) {
                $status = 'finished';
            } else {
                $status = 'input';
            }
            $stmt->execute(['id' => $semester->id, 'status' => $status]);
        }

        // Phases for planning timeline.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `whakamahere_timeline`
        (
            `phase_id` INT NOT NULL AUTO_INCREMENT,
            `semester_id` VARCHAR(32) NOT NULL REFERENCES `semester_data`.`semester_id`,
            `name` VARCHAR(255) NOT NULL DEFAULT '',
            `start` DATE NOT NULL,
            `end` DATE NOT NULL,
            `color` VARCHAR(7) NOT NULL DEFAULT '#ffffff',
            `auto_status` ENUM ('closed', 'input', 'prepare', 'planning', 'review', 'finished') NULL REFERENCES `whakamahere_semester_status`.`status`,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`phase_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        SimpleORMap::expireTableScheme();
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Drop database tables.
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_semester_status`");
        DBManager::get()->execute("DROP TABLE IF EXISTS `whakamahere_semester_timeline`");
    }

}
