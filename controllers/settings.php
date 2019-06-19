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
    }

}