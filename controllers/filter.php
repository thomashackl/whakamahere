<?php

/**
 * Class FilterController
 * Helper controller for handling filter in planning view.
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

class FilterController extends AuthenticatedController {

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

        $this->set_layout(null);
    }

    public function select_semester_action()
    {
        UserConfig::get($GLOBALS['user']->id)->store('WHAKAMAHERE_SELECTED_SEMESTER', Request::option('semester'));
        $this->relocate('planning');
    }

    public function select_institute_action()
    {
        UserConfig::get($GLOBALS['user']->id)->store('WHAKAMAHERE_SELECTED_INSTITUTE', Request::option('institute'));
        $this->relocate('planning');
    }

    public function select_room_action()
    {
        UserConfig::get($GLOBALS['user']->id)->store('WHAKAMAHERE_SELECTED_RESOURCE', Request::option('room'));
        $this->relocate('planning');
    }

    /**
     * Stores selection in filters to user preferences in database.
     */
    public function store_selection_action()
    {
        $field = '';

        switch (Request::option('type')) {
            case 'semester':
                $field = 'WHAKAMAHERE_SELECTED_SEMESTER';
                break;
            case 'institute':
                $field = 'WHAKAMAHERE_SELECTED_INSTITUTE';
                break;
            case 'lecturer':
                $field = 'WHAKAMAHERE_SELECTED_LECTURER';
                break;
            case 'room':
                $field = 'WHAKAMAHERE_SELECTED_ROOM';
                break;
        }

        if ($field !== '') {
            if (UserConfig::get(User::findCurrent()->id)->store($field, Request::get('value'))) {
                $this->set_status(200, 'Selection saved.');
            } else {
                $this->set_status(500, 'Could not save selection.');
            }
        } else {
            $this->set_status(404, 'Could not save selection: unknown field.');
        }

        $this->render_nothing();
    }

}
