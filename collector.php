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
 * A CSP report collector endpoint
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);
// @codingStandardsIgnoreStart
require_once(__DIR__ . '/../../config.php');
// @codingStandardsIgnoreEnd

$inputjson = file_get_contents('php://input');
$cspreport = json_decode($inputjson, true)['csp-report'];

global $DB, $SITE;

if ($cspreport) {
    $documenturi = remove_sesskey($cspreport['document-uri']);
    $blockeduri = remove_sesskey($cspreport['blocked-uri']);

    // We will be judging if CSP report is already recorded by searching over
    // the fields document-uri, blocked-uri, violated-directive, by hashing them.
    // This means that the truncated URI can be stored, while being properly deduped using the full data.
    $hash = sha1($documenturi . $blockeduri . $cspreport['violated-directive']);
    $existingrecord = $DB->get_record('local_csp', ['sha1hash' => $hash]);

    $dataobject = new stdClass();

    try {
        if ($existingrecord) {
            // Just increment failcounter of the existing record.
            $dataobject->id = $existingrecord->id;
            $dataobject->failcounter = $existingrecord->failcounter + 1;
            $dataobject->timeupdated = time();
            $DB->update_record('local_csp', $dataobject);
            echo "OK\n";
        } else {
            // Set the 'blockeddomain' and 'blockedurlpath' values.
            $parsedurl = parse_url($blockeduri);
            $blockeddomain = $parsedurl['host'];
            $blockedurlpath = $parsedurl['path'];

            // Insert a new record.
            // Truncate URIs of extreme length.
            $dataobject->courseid = optional_param('cid', $SITE->id, PARAM_INT);
            $dataobject->documenturi = substr($documenturi, 0, 1024);
            $dataobject->blockeduri = substr($blockeduri, 0, 1024);
            $dataobject->blockeddomain = $blockeddomain;
            $dataobject->blockedurlpath = $blockedurlpath;
            $dataobject->violateddirective = strtok($cspreport['violated-directive'], ' ');
            $dataobject->timecreated = time();
            $dataobject->sha1hash = $hash;
            $dataobject->failcounter = 1;
            $DB->insert_record('local_csp', $dataobject);
            echo "OK\n";
        }
    } catch (dml_exception $exception) {
        echo "There was a problem with recording CSP report to Moodle database.\n";
        throw $exception;
    }
} else {
    print_error("There was a problem with decoding JSON report.");
}

/**
 * Remove sesskey part from parameters and will never store it in DB.
 *
 * @param string $url
 * @return string URL
 */
function remove_sesskey($url) {
    try {
        $moodleurl = new moodle_url($url);
        $moodleurl->remove_params('sesskey');
        return $moodleurl->out();
    } catch (Exception $e) {
        // Not a url.
        return $url;
    }
}
