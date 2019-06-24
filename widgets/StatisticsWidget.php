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

class Statisticsidget extends Widgets\Widget {

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

        PageLayout::addScript($plugin->getPluginURL() . '/assets/javascripts/timeline.js?v=' . $version);
        PageLayout::addStylesheet($plugin->getPluginURL() . '/assets/stylesheets/timeline-style.css?v=' . $version);
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
        $response->addHeader('X-Title', dgettext('whakamahere', 'Listenansicht').': '.rawurlencode($this->getTitle()));

        return $this->getTemplate(
            'statistics-list.php',
            $this->getVariables($GLOBALS['user']->getAuthenticatedUser(), 'list')
        );
    }

}