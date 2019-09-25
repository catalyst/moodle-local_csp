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
 * This is an admin_externalpage 'local_csp_report' for displaying the recorded csp reports.
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

global $DB;

// Delete violation class if param set
if (($removeviolationclass = optional_param('removeviolationclass', false, PARAM_TEXT)) !== false && confirm_sesskey()) {
    $DB->delete_records('local_csp', array('blockeduri' => $removeviolationclass));
    $PAGE->set_url('/local/csp/csp_report.php', array(
        'page' => optional_param('redirecttopage', 0, PARAM_INT),
    ));
    redirect($PAGE->url);
}

// Delete individual violation records if set
if (($removerecordwithid = optional_param('removerecordwithid', false, PARAM_TEXT)) !== false && confirm_sesskey()) {
    $DB->delete_records('local_csp', array('id' => $removerecordwithid));
    $PAGE->set_url('/local/csp/csp_report.php', array(
        'page' => optional_param('redirecttopage', 0, PARAM_INT),
    ));
    redirect($PAGE->url);
}

$resetallcspstatistics = optional_param('resetallcspstatistics', 0, PARAM_INT);
if ($resetallcspstatistics == 1 && confirm_sesskey()) {
    $DB->delete_records('local_csp');
    redirect(new moodle_url('/local/csp/csp_report.php'));
}

admin_externalpage_setup('local_csp_report', '', null, '', array('pagelayout' => 'report'));

$title = get_string('cspreports', 'local_csp');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('admin');

global $OUTPUT, $DB;

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$action = new \confirm_action(get_string('areyousuretodeleteallrecords', 'local_csp'));
$urlresetallcspstatistics = new moodle_url($PAGE->url, array(
    'resetallcspstatistics' => 1,
    'sesskey' => sesskey(),
));
echo $OUTPUT->single_button($urlresetallcspstatistics,
    get_string('resetallcspstatistics', 'local_csp'), 'post', array('actions' => array($action)));

$blockeduri = get_string('blockeduri', 'local_csp');
$highestviolaters = get_string('highestviolaters', 'local_csp');
$violateddirective = get_string('violateddirective', 'local_csp');
$documenturi = get_string('documenturi', 'local_csp');
$failcounter = get_string('failcounter', 'local_csp');
$timeupdated = get_string('timeupdated', 'local_csp');
$action = get_string('action', 'local_csp');

$table = new \local_csp\table\csp_report('cspreportstable');
$table->define_baseurl($PAGE->url);
$table->sortable(true, 'failcounter', SORT_DESC);
$table->define_columns(array(
    'failcounter',
    'violateddirective',
    'blockeduri',
    'highestviolaters',
    'timecreated',
    'action',
));
$table->define_headers(array(
    $failcounter,
    $violateddirective,
    $blockeduri,
    $highestviolaters,
    $timeupdated,
    $action,
));

$viewviolationclass = optional_param('viewviolationclass', false, PARAM_TEXT);
// If user has clicked on a violation to view all violation entries
if ($viewviolationclass !== false) {
    $fields = 'id, sha1hash, blockeduri, violateddirective, failcounter, timeupdated, documenturi';
    $from = "{local_csp}";
    $where = "blockeduri = ?";
    $params = array($viewviolationclass);

    // Redefine columns to display Violation source
    $table->define_columns(array(
        'failcounter',
        'violateddirective',
        'blockeduri',
        'documenturi',
        'timeupdated',
        'action',
    ));
    $table->define_headers(array(
        $failcounter,
        $violateddirective,
        $blockeduri,
        $documenturi,
        $timeupdated,
        $action,
    ));

} else {
    $fields = 'id, blockeduri, violateddirective, failcounter, timecreated';
    // Select the first blockedURI of a type, and collapse the rest while summing failcounter
    //
    $from = "(SELECT MAX(id) AS id,
                     blockeduri,
                     violateddirective,
                     SUM(failcounter) AS failcounter,
                     MAX(timecreated) AS timecreated
                FROM {local_csp}
            GROUP BY blockeduri, violateddirective) AS A";
    $where = '1 = 1';
    $params = array();
}
$table->set_sql($fields, $from, $where, $params);

$table->out(30, true);

echo $OUTPUT->footer();
