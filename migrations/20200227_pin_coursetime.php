<?php

/**
 * Adds a database column for pinning a course time (so that it cannot be changed via drag&drop in the schedule view).
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

class PinCoursetime extends Migration {

    public function description()
    {
        return 'Adds a database column for pinning a course time (so that it cannot '.
            'be changed via drag&drop in the schedule view).';
    }

    public function up()
    {
        // Add column for pinning info.
        DBManager::get()->execute("ALTER TABLE `whakamahere_course_times`
            ADD `pinned` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `end`");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `whakamahere_course_times` DROP `pinned`");
    }

}
