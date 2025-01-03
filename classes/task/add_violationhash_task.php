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

/**
 * Adds violationhash to all CSP records.
 *
 * This is essentially a one off upgrade task that was moved to the background
 * because it can take a long time for large datasets.
 *
 * @package    local_csp
 * @author     Benjamin Walker <benjaminwalker@catalyst-au.net>
 * @copyright  2025 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_violationhash_task extends adhoc_task {
    /**
     * Get task name.
     */
    public function get_name(): string {
        return get_string('add_violationhash_task', 'local_csp');
    }

    /**
     * Execute the task.
     */
    public function execute(): void {
        global $DB;

        // Get a recordset of all the unique violateddirect and blockeddomain pairs.
        $recordset = $DB->get_recordset_sql("
            SELECT violateddirective, blockeddomain, count(*) as count
              FROM {local_csp}
             WHERE violationhash IS NULL
          GROUP BY violateddirective, blockeddomain
          ORDER BY count DESC
        ");

        // Iterate through each pair and update the records.
        foreach ($recordset as $record) {
            $violationhash = sha1($record->violateddirective . $record->blockeddomain);
            $DB->set_field('local_csp', 'violationhash', $violationhash, [
                'violationhash' => null,
                'violateddirective' => $record->violateddirective,
                'blockeddomain' => $record->blockeddomain,
            ]);
        }
        $recordset->close();
    }
}
