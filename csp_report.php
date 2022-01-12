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

$viewblockeddomain = optional_param('blockeddomain', false, PARAM_TEXT);
if ($viewblockeddomain) {
    $viewdirective = required_param('blockeddirective', PARAM_TEXT);
}
$removedirective = optional_param('removedirective', false, PARAM_TEXT);
$removedomain = optional_param('removedomain', false, PARAM_TEXT);
$removerecordwithid = optional_param('removerecordwithid', false, PARAM_TEXT);

admin_externalpage_setup('local_csp_report', '', null, '', array('pagelayout' => 'report'));

// Delete violation class if param set.
if ($removedirective && $removedomain && confirm_sesskey()) {
    $DB->delete_records('local_csp', [
            'violateddirective' => $removedirective,
            'blockeddomain' => $removedomain
        ]
    );
    $PAGE->set_url('/local/csp/csp_report.php', array(
        'page' => optional_param('redirecttopage', 0, PARAM_INT),
    ));
    redirect($PAGE->url);
}

// Delete individual violation records if set.
if ($removerecordwithid && confirm_sesskey()) {
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
$blockeddomain = get_string('blockeddomain', 'local_csp');
$blockedurlpath = get_string('blockedurlpaths', 'local_csp');
$highestviolaters = get_string('highestviolaters', 'local_csp');
$violateddirective = get_string('violateddirective', 'local_csp');
$documenturi = get_string('documenturi', 'local_csp');
$courses = get_string('courses');
$failcounter = get_string('failcounter', 'local_csp');
$timeupdated = get_string('timeupdated', 'local_csp');
$action = get_string('action', 'local_csp');

$table = new \local_csp\table\csp_report('cspreportstable');
$table->define_baseurl($PAGE->url);
$table->sortable(true, 'failcounter', SORT_DESC);
$table->set_attribute('class', 'generaltable generalbox table-sm');
$table->define_columns(array(
    'failcounter',
    'violateddirective',
    'blockeddomain',
    'blockedurlpaths',
    'highestviolaters',
    'courses',
    'timecreated',
    'action',
));
$table->no_sorting('blockedurlpaths');
$table->no_sorting('highestviolaters');
$table->no_sorting('courses');
$table->define_headers(array(
    $failcounter,
    $violateddirective,
    $blockeddomain,
    $blockedurlpath,
    $highestviolaters,
    $courses,
    $timeupdated,
    $action,
));

// If user has clicked on a violation to view all violation entries.
if ($viewblockeddomain && $viewdirective) {
    $fields = 'id, sha1hash, blockeduri, blockeddomain, violateddirective, failcounter, timeupdated, documenturi';
    $from = "{local_csp}";
    $where = "blockeddomain = ? AND violateddirective = ?";
    $params = [$viewblockeddomain, $viewdirective];

    // Redefine columns to display Violation source.
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
    $fields = 'id, blockeddomain, violateddirective, failcounter, timecreated';
    // Select the first blockedURI of a type, and collapse the rest while summing failcounter.
    $from = "(SELECT MAX(id) AS id,
                     blockeddomain,
                     violateddirective,
                     SUM(failcounter) AS failcounter,
                     MAX(timecreated) AS timecreated
                FROM {local_csp}
            GROUP BY violateddirective, blockeddomain) AS A";
    $where = '1 = 1';
    $params = array();
}
$table->set_sql($fields, $from, $where, $params);

$table->out(30, true);

echo $OUTPUT->footer();
