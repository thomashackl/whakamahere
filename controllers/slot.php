<?php

/**
 * Class SlotController
 * Helper controller for course slot related actions.
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

class SlotController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (!$this->plugin->hasPermission('read')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->selectedSemester = UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_SEMESTER != '' ?
            UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_SEMESTER :
            Semester::findNext()->id;

    }

    /**
     * Stores a course assignment to a time slot and (optionally) a room.
     */
    public function store_time_action()
    {
        $startDate = new DateTime(Request::get('start'));
        $endDate = new DateTime(Request::get('end'));

        $oldWeekday = false;

        if (Request::int('time_id', 0) != 0) {
            $time = WhakamahereCourseTime::find(Request::int('time_id'));
            $oldWeekday = $time->weekday;
        } else {
            $time = new WhakamahereCourseTime();
            $time->course_id = Request::option('course');
            $time->slot_id = Request::int('slot');
            $time->mkdate = date('Y-m-d H:i:s');
        }
        $time->weekday = $startDate->format('N');
        $time->start = $startDate->format('H:i');
        $time->end = $endDate->format('H:i');
        $time->chdate = date('Y-m-d H:i:s');

        // No room set, keep room bookings if applicable.
        if (Request::option('room', '') == '') {
            if (count($time->bookings) > 0) {

                $newBookings = new SimpleCollection();

                // If weekday stayed the same, try to to keep the booked room.
                if ($time->weekday === $oldWeekday) {
                    foreach ($time->bookings as $one) {

                        $one->booking->begin = strtotime(date('d.m.Y ', $one->booking->begin) . $time->start);
                        $one->booking->end = strtotime(date('d.m.Y ', $one->booking->end) . $time->end);
                        $one->booking->chdate = time();
                        $one->chdate = date('Y-m-d H:i:s');

                        if ($one->store() !== false) {
                            $rooms[(string) $one->booking->resource->name] = true;
                            $newBookings->append($one);
                        }
                    }
                }

                $time->bookings = $newBookings;
            }

        // A room is given, try to book it.
        } else {

            $newBookings = new SimpleCollection();
            $room = Room::find(Request::option('room'));

            $ranges = $time->buildTimeRanges();

            foreach ($ranges as $range) {

                if (($booking = $time->bookRoom($room, $range)) !== false) {
                    $result['booked'][] = [
                        'booking_id' => $booking->id,
                        'room' => (string) $room->name,
                        'begin' => (int) $range['begin'],
                        'end' => (int) $range['end']
                    ];
                    $newBookings->append($booking);
                } else {
                    $result['failed'][] = [
                        'room' => (string) $room->name,
                        'begin' => (int) $range['begin'],
                        'end' => (int) $range['end']
                    ];
                }
            }

            $time->bookings = $newBookings;
        }

        if ($time->store()) {
            $this->set_status(200, 'Time assignment saved.');
            $this->render_json($time->formatForSchedule());
        } else {
            $this->set_status(500, 'Could not save time assignment.');
            $this->render_nothing();
        }
    }

    /**
     * Stores an exception to a regular CourseTime in a given week.
     */
    public function store_exception_action()
    {
        $startDate = new DateTime(Request::get('start'));
        $endDate = new DateTime(Request::get('end'));

        $oldWeekday = false;

        $time = WhakamahereCourseTime::find(Request::int('time_id'));

        if (Request::int('exception_id', 0) != 0) {
            $exception = WhakamahereCourseTimeException::find(Request::int('exception_id'));
            $oldWeekday = $exception->weekday;
        } else {
            $exception = new WhakamahereCourseTimeException();
            $exception->time_id = Request::int('time_id');
            $exception->mkdate = date('Y-m-d H:i:s');
        }
        $exception->start = $startDate->format('Y-m-d H:i');
        $exception->end = $endDate->format('Y-m-d H:i');
        $exception->chdate = date('Y-m-d H:i:s');

        // No room set, keep room bookings if applicable.
        if (Request::option('room', '') == '') {
            if ($exception->booking != null) {

            }

        // A room is given, try to book it.
        } else {

        }

        if ($exception->store()) {
            $this->set_status(200, 'Time assignment saved.');
            $this->render_json($exception->formatForSchedule());
        } else {
            $this->set_status(500, 'Could not save time assignment.');
            $this->render_nothing();
        }
    }

    /**
     * Gets availability for time slots in a week. At the moment only lecturer availability is checked.
     * @param string $lecturer
     */
    public function availability_action($lecturer)
    {
        $slots = [];

        $start = DateTime::createFromFormat('H:i', Config::get()->WHAKAMAHERE_PLANNING_START_HOUR);
        $end = DateTime::createFromFormat('H:i', Config::get()->WHAKAMAHERE_PLANNING_END_HOUR);

        $occupied = WhakamahereCourseTime::findByUserAndSemester($lecturer, $this->selectedSemester);

        // Iterate over weekdays
        $endDay = Config::get()->WHAKAMAHERE_PLANNING_SHOW_WEEKENDS ? 7 : 5;
        for ($weekday = 1 ; $weekday <= $endDay ; $weekday++) {
            $thisDay = array_filter($occupied, function($value) use ($weekday) {
                return $value->weekday == $weekday;
            });

            if (count($thisDay) == 0) {

                $slots[] = [
                    'weekday' => $weekday,
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i'),
                    'free' => true
                ];

            } else {

                $currentStart = $start;

                while ($current = array_shift($thisDay)) {

                    if ($current->start != $currentStart->format('H:i:s')) {
                        $slots[] = [
                            'weekday' => $weekday,
                            'start' => $currentStart->format('H:i'),
                            'end' => $current->start,
                            'free' => true
                        ];
                    }

                    $slots[] = [
                        'weekday' => (int) $current->weekday,
                        'start' => $current->start,
                        'end' => $current->end,
                        'free' => false
                    ];

                    $currentStart = DateTime::createFromFormat('H:i:s', $current->end);

                }

                if ($currentStart != $end) {
                    $slots[] = [
                        'weekday' => $weekday,
                        'start' => $currentStart->format('H:i'),
                        'end' => $end->format('H:i'),
                        'free' => true
                    ];
                }

            }
        }

        $this->render_json($slots);
    }

    /**
     * Get fitting rooms for a request.
     *
     * @param int $time_id
     */
    public function room_proposals_action($time_id)
    {
        PageLayout::setTitle(dgettext('whakamahere', 'Raumvorschläge'));
        $time = WhakamahereCourseTime::find($time_id);

        if ($time) {

            $result = [
                'time_id' => $time_id,
                'slot_id' => $time->slot->id,
                'course_id' => $time->course->id,
                'course' => $time->course->getFullname(),
                'seats' => $time->slot->request->property_requests->findOneBy(
                    'property_id', WhakamaherePropertyRequest::getSeatsPropertyId()
                )->value,
                'rooms' => $time->proposeRooms()
            ];
            $this->render_json($result);

        } else {
            $this->set_status(404, 'Time assignment not found.');
            $this->render_text('Time assignment not found');
        }
    }

    public function manual_room_search_action($time_id)
    {
        $time = WhakamahereCourseTime::find($time_id);

        $rooms = DBManager::get()->fetchAll(
            "SELECT * FROM `resources` WHERE `name` LIKE :search",
            ['search' => '%' . Request::get('search') . '%'],
            'Room::buildExisting'
        );

        $props = $time->slot->request->property_requests;
        $seatsId = WhakamaherePropertyRequest::getSeatsPropertyId();
        $timeRanges = $time->buildTimeRanges();

        $result = [];
        foreach ($rooms as $room) {
            $result[] = $time->scoreRoom($room, $timeRanges, $props, $seatsId);
        }

        $this->render_json($result);
    }

    /**
     * Remove an already planned course from schedule.
     *
     * @param int $slot_id the slot to remove
     */
    public function unplan_action($slot_id)
    {
        $planned = WhakamahereCourseTime::findOneBySlot_id($slot_id);

        if ($planned) {

            if ($planned->delete()) {
                $this->set_status(200, 'Time assignment removed.');
            } else {
                $this->set_status(500, 'Could not remove time assignment.');
            }
        } else {
            $this->set_status(404, 'Time assignment not found.');
        }

        $this->render_nothing();
    }

    /**
     * (Un)Pins a course slot.
     *
     * @param int $slot_id the slot to (un)pin
     */
    public function setpin_action($slot_id)
    {
        $slot = WhakamahereCourseTime::findOneBySlot_id($slot_id);

        if ($slot) {
            $slot->pinned = $slot->pinned == 0 ? 1 : 0;
            $slot->chdate = date('Y-m-d H:i:s');

            if ($slot->store()) {
                $this->set_status(200, 'Pin setting saved.');
            } else {
                $this->set_status(500, 'Could not save pin setting.');
            }
        } else {
            $this->set_status(404, 'Time assignment not found.');
        }
        $this->render_nothing();
    }

    /**
     * Show full details for a given slot.
     *
     * @param int $slot_id the slot to show details for.
     */
    public function details_action($slot_id)
    {
        $slot = WhakamahereCourseSlot::find($slot_id);

        if ($slot) {
            $request = $slot->request;

            $this->render_json([
                'bookings' => $slot->planned_time->bookings->map(function ($value, $key) {
                    return [
                        'id' => $value->booking_id,
                        'room' => (string) $value->booking->resource->name,
                        'begin' => date('d.m.Y H:i', $value->booking->begin),
                        'end' => date('H:i', $value->booking->end),
                    ];
                }),
                'comment' => $request->comment,
                'course' => $request->course->getFullname(),
                'cycle' => (int) $request->cycle,
                'duration' => (int) $slot->duration,
                'internal_comment' => $request->internal_comment,
                'lecturer' => $slot->user_id ? $slot->user->getFullname() : 'N. N.',
                'property_requests' => $request->property_requests->map(function ($value, $key) {
                    return [
                        'id' => $value->property_id,
                        'name' => (string) $value->property->display_name,
                        'value' => $value->value == 1 ? 'ja' : $value->value
                    ];
                }),
                'room' => $request->room_id ? $request->room->name : dgettext('whakamahere', 'nicht angegeben'),
                'semester' => (string) $request->course->start_semester->name,
                'startweek' => (int) $request->startweek,
                'end_offset' => (int) $request->end_offset,
                'time' => $slot->time,
                'weekday' => (int) $slot->weekday
            ]);
        } else {
            $this->set_status(404, 'Time assignment not found.');
            $this->render_text('Time assignment not found');
        }
    }

    public function book_room_action($time_id)
    {
        $time = WhakamahereCourseTime::find($time_id);
        $room = Room::find(Request::option('room'));

        $result = [
            'booked' => [],
            'failed' => []
        ];

        if ($time && $room) {

            // First clear all existing bookings.
            $time->bookings = new SimpleCollection();
            // Try to book room.
            foreach ($time->buildTimeRanges() as $range) {
                if (($booking = $time->bookRoom($room, $range)) !== false) {
                    $result['booked'][] = [
                        'booking_id' => $booking->id,
                        'room' => (string) $room->name,
                        'begin' => (int) $range['begin'],
                        'end' => (int) $range['end']
                    ];
                } else {
                    $result['failed'][] = [
                        'room' => (string) $room->name,
                        'begin' => (int) $range['begin'],
                        'end' => (int) $range['end']
                    ];
                }
            }

            if (count($result['failed']) == 0) {
                $this->set_status(200);
                $this->render_json([
                    'booked' => $result['booked'],
                    'room_names' => [(string) $room->name]
                ]);
            } else {
                $this->set_status(206);
                $this->render_json([
                    'booked' => $result['booked'],
                    'failed' => $result['failed'],
                    'room_names' => [(string) $room->name]
                ]);
            }

        } else {
            $this->set_status(404, 'Time assignment or room not found.');
            $this->render_text('Time assignment or room not found');
        }
    }

    public function remove_bookings_action($time_id)
    {
        $time = WhakamahereCourseTime::find($time_id);

        if ($time) {
            $time->bookings = new SimpleCollection();
            $time->chdate = date('Y-m-d H:i:s');

            if ($time->store()) {
                $this->set_status(200);
                $this->render_json($time->formatForSchedule());
            } else {
                $this->set_status(500, 'Could not clear room assignments.');
                $this->render_text('Could not clear room assignments.');
            }
        } else {
            $this->set_status(404, 'Time assignment or room not found.');
            $this->render_text('Time assignment or room not found');
        }
    }

}
