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
    }

    public function index_action($show = 'semester')
    {
        // Navigation handling.
        Navigation::activateItem('/resources/whakamahere/planning');

        PageLayout::setTitle(dgettext('whakamahere', 'Planung'));

        $this->view = $show;

        $this->setupSidebar();
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

        $semesters = array_filter(WhakamahereSemesterStatus::findBySQL("1"),
            function ($s) {
                return !in_array($s->status, ['closed', 'finished']);
            });
        usort($semesters, function ($a, $b) {
            return $b->semester->beginn - $a->semester->beginn;
        });

        $selectedSemester = UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_SEMESTER != '' ?
            UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_SEMESTER :
            Semester::findNext()->id;
        $semesterFilter = new SelectWidget(dgettext('whakamahere', 'Semester'),
            $this->url_for('filter/select_semester'), 'semester');
        foreach ($semesters as $semester) {
            $semesterFilter->addElement(new SelectElement(
                $semester->semester_id,
                $semester->semester->name,
                $semester->semester_id === $selectedSemester
            ), 'semester-' . $semester->semester_id);
        }
        $sidebar->addWidget($semesterFilter);

        $instituteFilter = new SelectWidget(dgettext('whakamahere', 'Einrichtung'),
            $this->url_for('filter/select_institute'), 'institute');
        $instituteFilter->addElement(new SelectElement(
            '',
            dgettext('whakamahere', 'Keine Einrichtung'),
            UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_INSTITUTE == ''
        ), 'institute-empty');
        foreach (Institute::getMyInstitutes() as $inst) {
            $element = new SelectElement(
                $inst['Institut_id'],
                $inst['is_fak'] ? $inst['Name'] : '  ' . $inst['Name'],
                $inst['Institut_id'] === UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_INSTITUTE
            );
            if ($inst['is_fak']) {
                $element->setAsHeader();
            }
            $instituteFilter->addElement($element, 'institute-' . $inst['Institut_id']);

            if ($inst['is_fak']) {
                $instituteFilter->addElement(new SelectElement(
                    $inst['Institut_id'] . '+children',
                    '  ' . dgettext('whakamahere', ' + Untereinrichtungen'),
                    $inst['Institut_id'] === UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_INSTITUTE
                ), 'institute-' . $inst['Institut_id'] . '+children');
            }
        }
        $sidebar->addWidget($instituteFilter);

        $resourceFilter = new SelectWidget(dgettext('whakamahere', 'Raum'),
            $this->url_for('filter/select_room'), 'room');

        foreach (Location::findAll() as $location) {

            $element = new SelectElement(
                $location->id,
                $location->getFullname(),
                $location->id === UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_RESOURCE
            );
            $element->setIndentLevel(0);
            $element->setAsHeader();
            $resourceFilter->addElement($element, 'resource-' . $location->id);

            foreach ($location->buildings as $building) {
                $element = new SelectElement(
                    $building->id,
                    $building->getFullname(),
                    $building->id === UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_RESOURCE
                );
                $element->setIndentLevel(1);
                $element->setAsHeader();
                $resourceFilter->addElement($element, 'resource-' . $building->id);

                foreach ($building->rooms as $room) {
                    $element = new SelectElement(
                        $room->id,
                        '  ' . $room->name,
                        $room->id === UserConfig::get($GLOBALS['user']->id)->WHAKAMAHERE_SELECTED_RESOURCE
                    );
                    $element->setIndentLevel(2);
                    $resourceFilter->addElement($element, 'resource-' . $room->id);
                }
            }

        }

        $sidebar->addWidget($resourceFilter);
    }

}
