<?php
/**
 * WhakamaherePlugin.class.php
 *
 * Plugin for semester room and time planning of courses.
 * Kudos to the Maori people for having such an awesome culture.
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

class WhakamaherePlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        StudipAutoloader::addAutoloadPath(__DIR__ . '/widgets');

        // Localization
        bindtextdomain('whakamahere', realpath(__DIR__.'/locale'));

        // Plugin only available if there are corresponding permissions.
        if ($this->hasPermission('read')) {
            $navigation = new Navigation($this->getDisplayName(),
                PluginEngine::getURL($this, [], 'dashboard'));

            $navigation->addSubNavigation('dashboard',
                new Navigation(dgettext('whakamahere', 'Dashboard'),
                    PluginEngine::getURL($this, [], 'dashboard')));

            $navigation->addSubNavigation('planning',
                new Navigation(dgettext('whakamahere', 'Planung'),
                    PluginEngine::getURL($this, [], 'planning')));

            $navigation->addSubNavigation('semesters',
                new Navigation(dgettext('whakamahere', 'Semestereinstellungen'),
                    PluginEngine::getURL($this, [], 'semesters')));

            if ($this->hasPermission('root')) {

                $navigation->addSubNavigation('timelines',
                    new Navigation(dgettext('whakamahere', 'Planungszeiträume'),
                        PluginEngine::getURL($this, [], 'timelines')));

                $navigation->addSubNavigation('settings',
                    new Navigation(dgettext('whakamahere', 'Globale Einstellungen'),
                        PluginEngine::getURL($this, [], 'settings')));

            }

            Navigation::addItem('/resources/whakamahere', $navigation);
        }

        // Create navigation for resource requirements in courses
        if (Navigation::hasItem('/course/admin')) {
            $course = Course::findCurrent();
            $planningdata = WhakamaherePlanningRequest::findOneByCourse_id($course->id);
            $semesterstatus = WhakamahereSemesterStatus::find($course->start_semester->id);
            if ($planningdata || in_array($semesterstatus->status, ['input'])) {
                $navigation = new Navigation($this->getDisplayName(),
                    PluginEngine::getURL($this, array(), 'course/planningrequest'));
                $navigation->setImage(Icon::create('resources'));
                $navigation->setDescription(dgettext('whakamahere',
                    'Hier werden Ihre Anforderungen an Räume und Zeiten für die zentrale Raumplanung erfasst.'));
                $navigation->addSubNavigation('planningrequest',
                    new Navigation(dgettext('whakamahere', 'Angaben zur Semesterplanung'),
                        PluginEngine::getURL($this, array(), 'course/planningrequest')));
                Navigation::addItem('/course/admin/whakamahere', $navigation);
            }
        }
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName()
    {
        return dgettext('whakamahere', 'Semesterplanung');
    }

    public function getVersion()
    {
        $metadata = $this->getMetadata();
        return $metadata['version'];
    }

    /**
     * Central permissions checking for access.
     *
     * @param string $neededPermission Which permission shall be checked for?
     *                                 One of 'read', 'write' and 'root'.
     * @return bool current user
     */
    public function hasPermission($neededPermission)
    {
        return $GLOBALS['perm']->have_perm('root');
    }


    public function perform($unconsumed_path) {
        $range_id = Request::option('cid', Context::get()->id);

        URLHelper::removeLinkParam('cid');
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, [], null), '/'),
            'media'
        );
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

}
