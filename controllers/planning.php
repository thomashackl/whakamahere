<?php

/**
 * Class PlanningController
 * Controller for planning process.
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

class PlanningController extends AuthenticatedController {

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

        $version = $this->plugin->getVersion();

        PageLayout::addScript($this->plugin->getPluginURL() .
            '/assets/javascripts/planning.js?v=' . $version);
        PageLayout::addStylesheet($this->plugin->getPluginURL() .
            '/assets/stylesheets/planning.css?v=' . $version);

        $this->selectedSemester = UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_SEMESTER != '' ?
            UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_SEMESTER :
            Semester::findNext()->id;

        $this->institutes = Institute::getMyInstitutes();
        $this->selectedInstitute = UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_INSTITUTE != '' ?
            UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_INSTITUTE :
            '';

        $this->selectedLecturer = UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_LECTURER != '' ?
            UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_LECTURER :
            '';

    }

    /**
     * Default action which loads current settings and courses.
     *
     * @param string $show 'semester' or 'week' view?
     * @throws Exception
     */
    public function index_action($show = 'semester')
    {
        // Navigation handling.
        Navigation::activateItem('/resources/whakamahere/planning');

        PageLayout::setTitle(dgettext('whakamahere', 'Planung'));

        $this->view = 'semester';

        // Schedule view start and end hours.
        $this->minTime = Config::get()->WHAKAMAHERE_PLANNING_START_HOUR;
        $this->maxTime = Config::get()->WHAKAMAHERE_PLANNING_END_HOUR;

        // Use Stud.IP locale
        $this->locale = $GLOBALS['user']->info->preferred_language ?
            substr($GLOBALS['user']->info->preferred_language, 0, 2) :
            'de';

        // Get first lecture week of semester as start.
        $semester = Semester::find($this->selectedSemester);
        $this->semesterStart = new DateTime();
        $this->semesterStart->setTimestamp($semester->vorles_beginn);

        // Show weekends?
        $this->weekends = Config::get()->WHAKAMAHERE_PLANNING_SHOW_WEEKENDS ? 'true' : 'false';

        $filter = [
            'semester' => $this->selectedSemester
        ];

        if ($this->selectedLecturer != '') {
            $filter['lecturer'] = $this->selectedLecturer;
        }

        if ($this->selectedInstitute != '') {

            $filter['institute'] = $this->selectedInstitute;
            $this->unplanned_courses = $this->getUnplannedCourses($filter);

        } else {

            $this->unplanned_courses = [];

        }

        $this->planned_courses = $this->getPlannedCourses($filter);

        $this->lecturers = $this->getLecturers($filter);

        $this->setupSidebar();

    }

    /**
     * Get planned courses for given settings.
     */
    public function planned_courses_action()
    {
        $filter = [
            'semester' => Request::option('semester'),
            'institute' => Request::get('institute'),
            'lecturer' => Request::get('lecturer')
        ];

        $this->render_json($this->getPlannedCourses($filter));
    }

    /**
     * Get unplanned courses for given settings.
     */
    public function unplanned_courses_action()
    {
        $filter = [
            'semester' => Request::option('semester'),
            'institute' => Request::get('institute'),
            'lecturer' => Request::get('lecturer')
        ];

        $this->render_json($this->getUnplannedCourses($filter));
    }

    /**
     * Get lecturers for given settings.
     */
    public function lecturers_action()
    {
        $filter = [
            'semester' => Request::option('semester'),
            'institute' => Request::get('institute')
        ];

        $this->render_json($this->getLecturers($filter));
    }

    /**
     * Stores a course assignment to a time slot and (optionally) a room.
     */
    public function store_course_action()
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
            } else {
                $this->set_status(500, 'Could not save time assignment.');
            }
        }

        $this->render_nothing();
    }

    /**
     * Gets availability for time slots in a week. At the moment only lecturer availability is checked.
     * @param string $lecturer
     */
    public function slot_availability_action($lecturer)
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
     * Create sidebar entries.
     */
    private function setupSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/schedule-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(
            dgettext('whakamahere', 'Ganzes Semester'),
            $this->link_for('planning/index/semester')
        )->setActive($this->view === 'semester');
        $views->addLink(
            dgettext('whakamahere', 'Einzelne Woche'),
            $this->link_for('planning/index/week')
        )->setActive($this->view === 'week');
        $sidebar->addWidget($views);

        $semesterStatus = array_filter(WhakamahereSemesterStatus::findBySQL("1"),
            function ($s) {
                return !in_array($s->status, ['closed', 'finished']);
            });
        usort($semesterStatus, function ($a, $b) {
            return $b->semester->beginn - $a->semester->beginn;
        });

        $semesters = array_map(function($s) {
            return [
                    'id' => $s->semester->id,
                    'name' => (string) $s->semester->name
                ];
        }, $semesterStatus);

        $myRooms = SimpleCollection::createFromArray(RoomManager::getUserRooms(User::findCurrent()));
        $buildings = [];
        foreach ($myRooms as $room) {
            if ($room->properties->findOneBy('name', 'ignore_in_planning')->state != '1') {
                if (!$buildings[$room->parent_id]) {
                    $building = Building::find($room->parent_id);
                    $buildings[$room->parent_id] = [
                        'id' => $building->id,
                        'text' => $building->name
                    ];
                }

                $buildings[$room->parent_id]['children'][] = [
                    'id' => $room->id,
                    'text' => $room->name
                ];
            }
        }

        usort($buildings, function($a, $b) {
            return strnatcasecmp($a['text'], $b['text']);
        });

        $selectedRoom = UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_ROOM != '' ?
            UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_ROOM :
            '';

        $factory = $this->get_template_factory();
        $template = $factory->open('filter/sidebar');
        $sidebar->addWidget(new TemplateWidget(
            dgettext('whakamahere', 'Filter'),
            $template,
            [
                'semesters' => $semesters,
                'selectedSemester' => $this->selectedSemester,
                'institutes' => $this->institutes,
                'selectedInstitute' => $this->selectedInstitute,
                'lecturers' => $this->lecturers,
                'selectedLecturer' => $this->selectedLecturer,
                'rooms' => $buildings,
                'selectedRoom' => $selectedRoom,
                'controller' => $this
            ]
        ));
    }

    /**
     * Helper function for getting unplanned course(slot)s
     *
     * @param array $filter filter to apply, like semester, institute, lecturer etc.
     * @return array
     */
    private function getUnplannedCourses($filter)
    {
        $courses = [];

        $slots = WhakamahereCourseSlot::findUnplanned($filter);

        foreach ($slots as $slot) {
            $courses[] = [
                'id' => $slot->request->course_id . '-' . $slot->id,
                'course_id' => $slot->request->course_id,
                'course_name' => (string) $slot->request->course->name,
                'course_number' => $slot->request->course->veranstaltungsnummer,
                'url' => URLHelper::getLink('dispatch.php/course/overview?cid=' . $slot->request->course_id),
                'slot_id' => $slot->id,
                'lecturer_id' => $slot->user_id,
                'lecturer' => $slot->user_id ? $slot->user->getFullname() : 'N. N.',
                'duration' => $slot->duration,
                'weekday' => $slot->weekday,
                'time' => $slot->time
            ];
        }

        return $courses;
    }

    /**
     * Helper function for getting planned course(slot)s
     *
     * @param array $filter filter to apply, like semester, institute, lecturer etc.
     * @return array
     */
    private function getPlannedCourses($filter)
    {
        $sub = explode('+', $filter['institute']);
        if (count($sub) > 1) {
            $institutes = DBManager::get()->fetchFirst(
                "SELECT `Institut_id` FROM `Institute` WHERE `fakultaets_id` = :institute",
                ['institute' => $sub[0]]
            );
        } else {
            $institutes = [$filter['institute']];
        }

        $entries = WhakamahereCourseTime::findFiltered([
            'semester' => $filter['semester'],
            'institute' => $institutes,
            'lecturer' => $filter['lecturer']
        ]);

        $courses = [];
        foreach ($entries as $one) {
            $start = new DateTime('1970-01-01 ' . $one->start);
            $end = new DateTime('1970-01-01 ' . $one->end);

            $courses[] = [
                'id' => $one->course_id . '-' . $one->slot_id,
                'time_id' => $one->id,
                'course_id' => $one->course_id,
                'course_name' => (string) $one->course->name,
                'course_number' => $one->course->veranstaltungsnummer,
                'slot_id' => $one->slot_id,
                'lecturer_id' => $one->slot->user_id,
                'lecturer' => $one->slot->user_id ? $one->slot->user->getFullname() : 'N. N.',
                'pinned' => $one->pinned,
                'weekday' => $one->weekday,
                'start' => $one->start,
                'end' => $one->end
            ];
        }
        return $courses;
    }

    /**
     * Helper function for getting lecturers
     *
     * @param array $filter filter to apply, like semester, institute, lecturer etc.
     * @return array
     */
    private function getLecturers($filter)
    {
        $lecturers = [];

        foreach (WhakamaherePlanningRequest::findLecturers($filter) as $one) {
            $lecturers[] = [
                'user_id' => $one->id,
                'name' => $one->getFullname('full_rev')
            ];
        }

        return $lecturers;
    }

}
