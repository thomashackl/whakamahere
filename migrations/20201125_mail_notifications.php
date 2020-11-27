<?php

/**
 * Adds config entries for setting one or several email addresses
 * that will get notifications if a resource booking has been created,
 * updated or deleted by a set of configurable users.
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

class MailNotifications extends Migration {

    public function description()
    {
        return 'Adds config entries for setting one or several email addresses ' .
            'that will get notifications if a resource booking has been created, ' .
            'updated or deleted by a set of configurable users.';
    }

    public function up()
    {
        // Which email addresses shall get notifications?
        Config::get()->create('WHAKAMAHERE_NOTIFICATION_MAIL_ADDRESSES', [
            'value' => '',
            'type' => 'array',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'Eine oder mehrere Mailadressen, die bei Buchungserstellung/-änderung/-lösung benachrichtigt werden sollen'
        ]);
        // Which user's actions shall trigger mail notifications?
        Config::get()->create('WHAKAMAHERE_NOTIFY_ON_USERS', [
            'value' => '',
            'type' => 'array',
            'range' => 'global',
            'section' => 'whakamahereplugin',
            'description' =>
                'Die Buchungen welcher Personen sollen Benachrichtigungsmails auslösen?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entry.
        Config::get()->delete('WHAKAMAHERE_NOTIFICATION_MAIL_ADDRESSES');
        Config::get()->delete('WHAKAMAHERE_NOTIFY_ON_USERS');
    }

}
