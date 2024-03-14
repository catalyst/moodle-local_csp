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
 * csp_report table
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_csp\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Class csp_report implements processing of columns
 *
 * - Convert unix timestamp columns to human time.
 * - Adds a button to delete a record.
 *
 * @package local_csp
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csp_report extends \table_sql {

    /**
     * Embeds a link to a drilldown table showing only 1 violation class
     *
     * @param \stdClass $record fieldset object of db table with field timecreated
     * @return string Link to drilldown table
     */
    protected function col_failcounter($record) {
        // Get blocked URI, and set as param for page if clicked on.
        $url = new \moodle_url('/local/csp/csp_report.php',
            [
                'blockeddomain' => $record->blockeddomain,
                'blockeddirective' => $record->violateddirective
            ]
        );
        return \html_writer::link($url, $record->failcounter);
    }

    /**
     * Stop violateddirective from wrapping when long urls are present
     *
     * @param \stdClass $record fieldset object of db table with field timecreated
     * @return string Non breaking text line
     */
    protected function col_violateddirective($record) {
        // Stop line from wrapping.
        return \html_writer::tag('span', strtok($record->violateddirective, ' '), array('style' => 'white-space: nowrap'));
    }

    /**
     * Formatting unix timestamps in column named timecreated to human readable time.
     *
     * @param \stdClass $record fieldset object of db table with field timecreated
     * @return string human readable time
     */
    protected function col_timecreated($record) {
        if ($record->timecreated) {
            return userdate($record->timecreated, get_string('strftimedatetimeshort'))
                . '<br>'
                . format_time(time() - $record->timecreated);
        } else {
            return  '-';
        }
    }

    /**
     * Formatting unix timestamps in column named timeupdated to human readable time.
     *
     * @param \stdClass $record fieldset object of db table with field timeupdated
     * @return string human readable time
     */
    protected function col_timeupdated($record) {
        if ($record->timeupdated) {
            return userdate($record->timeupdated, get_string('strftimedatetimeshort'));
        } else {
            return  '-';
        }
    }

    /**
     * Formatting column documenturi that has URLs as links.
     *
     * @param \stdClass $record fieldset object of db table with field documenturi
     * @return string HTML e.g. <a href="documenturi">documenturi</a>
     */
    protected function col_documenturi($record) {
        return $this->format_uri($record->documenturi);
    }

    /**
     * Format a uri
     *
     * @param string $uri Unsafe uri data
     * @param string $label The label for the URL
     * @param int $size How many chars to show
     * @return string HTML e.g. <a href="documenturi">documenturi</a>
     */
    private function format_uri($uri, $label = '', $size = 40) {
        global $CFG;
        if (!$uri) {
            return '-';
        }
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            return s($uri);
        }

        if (empty($label)) {
            $label = $uri;
        }
        $label = str_replace($CFG->wwwroot, '', $label);
        $label = ltrim($label, '/');
        $label = shorten_text($label, $size, true);
        $label = s($label);

        return \html_writer::link($uri, $label);
    }

    /**
     * Formatting column blockeduri that has URLs as links.
     *
     * @param \stdClass $record fieldset object of db table with field blockeduri
     * @return string HTML e.g. <a href="blockeduri">blockeduri</a>
     */
    protected function col_blockeduri($record) {
        return $this->format_uri($record->blockeduri);
    }

    /**
     * Displays the column 'blockeddomain'.
     *
     * @param \stdClass $record fieldset object of db table with field blockeddomain
     * @return string The blocked domain
     */
    protected function col_blockeddomain($record) {
        if (is_null($record->blockeddomain)) {
            return '-';
        }

        return $this->format_uri('https://' . $record->blockeddomain, $record->blockeddomain);
    }

    /**
     * Displays the column 'blockedurlpath'.
     *
     * @param \stdClass $record fieldset object of db table with field blockedurlpath
     * @return string The blocked domain
     */
    protected function col_blockedurlpaths($record) {
        global $DB;

        // Get 3 highest blocked paths for each blocked directive + blocked domain.
        $subsql = "SELECT blockeduri,
                          blockedurlpath,
                          SUM(failcounter) AS failcounter
                     FROM {local_csp} csp
                    WHERE violateddirective = :directive
                      AND blockeddomain = :blockeddomain
                 GROUP BY blockeduri,
                          blockedurlpath
                 ORDER BY SUM(failcounter) DESC,
                          blockeduri";

        $params = [
            'directive' => $record->violateddirective,
            'blockeddomain' => $record->blockeddomain
        ];

        $blockedpaths = $DB->get_records_sql($subsql, $params, 0, 3);
        $return = '';
        foreach ($blockedpaths as $blockedpath) {
            // Strip the top level domain out of the display only if https.
            $label = $blockedpath->blockeduri;
            $label = str_replace('https://' . $record->blockeddomain, '', $label);
            $return .= $this->format_uri($blockedpath->blockeduri, $label);
            $return .= ' ';
            $return .= "<sup>($blockedpath->failcounter)</sup>";
            $return .= '<br />';
        }
        return $return;
    }

    /**
     * Gets the 3 highest violater documentURIs for each blockedURI
     *
     * @param \stdClass $record fieldset object of db table with field timecreated
     * @return string details of the highest violating documents
     */
    protected function col_highestviolaters($record) {
        global $DB;

        // Get 3 highest blocked paths for each blocked directive + blocked domain.
        $subsql = "SELECT documenturi,
                          SUM(failcounter) AS failcounter
                     FROM {local_csp} csp
                    WHERE violateddirective = :directive
                      AND blockeddomain = :blockeddomain
                 GROUP BY documenturi
                 ORDER BY SUM(failcounter) DESC,
                          documenturi ASC";

        $params = [
            'directive' => $record->violateddirective,
            'blockeddomain' => $record->blockeddomain
        ];

        $violators = $DB->get_records_sql($subsql, $params, 0, 3);
        $return = '';
        foreach ($violators as $violator) {
            // Strip the top level domain out of the display.
            $return .= $this->format_uri($violator->documenturi);
            $return .= ' ';
            $return .= "<sup>($violator->failcounter)</sup>";
            $return .= '<br />';
        }

        return $return;
    }

    /**
     * Gets the 3 highest violater courses for each blockedURI
     *
     * @param \stdClass $record fieldset object of db table
     * @return string details of the highest violating courses
     */
    protected function col_courses($record) {
        global $DB;

        // Get 3 highest courses for each blocked directive + blocked domain.
        // Don't get any rows that have the value of 0 for their courseid (legacy data).
        $subsql = "SELECT courseid,
                          shortname,
                          SUM(failcounter) AS failcounter
                     FROM {local_csp} csp
                     JOIN {course} c
                       ON c.id = csp.courseid
                    WHERE violateddirective = :directive
                      AND blockeddomain = :blockeddomain
                 GROUP BY courseid, shortname
                 ORDER BY SUM(failcounter) DESC,
                          shortname ASC";

        $params = [
            'directive' => $record->violateddirective,
            'blockeddomain' => $record->blockeddomain
        ];

        $courses = $DB->get_records_sql($subsql, $params, 0, 3);
        $return = '';
        foreach ($courses as $course) {
            $courseurl = new \moodle_url('/course/view.php', ['id' => $course->courseid]);

            $return .= \html_writer::link($courseurl, $course->shortname);
            $return .= ' ';
            $return .= "<sup>($course->failcounter)</sup>";
            $return .= '<br />';
        }

        return $return;
    }

    /**
     * Draw a link to the original table report URI with a param instructing to remove the record. e.g.
     *
     * @param \stdClass $record
     * @return string HTML link.
     */
    protected function col_action($record) {
        global $OUTPUT;

        // Find whether we are drilling down.
        $viewblockeddomain = optional_param('blockeddomain', false, PARAM_TEXT);
        $viewdirective = optional_param('blockeddirective', false, PARAM_TEXT);
        if ($viewblockeddomain && $viewdirective) {
            $action = new \confirm_action(get_string('areyousuretodeleteonerecord', 'local_csp'));
            $url = new \moodle_url($this->baseurl);
            $url->params(array(
                'removerecordwithid' => $record->id,
                'sesskey' => sesskey(),
                'redirecttopage' => $this->currpage,
            ));
            $actionlink = $OUTPUT->action_link($url, get_string('reset', 'local_csp'), $action);

            return $actionlink;
        } else {
            // Else delete entire violation class.
            $action = new \confirm_action(get_string('areyousuretodeleteonerecord', 'local_csp'));
            $url = new \moodle_url($this->baseurl);
            $url->params(
                [
                    'removedirective' => $record->violateddirective,
                    'removedomain' => $record->blockeddomain,
                    'sesskey' => sesskey(),
                    'redirecttopage' => $this->currpage,
                ]
            );
            $actionlink = $OUTPUT->action_link($url, get_string('reset', 'local_csp'), $action);

            return $actionlink;
        }
    }
}
