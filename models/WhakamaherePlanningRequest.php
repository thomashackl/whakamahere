<?php

/**
 * WhakamaherePlanningRequest.php
 * model class for requests: all data that is needed for course planning,
 * like time preferences, property requests etc.
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
 * @property int request_id database column
 * @property string course_id database column
 * @property string semester_id database column
 * @property string institute_id database column
 * @property string room_id database column
 * @property int cycle database column
 * @property int startweek database column
 * @property string comment database column
 * @property string internal_comment database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamaherePlanningRequest extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_requests';
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
            'assoc_foreign_key' => 'seminar_id'
        ];
        $config['belongs_to']['semester'] = [
            'class_name' => 'Semester',
            'foreign_key' => 'semester_id',
            'assoc_foreign_key' => 'semester_id'
        ];
        $config['has_many']['property_requests'] = [
            'class_name' => 'WhakamaherePropertyRequest',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];
        $config['has_many']['slots'] = [
            'class_name' => 'WhakamahereCourseSlot',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];

        parent::configure($config);
    }

    public static function findLecturers($filter)
    {
        $sql = "SELECT DISTINCT a.*
            FROM `auth_user_md5` a
                JOIN `whakamahere_course_slots` s USING (`user_id`)
                JOIN `whakamahere_requests` r USING (`request_id`)";
        $where = " WHERE r.`semester_id` = :semester";
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

            $where .= " AND r.`institute_id` IN (:institutes)";
            $params['institutes'] = $institutes;
        }

        $sql .= $where . " ORDER BY a.`Nachname`, a.`Vorname`, a.`username`";

        return DBManager::get()->fetchAll($sql, $params, 'User::buildExisting');
    }

    public static function getSeatsPropertyId()
    {
        // Try to read ID of "seats" property from cache.
        $cache = StudipCacheFactory::getCache();
        $seatsId = $cache->read('WHAKAMAHERE_SEATS_PROPERTY_ID');

        // No (valid) cache entry found, create new.
        if (!$seatsId) {
            $seatsId = DBManager::get()->fetchColumn("SELECT `property_id`
                    FROM `resource_property_definitions` WHERE `name` = 'seats'");
            // Write to cache with one week validity
            $cache->write('WHAKAMAHERE_SEATS_PROPERTY_ID', $seatsId, 604800);
        }

        return $seatsId;
    }

}
