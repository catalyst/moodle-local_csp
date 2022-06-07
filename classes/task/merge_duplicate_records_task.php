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

use core\task\adhoc_task;
use local_csp\helper;

/**
 * Ad-hoc task to depulicate local_csp records that have matching sha1 hash's.
 *
 * @package    local_csp
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2022 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class merge_duplicate_records_task extends adhoc_task {
    /**
     * Get task name.
     */
    public function get_name() {
        return get_string('merge_duplicate_records_task', 'local_csp');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        // Get sets of records to deduplicate.
        $duplicaterecords = $DB->get_recordset_sql($this->get_duplicate_list_sql());

        // Create sets of duplicate records from the total list.
        // We expect the dataset to be very large, so use iterators to conserve memory.
        $duplicatesets = [];
        if (!$duplicaterecords->valid()) {
            return;
        }
        foreach ($duplicaterecords as $record) {
            $duplicatesets[$record->sha1hash][] = $record;
        }
        $duplicaterecords->close();

        // Deduplicate the records.
        $duplicatesets = new \ArrayIterator($duplicatesets);
        $duplicatesets->rewind();
        do {
            helper::merge_duplicate_records($duplicatesets->current());
            $duplicatesets->next();
        } while ($duplicatesets->valid());
    }

    /**
     * Get SQL that will fetch all records that have a duplicate sha1hash in local_csp table.
     *
     * @return string
     */
    public function get_duplicate_list_sql(): string {
        return "SELECT *
                  FROM {local_csp}
                 WHERE (sha1hash)
                    IN (
                        SELECT sha1hash
                          FROM {local_csp}
                      GROUP BY sha1hash
                        HAVING count(*) > 1
                    )";
    }
}
