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

        $semesterId = UserConfig::get(User::findCurrent()->id)->WHAKAMAHERE_SELECTED_SEMESTER;

        $this->semester = $semesterId ? Semester::find($semesterId) : Semester::findNext();
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

        $this->selectedSemester = [
            'id' => $this->semester->id,
            'name' => (string) $this->semester->name
        ];
    }

    public function statistics_action()
    {
        $statistics = [];

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
                    'courses' => count($ids),
                    'slots' => DBManager::get()->fetchColumn($slotsCountQuery, ['ids' => $ids]),
                    'timePlanned' => DBManager::get()->fetchColumn($timePlannedCountQuery, ['ids' => $ids]),
                    'timeAndRoomPlanned' => DBManager::get()->fetchColumn($timeAndRoomPlannedCountQuery, ['ids' => $ids]),
                    'fulfilled' => DBManager::get()->fetchColumn($fulfilledCountQuery, ['ids' => $ids]),
                ];
            }

            $cache->write('planning-statistics-' . $this->semester->id, studip_json_encode($statistics), 86400);

        }

        $this->render_json($statistics);
    }

}
