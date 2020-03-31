<?php

/**
 * Class CourseController
 * Helper controller for course wizard step.
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

class CourseController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (!$this->plugin->hasPermission('read')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));
    }

    public function planningrequest_action()
    {
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/planningrequest.js');
        Navigation::activateItem('/course/admin/whakamahere');

        $course = Course::findCurrent();

        $properties = WhakamaherePropertyRequest::getRequestableProperties();
        $seatsId = WhakamaherePropertyRequest::getSeatsPropertyId();
        $this->properties = [];
        foreach ($properties as $one) {
            $this->properties[] = [
                'id' => $one['property_id'],
                'name' => $one['name'],
                'display_name' => $one['display_name'] ?: $one['name'],
                'type' => $one['type']
            ];
        }

        $this->seats = $seatsId;

        $this->lecturers = [];
        foreach (CourseMember::findByCourseAndStatus($course->id, 'dozent') as $one) {
            $this->lecturers[] = [
                'id' => $one->user_id,
                'name' => $one->getUserFullname('full_rev')
            ];
        }

        $this->rooms = WhakamaherePlanningRequest::getAvailableRooms();;
        $this->weeks = WhakamaherePlanningRequest::getStartWeeks($course->start_semester);

        $request = WhakamaherePlanningRequest::findOneByCourse_id($course->id);

        if ($request) {
            $this->regular = 1;
            $this->request = $request->toArray();

            $this->request['property_requests'] = [];
            foreach ($request->property_requests as $one) {
                $this->request['property_requests'][$one->property_id] = $one->value;
            }

            $this->request['slots'] = [];
            $i = 1;
            foreach ($request->slots as $one) {
                $this->request['slots'][$i] = $one->toArray();
                $i++;
            }
        } else {
            $this->regular = 0;
            $this->request = [];
        }

        $this->form = true;
    }

}
