<?php

/**
 * WhakamahereSemesterStatus.php
 * model class for assigning a planning status to a semester
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
 * @property string semester_id database column
 * @property string status database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class WhakamahereSemesterStatus extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'whakamahere_semester_status';
        $config['belongs_to']['semester'] = [
            'class_name' => 'Semester',
            'foreign_key' => 'semester_id',
            'assoc_foreign_key' => 'semester_id'
        ];
        $config['additional_fields']['statusname'] = true;
        $config['additional_fields']['statusvalues'] = true;
        $config['additional_fields']['semestername'] = true;
        parent::configure($config);
    }

    public function getStatusName()
    {
        $values = self::getStatusValues();
        return $values[$this->status];
    }

    public static function getStatusValues()
    {
        return [
            'closed' => dgettext('whakamahere', 'Keine Datenerfassung'),
            'input' => dgettext('whakamahere', 'Erfassung'),
            'prepare' => dgettext('whakamahere', 'Planung wird vorbereitet'),
            'planning' => dgettext('whakamahere', 'In Planung'),
            'review' => dgettext('whakamahere', 'Nachbearbeitung'),
            'finished' => dgettext('whakamahere', 'Planung abgeschlossen')
        ];
    }

    public function getSemesterName()
    {
        return $this->semester->name;
    }

    /**
     * Checks whether creating new WhakamaherePlanningRequest is allowed
     * according to semester status.
     */
    public function isCreatingAllowed()
    {
        $config = Config::get()->WHAKAMAHERE_ENABLED_IN_COURSES;
        return in_array($this->status, $config['create']);
    }

    /**
     * Checks whether changing an existing WhakamaherePlanningRequest
     * is allowed according to semester status.
     */
    public function isEditingAllowed()
    {
        $config = Config::get()->WHAKAMAHERE_ENABLED_IN_COURSES;
        return in_array($this->status, $config['edit']);
    }

    /**
     * Checks whether publishing the planning data into real course cycles
     * and dates is allowed according to semester status.
     */
    public function isPublishingAllowed()
    {
        return in_array($this->status, Config::get()->WHAKAMAHERE_PUBLISHING_ALLOWED);
    }

    /**
     * Checks whether semester planning is or was enabled for this semester.
     */
    public function isEnabled()
    {
        $config = Config::get()->WHAKAMAHERE_ENABLED_IN_COURSES;
        return in_array($this->status, array_merge($config['create'], $config['edit'], $config['readonly']));
    }

}
