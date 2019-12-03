<?php

/**
 * Adds a new room property for ignoring a room in semester planning.
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

class IgnoreRoomProperty extends Migration {

    public function description()
    {
        return 'Adds a new room property that specifies whether a room shall be available for semester planning.';
    }

    /**
     * Migration UP: We have just installed the plugin
     * and need to prepare all necessary data.
     */
    public function up()
    {
        // Add a new room property for excluding rooms from planning
        $def = new ResourcePropertyDefinition();
        $def->name = 'ignore_in_planning';
        $def->description = 'Räume, bei denen diese Eigenschaft gesetzt ist, '.
            'werden in der Semesterplanung nicht berücksichtigt.';
        $def->type = 'bool';
        $def->options = 'ignoriert';
        $def->display_name = 'In Semesterplanung ignorieren';
        $def->store();

        // Add the created property to all room categories.
        foreach (ResourceCategory::findByClass_name('Room') as $category) {
            $entry = new ResourceCategoryProperty();
            $entry->category_id = $category->id;
            $entry->property_id = $def->id;
            $entry->store();
        }
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove room property...
        $def = ResourcePropertyDefinition::findOneByName('ignore_in_planning');

        // ... and associated room assignments.
        ResourceProperty::deleteBySQL("`property_id` = ?", [$def->id]);

        $def->delete();
    }

}
