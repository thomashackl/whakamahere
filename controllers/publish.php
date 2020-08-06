<?php

/**
 * Class PublishController
 * Helper controller for publishing planning.
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

class PublishController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;

        if (!$this->plugin->hasPermission('admin')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();

        $semesterId = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_SELECTED_SEMESTER;

        $this->semester = $semesterId ? Semester::find($semesterId) : Semester::findNext();
    }

    /**
     * Find all courses in the given semester that are planned
     * (time + room) and can be published.
     */
    public function get_courses_action()
    {
        $this->render_json(
            DBManager::get()->fetchFirst("SELECT DISTINCT r.`course_id`
                FROM `whakamahere_requests` r
                    JOIN `whakamahere_course_slots` cs ON (cs.`request_id` = r.`request_id`)
                    JOIN `whakamahere_course_times` ct ON (ct.`slot_id` = cs.`slot_id`)
                    LEFT JOIN `whakamahere_time_bookings` tb ON (tb.`time_id` = ct.`time_id`)
                    LEFT JOIN `resource_bookings` b ON (b.`id` = tb.`booking_id`)
                    JOIN `seminare` s ON (s.`Seminar_id` = r.`course_id`)
                    JOIN `semester_data` sd ON (sd.`beginn` = s.`start_time`)
                WHERE sd.`semester_id` = :semester",
                ['semester' => $this->semester->id]
            )
        );
        /*$this->render_json([
            '019f2a6102558a61ab3794bd94264412',
            '021c468881d9454d3c25ca7a3a8e8526',
            '052730662655aacc6576a1206e315cd4',
            '0cd8de9b57255050fd2c775d11a2947f',
            '1035e794f89f2b421f7e80c56b58eb2d',
            '0e250995bab6b8ed8b24ae8a203d9fe9',
            '341086f83fcf8676259e8f9d57156b3a'
        ]);*/
    }

    /**
     * Publish a single course, creating real course times and resource bookings from planned data.
     */
    public function course_action($course_id)
    {
        $request = WhakamaherePlanningRequest::findOneByCourse_id($course_id);

        $result = [
            'course_id' => $course_id,
            'course_name' => $request->course->getFullname(),
            'slots' => [],
            'status' => 'success'
        ];

        $lastweek = max(array_keys(WhakamaherePlanningRequest::getEndWeeks($request->course->start_semester)));

        // Delete all course cycles in this course - planning takes precedence.
        SeminarCycleDate::deleteBySeminar_id($course_id);

        $status = WhakamaherePublishLogEntry::getStatusMessages();

        $errors = 0;

        foreach ($request->slots as $slot) {

            // Log what happens.
            $log = new WhakamaherePublishLogEntry();
            $log->semester_id = $request->course->start_semester->id;
            $log->course_id = $request->course->id;
            $log->user_id = $GLOBALS['user']->id;

            if ($slot->planned_time) {

                $log->time_id = $slot->planned_time->time_id;

                // Create a SeminarCycle = regular course time.
                $cycle = new SeminarCycleDate();
                $cycle->seminar_id = $course_id;
                $cycle->weekday = $slot->planned_time->weekday;
                $cycle->start_time = $slot->planned_time->start;
                $cycle->end_time = $slot->planned_time->end;
                $cycle->cycle = $request->cycle - 1;
                $cycle->week_offset = $request->startweek;
                $cycle->end_offset = $lastweek - $request->end_offset;
                // Real dates are generated on store.
                $cycle->store();

                $log->cycle_id = $cycle->id;

                /*
                 * Now assign the "reserved" rooms to every generated date,
                 * converting the planned bookings to real ones.
                 */
                if (count($cycle->dates) > 0) {

                    $failedDates = [];

                    foreach ($cycle->dates as $date) {
                        // If a room is booked, it is the same for all dates.
                        $room = null;

                        $log->date_id = $date->id;

                        $booking = WhakamahereTimeBooking::findByTimeAndDate(
                            $slot->planned_time->time_id, $date->date, $date->end_time);

                        if ($booking) {
                            $log->booking_id = $booking->booking_id;

                            if ($booking->booking) {

                                if ($room === null) {
                                    $room = Room::find($booking->booking->resource_id);
                                }

                                $booking->booking->booking_type = 0;
                                $booking->booking->range_id = $date->id;
                                $booking->booking->description = '';
                                if ($booking->booking->store() !== false) {

                                    $result['slots'][] = [
                                        'slot_id' => $slot->id,
                                        'status' => 'success',
                                        'date' => $date->date
                                    ];

                                    $log->state = 'success';

                                } else {

                                    $failedDates[] = [
                                        'date' => $date->date,
                                        'error' => str_replace(':room', $booking->booking->resource->name,
                                            $status['ERROR_STORE_BOOKING'])
                                    ];

                                    $result['slots'][] = [
                                        'slot_id' => $slot->id,
                                        'status' => 'error_store_booking',
                                        'date' => $date->date
                                    ];
                                    $errors++;

                                }

                            } else {

                                /*
                                 * Booking with the given ID does not exist, try to
                                 * create a new one.
                                 */
                                $timeRange = [
                                    'begin' => $date->date,
                                    'end' => $date->end_time
                                ];

                                /*
                                 * Some other date already has a room,
                                 * try to book it for this date as well.
                                 */
                                if ($room !== null) {
                                    if (count(ResourceBooking::findByResourceAndTimeRanges($room, [$timeRange])) == 0) {
                                        $rb = new ResourceBooking();
                                        $rb->resource_id = $room->id;
                                        $rb->range_id = $date->id;
                                        $rb->booking_user_id = $GLOBALS['user_id'];
                                        $rb->description = '';
                                        $rb->begin = $timeRange['begin'];
                                        $rb->end = $timeRange['end'];
                                        $rb->booking_type = 0;
                                        if ($rb->store() !== false) {

                                            $booking->id = $rb->id;
                                            $booking->chdate = date('Y-m-d H:i:s');
                                            $booking->store();

                                            $result['slots'][] = [
                                                'slot_id' => $slot->id,
                                                'status' => 'success',
                                                'date' => $date->date
                                            ];

                                            $log->state = 'success';

                                        } else {
                                            $failedDates[] = [
                                                'date' => $date->date,
                                                'error' => $status['ERROR_BOOKING_NOT_FOUND']
                                            ];
                                            $result['slots'][] = [
                                                'slot_id' => $slot->id,
                                                'status' => 'error_booking_not_found',
                                                'date' => $date->date
                                            ];
                                            $errors++;
                                        }
                                    } else {
                                        $failedDates[] = [
                                            'date' => $date->date,
                                            'error' => $status['ERROR_BOOKING_NOT_FOUND']
                                        ];
                                        $result['slots'][] = [
                                            'slot_id' => $slot->id,
                                            'status' => 'error_booking_not_found',
                                            'date' => $date->date
                                        ];
                                        $errors++;
                                    }
                                } else {
                                    $booking->delete();

                                    $failedDates[] = [
                                        'date' => $date->date,
                                        'error' => $status['ERROR_BOOKING_NOT_FOUND']
                                    ];
                                    $result['slots'][] = [
                                        'slot_id' => $slot->id,
                                        'status' => 'error_booking_not_found',
                                        'date' => $date->date
                                    ];
                                    $errors++;
                                }

                            }

                        // This date has no booked room.
                        } else {

                            /*
                             * Booking with the given ID does not exist, try to
                             * create a new one.
                             */
                            $timeRange = [
                                'begin' => $date->date,
                                'end' => $date->end_time
                            ];

                            /*
                             * Some other date already has a room,
                             * try to book it for this date as well.
                             */
                            if ($room !== null) {
                                if (count(ResourceBooking::findByResourceAndTimeRanges($room, [$timeRange])) == 0) {
                                    $rb = new ResourceBooking();
                                    $rb->resource_id = $room->id;
                                    $rb->range_id = $date->id;
                                    $rb->booking_user_id = $GLOBALS['user_id'];
                                    $rb->description = '';
                                    $rb->begin = $timeRange['begin'];
                                    $rb->end = $timeRange['end'];
                                    $rb->booking_type = 0;

                                    // Now create a matching WhakamahereTimeBooking.
                                    if ($rb->store() !== false) {

                                        $tb = new WhakamahereTimeBooking();
                                        $tb->time_id = $slot->planned_time->time_id;
                                        $tb->booking_id = $rb->id;
                                        $tb->mkdate = date('Y-m-d H:i:s');
                                        $tb->chdate = date('Y-m-d H:i:s');
                                        $tb->store();

                                        $result['slots'][] = [
                                            'slot_id' => $slot->id,
                                            'status' => 'success',
                                            'date' => $date->date
                                        ];

                                        $log->state = 'success';

                                    } else {
                                        $failedDates[] = [
                                            'date' => $date->date,
                                            'error' => $status['ERROR_NO_BOOKING_ASSIGNED']
                                        ];
                                        $result['slots'][] = [
                                            'slot_id' => $slot->id,
                                            'status' => 'error_no_booking_assigned',
                                            'date' => $date->date
                                        ];
                                        $errors++;
                                    }
                                } else {
                                    $failedDates[] = [
                                        'date' => $date->date,
                                        'error' => $status['ERROR_NO_BOOKING_ASSIGNED']
                                    ];
                                    $result['slots'][] = [
                                        'slot_id' => $slot->id,
                                        'status' => 'error_no_booking_assigned',
                                        'date' => $date->date
                                    ];
                                    $errors++;
                                }
                            } else {

                                $failedDates[] = [
                                    'date' => $date->date,
                                    'error' => $status['ERROR_NO_BOOKING_ASSIGNED']
                                ];
                                $result['slots'][] = [
                                    'slot_id' => $slot->id,
                                    'status' => 'error_no_booking_assigned',
                                    'date' => $date->date
                                ];
                                $errors++;
                            }

                        }

                    }

                    if (count($failedDates) > 0) {
                        if (count($failedDates) < count($cycle->dates)) {
                            $log->state = 'warning';
                            $log->note = implode("\n", array_map(function($entry) {
                                return date('d.m.Y H:i', $entry['date']) . ': ' . $entry['error'];
                            }, $failedDates));
                        } else {
                            $log->state = 'error';
                            $log->note = 'Alle Termine ohne Raumbuchung.';
                        }
                    }

                } else {
                    $result['slots'][] = [
                        'slot_id' => $slot->id,
                        'status' => 'error_no_dates',
                    ];
                    $errors++;

                    $log->state = 'error';
                    $log->note = $status['ERROR_NO_DATES'];
                }

            }

            $log->mkdate = date('Y-m-d H:i:s');
            $log->store();
        }

        if ($errors > 0) {
            if ($errors < count($request->slots)) {
                $result['status'] = 'warning';
            } else {
                $result['status'] = 'error';
            }
        }

        $this->render_json($result);
    }

}
