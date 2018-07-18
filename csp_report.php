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

// Remove CSP report record with specified hash. This is triggered from \local_csp\table\csp_report->col_action().
if (($removerecordwithhash = optional_param('removerecordwithhash', false, PARAM_TEXT)) !== false && confirm_sesskey()) {
    $DB->delete_records('local_csp', array('sha1hash' => $removerecordwithhash));
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

$documenturi = get_string('documenturi', 'local_csp');
$blockeduri = get_string('blockeduri', 'local_csp');
$violateddirective = get_string('violateddirective', 'local_csp');
$failcounter = get_string('failcounter', 'local_csp');
$timecreated = get_string('timecreated', 'local_csp');
$timeupdated = get_string('timeupdated', 'local_csp');
$action = get_string('action', 'local_csp');

$table = new \local_csp\table\csp_report('cspreportstable');
$table->define_baseurl($PAGE->url);
$table->sortable(true, 'failcounter', SORT_DESC);
$table->define_columns(array(
    'failcounter',
    'documenturi',
    'blockeduri',
    'violateddirective',
    'timecreated',
    'timeupdated',
    'action',
));
$table->define_headers(array(
    $failcounter,
    $documenturi,
    $blockeduri,
    $violateddirective,
    $timecreated,
    $timeupdated,
    $action,
));

$fields = 'id, sha1hash, documenturi, blockeduri, violateddirective, failcounter, timecreated, timeupdated';
$from = '{local_csp}';
$where = '1 = 1';
$table->set_sql($fields, $from, $where);

$table->out(30, true);

echo $OUTPUT->footer();
