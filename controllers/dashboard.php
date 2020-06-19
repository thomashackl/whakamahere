<?php

use Widgets\Container;

/**
 * Class DashboardController
 * Controller for dashboard.
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

class DashboardController extends AuthenticatedController {

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

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/schedule-sidebar.png');

        $this->flash = Trails_Flash::instance();
    }

    /**
     * Show widgets.
     */
    public function index_action()
    {
        // Navigation handling.
        Navigation::activateItem('/resources/whakamahere/dashboard');

        PageLayout::setTitle(dgettext('whakamahere', 'Dashboard'));

        $this->container = Widgets\Container::findOneByRange_id('whakamahere') ?: new Widgets\Container();

        $this->timelines = [];

        foreach (Semester::getAll() as $semester) {
            if ($phases = WhakamaherePlanningPhase::findBySemester_id($semester->id, "ORDER BY `start`, `end`")) {
                $this->timelines[(string) $semester->name] = $phases;
            }
        }

        $this->status = WhakamahereSemesterStatus::getStatusValues();
        $this->semester = Semester::findCurrent();
    }

}
