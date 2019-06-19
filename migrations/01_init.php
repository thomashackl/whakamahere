<?php

use Widgets\Widget;

/**
 * Migration 01: init
 * Creates necessary database tables and configuration entries for Whakamahere
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

class Init extends Migration {

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

        // Provide config options for weekdays and times for planning and statistics
        Config::get()->create('WHAKAMAHERE_PLANNING_SHOW_WEEKENDS', [
            'value' => '0',
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Sollen Wochenenden in der Planungsansicht angezeigt werden?'
        ]);
        Config::get()->create('WHAKAMAHERE_PLANNING_START_HOUR', [
            'value' => '8',
            'type' => 'integer',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Mit welcher Uhrzeit beginnt die Planungsansicht?'
        ]);
        Config::get()->create('WHAKAMAHERE_PLANNING_END_HOUR', [
            'value' => '22',
            'type' => 'integer',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Mit welcher Uhrzeit endet die Planungsansicht?'
        ]);
        Config::get()->create('WHAKAMAHERE_OCCUPATION_DAYS', [
            'value' => '[1,2,3,4,5]',
            'type' => 'array',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' => 'Welche Wochentage werden bei der Raumauslastungsstatistik berücksichtigt?'
        ]);
        Config::get()->create('WHAKAMAHERE_OCCUPATION_START_HOUR', [
            'value' => '8',
            'type' => 'integer',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'Ab welcher Uhrzeit sollen Belegungen in der Raumauslastungsstatistik berücksichtigt werden?'
        ]);
        Config::get()->create('WHAKAMAHERE_OCCUPATION_END_HOUR', [
            'value' => '22',
            'type' => 'integer',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'Bis zu welcher Uhrzeit sollen Belegungen in der Raumauslastungsstatistik berücksichtigt werden?'
        ]);

        // Create widget container for plugin dashboard.
        $container = new Widgets\Container();
        $container->range_id = 'whakamahere';
        $container->range_type = 'user';
        $container->scope = 'whakamahere_dashboard';
        $container->store();

        $files = glob(__DIR__ . '/../widgets/*.php');

        // Register plugin widgets
        foreach ($files as $file) {
            require_once $file;

            $class = basename($file, '.php');
            $widget = Widgets\Widget::registerWidget(new $class);
            $element = new Widgets\Element();
            $element->container_id = $container->id;
            $element->widget_id = $widget->id;
            $element->x = 0;
            $element->y = 0;
            $element->width = 12;
            $element->height = 3;
            $element->locked = 1;
            $element->removable = 0;
            $element->store();
        }

        SimpleORMap::expireTableScheme();
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `mahere_semester_status`");
    }

}