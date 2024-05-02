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

namespace local_csp;

use core\hook\output\before_http_headers;
use core\hook\output\before_standard_head_html_generation;

/**
 * Hook callbacks for local_csp
 *
 * @package    local_csp
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Callback to add head elements.
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        $hook->add_html(\local_csp\helper::enable_notifications());
    }

    /**
     * Callback to add http headers.
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function before_http_headers(before_http_headers $hook): void {
        \local_csp\helper::enable_csp_header();
        \local_csp\helper::enable_feature_policy();
    }
}
