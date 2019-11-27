<?php

/**
 * Class StatisticsWidget
 * Dashboard widget for some statistics.
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

class StatisticsWidget extends Widgets\Widget {

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return dgettext('whakamahere', 'Statistik');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return dgettext('whkaamahere', 'Zeigt Statistiken zur aktuellen Planung.');
    }

    /**
     * {@inheritdoc}
     */
    public function suitableForRange(\Range $range, $scope = null)
    {
        return $range->getRangeType() === 'user' && $scope === 'whakamahere_dashboard';
    }

    /**
     * Returns whether this widget instance may be removed from a container.
     *
     * @return bool
     */
    public function mayBeRemoved()
    {
        return false;
    }

    /**
     * Returns whether this widget instance may be duplicated or used more than
     * once in a container.
     *
     * @return bool
     */
    public function mayBeDuplicated()
    {
        return false;
    }

    public function getActions(Range $range, $scope)
    {
        $roomUsage = function ($element) {
            $action = new Widgets\WidgetAction('');
            $action->setCallback([$element, 'getRoomUsage']);
            $action->hasIcon(false);

            return $action;
        };

        $list = function ($element) {
            $action = new Widgets\WidgetAction(dgettext('whakamahere', 'Listenansicht im Dialog'));
            $action->setIcon(Icon::create('maximize', Icon::ROLE_CLICKABLE, ['size' => 20]));
            $action->setCallback([$element, 'getListTemplate']);
            $action->setAttributes(
                [
                    'href' => $element->url_for('list'),
                    'data-dialog' => 'size=auto',
                ]
            );

            return $action;
        };

        return array_filter(
            [
                'roomUsage' => $roomUsage($this),
                'list' => $list($this)
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(\Range $range, $scope)
    {
        $plugin = PluginEngine::getPlugin('WhakamaherePlugin');
        $version = $plugin->getVersion();

        PageLayout::addScript($plugin->getPluginURL() . '/assets/javascripts/statisticswidget.js?v=' . $version);

        return $this
            ->getTemplate('statistics.php')
            ->render($this->getVariables($range, $scope));
    }

    /**
     * This method return all the variables used for the templates of
     * the `basic` widget view and of the `list` view.
     *
     * @param Range $range The range whose files and folders shall be retrieved
     *
     * @return array an array of all the template variables
     */
    protected function getVariables(\Range $range, $scope)
    {
        $variables = [];

        return $variables;
    }

    /**
     * This method is the callback of the `list` action. It returns a
     * list view of the results of calling self::getFilesAndFolders.
     *
     * @param Element  $element  the widget element whose `list`
     *                           action is performing
     * @param Response $response a response object given to all widget
     *                           actions
     *
     * @return mixed Content of the response (might be a string or
     *               a flexi template)
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getListTemplate(Widgets\Element $element, Widgets\Response $response)
    {
        $response->addHeader('X-Title', dgettext('whakamahere', 'Listenansicht')
            . ': ' . rawurlencode($this->getTitle()));

        return $element->getTemplate(
            'statistics-list.php',
            $element->getVariables($GLOBALS['user']->getAuthenticatedUser(), 'list')
        );
    }

    public function getRoomUsage(Widgets\Element $element, Widgets\Response $response)
    {
        return json_encode(['totalUsage' => 0.784]);

        $locations = Location::findAll();

        $semester = Semester::findOneByName('WiSe 19/20');

        $start = new DateTime(date('Y-m-d 00:00:00', $semester->vorles_beginn));
        $end = new DateTime(date('Y-m-d 23:59:59', $semester->vorles_ende));

        $oneDay = new DateInterval('P1D');

        /*
         * $start and $end are absolute dates, get weeks,
         * respecting weekday selection setting.
         */
        $days = Config::get()->WHAKAMAHERE_OCCUPATION_DAYS;

        // Move start up until we find a weekday that shall be used.
        while (!in_array($start->format('w'), $days)) {
            $start->add($oneDay);
        }

        // ... Now the same for end time.
        while (!in_array($end->format('w'), $days)) {
            $end->sub($oneDay);
        }

        $weekdays = Config::get()->WHAKAMAHERE_OCCUPATION_DAYS;
        $startHour = Config::get()->WHAKAMAHERE_OCCUPATION_START_HOUR + 2;
        $endHour = Config::get()->WHAKAMAHERE_OCCUPATION_END_HOUR - 4;

        // Now iterate over days in timespan and build entries for fetching bookings.
        $days = 0;
        $times = [];
        $current = $start;
        while ($current <= $end) {
            if (in_array($current->format('w'), $weekdays) && !SemesterHoliday::isHoliday($current->getTimestamp(), false)) {
                $days++;

                $beginTime = mktime($startHour, 0, 0, $current->format('n'), $current->format('j'), $current->format('Y'));
                $endTime = mktime($endHour, 0, 0, $current->format('n'), $current->format('j'), $current->format('Y'));

                $times[] = [
                    'begin' => $beginTime,
                    'end' => $endTime
                ];
            }
            $current->add($oneDay);
        }

        // Full time that is available in selected timespan and hours.
        $theoretical = $days * ($endHour - $startHour) * 60;
        /*
         * Theoretical full time, added up across all rooms.
         * E.g.: for 4 rooms, we have 4 * $theoretical at our disposal.
         */
        $fullTime = 0;
        // Time that is occupied by bookings.
        $bookedTime = 0;

        foreach ($locations as $location) {
            foreach ($location->buildings as $building) {
                foreach ($building->rooms as $room) {

                    if (in_array($room->category->name, ['Hörsäle', 'Übungsräume'])) {

                        $fullTime += $theoretical;

                        $bookings = ResourceBooking::findByResourceAndTimeRanges($room, $times);

                        $minutes = 0;

                        // Get booked minutes for room.
                        foreach ($bookings as $booking) {
                            $minutes += ($booking->end - $booking->begin) / 60;
                        }

                        // Now add up bookings time to total booked time.
                        $bookedTime += $minutes;

                    }
                }
            }
        }

        return json_encode(['totalUsage' => $bookedTime / $fullTime]);
    }

}
