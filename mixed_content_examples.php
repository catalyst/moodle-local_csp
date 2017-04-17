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
 * An file which creates bad example links
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_csp_examples');

$title = get_string('mixedcontentexamples', 'local_csp');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('admin');

global $OUTPUT;

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$nonsslwwwroot = str_replace('https', 'http', $CFG->wwwroot);

echo html_writer::tag('h5', get_string('loadingmixedcontentdescription', 'local_csp'));

$insecurescript = $nonsslwwwroot . '/local/csp/samples/sample.js';
echo html_writer::tag('p', get_string('loadinsecurejavascript', 'local_csp', $insecurescript));
echo html_writer::start_tag('script', array(
    'type' => 'text/javascript',
    'src' => $insecurescript,
));
echo html_writer::end_tag('script');

$insecurecss = $nonsslwwwroot . '/local/csp/samples/sample.css';
echo html_writer::tag('p', get_string('loadinsecurecss', 'local_csp', $insecurecss));
echo html_writer::start_tag('link', array(
    'src' => $insecurecss,
    'rel' => "stylesheet",
));
echo html_writer::end_tag('link');

$insecureimage = $nonsslwwwroot . '/local/csp/samples/sample.jpg';
echo html_writer::tag('p', get_string('loadinsecureimage', 'local_csp', $insecureimage));
echo html_writer::tag('img', '', array(
    'src' => $insecureimage,
));

$insecureiframe = $nonsslwwwroot . '/local/csp/samples/sample.html';
echo html_writer::tag('p', get_string('loadinsecureimage', 'local_csp', $insecureiframe));
echo html_writer::tag('iframe', '', array(
    'src' => $insecureiframe,
));

echo $OUTPUT->footer();
