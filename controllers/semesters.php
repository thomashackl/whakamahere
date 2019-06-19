<?php

/**
 * Class SemestersController
 * Controller for semester status settings.
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

class SemestersController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;

        if (!$this->plugin->hasPermission('read')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/schedule-sidebar.png');

        $this->flash = Trails_Flash::instance();

        $version = $this->plugin->getVersion();

        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/semesterstatus.js?v=' . $version);
    }

    /**
     * List semesters with their corresponding planning status.
     *
     * @param bool $all show all or only current and future semesters?
     */
    public function index_action($all = false)
    {
        // Navigation handling.
        Navigation::activateItem('/resources/whakamahere/semesters');

        PageLayout::setTitle(dgettext('whakamahere', 'Semestereinstellungen'));

        $this->semesters = WhakamahereSemesterStatus::findBySQL("1");

        if (!$all) {
            $this->semesters = array_filter($this->semesters, function ($s) {
                return !$s->semester->getpast();
            });
        }

        usort($this->semesters, function ($a, $b) {
            return $b->semester->beginn - $a->semester->beginn;
        });

        /*
         * Provide option for showing all or just current and future semesters in sidebar.
         */
        $views = new ViewsWidget();
        $views->setTitle(dgettext('whakamahere', 'Angezeigte Semester'));
        $views->addLink(
            dgettext('whakamahere', 'Aktuelles und zukünftige'),
            $this->link_for('semesters/index')
        )->setActive(!$all);
        $views->addLink(
            dgettext('whakamahere', 'Alle Semester'),
            $this->link_for('semesters/index/1')
        )->setActive($all);
        $this->sidebar->addWidget($views);

    }

    /**
     * Sets a semester status.
     */
    public function update_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $entry = WhakamahereSemesterStatus::find(Request::option('semester'));
        $entry->status = Request::option('status');

        if ($entry->store()) {
            $msg = MessageBox::success(sprintf(
                dgettext('whakamahere', 'Der Planungsstatus des Semesters %s wurde geändert.'),
                $entry->semester->name
            ));
        } else {
            $msg = MessageBox::success(sprintf(
                dgettext('whakamahere', 'Der Planungsstatus des Semesters %s konnte nicht geändert werden.'),
                $entry->semester->name
            ));
        }

        $this->render_text($msg);
    }

}