<?php

/**
 * Registers a new step in course creation wizard for getting time and room preferences and requirements.
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

class AddCourseWizardStep extends Migration {

    public function description()
    {
        return 'Registers a new step in course creation wizard for getting time and room preferences and requirements.';
    }

    public function up()
    {
        // First get the maximal number of currently enabled steps, we will add the new step after the last one.
        $max = DBManager::get()->fetchColumn("SELECT MAX(`number`) FROM `coursewizardsteps` WHERE `enabled` = 1");

        CourseWizardStepRegistry::registerStep('Raum- und Zeitwünsche zur Semesterplanung',
            'WhakamahereWizardStep', $max + 1, true);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        $step = CourseWizardStepRegistry::findOneByClassname('WhakamahereWizardStep');
        $step->delete();
    }

}
