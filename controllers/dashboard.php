<?php

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

        $this->flash = Trails_Flash::instance();

        $semesterId = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_SELECTED_SEMESTER;

        $options = [];
        foreach (Semester::getAll() as $one) {
            $options[$one->id] = (string) $one->name;
        }
        $options = array_reverse($options);

        $this->semester = $semesterId ? Semester::find($semesterId) : Semester::findNext();

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/schedule-sidebar.png');
        $widget = new SelectWidget(
            dgettext('whakamahere', 'Semester'),
            $this->link_for('filter/store_selection', ['type' => 'semester']),
            'value'
        );
        $widget->setOptions($options, $this->semester->id);
        $this->sidebar->addWidget($widget);
    }

    /**
     * Show widgets.
     */
    public function index_action()
    {
        // Navigation handling.
        Navigation::activateItem('/resources/whakamahere/dashboard');

        PageLayout::setTitle(dgettext('whakamahere', 'Dashboard'));

        $version = $this->plugin->getVersion();
        PageLayout::addScript($this->plugin->getPluginURL() .
            '/assets/javascripts/dashboard.js?v=' . $version);

        $this->timelines = [];

        foreach (Semester::getAll() as $semester) {
            if ($phases = WhakamaherePlanningPhase::findBySemester_id($semester->id, "ORDER BY `start`, `end`")) {
                $this->timelines[(string) $semester->name] = $phases;
            }
        }

        $this->status = WhakamahereSemesterStatus::getStatusValues();
        $this->semesterStatus = WhakamahereSemesterStatus::find($this->semester->id)->status;

        $this->selectedSemester = [
            'id' => $this->semester->id,
            'name' => (string) $this->semester->name
        ];
    }

    public function statistics_action()
    {
        $cache = StudipCacheFactory::getCache();

        if ($data = $cache->read('planning-statistics-' . $this->semester->id)) {

            $statistics = studip_json_decode($data);

        } else {

            $courseIdsQuery = "SELECT DISTINCT s.`Seminar_id`
                FROM `semester_data` sem
                    JOIN `seminare` s ON (s.`start_time` BETWEEN sem.`beginn` AND sem.`ende`)
                    JOIN `Institute` i ON (i.`institut_id` = s.`Institut_id`)
                    JOIN `whakamahere_requests` r ON (r.`course_id` = s.`Seminar_id`)
                WHERE sem.`semester_id` = :semester
                    AND i.`fakultaets_id` = :institute";

            $slotsCountQuery = "SELECT COUNT(DISTINCT `slot_id`)
                FROM `whakamahere_course_slots` s
                    JOIN `whakamahere_requests` r ON (r.`request_id` = s.`request_id`)
                WHERE r.`course_id` IN (:ids)";

            $timePlannedCountQuery = "SELECT COUNT(DISTINCT `time_id`)
                FROM `whakamahere_course_times` t
                    JOIN `whakamahere_course_slots` s ON (s.`slot_id` = t.`slot_id`)
                    JOIN `whakamahere_requests` r ON (r.`request_id` = s.`request_id`)
                WHERE r.`course_id` IN (:ids)";

            $timeAndRoomPlannedCountQuery = "SELECT COUNT(DISTINCT `time_id`)
                FROM `whakamahere_course_times` t
                    JOIN `whakamahere_course_slots` s ON (s.`slot_id` = t.`slot_id`)
                    JOIN `whakamahere_requests` r ON (r.`request_id` = s.`request_id`)
                WHERE r.`course_id` IN (:ids)
                    AND EXISTS (SELECT `booking_id` FROM `whakamahere_time_bookings` WHERE `time_id` = t.`time_id`)";

            $fulfilledCountQuery = "SELECT COUNT(DISTINCT `time_id`)
                FROM `whakamahere_course_times` t
                    JOIN `whakamahere_course_slots` s ON (s.`slot_id` = t.`slot_id`)
                    JOIN `whakamahere_requests` r ON (r.`request_id` = s.`request_id`)
                WHERE r.`course_id` IN (:ids)
                    AND t.`weekday` = s.`weekday`
                    AND t.`start` = s.`time`";

            foreach (Config::get()->WHAKAMAHERE_DASHBOARD_STATISTICS_INSTITUTES as $institute) {
                $ids = DBManager::get()->fetchFirst(
                    $courseIdsQuery,
                    ['semester' => $this->semester->id, 'institute' => $institute]
                );
                $statistics[] = [
                    'institute' => (string)Institute::find($institute)->name,
                    'courses' => (int) count($ids),
                    'slots' => (int) DBManager::get()->fetchColumn($slotsCountQuery, ['ids' => $ids]),
                    'timePlanned' => (int) DBManager::get()->fetchColumn($timePlannedCountQuery, ['ids' => $ids]),
                    'timeAndRoomPlanned' => (int) DBManager::get()->fetchColumn($timeAndRoomPlannedCountQuery, ['ids' => $ids]),
                    'fulfilled' => (int) DBManager::get()->fetchColumn($fulfilledCountQuery, ['ids' => $ids]),
                ];
            }

            $cache->write('planning-statistics-' . $this->semester->id, studip_json_encode($statistics), 86400);

        }

        if ($data = $cache->read('planning-unplanned-' . $this->semester->id)) {

            $unplanned = studip_json_decode($data);

        } else {

            $unplanned = DBManager::get()->fetchAll("SELECT DISTINCT sl.`slot_id`
                FROM `semester_data` sem
                    JOIN `seminare` c ON (c.`start_time` BETWEEN sem.`beginn` AND sem.`ende`)
                    JOIN `whakamahere_requests` r ON (r.`course_id` = c.`Seminar_id`)
                    JOIN `whakamahere_course_slots` sl ON (sl.`request_id` = r.`request_id`)                    
                WHERE sem.`semester_id` = :semester
                    AND NOT EXISTS(SELECT `time_id` FROM `whakamahere_course_times` WHERE `slot_id` = sl.`slot_id`)",
                ['semester' => $this->semester->id]
            );

            $cache->write('planning-unplanned-' . $this->semester->id, studip_json_encode($unplanned), 86400);

        }

        $this->render_json([
            'institutes' => $statistics,
            'unplanned' => $unplanned
        ]);
    }

}
