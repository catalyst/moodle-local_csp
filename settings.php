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
 * moodle-local_csp settings.
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_csp', get_string('pluginname', 'local_csp')));

    $settings = new admin_settingpage('local_csp_settings', get_string('cspsettings', 'local_csp'));
    $ADMIN->add('local_csp', $settings);
    $ADMIN->add('local_csp',
        new admin_externalpage('local_csp_examples',
            get_string('mixedcontentexamples', 'local_csp'),
            new moodle_url('/local/csp/mixed_content_examples.php')
        ));
    $ADMIN->add('local_csp',
        new admin_externalpage('local_csp_report',
            get_string('cspreports', 'local_csp'),
            new moodle_url('/local/csp/csp_report.php')
        ));

    $settings->add(new admin_setting_heading('local_csp_heading_http_response_header',
        get_string('configurecspheader', 'local_csp'),
        get_string('cspsettingsinfo', 'local_csp')
    ));

    $settings->add(new admin_setting_configcheckbox('local_csp/csp_header_enable', get_string('cspheaderenable', 'local_csp'),
        get_string('cspheaderenabledescription', 'local_csp'), 0));

    $settings->add(new admin_setting_configtextarea('local_csp/csp_header_reporting', get_string('cspheaderreporting', 'local_csp'),
        get_string('cspheaderreportinghelp', 'local_csp'), get_string('cspheaderdefault', 'local_csp')));

    $settings->add(new admin_setting_configtextarea('local_csp/csp_header_enforcing', get_string('cspheaderenforcing', 'local_csp'),
        get_string('cspheaderenforcinghelp', 'local_csp'), ''));
}

