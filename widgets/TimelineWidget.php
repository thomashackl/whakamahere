<?php

/**
 * Class TimelineWidget
 * Dashboard widget for showing planning timelines.
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

class TimelineWidget extends Widgets\Widget {

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return dgettext('whakamahere', 'Planungsphasen');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return dgettext('whkaamahere', 'Zeigt aktuelle Planungsphasen an.');
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
        $getDates = function ($element) {
            $action = new Widgets\WidgetAction('');
            $action->setCallback([$element, 'getDates']);
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
                'getDates' => $getDates($this),
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

        PageLayout::addScript($plugin->getPluginURL() . '/assets/javascripts/timelinewidget.js?v=' . $version);
        PageLayout::addStylesheet($plugin->getPluginURL() . '/assets/stylesheets/timeline.css?v=' . $version);
        return $this
            ->getTemplate('timeline.php')
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

        switch($scope) {
            case 'list':
                $variables = [
                    'phases' => WhakamaherePlanningPhase::getCurrent()
                ];
                break;
            default:
                $plugin = PluginEngine::getPlugin('WhakamaherePlugin');

                $variables = [
                    'options' => $this->getOptions(),
                    'pluginurl' => $plugin->getPluginURL()
                ];
                break;
        }

        return $variables;
    }

    public function getDates(Widgets\Element $element, Widgets\Response $response)
    {

        $now = new DateTime();

        foreach (WhakamaherePlanningPhase::getCurrent() as $phase) {
            $dates[] = [
                'id' => $phase->id,
                'start' => $phase->start->format('Y-m-d 00:00:00'),
                'end' => $phase->end->format('Y-m-d 23:59:59'),
                'title' => $phase->name,
                'semester' => $phase->semester->name,
                'color' => $phase->color,
                'current' => $phase->start <= $now && $phase->end >= $now
            ];
        }

        return json_encode($dates);
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
        $response->addHeader('X-Title', dgettext('whakamahere', 'Listenansicht').': '.rawurlencode($this->getTitle()));

        return $this->getTemplate(
            'timeline-list.php',
            $this->getVariables($GLOBALS['user']->getAuthenticatedUser(), 'list')
        );
    }

}