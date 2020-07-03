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
        /*$this->render_json(
            DBManager::get()->fetchFirst("SELECT DISTINCT r.`course_id`
                FROM `whakamahere_requests` r
                    JOIN `whakamahere_course_slots` cs ON (cs.`request_id` = r.`request_id`)
                    JOIN `whakamahere_course_times` ct ON (ct.`slot_id` = cs.`slot_id`)
                    JOIN `whakamahere_time_bookings` tb ON (tb.`time_id` = ct.`time_id`)
                    JOIN `resource_bookings` b ON (b.`id` = tb.`booking_id`)
                    JOIN `seminare` s ON (s.`Seminar_id` = r.`course_id`)
                    JOIN `semester_data` sd ON (sd.`beginn` = s.`start_time`)
                WHERE sd.`semester_id` = :semester",
                ['semester' => $this->semester->id]
            )
        );*/
        $this->render_json([
            '019f2a6102558a61ab3794bd94264412',
            '021c468881d9454d3c25ca7a3a8e8526',
            '052730662655aacc6576a1206e315cd4',
            '0cd8de9b57255050fd2c775d11a2947f',
            '1035e794f89f2b421f7e80c56b58eb2d',
            '0e250995bab6b8ed8b24ae8a203d9fe9',
            '341086f83fcf8676259e8f9d57156b3a'
        ]);
    }

    /**
     * Publish a single course, creating real course times and resource bookings from planned data.
     */
    public function course_action($course_id)
    {
        $result = [];

        $request = WhakamaherePlanningRequest::findOneByCourse_id($course_id);

        $lastweek = max(array_keys(WhakamaherePlanningRequest::getEndWeeks($request->course->start_semester)));

        // Delete all course cycles in this course - planning takes precedence.
        SeminarCycleDate::deleteBySeminar_id($course_id);

        foreach ($request->slots as $slot) {

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

            /*
             * Now assign the "reserved" rooms to every generated date,
             * converting the planned bookings to real ones.
             */
            foreach ($cycle->dates as $date) {
                $booking = WhakamahereTimeBooking::findByTimeAndDate(
                    $slot->planned_time->time_id, $date->date, $date->end_time);

                if ($booking->booking) {
                    $booking->booking->booking_type = 0;
                    $booking->booking->range_id = $date->id;
                    $booking->booking->description = '';
                    $booking->booking->store();
                } else {
                    $this->set_status(206);
                    $result[] = [
                        'operation' => 'book_room',
                        'course_id' => $course_id,
                        'course_name' => $request->course->veranstaltungsnummer ?
                            $request->course->veranstaltungsnummer . ' ' . $request->course->name :
                            $request->course->name,
                        'failed_date' => $date->date
                    ];
                }
            }
        }

        $this->render_json($result);
    }

}