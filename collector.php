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
 * HTTP response header Content-Security-Policy-Report-Only has been configured to send JSON reports to this script.
 * Process JSON reports and store them in db.
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$inputjson = file_get_contents('php://input');
$cspreport = json_decode($inputjson, true)['csp-report'];

require_once(__DIR__ . '/../../config.php');
global $DB;

if ($cspreport) {
    // We will be judging if CSP report is already recorded by searching over sha1-hashed fields document-uri, blocked-uri, violated-directive.
    $hash = sha1($cspreport['document-uri'] . $cspreport['blocked-uri'] . $cspreport['violated-directive']);
    $existingrecord = $DB->get_record('local_csp', array('sha1hash' => $hash));

    $dataobject = new stdClass();

    try {
        if ($existingrecord) {
            // Just increment failcounter of the existing record.
            $dataobject->id = $existingrecord->id;
            $dataobject->failcounter = $existingrecord->failcounter + 1;
            $dataobject->timeupdated = time();
            $DB->update_record('local_csp', $dataobject);
            echo 'Repeated CSP violation, failcounter incremented.';
        } else {
            // Insert a new record.
            $dataobject->documenturi = $cspreport['document-uri'];
            $dataobject->blockeduri = $cspreport['blocked-uri'];
            $dataobject->violateddirective = $cspreport['violated-directive'];
            $dataobject->timecreated = time();
            $dataobject->sha1hash = $hash;
            $DB->insert_record('local_csp', $dataobject);
            echo 'New CSP violation recorded.';
        }
    } catch (dml_exception $exception) {
        echo 'There was a problem with recording CSP report to Moodle database.';
        throw $exception;
    }
} else {
    echo "There was a problem with decoding JSON report.";
}
