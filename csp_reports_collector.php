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

$dataobject = new stdClass();
$dataobject->blockeduri = $cspreport['blocked-uri'];
$dataobject->documenturi = $cspreport['document-uri'];
$dataobject->linenumber = $cspreport['line-number'];
$dataobject->originalpolicy = $cspreport['original-policy'];
$dataobject->scriptsample = $cspreport['script-sample'];
$dataobject->referrer = $cspreport['referrer'];
$dataobject->sourcefile = $cspreport['source-file'];
$dataobject->violateddirective = $cspreport['violated-directive'];
$dataobject->timecreated = time();

if ($DB->insert_record('local_csp', $dataobject)) {
    echo 'CSP report recorded.';
} else {
    echo 'There was a problem with recording CSP report to Moodle database.';
}


