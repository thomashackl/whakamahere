<?php

/**
 * WhakamaherePublishLogEntry.php
 * model class for log entries used in publishing.
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
 * @property int entry_id database column
 * @property string semester_id database column
 * @property string course_id database column
 * @property string time_id database column
 * @property string exception_id database column
 * @property string cycle_id database column
 * @property string date_id database column
 * @property string booking_id database column
 * @property string user_id database column
 * @property string state database column
 * @property string note database column
 * @property string mkdate database column
 */

class WhakamaherePublishLogEntry extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_publish_log';
        $config['belongs_to']['semester'] = [
            'class_name' => 'Semester',
            'foreign_key' => 'semester_id',
            'assoc_foreign_key' => 'semester_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
            'assoc_foreign_key' => 'seminar_id'
        ];
        $config['belongs_to']['time'] = [
            'class_name' => 'WhakamahereCourseTime',
            'foreign_key' => 'time_id',
            'assoc_foreign_key' => 'time_id'
        ];
        $config['belongs_to']['exception'] = [
            'class_name' => 'WhakamahereCourseTimeException',
            'foreign_key' => 'exception_id',
            'assoc_foreign_key' => 'exception_id'
        ];
        $config['belongs_to']['cycle'] = [
            'class_name' => 'SeminarCycleDate',
            'foreign_key' => 'cycle_id',
            'assoc_foreign_key' => 'metadate_id'
        ];
        $config['belongs_to']['date'] = [
            'class_name' => 'CourseDate',
            'foreign_key' => 'date_id',
            'assoc_foreign_key' => 'termin_id'
        ];
        $config['belongs_to']['booking'] = [
            'class_name' => 'ResourceBooking',
            'foreign_key' => 'booking_id',
            'assoc_foreign_key' => 'id'
        ];
        $config['belongs_to']['creator'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }

    public function formatForDisplay()
    {
        return [
            'id' => $this->id,
            'semester' => [
                'id' => $this->semester_id,
                'name' => (string) $this->semester->name
            ],
            'course' => [
                'id' => $this->course_id,
                'number' => $this->course->veranstaltungsnummer,
                'name' => (string) $this->course->name,
                'fullname' => $this->course->getFullname()
            ],
            'time' => [
                'id' => (int) $this->time_id,
                'weekday' => (int) $this->time->weekday,
                'start' => date('H:i', strtotime($this->time->start)),
                'end' => date('H:i', strtotime($this->time->end)),
                'text' => (string) $this->time
            ],
            'exception' => $this->exception_id == null ? [] : [],
            'date' => $this->date_id == null ? [] : [],
            'booking' => $this->booking_id == null ?
                [] :
                [
                    'id' => $this->booking->id,
                    'start' => (int) $this->booking->begin,
                    'end' => (int) $this->booking->end,
                    'room' => $this->booking->resource->name
                ],
            'user' => [
                'id' => $this->creator->id,
                'firstname' => $this->creator->vorname,
                'lastname' => $this->creator->nachname,
                'fullname' => $this->creator->getFullname()
            ],
            'state' => $this->state,
            'note' => $this->note,
            'mkdate' => $this->mkdate,
        ];
    }

    public static function countBySemester_id($semester_id, $filter = [])
    {
        $sql = "`semester_id` = :semester";
        $parameters = ['semester' => $semester_id];

        if ($filter['status']) {
            $sql .= " AND `state` = :status";
            $parameters['status'] = $filter['status'];
        }

        return WhakamaherePublishLogEntry::countBySQL($sql, $parameters);
    }

    public static function findFiltered($semester_id, $start, $limit, $filter = [])
    {
        $sql = "JOIN `seminare` s ON (s.`Seminar_id` = `whakamahere_publish_log`.`course_id`)
                JOIN `whakamahere_course_times` t ON (t.`time_id` = `whakamahere_publish_log`.`time_id`)
            WHERE `semester_id` = :semester";
        $parameters = ['semester' => $semester_id];

        if ($filter['status']) {
            $sql .= " AND `whakamahere_publish_log`.`state` = :status";
            $parameters['status'] = $filter['status'];
        }

        $sql .= " ORDER BY s.`VeranstaltungsNummer`, s.`Name`";

        if ($start != 0 && $limit != 0) {
            $sql .= "LIMIT :start, :limit";
            $parameters['start'] = (int) $start;
            $parameters['limit'] = (int) $limit;
        }

        return self::findBySQL($sql, $parameters);
    }

    public static function getStatusMessages()
    {
        return [
            'SUCCESS' => 'Erfolgreich.',
            'WARNING' => 'Teilweise erfolgreich.',
            'ERROR_NO_DATES' => 'Es wurden keine Termine erzeugt.',
            'ERROR_STORE_BOOKING' => 'Die Raumbuchung konnte nicht gespeichert werden.',
            'ERROR_BOOKING_NOT_FOUND' => 'Die verknüpfte Raumbuchung wurde nicht gefunden.',
            'ERROR_NO_BOOKING_ASSIGNED' => 'Es wurde kein Raum gebucht.'
        ];
    }

}
