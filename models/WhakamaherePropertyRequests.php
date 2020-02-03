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
        $config['db_table'] = 'whakamahere_requests';
        $config['belongs_to']['request'] = [
            'class_name' => 'WhakamaherePlanningRequest',
            'foreign_key' => 'request_id',
            'assoc_foreign_key' => 'request_id'
        ];

        parent::configure($config);
    }

}
