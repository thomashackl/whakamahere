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

        $this->course = Course::findCurrent();

        if (Navigation::hasItem('/course')) {
            $perm = $GLOBALS['perm']->have_studip_perm('dozent', $this->course->id);
        } else {
            $perm = $GLOBALS['perm']->have_perm('dozent');
        }

        if (!$perm) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));
    }

    public function planningrequest_action()
    {
        PageLayout::setTitle($this->course->getFullname() . ' - ' . $this->plugin->getDisplayname());
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/planningrequest.js');
        Navigation::activateItem('/course/admin/whakamahere');

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
        foreach (CourseMember::findByCourseAndStatus($this->course->id, 'dozent') as $one) {
            $this->lecturers[] = [
                'id' => $one->user_id,
                'name' => $one->getUserFullname('full_rev')
            ];
        }

        $this->rooms = WhakamaherePlanningRequest::getAvailableRooms();
        $this->start_weeks = WhakamaherePlanningRequest::getStartWeeks($this->course->start_semester);
        $this->end_weeks = WhakamaherePlanningRequest::getEndWeeks($this->course->start_semester);

        if ($this->flash['request']) {

            $request = WhakamaherePlanningRequest::build($this->flash['request'], $this->flash['request']['id'] == 0);
            $request->property_requests = new SimpleCollection();
            if ($this->flash['property_requests']) {
                foreach ($this->flash['property_requests'] as $one) {
                    $request->property_requests->append(
                        WhakamaherePropertyRequest::build($one, $one['property_request_id'] == 0)
                    );
                }
            }
            $request->slots = new SimpleCollection();
            if ($this->flash['slots']) {
                foreach ($this->flash['slots'] as $one) {
                    $request->slots->append(
                        WhakamahereCourseSlot::build($one, $one['slot_id'] == 0)
                    );
                }
            }

        } else {
            $request = WhakamaherePlanningRequest::findOneByCourse_id($this->course->id);
        }

        $status = WhakamahereSemesterStatus::find($this->course->start_semester->id);

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

            $this->disabled = !$status->isEditingAllowed();

            if ($this->disabled) {
                PageLayout::postWarning(dgettext('whakamahere','Sie können die gemachten Angaben ' .
                    'nicht ändern, da die Planung bereits begonnen hat oder abgeschlossen wurde. Bitte wenden ' .
                    'Sie sich direkt an die Raumvergabe.'));
            }

        } else {
            $this->regular = 0;
            $this->request = [
                'request_id' => 0,
                'semester_id' => $this->course->start_semester->id,
                'institute_id' => $this->course->institut_id,
                'room_id' => '',
                'cycle' => 1,
                'startweek' => 0,
                'comment' => '',
                'internal_comment' => '',
                'property_requests' => [],
                'slots' => []
            ];

            $this->disabled = !$status->isEditingAllowed();
            if ($this->disabled) {
                PageLayout::postWarning(dgettext('whakamahere','Sie können keine Wünsche zur ' .
                    'Semesterplanung mehr eintragen, da die Planung bereits begonnen hat oder abgeschlossen ' .
                    'wurde. Bitte wenden Sie sich direkt an die Raumvergabe.'));
            }

        }

        $this->form = true;
    }

    /**
     * Stores this request's data.
     */
    public function store_request_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $property_requests = Request::getArray('property_requests');
        $slots = Request::getArray('slots');
        $seats = WhakamaherePropertyRequest::getSeatsPropertyId();

        // Check for errors...
        $errors = [];
        if (!$property_requests[$seats] || count($slots) < 1) {

            if (!$property_requests[$seats]) {
                $errors[] = dgettext('whakamahere', 'Es ist keine Anzahl benötigter Sitzplätze angegeben.');
            }

            if (count($slots) < 1) {
                $errors[] = dgettext('whakamahere',
                    'Es muss mindestens eine regelmäßige Veranstaltungszeit angelegt sein. ' .
                    'Ist dies keine regelmäßige Veranstaltung, so geben Sie das ' .
                    'bitte weiter oben an.');
            } else {
                foreach ($slots as $number => $slot) {
                    if (!$slot['duration']) {
                        $errors[] = sprintf(dgettext('whakamahere',
                            'Regelmäßige Zeit %s: Es ist keine Dauer in Minuten angegeben.'), $number);
                    }
                    if (!$slot['weekday'] || !$slot['time']) {
                        $errors[] = sprintf(dgettext('whakamahere',
                            'Regelmäßige Zeit %s: Es ist keine Zeitpräferenz angegeben.'), $number);
                    }
                }
            }
        }

        $request = WhakamaherePlanningRequest::findOneByCourse_id($this->course->id);

        if (Request::int('regular') == 1) {

            if (!$request) {
                $request = new WhakamaherePlanningRequest();
                $request->course_id = $this->course->id;
                $request->semester_id = $this->course->start_semester->id;
                $request->institute_id = $this->course->institut_id;
                $request->property_requests = new SimpleCollection();
                $request->slots = new SimpleCollection();
                $request->mkdate = date('Y-m-d H:i:s');
            }

            $request->room_id = Request::option('room_id', null);
            $request->cycle = Request::int('cycle');
            $request->startweek = Request::int('startweek');
            $request->end_offset = Request::int('end_offset');
            $request->comment = Request::get('comment');
            $request->internal_comment = '';

            $propreq = new SimpleCollection();
            foreach (Request::getArray('property_requests') as $property_id => $value) {
                $one = $request->property_requests->findOneBy('property_id', $property_id);
                if (!$one) {
                    $one = new WhakamaherePropertyRequest();

                    if (!$request->isNew()) {
                        $one->request_id = $request->id;
                    }

                    $one->property_id = $property_id;
                    $one->mkdate = date('Y-m-d H:i:s');
                }

                $one->value = $value;
                $one->chdate = date('Y-m-d H:i:s');

                $propreq->append($one);
            }
            $request->property_requests = $propreq;

            $slots = new SimpleCollection();
            foreach (Request::getArray('slots') as $slot) {
                if ($slot['slot_id']) {
                    $one = $request->slots->findOneBy('slot_id', $slot['slot_id']);

                    /*
                     * Check if the time preference has been changed
                     * -> create new slot, discard old one
                     */
                    // Create DateTime objects because of appended seconds in database entry.
                    $t1 = new DateTime($slot['time']);
                    $t2 = new DateTime($one->time);
                    if ($one->planned_time && ($slot['weekday'] != $one->weekday || $t1 != $t2)) {
                        $one->planned_time->delete();
                    }
                    $one->chdate = date('Y-m-d H:i:s');

                } else {
                    $one = new WhakamahereCourseSlot();

                    if (!$request->isNew()) {
                        $one->request_id = $request->id;
                    }

                    $one->mkdate = date('Y-m-d H:i:s');
                }

                $one->duration = $slot['duration'];
                $one->user_id = $slot['user_id'];
                $one->weekday = $slot['weekday'];
                $one->time = $slot['time'];
                $one->chdate = date('Y-m-d H:i:s');

                $slots->append($one);
            }
            $request->slots = $slots;

            $request->chdate = date('Y-m-d H:i:s');

            if (count($errors) > 0) {

                PageLayout::postError(
                    _('Bitte beheben Sie erst folgende Fehler, bevor Sie fortfahren:'), $errors);

                $this->flash['request'] = $request->toArray();
                if (count($request->property_requests) > 0) {
                    $this->flash['property_requests'] = $request->property_requests->toArray();
                }
                if (count($request->slots) > 0) {
                    $this->flash['slots'] = $request->slots->toArray();
                }

            } else {

                if ($request->store()) {
                    PageLayout::postSuccess(dgettext('whakamahere', 'Die Änderungen wurden gespeichert.'));
                } else {
                    PageLayout::postError(dgettext('whakamahere',
                        'Die Änderungen konnten nicht gespeichert werden.'));
                }

            }

        } else {

            if (!$request || $request->delete()) {
                PageLayout::postSuccess(dgettext('whakamahere', 'Die Änderungen wurden gespeichert.'));
            } else {
                PageLayout::postError(dgettext('whakamahere',
                    'Die Änderungen konnten nicht gespeichert werden.'));
            }

        }

        $this->relocate('course/planningrequest');
    }

}
