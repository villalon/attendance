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

//page that lets the teacher open a session to check attendance

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/local/attendance/forms.php');
require_once($CFG->dirroot.'/local/attendance/tables.php');


global $PAGE, $CFG, $OUTPUT, $DB, $COURSE;
require_login();
$courseid = required_param('courseid', PARAM_INT);

if(!(isset($CFG->local_uai_debug) && $CFG->local_uai_debug==1)) {
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

//check if the course exists (if the user changed the courseid in url)
$courseExists = $DB->record_exists('course', array('id'=>$courseid));
if(!$courseExists){
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$url = new moodle_url('/local/attendance/attendance.php'); 
$context = context_course::instance($courseid); 
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

//check if the user is a teacher un thes course
if(!has_capability('local/attendance:teacherview', $context)){
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$courseName = $DB->get_record('course', array('id'=>$courseid));

//breadcrumbs
$PAGE->navbar->add($courseName->shortname,'/course/view.php?id='.$courseid);
$PAGE->navbar->add (get_string('attendances', 'local_attendance'));
$PAGE->navbar->add(get_string('checkattendance', 'local_attendance'),'/local/attendance/attendance.php?courseid='.$courseid);

$action = optional_param('action','startpage', PARAM_TEXT);

//closes the open session and shows startpage
if ($action=='close_session'){
	$session = $DB->get_record('local_attendance_session',array('courseid'=>$courseid,'open'=>1));
	if (!empty($session)){
		$DB->update_record('local_attendance_session',array("id"=>$session->id,"open"=>0));
	$message =  '<div class="alert alert-info">'.get_string('sessionclosed', 'local_attendance').'</div>';
	}
	$action = 'startpage';
}

//opens a new session and starts getting attended list
if ($action=='open_session'){
	$session = $DB->get_record('local_attendance_session',array('courseid'=>$courseid,'open'=>1));
	if (empty($session)){
	$openSessionForm = new openSession();
	if($fromform = $openSessionForm->get_data()){	
	$DB->insert_record('local_attendance_session',array('courseid'=>$courseid,'date'=>time(),'modifierid'=>$USER->id,'open'=>1,'comment'=>$fromform->comment,'duration'=>$fromform->duration));
	$action = 'check_attendance';
	}}
	else{
	$action = 'startpage';
	}
}

//continues a previously open session and starts getting attended list
if ($action=='continue_session'){
	$session = $DB->get_record('local_attendance_session',array('courseid'=>$courseid,'open'=>1));
	if (!empty($session)){
		$action = 'check_attendance';
	}
	else{
		$action = 'startpage';
	}
}

//shows course students and starts getAttended function
if ($action=='check_attendance'){
	$title = get_string('checkattendance', 'local_attendance');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);

	echo '<h3 id="sessionStatus">'.get_string('waitingfor', 'local_attendance') .'<span>. . . </span></h3>';
	echo '<script>var courseid = '.$courseid.';</script>'; //course id that will be used inside javascript
	echo '<div class="alert alert-info">'.get_string('knowledgemessage', 'local_attendance').' <span id="remainingTime"></span> '.get_string('minutes', 'local_attendance').'</div>';
	echo '<h4 id="percentage">'.get_string('total', 'local_attendance').': <span>0</span>%</h4>';
	echo '<h4 id="numberAttended">'.get_string('attendants', 'local_attendance').': <span>0</span></h4>';
	echo '<script type="text/javascript" language="javascript" src="scripts/java.js"></script>';
	echo $OUTPUT->single_button(new moodle_url('/local/attendance/attendance.php',array('courseid'=>$courseid,'action'=>'close_session')),get_string('closesession', 'local_attendance'));
	$table = tables::getCourseStudents($courseid);
	echo html_writer::table($table);
}

//start page that lets the user continue or start a session
if ($action=='startpage'){
	$title = get_string('checkattendance', 'local_attendance');
	$PAGE->set_title($title);
	$PAGE->set_heading($title);
	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);

	echo '<h3>'.$courseName->fullname.'</h3>';

	if (isset($message)){
	echo $message;
}

	$session = $DB->get_record('local_attendance_session',array('courseid'=>$courseid,'open'=>1));
	if (empty($session)){
		echo '<div class="alert alert-info">'.get_string('nosessions', 'local_attendance').'</div>';
		$openSessionForm = new openSession();
		$openSessionForm->display();
	}
	else{
		$course = $DB->get_record('course',array('id'=>$session->courseid));
		echo '<h3>'.get_string('alreadyopen', 'local_attendance').' '. $course->fullname.'</h3><br>';
		echo $OUTPUT->single_button(new moodle_url('/local/attendance/attendance.php',array('courseid'=>$courseid,'action'=>'continue_session')),get_string('staysession','local_attendance'));
		echo $OUTPUT->single_button(new moodle_url('/local/attendance/attendance.php',array('courseid'=>$courseid,'action'=>'close_session')),get_string('closesession', 'local_attendance'));
	}
}
$back = new moodle_url('/course/view.php?id='.$courseid);
echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));

echo $OUTPUT->footer();
?>
<link rel="stylesheet" type="text/css"  href="scripts/style.css" />