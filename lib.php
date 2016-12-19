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
 * General lib.php for the plugin.
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Moodle native lib/navigationlib.php calls this hook allowing us to override UI.
 * Here we instruct Moodle website to issue custom HTTP response header Content-Security-Policy-Report-Only on every page.
 */
function local_csp_extend_navigation() {
    // If the admin setting for monitoring is on, then send the Content-Security-Policy-Report-Only header to collect stats.
    if (get_config('local_csp', 'activation') == 'enabled') {
        $collectorurl = new \moodle_url('/local/csp/collector.php');
        header('Content-Security-Policy-Report-Only:'
            . 'style-src https:;'
            . 'script-src https:;'
            . 'img-src https:;'
            . 'child-sr  https:;'
            . 'default-src https:;'
            . 'report-uri ' . $collectorurl->out());
    }
}
