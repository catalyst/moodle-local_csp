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

/**
 * A listener is registered for the `securitypolicyviolation` event and the JS for the notifications is loaded.
 * The script for the event listener is injected into the page header.
 * This is done at this early stage to ensure that the event listener is in place before the events start coming.
 *
 * This is a legacy callback that is used for compatibility with older Moodle versions.
 * Moodle 4.4+ will use local_csp\hook_callbacks::before_standard_head_html_generation instead.
 */
function local_csp_before_standard_html_head() : string {
    return \local_csp\helper::enable_notifications();
}

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
 *
 * This is a legacy callback that is used for compatibility with older Moodle versions.
 * Moodle 4.4+ will use local_csp\hook_callbacks::before_http_headers instead.
 */
function local_csp_before_http_headers() {
    \local_csp\helper::enable_csp_header();
    \local_csp\helper::enable_feature_policy();
}
