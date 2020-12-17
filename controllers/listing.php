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

        $this->config = UserConfig::get(User::findCurrent()->id);

        $semesterId = $this->config->WHAKAMAHERE_SELECTED_SEMESTER;
        $this->semester = $semesterId ? Semester::find($semesterId) : Semester::findNext();
        $this->institute = $this->config->WHAKAMAHERE_LIST_INSTITUTE ?: null;

        $turnout = $this->config->WHAKAMAHERE_LIST_TURNOUT ?
            json_decode($this->config->WHAKAMAHERE_LIST_TURNOUT, true) :
            ['min' => 0, 'max' => 0];
        $this->min = $turnout['min'];
        $this->max = $turnout['max'];

        $this->semtype = $this->config->WHAKAMAHERE_LIST_SEMTYPE ?: null;
        $this->planningstatus = $this->config->WHAKAMAHERE_LIST_PLANNING ?: null;

        $version = $this->plugin->getVersion();
        PageLayout::addScript($this->plugin->getPluginURL() .
            '/assets/javascripts/listing.js?v=' . $version);

        $this->setupSidebar();

        PageLayout::setTitle(dgettext('whakamahere', 'Veranstaltungen'));
    }

    /**
     * Show list of courses for this semester.
     */
    public function index_action()
    {
        Navigation::activateItem(Navigation::hasItem('/resources') ?
            '/resources/whakamahere/dashboard' :
            '/tools/whakamahere/dashboard');

        $filter = $this->getFilter();

        $this->total = WhakamaherePlanningRequest::countAllCourses($filter);
        $this->courses = [];
        foreach (WhakamaherePlanningRequest::findAllCourses($filter, 0, 100) as $course) {
            $this->courses[] = $this->asJSON($course);
        }
    }

    /**
     * Show list of courses for this semester.
     */
    public function courses_action($offset = 0, $limit = 100)
    {
        $filter = $this->getFilter();

        $this->courses = [];
        foreach (WhakamaherePlanningRequest::findAllCourses($filter, $offset, $limit) as $course) {
            $this->courses[] = $this->asJSON($course);
        }

        $this->render_json($this->courses);
    }

    public function export_action()
    {
        $filter = $this->getFilter();

        $csv = [
            [
                'Nummer',
                'Name',
                '# TN',
                'Lehrende',
                'Gewünschte regelmäßige Zeit(en)',
                'Wunschraum'
            ]
        ];

        foreach (WhakamaherePlanningRequest::findAllCourses($filter) as $course) {
            $json = $this->asJSON($course);
            $csv[] = [
                $json['number'],
                $json['title'],
                $json['turnout'],
                implode("\n", array_map(function($l) {
                        return $l['name'];
                    }, $json['lecturers'])),
                $json['request'] ? implode("\n", array_map(function($s) {
                        return $s['name'];
                    }, $json['request']['slots'])) : '-',
                is_array($json['request']) && $json['request']['room_id'] ? $json['request']['room_name'] : '-'
            ];
        }

        $filename = strtolower('veranstaltungen-' . str_replace([' ', '/'], '-', $this->semester->name));

        $this->response->add_header('Content-Disposition', 'attachment;filename=' . $filename . '.csv');
        $this->render_text(array_to_csv($csv));
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
                dgettext('whakamahere', 'Veröffentlichungsprotokoll'),
                $this->link_for('log/view')
            )->setActive(false);
        }

        // Semester filter
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

        // Institute filter
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
        $institutes->setOptions($options, $this->config->WHAKAMAHERE_LIST_INSTITUTE);

        // Turnout filter
        $factory = $this->get_template_factory();
        $template = $factory->open('filter/turnout');
        $this->sidebar->addWidget(new TemplateWidget(
            dgettext('whakamahere', 'Teilnehmende'),
            $template,
            [
                'min' => $this->min,
                'max' => $this->max,
                'controller' => $this
            ]
        ));

        // Coursetype filter
        $options = [
            '' => '--' . dgettext('whakamahere', 'alle') . '--'
        ];
        $types = array_filter(SemType::getTypes(), function($t) { return $t['class'] == 1; });
        foreach ($types as $one) {
            $options[$one['id']] = $one['name'];
        }
        $semtypes = $this->sidebar->addWidget(new SelectWidget(
            dgettext('whakamahere', 'Veranstaltungstyp'),
            $this->link_for('filter/store_selection', ['type' => 'list_semtype']),
            'value'
        ));
        $semtypes->setOptions($options, $this->config->WHAKAMAHERE_LIST_SEMTYPE);

        // Planning status filter
        $options = [
            '' => '--' . dgettext('whakamahere', 'alle') . '--',
            'no-request' => dgettext('whakamahere', 'ohne regelmäßige Zeitwünsche'),
            'request' => dgettext('whakamahere', 'mit regelmäßigen Zeitwünschen'),
            'planned' => dgettext('whakamahere', 'bereits geplant'),
            'unplanned' => dgettext('whakamahere', 'Zeitwünsche, aber nicht geplant'),
        ];
        $planning = $this->sidebar->addWidget(new SelectWidget(
            dgettext('whakamahere', 'Planungsdaten'),
            $this->link_for('filter/store_selection', ['type' => 'list_planning']),
            'value'
        ));
        $planning->setOptions($options, $this->config->WHAKAMAHERE_LIST_PLANNING);

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
            'number' => $course->veranstaltungsnummer,
            'title' => $course->name,
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
            $result['turnout'] = $request->property_requests->findOneBy(
                    'property_id', WhakamaherePropertyRequest::getSeatsPropertyId()
                )->value;
            $result['request'] = [
                'id' => $request->id,
                'cycle' => $request->cycle,
                'startweek' => $request->cycle,
                'end_offset' => $request->end_offset,
                'comment' => $request->comment,
                'slots' => [],
                'room_id' => $request->room_id,
                'room_name' => $request->room_id != null ? $request->room->name : null
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
        } else {
            $result['turnout'] = $course->admission_turnout ?: '-';
        }

        return $result;
    }

    /**
     * Gets course filter as array.
     *
     * @return array
     */
    private function getFilter()
    {
        $filter = [
            'semester' => $this->semester->id
        ];

        if (!$GLOBALS['perm']->have_perm('root')) {
            $filter['my_institutes'] = array_map(function ($i) {
                    return $i['Institut_id'];
                },
                Institute::getMyInstitutes()
            );
        }

        if ($this->institute) {
            $filter['institute'] = $this->institute;
        }

        if ($this->min) {
            $filter['min'] = $this->min;
        }

        if ($this->max) {
            $filter['max'] = $this->max;
        }

        if ($this->semtype) {
            $filter['semtype'] = $this->semtype;
        }

        if ($this->planningstatus) {
            $filter['planningstatus'] = $this->planningstatus;
        }

        return $filter;
    }
}
