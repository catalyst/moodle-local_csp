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

namespace local_csp\task;

defined('MOODLE_INTERNAL') || die;

use local_csp\plugin_testcase;

global $CFG;
require_once("$CFG->dirroot/local/csp/tests/plugin_testcase.php");

/**
 * Test an ad-hoc task- merge_duplicate_records_task.
 *
 * @package    local_csp
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2022 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class merge_duplicate_records_task_test extends plugin_testcase {

    /**
     * Test that SQL gets all duplicate rows as expected.
     */
    public function test_get_duplicate_list_sql_with_none_found() {
        global $DB;
        $record1 = $this->create_test_record(['blockeduri' => 'test-extension-1']);
        $record2 = $this->create_test_record(['blockeduri' => 'test-extension-2']);
        $record3 = $this->create_test_record(['blockeduri' => 'test-extension-3']);
        $task = new merge_duplicate_records_task();
        $records = $DB->get_records_sql($task->get_duplicate_list_sql());
        // There are two sets of two duplicate records.
        $this->assertCount(0, $records);
        $ids = array_map(function($record) {
            return $record->id;
        }, $records);
        $this->assertFalse(in_array($record1->id, $ids));
        $this->assertFalse(in_array($record2->id, $ids));
        $this->assertFalse(in_array($record3->id, $ids));
    }

    /**
     * Test that SQL gets all duplicate rows as expected.
     */
    public function test_get_duplicate_list_sql() {
        global $DB;
        $record1 = $this->create_test_record(['blockeduri' => 'test-extension-1']);
        $record2 = $this->create_test_record(['blockeduri' => 'test-extension-1']);
        $record3 = $this->create_test_record(['blockeduri' => 'test-extension-2']);
        $record4 = $this->create_test_record(['blockeduri' => 'test-extension-2']);
        $record5 = $this->create_test_record(['blockeduri' => 'test-extension-3']);
        $task = new merge_duplicate_records_task();
        $records = $DB->get_records_sql($task->get_duplicate_list_sql());
        // There are two sets of two duplicate records.
        $this->assertCount(4, $records);
        $ids = array_map(function($record) {
            return $record->id;
        }, $records);
        $this->assertTrue(in_array($record1->id, $ids));
        $this->assertTrue(in_array($record2->id, $ids));
        $this->assertTrue(in_array($record3->id, $ids));
        $this->assertTrue(in_array($record4->id, $ids));
        $this->assertFalse(in_array($record5->id, $ids));
    }

    /**
     * Test that task does nothing if no duplicates exist.
     */
    public function test_execute_with_no_duplicate_records() {
        global $DB;
        $this->create_test_record(['blockeduri' => 'test-extension-1']);
        $this->create_test_record(['blockeduri' => 'test-extension-2']);
        $this->create_test_record(['blockeduri' => 'test-extension-3']);

        $task = new merge_duplicate_records_task();
        $task->execute();

        $this->assertCount(3, $DB->get_records('local_csp'));
    }

    /**
     * Test that task merges duplicate records if they exist.
     */
    public function test_execute_with_duplicate_records() {
        global $DB;
        $record1 = $this->create_test_record(['blockeduri' => 'test-extension-1', 'failcounter' => 1]);
        $record2 = $this->create_test_record(['blockeduri' => 'test-extension-1', 'failcounter' => 2]);
        $record3 = $this->create_test_record(['blockeduri' => 'test-extension-2', 'failcounter' => 3]);
        $record4 = $this->create_test_record(['blockeduri' => 'test-extension-2', 'failcounter' => 4]);
        $record5 = $this->create_test_record(['blockeduri' => 'test-extension-3', 'failcounter' => 5]);

        $task = new merge_duplicate_records_task();
        $task->execute();

        $records = $DB->get_records('local_csp');
        $this->assertCount(3, $records);

        // Check remaining records are merged as expected.
        $actualrecord1 = array_filter($records, function($record) use ($record1) {
            return ($record->sha1hash === $record1->sha1hash);
        });
        $actualrecord1 = array_pop($actualrecord1);
        $actualrecord2 = array_filter($records, function($record) use ($record3) {
            return ($record->sha1hash === $record3->sha1hash);
        });
        $actualrecord2 = array_pop($actualrecord2);
        $actualrecord3 = array_filter($records, function($record) use ($record5) {
            return ($record->sha1hash === $record5->sha1hash);
        });
        $actualrecord3 = array_pop($actualrecord3);
        $this->assertEquals(3, $actualrecord1->failcounter);
        $this->assertEquals(7, $actualrecord2->failcounter);
        $this->assertEquals(5, $actualrecord3->failcounter);

        // Duplicate records are removed.
        $this->assertFalse(array_key_exists($record2->id, $records));
        $this->assertFalse(array_key_exists($record4->id, $records));
    }
}
