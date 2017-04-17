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
 * General lib.php for the plugin.
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Moodle native lib/navigationlib.php calls this hook allowing us to override UI.
 * Here we instruct Moodle website to issue custom HTTP response header Content-Security-Policy-Report-Only on every page.
 */
function local_csp_extend_navigation() {
    \local_csp\helper::enable_csp_header();
}

/**
 * Moodle native lib/navigationlib.php calls this hook allowing us to override UI.
 * Here we instruct Moodle website to issue custom HTTP response header Content-Security-Policy-Report-Only on every page.
 */
function local_csp_before_http_headers() {
    \local_csp\helper::enable_csp_header();
}

