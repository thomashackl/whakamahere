<?php

/**
 * Class SettingsController
 * Controller for global semester planning settings.
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

class SettingsController extends AuthenticatedController {

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
     * Show global settings.
     */
    public function index_action()
    {
        // Navigation handling.
        Navigation::activateItem('/resources/whakamahere/settings');

        PageLayout::setTitle(dgettext('whakamahere', 'Einstellungen'));

        $this->days = [
            1 => dgettext('whakamahere', 'Montag'),
            2 => dgettext('whakamahere', 'Dienstag'),
            3 => dgettext('whakamahere', 'Mittwoch'),
            4 => dgettext('whakamahere', 'Donnerstag'),
            5 => dgettext('whakamahere', 'Freitag'),
            6 => dgettext('whakamahere', 'Samstag'),
            0 => dgettext('whakamahere', 'Sonntag'),
        ];

        $this->selectedInstitutes = Config::get()->WHAKAMAHERE_DASHBOARD_STATISTICS_INSTITUTES;
        $this->institutes = [];
        foreach (Institute::getInstitutes() as $one) {
            $this->institutes[] = [
                'id' => $one['Institut_id'],
                'name' => $one['is_fak'] ? (string) $one['Name'] : '&nbsp;&nbsp;' . ((string) $one['Name'])
            ];
        }

        // Get status and corresponding permissions to create and edit course planning data.
        $this->semesterstatus = WhakamahereSemesterStatus::getStatusValues();
        $in_courses = Config::get()->WHAKAMAHERE_ENABLED_IN_COURSES;
        $this->create = $in_courses['create'];
        $this->edit = $in_courses['edit'];
        $this->readonly = $in_courses['readonly'];

        $this->publish = Config::get()->WHAKAMAHERE_PUBLISHING_ALLOWED;
    }

    public function store_action() {
        CSRFProtection::verifyUnsafeRequest();


        $entries = [
            'planning_start_time' => [
                    'type' => 'string',
                    'config' => 'WHAKAMAHERE_PLANNING_START_HOUR'
                ],
            'planning_end_time' => [
                    'type' => 'string',
                    'config' => 'WHAKAMAHERE_PLANNING_END_HOUR'
                ],
            'show_weekends' => [
                    'type' => 'bool',
                    'config' => 'WHAKAMAHERE_PLANNING_SHOW_WEEKENDS'
                ],
            'occupation_start_time' => [
                    'type' => 'string',
                    'config' => 'WHAKAMAHERE_OCCUPATION_START_HOUR'
                ],
            'occupation_end_time' => [
                    'type' => 'string',
                    'config' => 'WHAKAMAHERE_OCCUPATION_END_HOUR'
                ],
            'occupation_days' => [
                    'type' => 'array',
                    'config' => 'WHAKAMAHERE_OCCUPATION_DAYS'
                ],
            'statistics_institutes' => [
                    'type' => 'array',
                    'config' => 'WHAKAMAHERE_DASHBOARD_STATISTICS_INSTITUTES'
                ]
        ];

        $oldInstitutes = Config::get()->WHAKAMAHERE_DASHBOARD_STATISTICS_INSTITUTES;

        $success = true;
        foreach ($entries as $name => $mapping) {
            switch ($mapping['type']) {
                case 'string':
                    $value = Request::get($name);
                    break;
                case 'bool':
                    $value = Request::int($name, 0);
                    break;
                case 'array':
                    $value = Request::getArray($name);
                    break;
            }

            Config::get()->store($mapping['config'], $value) !== false;
        }

        // Clear cache for semester statistics if institutes have changed.
        if ($oldInstitutes != Request::getArray('statistics_institutes')) {
            $cache = StudipCacheFactory::getCache();

            foreach (Semester::getAll() as $semester) {
                $cache->expire('planning-statistics-' . $semester->id);
            }
        }

        // Store settings for semester status - planning data permissions.
        $newdata = [
            'create' => Request::optionArray('create'),
            'edit' => Request::optionArray('edit'),
            'readonly' => Request::optionArray('read')
        ];
        Config::get()->store('WHAKAMAHERE_ENABLED_IN_COURSES', $newdata);
        Config::get()->store('WHAKAMAHERE_PUBLISHING_ALLOWED', Request::optionArray('publish'));


        if ($success) {
            PageLayout::postSuccess(dgettext('whakamahere', 'Die Einstellungen wurden gespeichert.'));
        } else {
            PageLayout::postError(dgettext('whakamahere', 'Die Einstellungen konnten nicht gespeichert werden.'));
        }

        $this->relocate('settings');
    }

}
