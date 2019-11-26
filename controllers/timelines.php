<?php

/**
 * Class TimelinesController
 * Controller for defining semester planning timelines.
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

class TimelinesController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;

        if (!$this->plugin->hasPermission('root')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/schedule-sidebar.png');

        $this->flash = Trails_Flash::instance();
    }

    /**
     * Show timelines and phases.
     */
    public function index_action()
    {
        // Navigation handling.
        Navigation::activateItem('/resources/whakamahere/timelines');

        PageLayout::setTitle(dgettext('whakamahere', 'Semesterzeitpläne'));

        $actions = new ActionsWidget();
        $actions->addLink(dgettext('whakamahere', 'Planungsphase hinzufügen'),
            $this->link_for('timelines/edit', $this->type, $this->context),
            Icon::create('add'))->asDialog('size=auto');
        $this->sidebar->addWidget($actions);

        $this->timelines = [];

        foreach (Semester::getAll() as $semester) {
            if ($phases = WhakamaherePlanningPhase::findBySemester_id($semester->id, "ORDER BY `start`, `end`")) {
                $this->timelines[$semester->id] = [
                    'semester' => $semester->name,
                    'phases' => $phases
                ];
            }

        }

        $this->status = WhakamahereSemesterStatus::getStatusValues();
        $this->semester = Semester::findCurrent();
    }

    /**
     * Create a new or edit an existing planning phase.
     *
     * @param int $phase_id optional: the phase to edit
     */
    public function edit_action($phase_id = '')
    {
        PageLayout::setTitle(
            $phase_id === '' ?
                dgettext('whakamahere', 'Planungsphase erstellen') :
                dgettext('whakamahere', 'Planungsphase bearbeiten'));

        $this->phase = $phase_id !== '' ?
            WhakamaherePlanningPhase::find($phase_id) :
            new WhakamaherePlanningPhase();

        $this->selectedSemester = $this->phase->isNew() ? Semester::findNext() : $this->phase->semester;

        if ($this->phase->isNew()) {
            $this->phase->start = new DateTime(date('Y-m-d', $this->selectedSemester->beginn));
            $this->phase->end = new DateTime(date('Y-m-d', $this->selectedSemester->ende));
        }

        $semesters = Semester::getAll();

        $this->semesters = array_reverse(array_filter($semesters, function ($s) {
            return !$s->getpast();
        }));

        $this->status = WhakamahereSemesterStatus::getStatusValues();

    }

    public function store_action($phase_id = '')
    {
        CSRFProtection::verifyUnsafeRequest();

        $phase = $phase_id !== '' ? WhakamaherePlanningPhase::find($phase_id) : new WhakamaherePlanningPhase();

        $phase->name = Request::get('name');
        $phase->start = new DateTime(Request::get('start'));
        $phase->end = new DateTime(Request::get('end'));
        $phase->color = Request::get('color');
        $phase->auto_status = Request::option('auto_status') !== '' ? Request::option('auto_status') : null;

        if ($phase->isNew()) {
            $phase->semester_id = Request::option('semester');
        }

        if ($phase->store()) {
            PageLayout::postSuccess(dgettext('whakamahere', 'Die Planungsphase wurde gespeichert.'));
        } else {
            PageLayout::postError(dgettext('whakamahere', 'Die Planungsphase konnte nicht gespeichert werden.'));
        }

        $this->relocate('timelines');
    }

    public function delete_action($phase_id)
    {
        $phase = WhakamaherePlanningPhase::find($phase_id);

        if ($phase->delete()) {
            PageLayout::postSuccess(dgettext('whakamahere', 'Die Planungsphase wurde gelöscht.'));
        } else {
            PageLayout::postError(dgettext('whakamahere', 'Die Planungsphase konnte nicht gelöscht werden.'));
        }

        $this->relocate('timelines');
    }

}
