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
                $value = Request::get('value');
                $target = Request::isXhr() ? null : 'dashboard';
                break;
            case 'list_semester':
                $field = 'WHAKAMAHERE_SELECTED_SEMESTER';
                $value = Request::get('value');
                $target = 'listing';
                break;
            case 'searchterm':
                $field = 'WHAKAMAHERE_SEARCHTERM';
                $value = Request::get('value');
                $target = 'planning';
                break;
            case 'seats':
                $field = 'WHAKAMAHERE_MINMAX_SEATS';
                $decoded = studip_json_decode(Request::get('value'));
                if ($decoded['min'] || $decoded['max']) {
                    $value = Request::get('value');
                } else {
                    $value = null;
                }
                $target = 'planning';
                break;
            case 'institute':
                $field = 'WHAKAMAHERE_SELECTED_INSTITUTE';
                $value = Request::get('value');
                $target = 'planning';
                break;
            case 'list_institute':
                $field = 'WHAKAMAHERE_LIST_INSTITUTE';
                $value = Request::get('value');
                $target = 'listing';
                break;
            case 'lecturer':
                $field = 'WHAKAMAHERE_SELECTED_LECTURER';
                $value = Request::get('value');
                $target = 'planning';
                break;
            case 'room':
                $field = 'WHAKAMAHERE_SELECTED_ROOM';
                $value = Request::get('value');
                $target = 'planning';
                break;
            case 'no_room':
                $field = 'WHAKAMAHERE_SHOW_NO_ROOM';
                $value = Request::get('value');
                $target = 'planning';
                break;
            case 'week':
                $field = 'WHAKAMAHERE_SELECTED_WEEK';
                $value = Request::get('value') + 1;
                $target = 'planning';
                break;
            case 'log_status':
                $field = 'WHAKAMAHERE_LOG_STATUS';
                $value = Request::get('value');
                $target = 'log/view';
                break;
        }

        if ($field !== '') {
            if (UserConfig::get(User::findCurrent()->id)->$field != $value) {
                if (UserConfig::get(User::findCurrent()->id)->store($field, $value)) {
                    $this->set_status(200, 'Selection saved.');
                } else {
                    $this->set_status(500, 'Could not save selection.');
                }
            } else {
                $this->set_status(200, 'No changes to save in selection.');
            }
        } else {
            $this->set_status(404, 'Could not save selection: unknown field.');
        }

        if ($target === null) {
            $this->render_nothing();
        } else {
            $this->relocate($target);
        }
    }

}
