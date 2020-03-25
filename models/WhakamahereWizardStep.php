<?php
/**
 * WhakamahereWizardStep.php
 * Course wizard step for getting the time and room requests.
 * This step is only required for regular courses created in semesters
 * that are still to be planned.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class WhakamahereWizardStep implements CourseWizardStep
{
    /**
     * Returns the Flexi template for entering the necessary values
     * for this step.
     *
     * @param Array $values Pre-set values
     * @param int $stepnumber which number has the current step in the wizard?
     * @param String $temp_id temporary ID for wizard workflow
     * @return String a Flexi template for getting needed data.
     */
    public function getStepTemplate($values, $stepnumber, $temp_id)
    {
        // Load template from step template directory.
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../views'));
        $tpl = $factory->open('course/planningrequest');

        $lecturers = [];
        // Get lecturers, find the semester the new course will be in and get status and home institute.
        $lecturers = array_keys($values['BasicDataWizardStep']['lecturers']);
        $institute = $values['BasicDataWizardStep']['institute'];
        $start_time = $values['BasicDataWizardStep']['start_time'];
        $semester = Semester::findByTimestamp($start_time);

        $status = WhakamahereSemesterStatus::find($semester->id);
        $tpl->set_attribute('status', $status->status);

        // Get lecturers assigned to this course-to-be
        $lecs = [];
        foreach (User::findMany($lecturers) as $one) {
            $lecs[] = [
                'id' => $one->id,
                'name' => $one->getFullname('full_rev')
            ];
        }
        $tpl->set_attribute('lecturers', $lecs);

        // Get all available properties.
        $properties = WhakamaherePropertyRequest::getRequestableProperties();
        $seats = WhakamaherePropertyRequest::getSeatsPropertyId();

        $props = [];
        foreach ($properties as $one) {
            $props[] = [
                'id' => $one['property_id'],
                'name' => $one['name'],
                'display_name' => $one['display_name'] ?: $one['name'],
                'type' => $one['type']
            ];
        }

        $tpl->set_attribute('properties', $props);
        $tpl->set_attribute('seats', WhakamaherePropertyRequest::getSeatsPropertyId());

        // Available rooms for making a wish.
        $rooms = WhakamaherePlanningRequest::getAvailableRooms();
        $tpl->set_attribute('rooms', $rooms);

        // Available start weeks for given semester.
        $start_weeks = WhakamaherePlanningRequest::getStartWeeks($semester);
        $tpl->set_attribute('weeks', $start_weeks);

        // Model a WhakamaherePlanningRequest structure for passing the values.
        $values = $values[__CLASS__];
        $request = [
            'request_id' => 0,
            'semester_id' => $semester->id,
            'institute_id' => $institute,
            'room_id' => $values['room_id'] ?: '',
            'cycle' => $values['cycle'] ?: 1,
            'startweek' => $values['startweek'] ?: 0,
            'comment' => $values['comment'] ?: '',
            'internal_comment' => $values['internal_comment'] ?: '',
            'property_requests' => $values['property_requests'] ?: [],
            'slots' => $values['slots'] ?: []
        ];
        $tpl->set_attribute('request', $request);

        $plugin_manager = PluginManager::getInstance();
        $plugin_info = $plugin_manager->getPluginInfo('WhakamaherePlugin');
        $url = URLHelper::getURL('plugins_packages/' . $plugin_info['path']);
        $pure = explode('?', $url);
        $pluginUrl = $pure[0];

        PageLayout::addScript($pluginUrl . '/assets/javascripts/planningrequest.js');

        return $tpl->render();
    }

    /**
     * The function only needs to handle person adding and removing
     * as other actions are handled by normal request processing.
     * @param Array $values currently set values for the wizard.
     * @return bool
     */
    public function alterValues($values)
    {
        // We only need our own stored values here.
        return $values[__CLASS__];
    }

    /**
     * Validates if given values are sufficient for completing the current
     * course wizard step and switch to another one. If not, all errors are
     * collected and shown via PageLayout::postMessage.
     *
     * @param mixed $values Array of stored values
     * @return bool Everything ok?
     */
    public function validate($values)
    {
        // We only need our own stored values here.
        $values = $values[__CLASS__];

        $ok = true;
        $errors = [];

        if (!$values['property_requests'][WhakamaherePropertyRequest::getSeatsPropertyId()]) {
            $errors[] = dgettext('whakamahere', 'Es ist keine Anzahl benötigter Sitzplätze angegeben.');
        }

        if (count($values['slots']) < 1) {
            $errors[] = dgettext('whakamahere',
                'Es muss mindestens eine regelmäßige Veranstaltungszeit angelegt sein. '.
                'Ist dies keine regelmäßige Veranstaltung, so geben Sie das '.
                'bitte weiter oben an.');
        } else {
            foreach ($values['slots'] as $number => $slot) {
                if (!$slot['duration']) {
                    $errors[] = sprintf(dgettext('whakamahere',
                        'Regelmäßige Zeit %s: Es ist keine Dauer in Minuten angegeben.'), $number);
                }
                if (!$slot['weekday']) {
                    $errors[] = sprintf(dgettext('whakamahere',
                        'Regelmäßige Zeit %s: Es ist keine Zeitpräferenz angegeben.'), $number);
                }
            }
        }

        if ($errors) {
            $ok = false;
            PageLayout::postError(
                _('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors);
        }
        return $ok;
    }

    /**
     * Stores the given values to the given course.
     *
     * @param Course $course the course to store values for
     * @param Array $values values to set
     * @return Course The course object with updated values.
     */
    public function storeValues($course, $values)
    {
        $request = new WhakamaherePlanningRequest();
        $request->course_id = $course->id;

        $start_time = $values['BasicDataWizardStep']['start_time'];
        $semester = Semester::findByTimestamp($start_time);
        $request->semester_id = $semester->id;
        $request->institute_id = $values['BasicDataWizardStep']['institute'];

        $values = $values[__CLASS__];
        $request->room_id = $values['room_id'];
        $request->cycle = $values['cycle'];
        $request->startweek = $values['startweek'];
        $request->comment = $values['comment'];
        $request->internal_comment = $values['internal_comment'] ?:'';
        $request->mkdate = date('Y-m-d H:i:s');
        $request->chdate = date('Y-m-d H:i:s');

        // Property requests.
        $request->property_requests = new SimpleCollection();
        foreach ($values['property_requests'] as $id => $value) {
            $prop = new WhakamaherePropertyRequest();
            $prop->property_id = $id;
            $prop->value = $value;
            $prop->mkdate = date('Y-m-d H:i:s');
            $prop->chdate = date('Y-m-d H:i:s');

            $request->property_requests->append($prop);
        }

        // Course slots.
        $request->slots = new SimpleCollection();
        foreach ($values['slots'] as $number => $slot) {
            $s = new WhakamahereCourseSlot();
            $s->duration = $slot['duration'];
            $s->user_id = $slot['lecturer'];
            $s->weekday = $slot['weekday'];
            $s->time = $slot['time'];
            $s->mkdate = date('Y-m-d H:i:s');
            $s->chdate = date('Y-m-d H:i:s');

            $request->slots->append($s);
        }

        if ($request->store()) {
            return $course;
        } else {
            return false;
        }
    }

    /**
     * Data for semester planning needs only be collected if the course semester
     * is not already planned or completely disabled for semester planning.
     *
     * @param Array $values values specified from previous steps
     * @return bool Is the current step required for a new course?
     */
    public function isRequired($values)
    {
        return true;
    }

    /**
     * No roomplanning data will be copied.
     * @param Course $course
     * @param Array $values
     */
    public function copy($course, $values)
    {
        return $values;
    }

}
