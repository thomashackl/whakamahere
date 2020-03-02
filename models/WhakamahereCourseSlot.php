<?php

/**
 * WhakamahereCourseSlot.php
 * model class for course slots. Courses may occur several times a week, e.g. in two two-hour slots.
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
 * @property int slot_id database column
 * @property int request_id database column
 * @property int duration database column
 * @property string user_id database column
 * @property int weekday database column
 * @property string time database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamahereCourseSlot extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_course_slots';
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['belongs_to']['request'] = [
            'class_name' => 'WhakamaherePlanningRequest',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id'
        ];

        parent::configure($config);
    }

    public static function findUnplanned($filter = [])
    {
        SimpleORMap::expireTableScheme();
        $sql = "SELECT cs.*
            FROM `whakamahere_course_slots` cs
                JOIN `whakamahere_requests` r ON (r.`request_id` = cs.`request_id`)
                JOIN `seminare` s ON (s.`Seminar_id` = r.`course_id`)
            WHERE r.`semester_id` = :semester
                AND NOT EXISTS (
                    SELECT `slot_id` FROM `whakamahere_course_times` WHERE `slot_id` = cs.`slot_id`
                )";
        $params = [
            'semester' => $filter['semester']
        ];

        if ($filter['institute'] != '') {

            // Our institute_id is like '<id>+sub', so we need to get sub institutes, too
            $sub = explode('+', $filter['institute']);
            if (count($sub) > 1) {
                $institutes = DBManager::get()->fetchFirst(
                    "SELECT `Institut_id` FROM `Institute` WHERE `fakultaets_id` = :institute",
                    ['institute' => $sub[0]]
                );
            } else {
                $institutes = [$filter['institute']];
            }

            $sql .= " AND s.`Institut_id` IN (:institutes)";
            $params['institutes'] = $institutes;
        }

        if ($filter['lecturer'] != '') {

            $sql .= " AND cs.`user_id` = :lecturer";
            $params['lecturer'] = $filter['lecturer'];
        }

        if (Config::get()->IMPORTANT_SEMNUMBER) {
            $sql .= " ORDER BY s.`VeranstaltungsNummer`, s.`Name`";
        } else {
            $sql .= " ORDER BY s.`Name`";
        }

        return DBManager::get()->fetchAll($sql, $params, 'WhakamahereCourseSlot::buildExisting');

    }

}