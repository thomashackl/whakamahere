<?php

/**
 * WhakamaherePlanningPhase.php
 * model class for defining a phase in a semester-related planning timeline
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
 * @property int phase_id database column
 * @property string semester_id database column
 * @property string name database column
 * @property string start database column
 * @property string end database column
 * @property string color database column
 * @property string auto_status database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamaherePlanningPhase extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_timeline';
        $config['belongs_to']['semester'] = [
            'class_name' => 'Semester',
            'foreign_key' => 'semester_id',
            'assoc_foreign_key' => 'semester_id'
        ];
        // Start and end dates as DateTime objects.
        $config['registered_callbacks']['before_store'][] = 'cbMakeDateTime';
        $config['registered_callbacks']['after_initialize'][] = 'cbMakeDateTime';

        parent::configure($config);
    }

    /**
     * Finds all phases that lie in the current semester.
     *
     * @return array
     */
    public static function getCurrent()
    {
        $semester = Semester::findCurrent();

        return self::findBySQL(
            "`start` BETWEEN :start AND :end OR `end` BETWEEN :start AND :end ORDER BY `start`, `end`",
            ['start' => date('Y-m-d', $semester->beginn), 'end' => date('Y-m-d', $semester->ende)]);
    }

    /**
     * Auto-convert phase dates from and to DateTime objects.
     *
     * @param string $type event type
     * @throws Exception
     */
    protected function cbMakeDateTime($type)
    {
        if ($type === 'after_initialize') {
            $this->start = new DateTime($this->start);
            $this->end = new DateTime($this->end);
        }
        if ($type === 'before_store') {
            $this->start = $this->start->format('Y-m-d');
            $this->end = $this->end->format('Y-m-d');
        }
    }

}
