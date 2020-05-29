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

        if (Request::option('room', '') == '') {
            if (Request::int('time_id', 0) != 0) {
                $time = WhakamahereCourseTime::find(Request::int('time_id'));
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
            if ($time->store()) {
                $this->set_status(200, 'Time assignment saved.');
                $this->render_json($time->toArray());
            } else {
                $this->set_status(500, 'Could not save time assignment.');
                $this->render_nothing();
            }
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
                'semester' => (string) $request->semester->name,
                'startweek' => (int) $request->startweek,
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

        if ($time && $room) {

            $timeRanges = $time->buildTimeRanges();

            // Some structure for reporting what happened.
            $booked = [];
            $failed = [];
            foreach ($timeRanges as $range) {
                /*
                 * First check if room is already occupied at the current time,
                 * otherwise we don't need to try to book. We must do that
                 * before we create the ResourceBooking object because
                 * reservations are deleted automatically which we don't want.
                 */
                if (count(ResourceBooking::findByResourceAndTimeRanges($room, [$range])) == 0) {
                    $booking = new ResourceBooking();
                    $booking->resource_id = Request::option('room');
                    $booking->range_id = $time->slot->request->course->id;
                    $booking->booking_user_id = $GLOBALS['user_id'];
                    $booking->description = 'Planung: ' . $time->slot->request->course->getFullname();
                    $booking->begin = $range['begin'];
                    $booking->end = $range['end'];
                    $booking->booking_type = 3;
                    if ($booking->store() !== false) {
                        $tb = new WhakamahereTimeBooking();
                        $tb->time_id = $time_id;
                        $tb->booking_id = $booking->id;
                        $tb->mkdate = date('Y-m-d H:i:s');;
                        $time->bookings->append($tb);

                        $booked[] = [
                            'id' => $booking->id,
                            'begin' => $range['begin'],
                            'end' => $range['end']
                        ];
                    } else {
                        $failed[] = [
                            'begin' => $range['begin'],
                            'end' => $range['end']
                        ];
                    }
                }
            }

            $time->chdate = date('Y-m-d H:i:s');
            $time->store();

            if (count($failed) == 0) {
                $this->set_status(200);
                $this->render_json($booked);
            } else {
                $this->set_status(206);
                $this->render_json([
                    'booked' => $booked,
                    'failed' => $failed
                ]);
            }

        } else {
            $this->set_status(404, 'Time assignment or room not found.');
            $this->render_text('Time assignment or room not found');
        }
    }

}
