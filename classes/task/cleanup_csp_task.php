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

use core\task\scheduled_task;

/**
 * Scheduled task to cleanup old CSP records.
 *
 * @package    local_csp
 * @author     Benjamin Walker <benjaminwalker@catalyst-au.net>
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_csp_task extends scheduled_task {
    /**
     * Get task name.
     */
    public function get_name(): string {
        return get_string('cleanup_csp_task', 'local_csp');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        // Clean up all CSP records that haven't had any recent violations.
        $duration = get_config('local_csp', 'cleanup_duration');
        if (is_numeric($duration)) {
            $params = [
                'timeexpired' => time() - $duration,
            ];
            $DB->delete_records_select('local_csp', 'COALESCE(timeupdated, timecreated) < :timeexpired', $params);
        }
    }
}
