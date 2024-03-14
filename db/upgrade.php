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

/**
 * Function to upgrade local_csp.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
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

    if ($oldversion < 2020032400) {

        // Changing precision of field violateddirective on table local_csp to (1333).
        $table = new xmldb_table('local_csp');
        $field = new xmldb_field('violateddirective', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'blockeduri');

        // Launch change of precision for field violateddirective.
        $dbman->change_field_precision($table, $field);

        // Csp savepoint reached.
        upgrade_plugin_savepoint(true, 2020032400, 'local', 'csp');
    }

    if ($oldversion < 2020070301) {
        // Add field 'blockeddomain' to 'local_csp' to store the blocked domain.
        $table = new xmldb_table('local_csp');
        $field = new xmldb_field('blockeddomain', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'blockeduri');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field 'blockedurlpath' to 'local_csp' to store the blocked path.
        $field = new xmldb_field('blockedurlpath', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'blockeddomain');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // CSP savepoint reached.
        upgrade_plugin_savepoint(true, 2020070301, 'local', 'csp');
    }

    if ($oldversion < 2020070302) {
        // Add field 'courseid' to 'local_csp' to store the course id.
        $table = new xmldb_table('local_csp');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Set 'courseid' as a foreign key.
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $dbman->add_key($table, $key);

        // CSP savepoint reached.
        upgrade_plugin_savepoint(true, 2020070302, 'local', 'csp');
    }

    if ($oldversion < 2022060300) {
        \core\task\manager::queue_adhoc_task(new \local_csp\task\merge_duplicate_records_task());

        // CSP savepoint reached.
        upgrade_plugin_savepoint(true, 2022060300, 'local', 'csp');
    }

    return true;
}

