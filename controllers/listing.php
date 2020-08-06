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
    }

    /**
     * Show list of courses for this semester.
     */
    public function courses_action($semester_id)
    {
        Navigation::activateItem('/resources/whakamahere/dashboard');

        $sidebar = Sidebar::get();
        // Views widget
        $views = new ViewsWidget();
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
        $sidebar->addWidget($views);

        $version = $this->plugin->getVersion();
        PageLayout::addScript($this->plugin->getPluginURL() .
            '/assets/javascripts/log.js?v=' . $version);

        $export = $sidebar->addWidget(new ExportWidget());
        $export->addLink(dgettext('whakamahere', 'Diese Ansicht als CSV exportieren'),
            $this->link_for('log/export'),
            Icon::create('log+move_right')
        );

    }

}
