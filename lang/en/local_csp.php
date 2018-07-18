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
 * Lang pack for local_csp
 *
 * @package   local_csp
 * @author    Suan Kan <suankan@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['action'] = 'Action';
$string['areyousuretodeleteallrecords'] = 'Are you sure to delete all CSP report records?';
$string['areyousuretodeleteonerecord'] = 'Are you sure to delete this CSP report record?';
$string['blockeduri'] = 'Blocked URI';
$string['configurecspheader'] = 'Configure CSP header';
$string['cspdirectives'] = 'CSP directives';
$string['cspdirectivesinfo'] = '<p>Example of CSP directives (please refer to the above link for exact syntax):<br /><span style="color:#00acdf">script-src https:; style-src cdn.example.com; default-src \'self\';</span></p>';
$string['cspheaderdefault'] = "default-src https:;\nscript-src 'self' *.catalyst-analytics.nz 'unsafe-inline' 'unsafe-eval';\nfont-src https: data:;\nstyle-src https: 'unsafe-inline';\nimg-src https: data:;";
$string['cspheaderenable'] = 'CSP header enable';
$string['cspheaderenabledescription'] = 'Tick this checkbox to enable CSP headers';
$string['cspheaderenforce'] = 'Content-Security-Policy';
$string['cspheaderenforcing'] = 'Content-Security-Policy';
$string['cspheaderenforcinghelp'] = 'Enforce browsers to follow CSP directives, e.g. block loading insecure content';
$string['cspheaderreporting'] = 'Content-Security-Policy-Report-Only';
$string['cspheaderreportinghelp'] = 'Monitor and report CSP violations';
$string['csphttpresponseheader'] = 'CSP HTTP response header';
$string['cspreports'] = 'CSP violation reports';
$string['cspsettings'] = 'Content security policy settings';
$string['cspsettingsinfo'] = '<p>CSP works through adding a special HTTP response header to every Moodle page. Modern browsers, when they see this header, are able to perform certain actions e.g. block insecure content on such pages. Please read more about CSP <a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP">here</a>.</p><p>If you leave any of these settings blank CSP headers will not be used.</p>';
$string['documenturi'] = 'Violation at';
$string['failcounter'] = 'Count';
$string['loadingmixedcontentdescription'] = 'When accessing moodle website via HTTPS browser prohibits displaying of the below resources because they origin from HTTP.<br />You should be able to see it in your browser\'s Javascript console.';
$string['loadinsecurecss'] = 'Load insecure css from {$a}';
$string['loadinsecureiframe'] = 'Load insecure iframe from {$a}';
$string['loadinsecureimage'] = 'Load insecure image from {$a}';
$string['loadinsecurejavascript'] = 'Load insecure javascript from {$a}';
$string['localcspheadingdirectives'] = 'Configure CSP directives';
$string['localcspheadinghttpresponseheader'] = 'Choose CSP HTTP response header';
$string['mixedcontentexamples'] = 'Mixed content examples';
$string['pluginname'] = 'Content security policy';
$string['reset'] = 'Reset';
$string['resetallcspstatistics'] = 'Reset all statistics';
$string['scspheadernone'] = 'Not used';
$string['timecreated'] = 'Recorded';
$string['timeupdated'] = 'Last';
$string['violateddirective'] = 'Policy';
$string['privacy:metadata'] = 'The CSP plugin contains no user specific data.';
