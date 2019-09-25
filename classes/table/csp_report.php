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
 * @package local_csp\table
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csp_report extends \table_sql {

    /**
     * Embeds a link to a drilldown table showing only 1 violation class
     *
     * @param stdObject $record fieldset object of db table with field timecreated
     * @return string Link to drilldown table
     */
    protected function col_failcounter($record) {
        // Get blocked URI, and set as param for page if clicked on
        $url = new \moodle_url('/local/csp/csp_report.php', array ('viewviolationclass' => $record->blockeduri));
        return  \html_writer::link($url, $record->failcounter);
    }

    /**
     * Stop violateddirective from wrapping when long urls are present
     *
     * @param stdObject $record fieldset object of db table with field timecreated
     * @return string Non breaking text line
     */
    protected function col_violateddirective($record) {
        // Stop line from wrapping
        return \html_writer::tag('span', $record->violateddirective, array('style' => 'white-space: nowrap'));
    }

    /**
     * Formatting unix timestamps in column named timecreated to human readable time.
     *
     * @param stdObject $record fieldset object of db table with field timecreated
     * @return string human readable time
     */
    protected function col_timecreated($record) {
        if ($record->timecreated) {
            $timecreated = userdate($record->timecreated, get_string('strftimedatetimeshort'));
            return $timecreated;
        } else {
            return  '-';
        }
    }

    /**
     * Formatting unix timestamps in column named timeupdated to human readable time.
     *
     * @param stdObject $record fieldset object of db table with field timeupdated
     * @return string human readable time
     */
    protected function col_timeupdated($record) {
        if ($record->timeupdated) {
            $timeupdated = userdate($record->timeupdated, get_string('strftimedatetimeshort'));
            return $timeupdated;
        } else {
            return  '-';
        }
    }

    /**
     * Formatting column documenturi that has URLs as links.
     *
     * @param stdObject $record fieldset object of db table with field documenturi
     * @return string HTML e.g. <a href="documenturi">documenturi</a>
     */
    protected function col_documenturi($record) {
        if ($record->documenturi) {
            if (filter_var($record->documenturi, FILTER_VALIDATE_URL) === false) {
                return $record->documenturi;
            } else {
                $documenturi = '<a href="' . $record->documenturi . '">' . $record->documenturi . '</a>';
                return $documenturi;
            }
        } else {
            return  '-';
        }
    }

    /**
     * Formatting column blockeduri that has URLs as links.
     *
     * @param stdObject $record fieldset object of db table with field blockeduri
     * @return string HTML e.g. <a href="blockeduri">blockeduri</a>
     */
    protected function col_blockeduri($record) {
        if ($record->blockeduri) {
            if (filter_var($record->blockeduri, FILTER_VALIDATE_URL) === false) {
                return $record->blockeduri;
            } else {
                $blockeduri = '<a href="' . $record->blockeduri . '">' . $record->blockeduri . '</a>';
                return $blockeduri;
            }
        } else {
            return  '-';
        }
    }

    /**
     * Draw a link to the original table report URI with a param instructing to remove the record. e.g.
     *
     * @param stdObject $record
     * @return string HTML link.
     */
    protected function col_action($record) {
        global $OUTPUT, $PAGE;

        // Find whether drilldown flag is present in PAGE params
        $viewviolationclass = optional_param('viewviolationclass', false, PARAM_TEXT);
        if ($viewviolationclass !== false) {
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
            // Else delete entire violation class
            $action = new \confirm_action(get_string('areyousuretodeleteonerecord', 'local_csp'));
            $url = new \moodle_url($this->baseurl);
            $url->params(array(
                'removeviolationclass' => $record->blockeduri,
                'sesskey' => sesskey(),
                'redirecttopage' => $this->currpage,
            ));
            $actionlink = $OUTPUT->action_link($url, get_string('reset', 'local_csp'), $action);

            return $actionlink;
        }
    }

    /**
     * Gets the 3 highest violater documentURIs for each blockedURI
     *
     * @param stdObject $record fieldset object of db table with field timecreated
     * @return string details of the highest violating documents
     */
    protected function col_highestviolaters($record) {
        global $DB, $CFG;

        // Get 3 highest violaters for each blocked URI
        $sql = "SELECT *
                  FROM {local_csp}
                 WHERE blockeduri = ?
              ORDER BY failcounter DESC";
        $violaters = $DB->get_records_sql($sql, array($record->blockeduri), 0, 3);
        $return = '';
        foreach ($violaters as $violater) {
            // Strip the top level domain out of the display
            $urlstring = str_replace($CFG->wwwroot, '', $violater->documenturi);
            $return .= get_string('highestviolaterscount', 'local_csp', $violater->failcounter).' '.\html_writer::link($violater->documenturi, $urlstring).'<br>';
        }
        return $return;
    }
} // end class csp_report
