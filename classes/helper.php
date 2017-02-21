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
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_csp;

defined('MOODLE_INTERNAL') || die;

/**
 * Class helper
 *
 * @package local_csp
 */
class helper {

    /**
     * @var bool Have we sent CSP headers already?
     */
    private static $cspheaderssent = false;

    /**
     * Sets CSP HTTP header depending on plugin settings.
     */
    public static function enable_csp_header() {
        if (self::$cspheaderssent) {
            return;
        } else {
            $settings = get_config('local_csp');

            if (!empty($settings->csp_header_enable) and $settings->csp_header_enable == 1) {
                if (!empty($settings->csp_header_reporting)) {
                    $cspheaderreporting = trim(str_replace(array("\r\n", "\r"), " ", $settings->csp_header_reporting));
                    $collectorurl = new \moodle_url('/local/csp/collector.php');
                    @header('Content-Security-Policy-Report-Only: ' . $cspheaderreporting . ' report-uri ' . $collectorurl->out());
                }

                if (!empty($settings->csp_header_enforcing)) {
                    $cspheaderenforcing = trim(str_replace(array("\r\n", "\r"), " ", $settings->csp_header_enforcing));
                    @header('Content-Security-Policy: ' . $cspheaderenforcing);
                }

                self::$cspheaderssent = true;

                return;
            } else {
                return;
            }
        }
    }
} // end class csp_report
