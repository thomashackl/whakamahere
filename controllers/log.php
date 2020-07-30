<?php

/**
 * Class LogController
 * Controller for log viewing.
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

class LogController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;

        if (!$this->plugin->hasPermission('admin')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();
    }

    /**
     * View log entries for the given semester.
     *
     * @param string $semester_id the selected semester
     * @param int $start start at entry number $start
     * @param int $limit show $limit entries
     */
    public function view_action($semester_id)
    {
        Navigation::activateItem('/resources/whakamahere/dashboard');

        $sidebar = Sidebar::get();
        $views = new ViewsWidget();
        $views->setTitle(dgettext('whakamahere', 'Dashboard'));
        $views->addLink(
            dgettext('whakamahere', 'Übersicht'),
            $this->link_for('dashboard')
        )->setActive(false);
        $views->addLink(
            dgettext('whakamahere', 'Veröffentlichungslog'),
            $this->link_for('log/view')
        )->setActive(true);
        $sidebar->addWidget($views);

        $options = [
            '' => dgettext('whakamahere', 'alle'),
            'error' => dgettext('whakamahere', 'Fehler'),
            'warning' => dgettext('whakamahere', 'Warnung'),
            'success' => dgettext('whakamahere', 'Erfolg'),
        ];
        $widget = new SelectWidget(
            dgettext('whakamahere', 'Status'),
            $this->link_for('filter/store_selection', ['type' => 'log_status']),
            'value'
        );
        $widget->setOptions($options, UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_LOG_STATUS);
        $sidebar->addWidget($widget);

        $version = $this->plugin->getVersion();
        PageLayout::addScript($this->plugin->getPluginURL() .
            '/assets/javascripts/log.js?v=' . $version);

        $filter = [];
        if (($status = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_LOG_STATUS) !== null) {
            $filter['status'] = $status;
        }

        $this->entries = [];
        foreach (WhakamaherePublishLogEntry::findFiltered($semester_id, 0, 100, $filter) as $entry) {
            $this->entries[] = $entry->formatForDisplay();
        }

        $this->total = WhakamaherePublishLogEntry::countBySemester_id($semester_id, $filter);
        $this->semester = $semester_id;
    }


    public function get_entries_action($semester_id, $start = 0, $limit = 100)
    {
        $entries = [];

        $filter = [];
        if (($status = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_LOG_STATUS) !== null) {
            $filter['status'] = $status;
        }

        foreach (WhakamaherePublishLogEntry::findFiltered($semester_id, $start, $limit, $filter) as $entry) {
            $entries[] = $entry->formatForDisplay();
        }

        $this->render_json($entries);
    }

}
