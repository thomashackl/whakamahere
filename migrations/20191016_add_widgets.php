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
     */
    public function down()
    {
        Widgets\Widget::deleteBySQL("`class` IN (:classes)",
            ['classes' => array_column(self::$widgets, 'class')]);

        Widgets\Container::deleteBySQL("`range_type` = 'whakamahere'");
    }

}
