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
        $config['has_one']['planned_time'] = [
            'class_name' => 'WhakamahereCourseTime',
            'foreign_key' => 'slot_id',
            'assoc_foreign_key' => 'slot_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];

        parent::configure($config);
    }

    public static function findUnplanned($filter = [])
    {
        $select = "SELECT cs.*
            FROM `whakamahere_course_slots` cs
                JOIN `whakamahere_requests` r ON (r.`request_id` = cs.`request_id`)
                JOIN `seminare` s ON (s.`Seminar_id` = r.`course_id`)
                JOIN `semester_data` sem ON (s.`start_time` BETWEEN sem.`beginn` AND sem.`ende`)";
        $where = "WHERE sem.`semester_id` = :semester
                AND NOT EXISTS (
                    SELECT `slot_id` FROM `whakamahere_course_times` WHERE `slot_id` = cs.`slot_id`
                )";
        $params = [
            'semester' => $filter['semester']
        ];

        // Search for course number or name
        if ($filter['searchterm'] != '') {
            $where .= " AND (s.`VeranstaltungsNummer` LIKE :search OR s.`Name` LIKE :search)";
            $params['search'] = '%' . $filter['searchterm'] . '%';
        }

        // A range for turnout is given.
        if (is_array($filter['seats'])) {

            $select .= " JOIN `whakamahere_property_requests` pr ON (pr.`request_id` = r.`request_id`)";
            $where .= " AND pr.`property_id` = :seats";
            $params['seats'] = WhakamaherePropertyRequest::getSeatsPropertyId();

            if ($filter['seats']['min'] && $filter['seats']['max']) {

                $where .= " AND pr.`value` BETWEEN :min AND :max";
                $params['min'] = $filter['seats']['min'];
                $params['max'] = $filter['seats']['max'];

            } else if ($filter['seats']['min']) {

                $where .= " AND pr.`value` >= :min";
                $params['min'] = $filter['seats']['min'];

            } else if ($filter['seats']['max']) {

                $where .= " AND pr.`value` <= :max";
                $params['max'] = $filter['seats']['max'];

            }
        }

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

            $where .= " AND s.`Institut_id` IN (:institutes)";
            $params['institutes'] = $institutes;
        }

        if ($filter['lecturer'] != '') {
            $where .= " AND cs.`user_id` = :lecturer";
            $params['lecturer'] = $filter['lecturer'];
        }

        if ($filter['room'] != '') {
            $where .= " AND r.`room_id` = :room";
            $params['room'] = $filter['room'];
        }

        if (Config::get()->IMPORTANT_SEMNUMBER) {
            $order = " ORDER BY s.`VeranstaltungsNummer`, s.`Name`";
        } else {
            $order = " ORDER BY s.`Name`";
        }

        return DBManager::get()->fetchAll($select.$where.$order, $params, 'WhakamahereCourseSlot::buildExisting');

    }

    public function __toString()
    {
        $weekdays = $this->getWeekdays();

        $start = strtotime('next ' . $weekdays[$this->weekday] . ' ' . $this->time);

        return strftime('%a %H:%M', $start) . ', ' . $this->duration . ' ' .
            dgettext('whakamahere', 'Minuten');
    }

    public static function getWeekdays()
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

}
