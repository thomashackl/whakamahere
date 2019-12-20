<?php

/**
 * WhakamahereCourseTime.php
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
 * @property int time_id database column
 * @property string course_id database column
 * @property int part_num database column
 * @property int weekday database column
 * @property time start database column
 * @property time end database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamahereCourseTime extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_course_times';
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
            'assoc_foreign_key' => 'seminar_id'
        ];

        parent::configure($config);
    }

    /**
     * Gets entries that match the given (optional) filter.
     * Filter can contain one or more semesters, one or more institutes,
     * one or more rooms, or a combination of these.
     *
     * @param array $filter
     * @return array Array of found WhakamahereCourseTime entries.
     */
    public static function findFiltered($filter = [])
    {
        $query = "SELECT t.* FROM `whakamahere_course_times` t";

        $joins = [
            " JOIN `seminare` s ON (s.`Seminar_id` = t.`course_id`)"
        ];
        $where = [
            " WHERE 1"
        ];
        $params = [];

        foreach ($filter as $type => $one) {
            switch ($type) {
                case 'semester':
                    $joins[] = "JOIN `semester_data` sem ON (
                        s.`start_time` + s.`duration_time` BETWEEN sem.`beginn` AND sem.`ende`)";
                    $where[] = "AND sem.`semester_id` IN (:semesters)";
                    $params['semesters'] = is_array($one) ? $one : [ $one ];
                    break;
                case 'institute':
                    $where[] = "AND s.`Institut_id` IN (:institutes)";
                    $params['institutes'] = is_array($one) ? $one : [ $one ];
                    break;
                case 'room':
                    break;
            }
        }

        $query .= implode(' ', $joins);
        $query .= implode(' ', $where);

        $query .= " ORDER BY t.`weekday`, t.`start`, t.`end`";

        return DBManager::get()->fetchAll($query, $params, 'WhakamahereCourseTime::buildExisting');
    }

}
