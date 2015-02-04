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


//page that lets a student mark attendance on an open session

require_once(dirname(__FILE__) . '/../../config.php'); 
require_once($CFG->dirroot.'/local/attendance/forms.php');
require_once($CFG->dirroot.'/local/attendance/tables.php');


global $PAGE, $CFG, $OUTPUT, $DB, $COURSE, $USER;
require_login();

if(!(isset($CFG->local_uai_debug) && $CFG->local_uai_debug==1)) {
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$url = new moodle_url('/local/attendance/attendance.php'); 
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//breadcrumbs
$PAGE->navbar->add(get_string('attendances', 'local_attendance'));
$PAGE->navbar->add(get_string('markattendance', 'local_attendance'),'/local/attendance/markattendance.php');

$action = optional_param('action','startpage',PARAM_TEXT);

$title = get_string('markattendance', 'local_attendance');
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if ($action=='mark_attendance'){
	
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {$ip = $_SERVER['HTTP_CLIENT_IP'];}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];}
	else {$ip = $_SERVER['REMOTE_ADDR'];}
	
	$courseid = required_param('courseid',PARAM_INT);
	$session = $DB->get_record_sql('SELECT s.id,c.fullname FROM {user} u INNER JOIN {user_enrolments} ue ON (ue.userid = u.id) INNER JOIN {enrol} e ON (e.id = ue.enrolid) INNER JOIN {course} c ON (e.courseid = c.id) INNER JOIN {local_attendance_session} s ON (c.id=s.courseid) WHERE ue.userid = '.$USER->id.' AND s.open=1 AND c.id='.$courseid);
	$attendanceExists = $DB->record_exists("local_attendance_attendance",array('sessionid'=>$session->id,'userid'=>$USER->id));
		if ($attendanceExists){
			echo '<div class="alert alert-danger">'.get_string('alreadyregistered', 'local_attendance').'</div>';
		}
		else{
			$DB->insert_record('local_attendance_attendance',array('sessionid'=>$session->id,'userid'=>$USER->id));
			echo '<div class="alert alert-success">'.get_string('attendancemarkedon', 'local_attendance').' '.$session->fullname.'</div>';
		}
		$action = 'startpage';	
}	



if ($action=='startpage'){

$sessions = $DB->get_records_sql('SELECT c.id,c.fullname,u.id as userid,s.id as sessionid FROM {user} u INNER JOIN {user_enrolments} ue ON (ue.userid = u.id) INNER JOIN {enrol} e ON (e.id = ue.enrolid) INNER JOIN {course} c ON (e.courseid = c.id) INNER JOIN {local_attendance_session} s ON (c.id=s.courseid) WHERE ue.userid = '.$USER->id.' AND s.open=1');

if (empty($sessions)){
	echo '<div class="alert alert-info">'.get_string('nosessions', 'local_attendance').'</div>';
}
else{
	$n = 0;
	foreach($sessions as $session){
		$sessionContext = context_course::instance($session->id);
		if(!has_capability('local/attendance:teacherview', $sessionContext)){
			$n++;
		}
	}
	if($n > 0){
	echo '<h3>'.get_string('followingopensessions', 'local_attendance').'</h3>';
	$table = tables::getOpenSessions($sessions);
	echo html_writer::table($table);
	}else{
		echo '<div class="alert alert-info">'.get_string('nosessions', 'local_attendance').'</div>';
	}
}
}

echo $OUTPUT->single_button(new moodle_url('/'),get_string('back', 'local_attendance'));

echo $OUTPUT->footer();
?>
<link rel="stylesheet" type="text/css"  href= "scripts/style.css"/>