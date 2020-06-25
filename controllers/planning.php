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

        $me = User::findCurrent()->id;
        $config = UserConfig::get($me);

        $this->selectedSemester = $config->WHAKAMAHERE_SELECTED_SEMESTER != '' ?
            $config->WHAKAMAHERE_SELECTED_SEMESTER :
            Semester::findNext()->id;

        $this->institutes = Institute::getMyInstitutes();
        $this->selectedInstitute = $config->WHAKAMAHERE_SELECTED_INSTITUTE;
        $this->selectedLecturer = $config->WHAKAMAHERE_SELECTED_LECTURER;
        $this->selectedRoom = $config->WHAKAMAHERE_SELECTED_ROOM;
        $this->searchterm = $config->WHAKAMAHERE_SEARCHTERM;
        $this->selectedWeek = (int) $config->WHAKAMAHERE_SELECTED_WEEK;
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
        PageLayout::allowFullscreenMode();

        $this->view = $show;

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

        $this->weeks = WhakamaherePlanningRequest::getStartWeeks($semester);

        // Show weekends?
        $this->weekends = Config::get()->WHAKAMAHERE_PLANNING_SHOW_WEEKENDS ? 'true' : 'false';

        // A semester must always be set.
        $filter = [
            'semester' => $this->selectedSemester
        ];

        // Lecturer filter.
        if ($this->selectedLecturer != '') {
            $filter['lecturer'] = $this->selectedLecturer;
        }

        // Seats filter.
        $seats = studip_json_decode(UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_MINMAX_SEATS);
        $this->minSeats = $seats['min'] ?: 0;
        $this->maxSeats = $seats['max'] ?: 0;

        if ($seats) {

            if ($this->minSeats != '' || $this->maxSeats != '') {
                $filter['seats'] = [];

                if ($this->minSeats != '') {
                    $filter['seats']['min'] = $this->minSeats;
                }
                if ($this->maxSeats != '') {
                    $filter['seats']['max'] = $this->maxSeats;
                }
            }

        }

        // Text filter.
        if ($this->searchterm != '') {
            $filter['searchterm'] = $this->searchterm;
        }

        // Institute filter.
        if ($this->selectedInstitute != '') {
            $filter['institute'] = $this->selectedInstitute;
        }

        // Room filter.
        if ($this->selectedRoom != '') {
            $filter['room'] = $this->selectedRoom;
        }

        $this->lecturers = $this->getLecturers($filter);

        $this->setupSidebar();

    }

    /**
     * Get planned courses for given settings.
     */
    public function planned_courses_action()
    {
        $filter = array_filter([
            'semester' => Request::option('semester', null),
            'searchterm' => Request::get('searchterm', null),
            'seats' => (array) studip_json_decode(Request::get('seats'), null),
            'institute' => Request::get('institute'. null),
            'lecturer' => Request::option('lecturer', null),
            'room' => Request::get('room', null),
            'week' => Request::int('week', null),
            'lastweek' => Request::int('lastweek', null)
        ], function($entry) {
            return (is_array($entry) && count($entry) != 0) || (!is_array($entry) && $entry !== null);
        });

        $this->render_json($this->getPlannedCourses($filter));
    }

    /**
     * Get unplanned courses for given settings.
     */
    public function unplanned_courses_action()
    {
        $filter = array_filter([
            'semester' => Request::option('semester', null),
            'searchterm' => Request::get('searchterm', null),
            'seats' => (array) studip_json_decode(Request::get('seats'), null),
            'institute' => Request::get('institute'. null),
            'lecturer' => Request::option('lecturer', null),
            'room' => Request::option('room', null)
        ]);

        $this->render_json($this->getUnplannedCourses($filter));
    }

    /**
     * Get lecturers for given settings.
     */
    public function lecturers_action()
    {
        $filter = [
            'semester' => Request::option('semester'),
            'institute' => Request::get('institute'),
            'seats' => (array) studip_json_decode(Request::get('seats'))
        ];

        $this->render_json($this->getLecturers($filter));
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

        $selectedRoom = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_SELECTED_ROOM != '' ?
            UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_SELECTED_ROOM :
            '';

        $factory = $this->get_template_factory();
        $template = $factory->open('filter/sidebar');
        $sidebar->addWidget(new TemplateWidget(
            dgettext('whakamahere', 'Filter'),
            $template,
            [
                'semesters' => $semesters,
                'selectedSemester' => $this->selectedSemester,
                'searchterm' => $this->searchterm,
                'minSeats' => $this->minSeats,
                'maxSeats' => $this->maxSeats,
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
     * Helper function for getting planned course(slot)s
     *
     * @param array $filter filter to apply, like semester, institute, lecturer etc.
     * @return array
     */
    private function getPlannedCourses($filter)
    {
        $courses = [];

        /*
         * Semester is always set, so we check if anything else is selected.
         * Without other filter criteria, we don't load any courses and lecturers.
         */
        if (count($filter) > 1) {

            $entries = WhakamahereCourseTime::findFiltered($filter);
            foreach ($entries as $one) {
                $courses[] = $one->formatForSchedule();
            }

        }

        return $courses;
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

        /*
         * Semester is always set, so we check if anything else is selected.
         * Without other filter criteria, we don't load any courses and lecturers.
         */
        if (count($filter) > 1) {

            $seatsId = WhakamaherePropertyRequest::getSeatsPropertyId();

            $slots = WhakamahereCourseSlot::findUnplanned($filter);

            foreach ($slots as $slot) {
                $courses[] = [
                    'id' => $slot->request->course_id . '-' . $slot->id,
                    'course_id' => $slot->request->course_id,
                    'course_name' => (string)$slot->request->course->name,
                    'course_number' => $slot->request->course->veranstaltungsnummer,
                    'turnout' => (int) $slot->request->property_requests->findOneBy('property_id', $seatsId)->value,
                    'url' => URLHelper::getLink('dispatch.php/course/overview?cid=' . $slot->request->course_id),
                    'slot_id' => (int) $slot->id,
                    'lecturer_id' => $slot->user_id,
                    'lecturer' => $slot->user_id ? $slot->user->getFullname() : 'N. N.',
                    'duration' => (int) $slot->duration,
                    'weekday' => (int) $slot->weekday,
                    'time' => $slot->time
                ];
            }

        }

        return $courses;
    }


    /**
     * Helper function for getting lecturers
     *
     * @param array $filter filter to apply, like semester, institute etc.
     * @return array
     */
    private function getLecturers($filter)
    {
        $lecturers = [];

        // Keep track if the currently set lecturer ID is still part of the result.
        $lecturerFound = false;

        foreach (WhakamaherePlanningRequest::findLecturers($filter) as $one) {
            $lecturers[] = [
                'user_id' => $one->id,
                'name' => $one->getFullname('full_rev')
            ];

            if ($this->selectedLecturer == $one->id) {
                $lecturerFound = true;
            }
        }

        if (!$lecturerFound) {
            $this->selectedLecturer = '';
        }

        return $lecturers;
    }

}
