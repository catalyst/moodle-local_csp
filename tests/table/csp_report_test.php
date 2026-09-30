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

namespace local_csp\table;

/**
 * Unit tests for the csp_report table.
 *
 * @package    local_csp
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_csp\table\csp_report
 */
final class csp_report_test extends \advanced_testcase {
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Reported URIs with a non-web scheme must not be rendered as links.
     */
    public function test_blockeduri_with_javascript_scheme_is_not_linked(): void {
        $html = $this->render_blockeduri('javascript://cdn.example.org/%0aalert(document.domain)');

        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringNotContainsString('href=', $html);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Reported web URIs are still rendered as links.
     */
    public function test_blockeduri_with_https_scheme_is_linked(): void {
        $html = $this->render_blockeduri('https://cdn.example.org/script.js');

        $this->assertStringContainsString('href="https://cdn.example.org/script.js"', $html);
    }

    /**
     * Render the blocked URI column for a single record.
     *
     * @param string $uri Reported blocked URI
     * @return string Rendered cell HTML
     */
    private function render_blockeduri(string $uri): string {
        $table = new csp_report('local_csp_test');
        $method = new \ReflectionMethod($table, 'col_blockeduri');
        return $method->invoke($table, (object) ['blockeduri' => $uri]);
    }
}
