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
 * @property int slot_id database column
 * @property int weekday database column
 * @property time start database column
 * @property time end database column
 * @property int pinned database column
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
        $config['belongs_to']['slot'] = [
            'class_name' => 'WhakamahereCourseSlot',
            'foreign_key' => 'slot_id',
            'assoc_foreign_key' => 'slot_id'
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
            if ($one) {
                switch ($type) {
                    case 'semester':
                        $joins[] = "JOIN `whakamahere_requests` r USING (`course_id`)";
                        $where[] = "AND r.`semester_id` = :semester";
                        $params['semester'] = $one;
                        break;
                    case 'institute':
                        $sub = explode('+', $one);
                        if (count($sub) > 1) {
                            $institutes = DBManager::get()->fetchFirst(
                                "SELECT `Institut_id` FROM `Institute` WHERE `fakultaets_id` = :institute",
                                ['institute' => $sub[0]]
                            );
                        } else {
                            $institutes = [$one];
                        }

                        $where[] = "AND s.`Institut_id` IN (:institutes)";
                        $params['institutes'] = $institutes;
                        break;
                    case 'lecturer':
                        $joins[] = "JOIN `whakamahere_course_slots` cs ON (t.`slot_id` = cs.`slot_id`)";
                        $where[] = "AND cs.`user_id` = :lecturer";
                        $params['lecturer'] = $one;
                        break;
                    case 'room':
                        break;
                    case 'seats':
                        $joins[] = " JOIN `whakamahere_property_requests` pr USING (`request_id`)";
                        $qhere[] = " AND pr.`property_id` = :seats";
                        $params['seats'] = WhakamaherePropertyRequest::getSeatsPropertyId();
                        if ($filter['seats']['min'] && $filter['seats']['max']) {
                            $where[] = " AND pr.`value` BETWEEN :min AND :max";
                            $params['min'] = $one['min'];
                            $params['max'] = $one['max'];
                        } else if ($filter['seats']['min']) {
                            $where[] = " AND pr.`value` >= :min";
                            $params['min'] = $one['min'];
                        } else if ($filter['seats']['max']) {
                            $where[] = " AND pr.`value` <= :max";
                            $params['max'] = $one['max'];
                        }
                        break;
                }
            }
        }

        $query .= implode(' ', $joins);
        $query .= implode(' ', $where);

        $query .= " ORDER BY t.`weekday`, t.`start`, t.`end`";

        return DBManager::get()->fetchAll($query, $params, 'WhakamahereCourseTime::buildExisting');
    }

    /**
     * Finds all planned times for a given user in the given semester. This can be used
     * to check for user availability.
     *
     * @param string $user_id
     * @param string $semester_id
     * @return mixed
     */
    public static function findByUserAndSemester($user_id, $semester_id)
    {
        return self::findBySQL("JOIN `whakamahere_course_slots` USING (`slot_id`)
            JOIN `whakamahere_requests` USING (`request_id`)
            WHERE `whakamahere_course_slots`.`user_id` = :user
                AND `whakamahere_requests`.`semester_id` = :semester
            ORDER BY `weekday`, `start`, `end`",
            ['user' => $user_id, 'semester' => $semester_id]);
    }

    /**
     * Finds rooms which are available for the current planned time.
     */
    public function findAvailableRooms()
    {
        $props = $this->slot->request->property_requests;

        // Aggregate required props.
        $requiredProps = [];
        $joins = [];
        $where = [];
        $params = [
            'wish' => $this->slot->request->room_id
        ];
        $i = 0;
        foreach ($props as $prop) {
            // Seats are extra.
            if ($prop->name == 'seats') {
                $seats = $prop->value;

                // Use some tolerance in requested seats.
                $seatsMin = (Config::get()->WHAKAMAHERE_SEATS_LOWER_LIMIT / 100) * $seats;
                $seatsMax = (Config::get()->WHAKAMAHERE_SEATS_UPPER_LIMIT / 100) * $seats;

                $joins[] = " INNER JOIN `resources_properties` s " .
                    "ON (s.`resource_id` = `resources`.`id` AND s.`property_id` = :seatsid)";
                $params['seatsid'] = $prop->property_id;

                $where[] = "AND s.`state` BETWEEN :seatsmin AND :seatsmax";
                $params['seatsmin'] = $seatsMin;
                $params['seatsmax'] = $seatsMax;
            } else {
                $requiredProps[$prop->property_id] = $prop->value;

                $joins[] = " INNER JOIN `resources_properties` rp" . $i .
                    " ON (rp" . $i . ".`resource_id` = `resources`.`id` " .
                        "AND rp" . $i . ".`property_id` = :propid" . $i . ")";
                $params['propid' . $i] = $prop->property_id;

                $where[] = "AND rp" . $i . ".`state` = :propvalue" . $i;
                $params['propvalue' . $i] = $prop->value;

                $i++;
            }

        }

        $select = "SELECT DISTINCT r.`id`, r.`name`, s.`state` AS seats FROM `resources` r";
        $order = " ORDER BY seats, r.`name`";

        /*
         * Find all rooms which have approximately the suitable size and the necessary properties.
         */
        $entries = DBManager::get()->fetchAll($select . $joins . $where, $params);

        // Categories for matching number of seats.
        $wished = [];
        $equal = [];
        $larger = [];
        $smaller = [];
        foreach ($entries as $one) {
            // The wished room is prioritized.
            if ($one['id'] == $this->slot->request->room_id) {
                $one['wish'] = true;
                $wished[] = $one;
            } else {
                $one['wish'] = false;
                if ($one['seats'] ==  $seats) {
                    $equal[] = $one;
                } else if ($one['seats'] > $seats) {
                    $larger[] = $one;
                } else {
                    $smaller[] = $one;
                }
            }
        }

        return array_merge($wished, $equal, $larger, $smaller);

    }

}
