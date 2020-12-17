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
 * @property string institute_id database column
 * @property string room_id database column
 * @property int cycle database column
 * @property int startweek database column
 * @property int end_offset database column
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
        $config['has_one']['room'] = [
            'class_name' => 'Room',
            'foreign_key' => 'room_id',
            'assoc_foreign_key' => 'id'
        ];

        parent::configure($config);
    }

    /**
     * Gets all requests belonging in the given semester.
     *
     * @param array $filter criteria for searching, like semester, institute, semtype etc.
     * @param int $offset start at entry $offset
     * @param int $limit get up to $limit entries
     * @return array Courses matching the given filter
     */
    public static function findAllCourses($filter, $offset = 0, $limit = 0)
    {
        $query = self::getFilteredCoursesSQL($filter, $offset, $limit);

        return Course::findBySQL($query['sql'], $query['parameters']);
    }

    /**
     * Gets all requests belonging in the given semester.
     *
     * @param array $filter criteria for searching, like semester, institute, semtype etc.
     * @return array
     */
    public static function countAllCourses($filter)
    {
        $query = self::getFilteredCoursesSQL($filter, 0, 0);

        return Course::countBySQL($query['sql'], $query['parameters']);
    }

    /**
     * Find lecturers who have courses in this semester, according to the given filter.
     *
     * @param array $filter an array of filter conditions
     * @return mixed
     */
    public static function findLecturers($filter)
    {
        $sql = "SELECT DISTINCT a.*
            FROM `auth_user_md5` a
                JOIN `whakamahere_course_slots` s ON (s.`user_id` = a.`user_id`)
                JOIN `whakamahere_requests` r ON (r.`request_id` = s.`request_id`)";
        $where = "";
        $params = [];

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

    public static function getAvailableRooms()
    {
        return DBManager::get()->fetchAll(
            "SELECT DISTINCT `id`, `name` FROM `resources` WHERE `category_id` NOT IN (:ignore) ORDER BY `name`",
            ['ignore' => Config::get()->WHAKAMAHERE_PLANNING_IGNORE_ROOM_CATEGORIES ?: ['']]
        );
    }

    /**
     * Gets all available start weeks for the given semester.
     *
     * @param Semester $semester
     * @return array Numbered weeks, 0 is first week in lecturing period.
     */
    public static function getStartWeeks($semester)
    {
        $cache = StudipCacheFactory::getCache();

        // Use cached entry if available.
        if ($startWeeks = $cache->read('start-weeks-semester-' . $semester->id)) {

            return studip_json_decode($startWeeks);

        } else {

            // Available start weeks for given semester.
            $startWeeks = [];
            $firstWeek = $semester->first_sem_week;
            $currentWeek = $firstWeek;
            $i = 1;
            $tz = new DateTimeZone('Europe/Berlin');
            $start = new DateTime('now', $tz);
            $start->setTimestamp($semester->vorles_beginn);
            $end = new DateTime('now', $tz);
            $end->setTimestamp($semester->vorles_ende);
            $oneWeek = new DateInterval('P1W');
            $sixDays = new DateInterval('P6D');
            while ($start < $end) {
                $sixDaysLater = new DateTime('now', $tz);
                $sixDaysLater->setTimestamp($start->getTimestamp());
                $sixDaysLater->add($sixDays);
                $startWeeks[$i-1] = [
                    'text' => sprintf(dgettext('whakamahere', '%s. Semesterwoche (ab %s)'),
                        $i, $start->format('d.m.Y')),
                    'startDate' => $start->format('Y-m-d'),
                    'endDate' => $sixDaysLater->format('Y-m-d')

                ];
                $currentWeek++;
                $start->add($oneWeek);
                $i++;
            }

            $cache->write('start-weeks-semester-' . $semester->id, studip_json_encode($startWeeks), 86400);

        }

        return $startWeeks;
    }

    /**
     * Get possible end weeks for the given semester.
     *
     * @param Semester $semester
     * @return array Numbered weeks, index represents offset from last lecturing period week.
     */
    public static function getEndWeeks($semester)
    {
        $cache = StudipCacheFactory::getCache();

        // Use cached entry if available.
        if ($endWeeks = $cache->read('end-weeks-semester-' . $semester->id)) {

            return studip_json_decode($endWeeks);

        } else {

            $weeks = [];
            $firstWeek = $semester->first_sem_week;
            $currentWeek = $firstWeek;
            $i = 1;

            /*
             * Since the last day of lecturing period is not necessarily a Sunday,
             * we need this in ordner to show the correct end day for last semester week.
             */
            $semEnd = new DateTime();
            $semEnd->setTimestamp($semester->vorles_ende);

            $start = $semester->vorles_beginn;
            $endDay = new DateTime();
            $endDay->setTimestamp($semester->vorles_beginn);
            $endDay->modify('next Sunday');

            $oneweek = new DateInterval('P1W');

            while ($start < $semester->vorles_ende) {
                $weekEndDay = min($endDay, $semEnd);

                $endWeeks[$i - 1] = sprintf(dgettext('whakamahere', '%s. Semesterwoche (bis %s)'),
                    $i, $weekEndDay->format('d.m.Y'));
                $currentWeek++;
                $start += (7 * 24 * 60 * 60);
                $endDay->add($oneweek);
                $i++;
            }

            $cache->write('end-weeks-semester-' . $semester->id, studip_json_encode(array_reverse($endWeeks)), 86400);

            return array_reverse($endWeeks);
        }

    }

    private static function getFilteredCoursesSQL($filter, $offset, $limit)
    {
        $parameters = ['semester' => $filter['semester']];

        $joins = [
            "JOIN `semester_data` sem ON (sem.`beginn` = `seminare`.`start_time`)",
            "JOIN `sem_types` t ON (t.`id` = `seminare`.`status`)"
        ];
        $where = " WHERE sem.`semester_id` = :semester AND t.`class` = 1";

        if (isset($filter['institute'])) {
            $joins[] = "JOIN `Institute` i ON (i.`institut_id` = `seminare`.`institut_id`)";
            $where .= " AND (i.`Institut_id` = :institute OR i.`fakultaets_id` = :institute)";
            $parameters['institute'] = $filter['institute'];
        }

        if (isset($filter['my_institutes'])) {
            $joins[] = "JOIN `Institute` my ON (my.`institut_id` = `seminare`.`institut_id`)";
            $where .= " AND (my.`Institut_id` IN (:institutes) OR my.`fakultaets_id` IN (:institutes))";
            $parameters['institutes'] = $filter['my_institutes'];
        }

        if (isset($filter['semtype'])) {
            $where .= " AND `seminare`.`status` = :semtype";
            $parameters['semtype'] = $filter['semtype'];
        }

        if (isset($filter['planningstatus'])) {
            switch ($filter['planningstatus']) {
                case 'no-request':
                    $where .= " AND NOT EXISTS (
                            SELECT `request_id` FROM `whakamahere_requests`
                            WHERE `course_id` = `seminare`.`Seminar_id`
                        )";
                    break;
                case 'request':
                    $joins[] = "JOIN `whakamahere_requests` r ON (r.`course_id` = `seminare`.`Seminar_id`)";
                    break;
                case 'planned':
                    $joins[] = "JOIN `whakamahere_requests` r ON (r.`course_id` = `seminare`.`Seminar_id`)";
                    $joins[] = "JOIN `whakamahere_course_slots` sl ON (sl.`request_id` = r.`request_id`)";
                    $joins[] = "JOIN `whakamahere_course_times` ti ON (ti.`slot_id` = sl.`slot_id`)";
                    break;
                case 'unplanned':
                    $joins[] = "JOIN `whakamahere_requests` r ON (r.`course_id` = `seminare`.`Seminar_id`)";
                    $where .= " AND NOT EXISTS (
                            SELECT ws.`request_id` FROM `whakamahere_course_slots` ws
                                JOIN `whakamahere_course_times` wt ON (wt.`slot_id` = ws.`slot_id`)
                            WHERE ws.`request_id` = r.`request_id`
                        )";
                    break;
            }
        }

        if ($filter['min'] || $filter['max']) {
            $joins[] = "LEFT JOIN `whakamahere_requests` tr ON (tr.`course_id` = `seminare`.`Seminar_id`)";
            $joins[] = "LEFT JOIN `whakamahere_property_requests` tpr ON (tpr.`request_id` = tr.`request_id`)";

            if ($filter['min'] && !$filter['max']) {
                $where .= " AND (
                        (tr.`request_id` IS NOT NULL AND tpr.`property_id` = :seats AND tpr.`value` >= :min)
                        OR
                        (tr.`request_id` IS NULL AND `seminare`.`admission_turnout` >= :min)
                    )";
                $parameters['min'] = (int) $filter['min'];
            } else if (!$filter['min'] && $filter['max']) {
                $where .= " AND (
                        (tr.`request_id` IS NOT NULL AND tpr.`property_id` = :seats AND tpr.`value` <= :max)
                        OR
                        (tr.`request_id` IS NULL AND `seminare`.`admission_turnout` <= :max)
                    )";
                $parameters['max'] = (int) $filter['max'];
            } else {
                $where .= " AND (
                        (tr.`request_id` IS NOT NULL AND tpr.`property_id` = :seats AND tpr.`value` BETWEEN :min AND :max)
                        OR
                        (tr.`request_id` IS NULL AND `seminare`.`admission_turnout` BETWEEN :min AND :max)
                    )";
                $parameters['min'] = (int) $filter['min'];
                $parameters['max'] = (int) $filter['max'];
            }

            $parameters['seats'] = WhakamaherePropertyRequest::getSeatsPropertyId();
        }

        $order = " ORDER BY `seminare`.`VeranstaltungsNummer`, `seminare`.`Name`";

        if ($limit != 0) {
            $order .= " LIMIT :offset, :limit";
            $parameters['offset'] = (int) $offset;
            $parameters['limit'] = (int) $limit;
        }

        return [
            'sql' => implode(" ", $joins) . $where . $order,
            'parameters' => $parameters
        ];
    }

}
