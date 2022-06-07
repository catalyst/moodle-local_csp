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

namespace local_csp;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once("$CFG->dirroot/local/csp/tests/plugin_testcase.php");

/**
 * Helper class unit tests.
 *
 * @package    local_csp
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2022 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_csp\helper
 */
class helper_test extends plugin_testcase {

    /**
     * Test merge fails if no records provided.
     */
    public function test_merge_duplicate_records_with_no_records() {
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('No records found');
        helper::merge_duplicate_records([]);
    }

    /**
     * Test merging with only one record provided.
     */
    public function test_merge_duplicate_records_with_one_records() {
        $expectedrecord = $this->create_test_record();
        $record = helper::merge_duplicate_records([$expectedrecord]);
        $this->assertEquals($expectedrecord->id, $record->id);
        $this->assertEquals($expectedrecord->failcounter, $record->failcounter);
        $this->assertEquals($expectedrecord->sha1hash, $record->sha1hash);
    }

    /**
     * Test merge fails if provided records are not duplicates.
     */
    public function test_merge_duplicate_records_with_two_non_matching_records() {
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('Non dupilcate records cannot be merged');
        helper::merge_duplicate_records([
            $this->create_test_record(['violateddirective' => 'default-src']),
            $this->create_test_record(['violateddirective' => 'font-src']),
        ]);
    }

    /**
     * Test merging multiple records succussfully.
     */
    public function test_merge_duplicate_records_with_two_matching_records() {
        global $DB;
        $record1 = $this->create_test_record(['failcounter' => 3]);
        $record2 = $this->create_test_record(['failcounter' => 2]);
        $record = helper::merge_duplicate_records([$record1, $record2]);
        // We expect $record1 to be the base record as it has the highest failcounter.
        $this->assertEquals(5, $record->failcounter);
        $this->assertEquals($record1->sha1hash, $record->sha1hash);

        // Check that only one record now exists.
        $this->assertCount(1, $DB->get_records('local_csp', ['sha1hash' => $record1->sha1hash]));
    }
}
