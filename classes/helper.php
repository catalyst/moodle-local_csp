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
 * Helper class
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright 2016 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_csp;

defined('MOODLE_INTERNAL') || die;

/**
 * Class helper
 *
 * @package local_csp
 * @copyright 2016 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * @var bool Have we sent CSP headers already?
     */
    private static $bootstrapped = false;

    /**
     * Sets CSP HTTP header depending on plugin settings.
     */
    public static function enable_csp_header() {
        global $USER, $COURSE;

        $settings = get_config('local_csp');
        if (self::$bootstrapped or empty($settings->csp_header_enable)) {
            return;
        }
        self::$bootstrapped = true;

        $cspheaderreporting = trim(str_replace(array("\r\n", "\r", "\n"), " ", $settings->csp_header_reporting));
        if (!empty($cspheaderreporting)) {
            $collectorurl = new \moodle_url('/local/csp/collector.php');
            $collectorurl->param('uid', $USER->id);
            if (!empty($COURSE->id)) {
                $collectorurl->param('cid', $COURSE->id);
            }
            @header('Content-Security-Policy-Report-Only: ' . $cspheaderreporting . ' report-uri ' . $collectorurl->out(false));
        }
        $cspheaderenforcing = trim(str_replace(array("\r\n", "\r", "\n"), " ", $settings->csp_header_enforcing));
        if (!empty($cspheaderenforcing)) {
            @header('Content-Security-Policy: ' . $cspheaderenforcing);
        }
    }

    public static function enable_feature_policy() {
        $settings = get_config('local_csp');
        if (empty($settings->feature_policy_enable)) {
            return;
        }

        $featureheader = trim(str_replace(array("\r\n", "\r", "\n"), " ", $settings->feature_policy));
        if (!empty($featureheader)) {
            @header('Feature-Policy: ' . $featureheader);
        }
    }

    public static function get_violations_listener() : string {
        $settings = get_config('local_csp');
        if (empty($settings->popup_enable)) {
            return '';
        }
        return '
        <script>
        let localCspViolationEvents = [];
        let localCspViolationEventTimeout = setTimeout(() => { Object.freeze(localCspViolationEvents); }, 1000);
        document.addEventListener("securitypolicyviolation", (e) => {
            clearTimeout(localCspViolationEventTimeout);
            localCspViolationEvents.push(e);
            localCspViolationEventTimeout = setTimeout(() => { Object.freeze(localCspViolationEvents); }, 500);
        });
        </script>' . PHP_EOL;
    }

    public static function enable_popup() {
        global $PAGE;
        global $USER;
        $settings = get_config('local_csp');
        if (empty($settings->popup_enable) || !has_capability('local/csp:seeviolationspopup', $PAGE->context, $USER->id)) {
            return;
        }
        $PAGE->requires->js_call_amd('local_csp/modal','init', [boolval($settings->popup_enforced_only)]);
    }
}
