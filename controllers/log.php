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
            $this->link_for('log/view', $semester_id)
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
            $this->link_for('filter/store_selection', ['semester' => $semester_id, 'type' => 'log_status']),
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

        if ($this->total > 0) {
            $export = $sidebar->addWidget(new ExportWidget());
            $export->addLink(dgettext('whakamahere', 'Diese Ansicht als CSV exportieren'),
                $this->link_for('log/export', $semester_id),
                Icon::create('log+move_right')
            );
        }

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

    public function export_action($semester_id)
    {
        $filter = [];
        if (($status = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_LOG_STATUS) !== null) {
            $filter['status'] = $status;
        }

        $csv = [
            [
                dgettext('whakamahere', 'Nummer'),
                dgettext('whakamahere', 'Veranstaltung'),
                dgettext('whakamahere', 'Regelmäßige Zeit'),
                dgettext('whakamahere', 'Gebuchter Raum'),
                dgettext('whakamahere', 'Status'),
                dgettext('whakamahere', 'Kommentar'),
                dgettext('whakamahere', 'Datum')
            ]
        ];

        foreach (WhakamaherePublishLogEntry::findFiltered($semester_id, 0, 0, $filter) as $entry) {
            $room = null;
            if (count($entry->time->bookings) > 0) {
                if ($entry->time->bookings->first()->booking) {
                    $room = $entry->time->bookings->first()->booking->resource->name;
                }
            }

            $csv[] = [
                $entry->course->veranstaltungsnummer,
                $entry->course->name,
                (string) $entry->time,
                $room ?: '-',
                $entry->state == 'success' ?
                    dgettext('whakamahere', 'erfolgreich') :
                    ($entry->state == 'warning' ?
                        dgettext('whakamahere', 'teilweise erfolgreich') :
                        dgettext('whakamahere', 'fehlerhaft')),
                $entry->note ?: '-',
                date('d.m.Y H:i:s', strtotime($entry->mkdate))
            ];
        }

        $semester = Semester::find($semester_id);
        $filename = strtolower('semesterplanung-' . str_replace([' ', '/'], '-', $semester->name));

        $this->response->add_header('Content-Disposition', 'attachment;filename=' . $filename . '.csv');
        $this->render_text(array_to_csv($csv));
    }

}
