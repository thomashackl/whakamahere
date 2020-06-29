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
        $config['has_many']['bookings'] = [
            'class_name' => 'WhakamahereTimeBooking',
            'foreign_key' => 'time_id',
            'assoc_foreign_key' => 'time_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];
        $config['has_many']['exceptions'] = [
            'class_name' => 'WhakamahereCourseTimeException',
            'foreign_key' => 'time_id',
            'assoc_foreign_key' => 'time_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
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
        $query = "SELECT DISTINCT t.* FROM `whakamahere_course_times` t";

        $joins = [
            " JOIN `seminare` s ON (s.`Seminar_id` = t.`course_id`)",
        ];
        $where = [
            " WHERE 1"
        ];
        $params = [];

        foreach ($filter as $type => $one) {
            switch ($type) {
                case 'semester':
                    $joins[] = "JOIN `semester_data` sem ON (s.`start_time` BETWEEN sem.`beginn` AND sem.`ende`)";
                    $where[] = "AND sem.`semester_id` = :semester";
                    $params['semester'] = $one;
                    break;
                case 'searchterm':
                    $where[] = "AND (s.`VeranstaltungsNummer` LIKE :search OR s.`Name` LIKE :search)";
                    $params['search'] = '%' . $one . '%';
                    break;
                case 'seats':
                    $joins[] = "JOIN `whakamahere_requests` r ON (r.`course_id` = t.`course_id`)";
                    $joins[] = "JOIN `whakamahere_property_requests` pr ON (pr.`request_id` = r.`request_id`)";
                    $qhere[] = "AND pr.`property_id` = :seats";
                    $params['seats'] = WhakamaherePropertyRequest::getSeatsPropertyId();
                    if ($filter['seats']['min'] && $filter['seats']['max']) {
                        $where[] = "AND pr.`value` BETWEEN :min AND :max";
                        $params['min'] = $one['min'];
                        $params['max'] = $one['max'];
                    } else if ($filter['seats']['min']) {
                        $where[] = "AND pr.`value` >= :min";
                        $params['min'] = $one['min'];
                    } else if ($filter['seats']['max']) {
                        $where[] = "AND pr.`value` <= :max";
                        $params['max'] = $one['max'];
                    }
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
                    $joins[] = "JOIN `whakamahere_course_slots` cs ON (cs.`slot_id` = t.`slot_id`)";
                    $where[] = "AND cs.`user_id` = :lecturer";
                    $params['lecturer'] = $one;
                    break;
                case 'room':
                    if ($one != 'without-room') {
                        $joins[] = "JOIN `whakamahere_time_bookings` tb ON (tb.`time_id` = t.`time_id`)";
                        $joins[] = "JOIN `resource_bookings` rb ON (rb.`id` = tb.`booking_id`)";
                        $where[] = "AND rb.`resource_id` = :room";
                        $params['room'] = $one;
                    } else {
                        $where[] = "AND NOT EXISTS (
                            SELECT `booking_id` FROM `whakamahere_time_bookings` WHERE `time_id` = t.`time_id`
                        )";
                    }
                    break;
                case 'week':
                    $joins[] = "JOIN `whakamahere_requests` wr ON (wr.`course_id` = t.`course_id`)";
                    $where[] = "AND (
                            :week >= wr.`startweek` + 1
                            AND :week <= :lastweek - wr.`end_offset`
                            AND MOD(:week - wr.`startweek` + 1, wr.`cycle`) = 0
                            AND t.`weekday` IN (:weekdays)
                        )";
                    $params['week'] = $one + 1;
                    $params['lastweek'] = $filter['lastweek'];
                    $params['weekdays'] = self::getNonHolidayWeekdays($filter['semester'], $one);
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
    public function proposeRooms()
    {
        $requestedProperties = $this->slot->request->property_requests;

        $roomWish = $this->slot->request->room_id;
        $seatsId = WhakamaherePropertyRequest::getSeatsPropertyId();
        $seats = $requestedProperties->findOneBy('property_id', $seatsId)->value;

        // Use some tolerance in requested seats.
        $seatsMin = (Config::get()->WHAKAMAHERE_SEATS_LOWER_LIMIT / 100) * $seats;
        $seatsMax = (Config::get()->WHAKAMAHERE_SEATS_UPPER_LIMIT / 100) * $seats;

        /*
         * Find all rooms which have approximately the suitable size.
         * Requested properties will be checked later on. An explicitly requested
         * room will always be part of the result, no matter if it fits.
         */
        $entries = DBManager::get()->fetchAll(
            "SELECT DISTINCT r.*
            FROM `resources` r
                INNER JOIN `resource_properties` s ON (s.`resource_id` = r.`id` AND s.`property_id` = :seatsid)
            WHERE r.`id` = :roomwish
                OR s.`state` BETWEEN :seatsmin AND :seatsmax
            ORDER BY r.`name`",
            [
                'seatsid' => $seatsId,
                'roomwish' => $roomWish,
                'seatsmin' => (int) $seatsMin,
                'seatsmax' => (int) $seatsMax
            ],
            'Room::buildExisting'
        );

        $timeRanges = $this->buildTimeRanges();

        /*
         * Now iterate over found rooms and set a score according
         * to how good the room matches the request.
         */
        $rooms = [];
        foreach ($entries as $one) {
            $rooms[] = $this->scoreRoom($one, $timeRanges, $requestedProperties, $seatsId);
        }

        // Sort found rooms by score.
        usort($rooms, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        return $rooms;
    }

    /**
     * Fetches all already existing bookings for a room overlapping with the current CourseTime.
     *
     * @param string $room_id the room to check
     */
    public function checkForBookings($room_id, $timeranges)
    {
        return count($timeranges) > 0 ?
            ResourceBooking::findByResourceAndTimeRanges(Room::find($room_id), $timeranges) :
            [];
    }

    /**
     * Builds all relevant real dates for this CourseTime.
     */
    public function buildTimeRanges()
    {
        // Weekday numbers and names for calculation.
        $weekdays = $this->getWeekdays();

        // Timeranges to check for bookings.
        $timeranges = [];

        $request = $this->slot->request;

        // Find first date matching the selected weekday.
        $start = new DateTime('', new DateTimeZone('Europe/Berlin'));
        $start->setTimestamp($request->course->start_semester->vorles_beginn);
        $start->sub(new DateInterval('P1D'));
        $start->modify('next ' . $weekdays[$this->weekday] . ' ' . $this->start);

        $end = new DateTime('', new DateTimeZone('Europe/Berlin'));
        $end->setTimestamp($request->course->start_semester->vorles_beginn);
        $end->sub(new DateInterval('P1D'));
        $end->modify('next ' . $weekdays[$this->weekday] . ' ' . $this->end);

        // Week offset if necessary
        if ($request->startweek > 0) {
            $startweekInterval = new DateInterval('P' . ($request->startweek - 1) . 'W');
            $start->add($startweekInterval);
            $end->add($startweekInterval);
        }

        $interval = new DateInterval('P' . ($request->cycle * 7) . 'D');

        /*
         * Build all relevant days and times for checking.
         */
        while ($start->getTimestamp() < $request->course->start_semester->vorles_ende) {
            if (!SemesterHoliday::isHoliday($start->getTimestamp())) {
                $timeranges[] = [
                    'begin' => $start->getTimestamp(),
                    'end' => $end->getTimestamp()
                ];
            }
            $start->add($interval);
            $end->add($interval);
        }

        return $timeranges;
    }

    /**
     * Books the given room for the relevant time ranges. If the room is
     * already occupied at some date, a corresponding entry will be returned.
     *
     * @param Room $room
     * @param array $timeRange an array specifying the time range to book
     *                         (['begin' => <begin>, 'end' => <end>])
     *
     * @return WhakamahereTimeBooking|false
     */
    public function bookRoom($room, $timeRange)
    {
        $success = false;

        /*
         * First check if room is already occupied at the current time,
         * otherwise we don't need to try to book. We must do that
         * before we create the ResourceBooking object because
         * reservations are deleted automatically which we don't want.
         */
        if (count(ResourceBooking::findByResourceAndTimeRanges($room, [$timeRange])) == 0) {
            $booking = new ResourceBooking();
            $booking->resource_id = Request::option('room');
            $booking->range_id = $this->slot->request->course->id;
            $booking->booking_user_id = $GLOBALS['user_id'];
            $booking->description = 'Planung: ' . $this->slot->request->course->getFullname();
            $booking->begin = $timeRange['begin'];
            $booking->end = $timeRange['end'];
            $booking->booking_type = 3;
            if ($booking->store() !== false) {
                $tb = new WhakamahereTimeBooking();
                $tb->time_id = $this->time_id;
                $tb->booking_id = $booking->id;
                $tb->mkdate = date('Y-m-d H:i:s');
                $tb->chdate = date('Y-m-d H:i:s');

                if ($this->time_id) {
                    $tb->store();
                }

                $success = $tb;
            }
        }

        return $success;
    }

    /**
     * Deletes all assigned room bookings.
     */
    public function clearBookings()
    {
        // Remove all associated room bookings.
        if (count($this->bookings) > 0) {
            ResourceBooking::deleteBySQL("`id` IN (?)", [$this->bookings->pluck('booking_id')]);
        }
    }

    /**
     * Checks whether all planned dates have an assigned room booking.
     *
     * @return bool true if not all planned dates have a room
     */
    public function hasPartialBookings()
    {
        return (count($this->bookings) > 0 && (count($this->buildTimeRanges()) != count($this->bookings)));
    }

    /**
     * Provides an array structure usable for JSON encoding.
     *
     * @return array
     */
    public function formatForSchedule()
    {
        $seatsId = WhakamaherePropertyRequest::getSeatsPropertyId();

        $result = [
            'id' => $this->course_id . '-' . $this->slot_id,
            'time_id' => (int) $this->id,
            'course_id' => $this->course_id,
            'course_name' => (string) $this->course->name,
            'course_number' => $this->course->veranstaltungsnummer,
            'turnout' => (int) $this->slot->request->property_requests->findOneBy('property_id', $seatsId)->value,
            'slot_id' => (int) $this->slot_id,
            'lecturer_id' => $this->slot->user_id,
            'lecturer' => $this->slot->user_id ? $this->slot->user->getFullname() : 'N. N.',
            'partial_bookings' => $this->hasPartialBookings(),
            'pinned' => $this->pinned == 0 ? false : true,
            'weekday' => (int) $this->weekday,
            'start' => $this->start,
            'end' => $this->end
        ];

        $result['bookings'] = [];
        $rooms = [];
        foreach ($this->bookings as $booking) {
            $rooms[(string) $booking->booking->resource->name] = true;
            $result['bookings'][] = [
                'booking_id' => $booking->booking_id,
                'room' => (string) $booking->booking->resource->name,
                'begin' => (int) $booking->booking->begin,
                'end' => (int) $booking->booking->end
            ];
        }
        $result['rooms'] = implode(',', array_keys($rooms));

        usort($result['bookings'], function($a, $b) {
            return $a['begin'] - $b['begin'];
        });

        return $result;
    }

    public function scoreRoom($room, $timeRanges, $requestedProperties, $seatsId)
    {
        $entry = [
            'id' => $room->id,
            'name' => $room->name,
            'missing_properties' => [],
            'score' => 100
        ];
        // Check if current room is already occupied anywhere in the semester.
        $entry['occupied'] = array_map(function($b) {
            return [
                'id' => $b->id,
                'begin' => $b->begin,
                'end' => $b->end,
                'type' => $b->booking_type
            ];
        }, $this->checkForBookings($room->id, $timeRanges));

        // Mark room as "always occupied" if all dates are already booked.
        $entry['always_occupied'] = (count($entry['occupied']) == count($timeRanges));

        // The wished room is prioritized.
        if ($room->id == $this->slot->request->room_id) {

            $entry['seats'] = (int) $room->properties->findOneBy('property_id', $seatsId)->state;
            $entry['score'] = 100.49;

        } else {

            // Now increase score for each matching property.
            foreach ($requestedProperties as $property) {

                $roomProperty = $room->properties->findOneBy('property_id', $property->property_id);

                // Seats are treated specially
                if ($property->property_id == $seatsId) {

                    $entry['seats'] = (int) $roomProperty->state;

                    // Generate score depending on seats number difference
                    if ($roomProperty->state == 0) {
                        $entry['score'] = 0;
                    } else if ($roomProperty->state >= $property->value) {
                        $entry['score'] *= $property->value / $roomProperty->state;
                    } else {
                        $entry['score'] = 0.75 * $entry['score'] * ($roomProperty->state / $property->value);
                    }

                } else {

                    // Lower score points for each unfulfilled property request.
                    if (!$roomProperty || $roomProperty->state != $property->value) {
                        $entry['score'] *= 0.9;
                        $entry['missing_properties'][] = (string) $property->property->display_name;
                    }

                }
            }

        }

        return $entry;
    }

    /**
     * Fetch all weekdays in a given week that are no holidays.
     *
     * @param string $semester_id semester ID
     * @param int $week week number relative to lecturing period start
     */
    public static function getNonHolidayWeekdays($semester_id, $week)
    {
        $weekdays = [];

        $weeks = WhakamaherePlanningRequest::getStartWeeks(Semester::find($semester_id));
        $weekData = $weeks[$week];
        $tz = new DateTimeZone('Europe/Berlin');
        $start = new DateTime($weekData['startDate'], $tz);
        $end = new DateTime($weekData['endDate'], $tz);
        $oneDay = new DateInterval('P1D');

        while ($start <= $end) {
            if (!SemesterHoliday::isHoliday($start->getTimestamp())) {
                $weekdays[] = $start->format('N');
            }
            $start->add($oneDay);
        }

        return $weekdays;
    }

    private function getWeekdays()
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
