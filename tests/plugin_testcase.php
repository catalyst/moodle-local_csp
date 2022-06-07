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

/**
 * Testcase for plugin that extends advanced test case.
 *
 * @package    local_csp
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2022 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class plugin_testcase extends \advanced_testcase {

    /**
     * Run before every test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Create a new local_csp record.
     *
     * @param array $record Override the default values.
     * @return \stdClass The created record.
     */
    public function create_test_record(array $record = []): \stdClass {
        global $DB;
        $defaults = [
            'courseid' => 1,
            'documenturi' => 'https://www.example.com/mod/quiz/attempt.php?attempt=123&cmid=456',
            'blockeduri' => 'chrome-extension',
            'blockeddomain' => '',
            'violateddirective' => 'default-src',
            'failcounter' => 1,
            'timecreated' => time(),
            'timeupdated' => time(),
        ];
        foreach ($record as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                $defaults[$key] = $value;
            }
        }
        $defaults['sha1hash'] = hash('sha1',
            $defaults['documenturi'] . $defaults['blockeduri'] . $defaults['violateddirective']);
        $id = $DB->insert_record('local_csp', $defaults);
        return $DB->get_record('local_csp', ['id' => $id]);
    }
}
