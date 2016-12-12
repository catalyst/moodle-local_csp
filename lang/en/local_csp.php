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

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Content security policy';
$string['cspenable'] = 'Content security policy';
$string['cspdescription'] = 'Enables monitoring of insecure content in Moodle';
$string['cspmonitoringmodenone'] = 'Do not monitor insecure content';
$string['cspmonitoringenabled'] = 'Monitor insecure content';
$string['mixedcontentexamples'] = 'Mixed content examples';
$string['activemixedcontent'] = 'Active mixed content';
$string['passivemixedcontent'] = 'Passive mixed content';
$string['simplemixedcontent'] = 'Simple mixed content';
$string['imagegallerymixedcontent'] = 'Image gallery mixed content';
$string['xmlhttprequestmixedcontent'] = 'XML HTTP request mixed content';
$string['loadinsecureelements'] = 'Load insecure elements to this page';
$string['loadinsecurejavascript'] = 'Load insecure javascript from {$a}';
$string['loadinsecurecss'] = 'Load insecure css from {$a}';
$string['loadinsecureimage'] = 'Load insecute image from {$a}';
$string['loadinsecureiframe'] = 'Load insecure iframe from {$a}';
$string['loadingmixedcontentdescription'] = 'When accessing moodle website via HTTPS browser prohibits displaying of the below resources because they origin from HTTP.<br />You should be able to see it in your browser\'s Javascript console.';
