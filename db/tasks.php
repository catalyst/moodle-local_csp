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
 * Schedule tasks definition.
 *
 * @package    local_csp
 * @author     Benjamin Walker <benjaminwalker@catalyst-au.net>
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_csp\task\cleanup_csp_task',
        'blocking'  => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
