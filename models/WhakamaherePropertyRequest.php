<?php

/**
 * WhakamaherePropertyRequest.php
 * model class for property requests for course planning.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Whakamahere
 *
 * @property int property_request_id database column
 * @property string request_id database column
 * @property string property_id database column
 * @property string value database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamaherePropertyRequest extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_property_requests';
        $config['belongs_to']['request'] = [
            'class_name' => 'WhakamaherePlanningRequest',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id'
        ];
        $config['belongs_to']['property'] = [
            'class_name' => 'ResourcePropertyDefinition',
            'foreign_key' => 'property_id',
            'assoc_foreign_key' => 'property_id'
        ];
        $config['additional_fields']['name'] = ['property', 'name'];

        parent::configure($config);
    }

    public static function getRequestableProperties()
    {
        return DBManager::get()->fetchAll(
            "SELECT DISTINCT d.`property_id`, d.`name`, d.`display_name`, d.`type`
            FROM `resource_property_definitions` d
                JOIN `resource_category_properties` cp USING (`property_id`)
            WHERE cp.`category_id` NOT IN (:ignore)
                AND cp.`requestable` = 1
            ORDER BY d.`display_name`, d.`name`",
            ['ignore' => Config::get()->WHAKAMAHERE_PLANNING_IGNORE_ROOM_CATEGORIES ?: ['']]
        );
    }

    public static function getSeatsPropertyId()
    {
        // Try to read ID of "seats" property from cache.
        $cache = StudipCacheFactory::getCache();
        $seatsId = $cache->read('WHAKAMAHERE_SEATS_PROPERTY_ID');

        // No (valid) cache entry found, create new.
        if (!$seatsId) {
            $seatsId = DBManager::get()->fetchColumn("SELECT `property_id`
                    FROM `resource_property_definitions` WHERE `name` = 'seats'");
            // Write to cache with one week validity
            $cache->write('WHAKAMAHERE_SEATS_PROPERTY_ID', $seatsId, 604800);
        }

        return $seatsId;
    }

}
