<?php

/**
 * Adds a database column for chdate of a WhakamahereTimeBooking.
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

class TimebookingsChdate extends Migration {

    public function description()
    {
        return 'Adds a database column for chdate of a WhakamahereTimeBooking.';
    }

    public function up()
    {
        DBManager::get()->execute("ALTER TABLE `whakamahere_time_bookings`
            ADD `chdate` DATETIME NOT NULL DEFAULT NOW() AFTER `mkdate`");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `whakamahere_time_bookings` DROP `chdate`");
    }

}
