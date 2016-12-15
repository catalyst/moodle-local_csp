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
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_csp\table;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Class table_sql_time_pretty implements formatting unix timestamps columns to human time.
 * @package local_csp\table
 */
class csp_report extends \table_sql {
    /**
     * Formatting unix timestamps in column named timecreated to human readable time.
     *
     * @param $record fieldset object of db table with field timecreated
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
     * @param $record fieldset object of db table with field timeupdated
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
     * @param $record fieldset object of db table with field documenturi
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
     * @param $record fieldset object of db table with field blockeduri
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


}
