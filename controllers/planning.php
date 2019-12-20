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
    }

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

        $this->unplanned_courses = $this->getUnplannedCourses($this->selectedSemester, $this->selectedInstitute);

        $this->setupSidebar();

    }

    public function unplanned_courses_action($semester, $institute)
    {
        $this->render_json($this->getUnplannedCourses($semester, $institute));
    }

    public function planned_courses_action($semester, $institute)
    {
        $this->render_json($this->getPlannedCourses($semester, $institute));
    }

    public function store_selection($type, $value)
    {
        $field = '';

        switch ($type) {
            case 'semester':
                $field = 'WHAKAMAHERE_SELECTED_SEMESTER';
                break;
            case 'institute':
                $field = 'WHAKAMAHERE_SELECTED_INSTITUTE';
                break;
            case 'room':
                $field = 'WHAKAMAHERE_SELECTED_ROOM';
                break;
        }

        if ($field !== '') {
            UserConfig::get(User::findCurrent()->id)->store($field, $value);
        }
    }

    /**
     * Stores a course assignment to a time slot and (optionally) a room.
     */
    public function store_course_action()
    {
        $startDate = new DateTime(Request::get('start'));
        $endDate = new DateTime(Request::get('end'));

        if (Request::option('room', '') == '') {
            $time = new WhakamahereCourseTime();
            $time->course_id = Request::option('course');
            $time->part_num = Request::get('part', 0);
            $time->weekday = $startDate->format('N');
            $time->start = $startDate->format('H:i');
            $time->end = $endDate->format('H:i');
            $time->mkdate = date('Y-m-d H:i:s');
            $time->chdate = date('Y-m-d H:i:s');
            if ($time->store()) {
                $this->set_status(200, 'Time assignment saved.');
            } else {
                $this->set_status(500, 'Could not save time assignment.');
            }
        }

        $this->render_nothing();
    }

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
                'rooms' => $buildings,
                'selectedRoom' => $selectedRoom,
                'controller' => $this
            ]
        ));
    }

    private function getUnplannedCourses($semester, $institute)
    {
        $sub = explode('+', $institute);
        if (count($sub) > 1) {
            $institutes = DBManager::get()->fetchFirst(
                "SELECT `Institut_id` FROM `Institute` WHERE `fakultaets_id` = :institute",
                ['institute' => $sub[0]]
            );
        } else {
            $institutes = [$institute];
        }
        return DBManager::get()->fetchAll(
            "SELECT DISTINCT s.`Seminar_id` AS id, s.`VeranstaltungsNummer` AS number, s.`Name` AS name, 2 AS duration
                FROM `seminare` s
                    JOIN `semester_data` sem ON (
                            s.`start_time` + s.`duration_time` BETWEEN sem.`beginn` AND sem.`ende`
                            OR s.`start_time` <= sem.`beginn` AND s.`duration_time` = -1
                        )
                WHERE s.`Institut_id` IN (:institutes)
                    AND sem.`semester_id` = :semester
                    AND s.`status` NOT IN (:studygroups)
                    AND s.`Seminar_id` NOT IN (SELECT `course_id` FROM `whakamahere_course_times`)
                ORDER BY s.`VeranstaltungsNummer`, s.`Name`",
            [
                'institutes' => $institutes,
                'semester' => $semester,
                'studygroups' => studygroup_sem_types()
            ]
        );
    }

    private function getPlannedCourses($semester, $institute)
    {
        $sub = explode('+', $institute);
        if (count($sub) > 1) {
            $institutes = DBManager::get()->fetchFirst(
                "SELECT `Institut_id` FROM `Institute` WHERE `fakultaets_id` = :institute",
                ['institute' => $sub[0]]
            );
        } else {
            $institutes = [$institute];
        }

        $entries = WhakamahereCourseTime::findFiltered([
            'semester' => $semester,
            'institute' => $institutes
        ]);

        $courses = [];
        foreach ($entries as $one) {
            $courses[] = [
                'course_id' => $one->course_id,
                'course_name' => (string) $one->course->name,
                'course_number' => $one->course->veranstaltungsnummer,
                'weekday' => $one->weekday,
                'start' => $one->start,
                'end' => $one->end
            ];
        }
        return $courses;
    }

}
