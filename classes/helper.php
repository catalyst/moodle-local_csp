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
        if (self::$bootstrapped || empty($settings->csp_header_enable)) {
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

    /**
     * Enables feature policy
     * @return void
     */
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

    /**
     * If the notifications were enabled in the website settings **and** the user has the capability to see them,
     * this method does two things:
     * It calls upon the AMD module containing the code for generating the notifications to be loaded.
     * In addition, a short script for registering the necessary event listener is returned to be injected directly
     * into the page. This is done here to ensure that the event listener is in place before the events start coming.
     * Putting the event listener in a regular AMD module can (and in some tests, does) cause it to be run way too
     * late and thus miss the `securitypolicyviolation` events being fired. Additional tests showed that it made no
     * difference, which of the Moodle hooks were used. It appears that the requirejs module loader itself runs too
     * late, i.e. after the events of interest to us here.
     */
    public static function enable_notifications() : string {
        global $PAGE, $USER;
        $conf = get_config('local_csp');
        $cansee = false;
        if (get_capability_info('local/csp:seenotifications')) {
            $cansee = has_capability('local/csp:seenotifications', $PAGE->context, $USER->id);
        }

        if (!$cansee) {
            return '';
        }

        $notificationsenableenforced = !empty($conf->notifications_enable_enforced);
        $notificationsenablereported = !empty($conf->notifications_enable_reported);
        if (!$notificationsenableenforced && !$notificationsenablereported) {
            return '';
        }

        $collectenforced = $notificationsenableenforced == 1 ? 'true' : 'false';
        $collectreported = $notificationsenablereported == 1 ? 'true' : 'false';

        $PAGE->requires->js_call_amd(
            'local_csp/notifications',
            'init',
            [1000]  // TODO: Consider making the event trigger timeout value a setting in the admin panel.
        );
        return "
        <script>
        /* Start listening to violation events */
        let localCspViolationsEnforced = [];
        let localCspViolationsReported = [];
        document.addEventListener('securitypolicyviolation', (event) => {
            if ($collectenforced && event.disposition === 'enforce') {
                localCspViolationsEnforced.push(event);
            } else if ($collectreported && event.disposition === 'report') {
                localCspViolationsReported.push(event);
            }
        });
        </script>" . PHP_EOL;
    }

    /**
     * Merge duplicate local_csp records that have matching sha1hash fields.
     *
     * @param array $records Array of local_csp records with matching sha1hash. Must contain at least two records.
     * @return \stdClass The final object representing a local_csp record once all records are merged into one.
     */
    public static function merge_duplicate_records(array $records): \stdClass {
        global $DB;
        // If no records provided, there is no object to return so throw exception.
        if (empty($records)) {
            throw new \moodle_exception('norecordsfound', 'local_csp');
        }

        // If only 1 record provided, just return it.
        if (count($records) === 1) {
            return reset($records);
        }

        // Check that sha1hash is identical for all records, then combine fields.
        $baserecord = array_shift($records);
        foreach ($records as $record) {
            if ($baserecord->sha1hash != $record->sha1hash) {
                throw new \moodle_exception('nonduplicaterecords', 'local_csp');
            }
            $baserecord->failcounter += $record->failcounter;
        }
        $baserecord->timeupdated = time();

        // Remove all duplicates, and insert new base record.
        $transaction = $DB->start_delegated_transaction();
        unset($baserecord->id);
        $DB->delete_records('local_csp', ['sha1hash' => $baserecord->sha1hash]);
        $baserecord->id = $DB->insert_record('local_csp', $baserecord);
        $transaction->allow_commit();

        return $baserecord;
    }
}
