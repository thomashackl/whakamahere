<?php

/**
 * Class ListingController
 * Controller for course overviews and exporting.
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

class ListingController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;

        if (!$this->plugin->hasPermission('read')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();

        $semesterId = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_SELECTED_SEMESTER;
        $this->semester = $semesterId ? Semester::find($semesterId) : Semester::findNext();

        $this->institute = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_LIST_INSTITUTE;

        $version = $this->plugin->getVersion();
        PageLayout::addScript($this->plugin->getPluginURL() .
            '/assets/javascripts/listing.js?v=' . $version);

        $this->setupSidebar();
    }

    /**
     * Show list of courses for this semester.
     */
    public function index_action()
    {
        Navigation::activateItem('/resources/whakamahere/dashboard');

        $this->total = WhakamaherePlanningRequest::countAllCourses($this->semester->id,
            UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_LIST_INSTITUTE);
        $this->courses = [];
        foreach (WhakamaherePlanningRequest::findAllCourses($this->semester->id, $this->institute,
                0, 100) as $course) {
            $this->courses[] = $this->asJSON($course);
        }
    }

    /**
     * Show list of courses for this semester.
     */
    public function courses_action($offset = 0, $limit = 100)
    {
        $this->courses = [];
        foreach (WhakamaherePlanningRequest::findAllCourses($this->semester->id,
                $this->institute, $offset, $limit) as $course) {
            $this->courses[] = $this->asJSON($course);
        }

        $this->render_json($this->courses);
    }

    private function setupSidebar()
    {
        $this->sidebar = Sidebar::get();
        // Views widget
        $views = $this->sidebar->addWidget(new ViewsWidget());
        $views->setTitle(dgettext('whakamahere', 'Dashboard'));
        $views->addLink(
            dgettext('whakamahere', 'Übersicht'),
            $this->link_for('dashboard')
        )->setActive(false);
        $views->addLink(
            dgettext('whakamahere', 'Veranstaltungen'),
            $this->link_for('listing')
        )->setActive(true);
        if (WhakamaherePublishLogEntry::countBySemester_id($this->semester->id)) {
            $views->addLink(
                dgettext('whakamahere', 'Veröffentlichungslog'),
                $this->link_for('log/view')
            )->setActive(false);
        }

        $options = [];
        foreach (Semester::getAll() as $one) {
            $options[$one->id] = (string) $one->name;
        }
        $semester = $this->sidebar->addWidget(new SelectWidget(
            dgettext('whakamahere', 'Semester'),
            $this->link_for('filter/store_selection', ['type' => 'list_semester']),
            'value'
        ));
        $semester->setOptions($options, $this->semester->id);

        $options = [
            '' => '--' . dgettext('whakamahere', 'alle') . '--'
        ];
        foreach (Institute::getMyInstitutes() as $one) {
            $options[$one['Institut_id']] = ($one['is_fak'] ? '' : '  ') . $one['Name'];
        }
        $institutes = $this->sidebar->addWidget(new SelectWidget(
            dgettext('whakamahere', 'Fakultät/Einrichtung'),
            $this->link_for('filter/store_selection', ['type' => 'list_institute']),
            'value'
        ));
        $institutes->setOptions($options, UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_LIST_INSTITUTE);

        $export = $this->sidebar->addWidget(new ExportWidget());
        $export->addLink(dgettext('whakamahere', 'Diese Ansicht als CSV exportieren'),
            $this->link_for('listing/export'),
            Icon::create('export')
        );
    }

    private function asJSON($course)
    {
        $result = [
            'id' => $course->id,
            'name' => $course->getFullname(),
            'lecturers' => []
        ];

        foreach (CourseMember::findByCourseAndStatus($course->id, 'dozent') as $lecturer) {
            $result['lecturers'][] = [
                'id' => $lecturer->user_id,
                'name' => $lecturer->getUserFullname()
            ];
        }

        if ($request = WhakamaherePlanningRequest::findOneByCourse_id($course->id)) {
            $result['request'] = [
                'id' => $request->id,
                'cycle' => $request->cycle,
                'startweek' => $request->cycle,
                'end_offset' => $request->end_offset,
                'comment' => $request->comment,
                'slots' => []
            ];

            foreach ($request->slots as $slot) {
                $slot = [
                    'id' => $slot->id,
                    'time' => $slot->time,
                    'duration' => $slot->duration,
                    'name' => (string) $slot,
                    'user' => [
                        'id' => $slot->user_id,
                        'name' => $slot->user ? $slot->user->getFullname() : 'N. N.'
                    ],
                    'planned_time' => []
                ];

                if ($slot->planned_time) {
                    $slot['planned_time'] = [
                        'id' => $slot->planned_time->id,
                        'weekday' => $slot->planned_time->weekday,
                        'start' => $slot->planned_time->start,
                        'end' => $slot->planned_time->end,
                        'room' => []
                    ];

                    if (count($slot->planned_time->bookings) > 0) {
                        $slot['planned_time']['room'] = [
                            'id' => $slot->planned_time->bookings->first()->resource_id,
                            'name' => $slot->planned_time->bookings->first()->booking->resource->name
                        ];
                    }
                }

                $result['request']['slots'][] = $slot;
            }
        }

        return $result;
    }
}
