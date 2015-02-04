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
 *
 *
 * @package    local
 * @subpackage attendance
 * @copyright  2015 Juan Pablo Baltra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


//this page returns assistance percentage of the open session and the id of each present student
//this data is parsed and shown as json

require_once(dirname(__FILE__) . '/../../../config.php');
global $PAGE, $CFG, $OUTPUT, $DB;

$courseid = required_param('courseid',PARAM_INT);

//current open session in the course
$sessionid = $DB->get_record('local_attendance_session',array("courseid"=>$courseid,"open"=>1));

if (!empty($sessionid)){
$data = array(); //array that contains the json data

//current students that have marked attendance
$attended = $DB->get_records('local_attendance_attendance',array("sessionid"=>$sessionid->id));

//all the students in the course
$students = $DB->get_record_sql('SELECT count(*) as total FROM {user} as u
		INNER JOIN {user_enrolments} as ue ON (ue.userid = u.id)
		INNER JOIN {enrol} as e ON (e.id = ue.enrolid)
		INNER JOIN {course} as c ON (e.courseid = c.id)
		WHERE c.id = '.$courseid.'
		AND u.id IN (SELECT u.id FROM {course} c LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid AND ra.roleid = "5" LEFT OUTER JOIN {user} u ON ra.userid = u.id WHERE cx.contextlevel = "50" and c.id = '.$courseid.')'
		);

$data['percentage'] = round(100*count($attended)/$students->total);
$data['studentIds'] = array(); //array that contains the ids of the attending students
$data['string'] = get_string('attendant','local_attendance'); //string that replaces "absent"
$data['remainingTime'] = ceil($sessionid->duration-(time()-$sessionid->date)/60); //remaining time to close session
foreach ($attended as $student){$data['studentIds'][]=$student->userid;} 
echo json_encode($data); 
}
?>