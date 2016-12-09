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
 * @package   tool_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_csp\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

class renderer extends plugin_renderer_base {
    /**
     * @return string html for the page
     */
    public function render_mixed_content_examples() {
        return parent::render_from_template('tool_csp/mixed_content_examples', null);
    }

    /**
     * We want to add a new custom HTTP response header.
     * So far we will be sending this custom header only in plugin tool_csp.
     * In order to enable whole Moodle website to respond with this HTTP response header, we will need to override method header() in theme.
     *
     * @return mixed
     */
    public function header() {
        // If the admin setting for monitoring is on, then send the Content-Security-Policy-Report-Only header to collect stats
        if (get_config('tool_csp', 'activation') == 'enabled'){
            $collectorurl = new \moodle_url('/admin/tool/csp/csp_reports_collector.php');
            header('Content-Security-Policy-Report-Only: default-src https:; report-uri ' . $collectorurl->out());
        }
        return parent::header();
    }
}

