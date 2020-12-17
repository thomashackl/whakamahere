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

            if ($this->hasPermission('root')) {

                $navigation->addSubNavigation('semesters',
                    new Navigation(dgettext('whakamahere', 'Semestereinstellungen'),
                        PluginEngine::getURL($this, [], 'semesters')));

                $navigation->addSubNavigation('timelines',
                    new Navigation(dgettext('whakamahere', 'Planungszeiträume'),
                        PluginEngine::getURL($this, [], 'timelines')));

                $navigation->addSubNavigation('settings',
                    new Navigation(dgettext('whakamahere', 'Globale Einstellungen'),
                        PluginEngine::getURL($this, [], 'settings')));

            }

            if (Navigation::hasItem('/resources')) {
                Navigation::addItem('/resources/whakamahere', $navigation);
            } else {
                Navigation::addItem('/tools/whakamahere', $navigation);
            }

        }

        // Create navigation for resource requirements in courses
        if (Navigation::hasItem('/course/admin')) {
            $course = Course::findCurrent();

            if ($course) {
                $status = WhakamahereSemesterStatus::find($course->start_semester->id);
                if ($status->isEnabled()) {
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

        NotificationCenter::addObserver($this, 'removeDeletedBooking', 'ResourceBookingDidDelete');

        // Add Observers for notifying room management on booking updates or deletions
        NotificationCenter::addObserver($this, 'mailNotification', 'ResourceBookingDidCreate');
        NotificationCenter::addObserver($this, 'mailNotification', 'ResourceBookingDidUpdate');
        NotificationCenter::addObserver($this, 'mailNotification', 'ResourceBookingDidDelete');
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
        switch ($neededPermission) {
            case 'read':
                return $GLOBALS['perm']->have_perm('admin');
            case 'write':
            case 'root':
                return $GLOBALS['perm']->have_perm('root');
        }
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

    public function removeDeletedBooking($event, $affected, $data)
    {
        WhakamahereTimeBooking::deleteByBooking_id($affected->id);
    }

    /**
     * Send mail notifications to configured users if necessary.
     *
     * @param string $event event that happened, like "ResourceBookingDidCreate"
     * @param ResourceBooking $affected affected ResourceBooking object
     * @param mixed $data
     */
    public function mailNotification($event, $affected, $data)
    {
        $mailto = Config::get()->WHAKAMAHERE_NOTIFICATION_MAIL_ADDRESSES;
        $users = Config::get()->WHAKAMAHERE_NOTIFY_ON_USERS;

        $log = fopen('/Users/thomashackl/Downloads/whaka.log', 'w');
        fwrite('Event caught: ' . $event . "\n");

        if ($event == 'ResourceBookingDidDelete') {
            $shouldSend = count($mailto) > 0 && $affected->booking_type < 3;
        } else {
            in_array(User::findCurrent()->username, $users) && count($mailto) > 0 && $affected->booking_type < 3;
        }

        fwrite('Send mail? ' . $shouldSend . "\n");

        if ($shouldSend) {

            $subject = '';
            $action = '';

            $type = 'Raumbuchung';
            switch ($affected->booking_type) {
                case 1:
                    $type = 'Reservierung';
                    break;
                case 2:
                    $type = 'Sperrbuchung';
                    break;
            }

            switch ($event) {
                case 'ResourceBookingDidCreate':
                    $action = 'angelegt';
                    break;
                case 'ResourceBookingDidUpdate':
                    $action = 'verändert';
                    break;
                case 'ResourceBookingDidDelete':
                    $action = 'gelöscht';
                    break;
            }

            $subject = sprintf('%1$s wurde %2$s', $type, $action);

            $message = sprintf(
                'Hallo,

%1$s hat folgende %2$s %3$s:

Raum: %4$s
Zeit: %5$s
Veranstaltung: %6$s

Gruß,
Ihr Stud.IP',
                User::findCurrent()->getFullname(),
                $type,
                $action,
                $affected->room_name,
                date('d.m.Y H:i', $affected->begin) . ' - ' . date('d.m.Y H:i', $affected->end),
                $affected->course_id ? Course::find($affected->course_id)->getFullname() : '-'
            );

            fwrite($log, $subject . "\n");
            fwrite($log, $message . "\n");

            foreach ($mailto as $mail) {
                StudipMail::sendMessage($mail, $subject, $message);
            }

        }
    }

}
