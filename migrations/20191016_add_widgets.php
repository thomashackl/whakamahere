<?php

use Widgets\Widget;

/**
 * Widgets used in Whakamahere dashboard.
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

class AddWidgets extends Migration {

    public function description()
    {
        return 'Add and initialize Widgets used in Whakamahere dashboard.';
    }

    static $widgets = [
        [
            'class' => 'TimelineWidget',
            'x' => 0,
            'y' => 0,
            'width' => 6,
            'height' => 1
        ],
        [
            'class' => 'StatisticsWidget',
            'x' => 0,
            'y' => 1,
            'width' => 3,
            'height' => 1
        ]
    ];

    /**
     * Migration UP: We have just installed the plugin
     * and need to prepare all necessary data.
     */
    public function up()
    {
        // Create widget container for plugin dashboard.
        $container = new Widgets\Container();
        $container->range_id = 'whakamahere';
        $container->range_type = 'user';
        $container->scope = 'whakamahere_dashboard';
        $container->store();

        // Register plugin widgets
        foreach (self::$widgets as $one) {
            require_once __DIR__ . '/../widgets/' . $one['class'] . '.php';

            $widget = Widgets\Widget::registerWidget(new $one['class']);
            $container->addWidget($widget, $one['width'], $one['height'], $one['x'], $one['y']);
        }
    }

    /**
     * Migration DOWN: cleanup all created data.
     * This is done directly by SQL as Widgets and their
     * containers do not (yet) any methods for cleanup.
     */
    public function down()
    {
        $entries = DBManager::get()->fetchAll(
            "SELECT DISTINCT w.`widget_id`, e.`element_id`, c.`container_id`
             FROM `widgets` w
	            JOIN `widget_elements` e USING (`widget_id`)
                JOIN `widget_containers` c USING (`container_id`)
             WHERE w.`class` IN (:classnames)",
            ['classnames' => array_column(self::$widgets, 'class')]);

        // Delete container elements containing plugin widgets.
        DBManager::get()->execute(
            "DELETE FROM `widget_elements` WHERE `element_id` IN (?)",
            [array_unique(array_column($entries, 'element_id'))]
        );
        // Delete containers containing plugin widgets.
        DBManager::get()->execute(
            "DELETE FROM `widget_containers` WHERE `container_id` IN (?)",
            [array_unique(array_column($entries, 'container_id'))]
        );
        // Delete widget entries.
        DBManager::get()->execute(
            "DELETE FROM `widgets` WHERE `widget_id` IN (?)",
            [array_unique(array_column($entries, 'widget_id'))]
        );
    }

}
