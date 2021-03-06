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

/*
 * Strings for component 'field_time', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    survey
 * @subpackage item_time
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');

$string['parentformat'] = SURVEYFIELD_TIME_FORMAT;
$string['parentcontentinvalidtime_err'] = 'Provided data is not a regular time';
$string['parentcontentdateoutofrange_err'] = 'Provided time is out of the range requested to the choosen item';
$string['pluginname'] = 'Time';
$string['userfriendlypluginname'] = 'Time';
$string['defaultvalue_help'] = 'This is the time the remote user will find answered by default. The default for this type of question is mandatory. If "Current time" is choosed as default, boundaries are not supposed to apply.';
$string['defaultvalue'] = 'Default';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['lowerbound_help'] = 'The lower time the user will be allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['upperbound_help'] = 'The upper time the user will be allowed to enter';
$string['upperbound'] = 'Upper bound';
$string['rangetype'] = 'Range type';
$string['rangetype_help'] = 'The time provided by the user will need to fall between lower and upper bound or will need to be lower than the lower bound or greater than the upper bound?';
$string['internalrange'] = 'internal range';
$string['externalrange'] = 'external range';
$string['outofrangedefault'] = 'Default does not fall within the specified range';
$string['and'] = ' and ';
$string['restriction_lowerupper_internal'] = 'Time is supposed to fit between {$a}';
$string['restriction_lowerupper_external'] = 'Time is supposed to be lower than {$a->lowerbound} or greater than {$a->upperbound}';
$string['restriction_lower'] = 'Time is supposed to be greater than {$a}';
$string['restriction_upper'] = 'Time is supposed to be lower than {$a}';
$string['uerr_lowerthanminimum'] = 'Provided time is too small';
$string['uerr_greaterthanmaximum'] = 'Provided time is too high';
$string['customdefault'] = 'Custom';

$string['currenttimedefault'] = 'Current time';
$string['invitationhour'] = 'Choose an hour';
$string['invitationminute'] = 'Choose a minute';
$string['uerr_hournotset'] = 'Please choose an hour';
$string['uerr_minutenotset'] = 'Please choose a minute';
