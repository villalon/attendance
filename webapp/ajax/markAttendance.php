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

require_once(dirname(__FILE__) . '/../../../../config.php');
global $PAGE, $CFG, $OUTPUT, $DB;

$data = array();
$courseid = required_param('courseid',PARAM_INT);
$session = $DB->get_record_sql('SELECT s.id,c.fullname FROM {user} u INNER JOIN {user_enrolments} ue ON (ue.userid = u.id) INNER JOIN {enrol} e ON (e.id = ue.enrolid) INNER JOIN {course} c ON (e.courseid = c.id) INNER JOIN {local_attendance_session} s ON (c.id=s.courseid) WHERE ue.userid = '.$_SESSION['user_webapp']->id.' AND s.open=1 AND c.id='.$courseid);
$attendanceExists = $DB->record_exists("local_attendance_attendance",array('sessionid'=>$session->id,'userid'=>$_SESSION['user_webapp']->id));
if (!$attendanceExists){
	$DB->insert_record('local_attendance_attendance',array('sessionid'=>$session->id,'userid'=>$_SESSION['user_webapp']->id));
}

$data['string'] = get_string('attendancemarked', 'local_attendance');
echo json_encode($data); 

?>