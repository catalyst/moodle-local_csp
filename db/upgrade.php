<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade script
 *
 * @package   local_csp
 * @author    Peter Burnett <peterburnett@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_local_csp_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019100100) {

        // Changing precision of field documenturi on table local_csp to (1333).
        $table = new xmldb_table('local_csp');
        $field = new xmldb_field('documenturi', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'id');

        // Launch change of precision for field documenturi.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field blockeduri on table local_csp to (1333).
        $table = new xmldb_table('local_csp');
        $field = new xmldb_field('blockeduri', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'documenturi');

        // Launch change of precision for field blockeduri.
        $dbman->change_field_precision($table, $field);

        // Csp savepoint reached.
        upgrade_plugin_savepoint(true, 2019100100, 'local', 'csp');
    }

    return true;
}

