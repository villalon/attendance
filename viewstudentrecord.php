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


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/local/attendance/forms.php');
require_once($CFG->dirroot.'/local/attendance/tables.php');


global $PAGE, $CFG, $OUTPUT, $DB, $COURSE, $USER;

require_login();

if(!(isset($CFG->local_uai_debug) && $CFG->local_uai_debug==1)) {
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$courseid = required_param('courseid', PARAM_INT);
$courseExists = $DB->record_exists('course', array('id'=>$courseid));
if(!$courseExists){
	print_error(get_string('INVALID_ACCESS','local_asistencias'));
}
$url = new moodle_url('/local/attendance/attendance.php');
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');


if(!has_capability('local/attendance:teacherview', $context)){
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$courseName = $DB->get_record('course', array('id'=>$courseid));

//breadcrumbs
$PAGE->navbar->add($courseName->shortname,'/course/view.php?id='.$courseid);
$PAGE->navbar->add (get_string('attendances', 'local_attendance'));
$PAGE->navbar->add(get_string('viewattendance', 'local_attendance'));
$PAGE->navbar->add(get_string('bystudents', 'local_attendance'),"/local/attendance/viewstudentrecord.php?courseid=$courseid");

$action = optional_param('action', 'view_by_student', PARAM_TEXT);

echo $OUTPUT->header();

$toprow = array();
$toprow[] = new tabobject("bystudent", new moodle_url('/local/attendance/viewstudentrecord.php', array('courseid'=>$courseid)), get_string('bystudents', 'local_attendance'));
$toprow[] = new tabobject("bysession", new moodle_url('/local/attendance/viewsessionrecord.php', array('courseid'=>$courseid)), get_string('bysessions', 'local_attendance'));
echo $OUTPUT->tabtree($toprow, "bystudent");

if($action == 'view_by_student'){
	$title = $courseName->fullname.' - '.get_string('attendancerecord', 'local_attendance');
	echo $OUTPUT->heading($title);
	echo '<br>';
	echo '<div id="filter"><strong>'.get_string('filter', 'local_attendance').': </strong> <input type="text"></div>';
	echo '<br>';
	$table = tables::getAssistByStudent($courseid);
	echo html_writer::table($table);
	$back = new moodle_url('/course/view.php?id='.$courseid);
	echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));
}


if($action == 'modify_student_attendance'){
	$subject = get_string('attendancemodified', 'local_attendance');
	$subjectTosend = $courseName->shortname.': '.$subject;
	$userid = required_param('userid', PARAM_INT);
	$actionsForm = new actionsForStudent();
	$username = $DB->get_record('user', array('id'=>$userid));
	$table = new html_table();
	$table->head = array(get_string('sessionname','local_attendance'), get_string('changes','local_attendance'),get_string('report','local_attendance'));
	
	if($fromform = $actionsForm->get_data()){
		if (isset($_POST["sessions"])){
			if ($fromform->action_for_checked=='mark_as_absent'){
				$dateData = '';
				$dataHead = get_string('date','local_attendance').' - '.get_string('sessionname','local_attendance').'<br><br>';
				
				$eventdata = new stdClass();
				$eventdata->component 		  = 'local_attendance';
				$eventdata->name              = 'changenotification';
				$eventdata->userfrom		  = $USER->id;
				$eventdata->userto            = $userid;
				$eventdata->subject           = $subjectTosend;
				$eventdata->fullmessageformat = FORMAT_HTML;
				$eventdata->fullmessagehtml   = '';
				$eventdata->smallmessage      = '';
				$eventdata->notification      = 1; //this is only set to 0 for personal messages between users
								
				foreach ($_POST['sessions'] as $session){
					$attendance_exists = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$session,'userid'=>$fromform->userid));
					$sessiondate = $DB->get_record('local_attendance_session', array('id'=>$session));
					$name = (empty($sessiondate->comment) || is_null($sessiondate->comment)) ? get_string('noname', 'local_attendance') : $sessiondate->comment;
					if ($attendance_exists){
						$dateData .= date('d/m/Y',$sessiondate->date).' - '.$name.'<br>';
						if($DB->delete_records('local_attendance_attendance',array('sessionid'=>$session,'userid'=>$fromform->userid))){ 
							$row = new html_table_row(array($name, get_string('yes','local_attendance'),'<div class="green">'. get_string('emailsent','local_attendance').'</div>'));
							$table->data[] = $row;
						}
					}
					else{
						$row = new html_table_row(array($name, 'No','<div class="red">'. get_string('alreadyabsent','local_attendance').'</div>'));
						$table->data[] = $row;
					}
				}
				if($dateData != NULL){
				$message  = '<br><br><br>'
						.  get_string('dear','local_attendance').','
						. '<br>'
						. get_string('professor','local_attendance').' '.$USER->firstname.' '.$USER->lastname.' '
						. get_string('changestatus','local_attendance').' '.get_string('absent','local_attendance').' '
					 	. get_string('onthecourse','local_attendance').' '.$courseName->fullname.', '
						. get_string('inthesession','local_attendance').':<br><br>'
						. $dataHead
						. $dateData;
																						
				
				$eventdata->fullmessage       = format_text_email ($message, FORMAT_HTML );
				$send = message_send($eventdata);				
				}
			}
			elseif ($fromform->action_for_checked=='mark_as_attended'){
				$dateData = '';
				$dataHead = get_string('date','local_attendance').' - '.get_string('sessionname','local_attendance').'<br><br>';
				
				$eventdata = new stdClass();
				$eventdata->component 		  = 'local_attendance';
				$eventdata->name              = 'changenotification';
				$eventdata->userfrom		  = $USER->id;
				$eventdata->userto            = $userid;
				$eventdata->subject           = $subjectTosend;
				$eventdata->fullmessageformat = FORMAT_HTML;
				$eventdata->fullmessagehtml   = '';
				$eventdata->smallmessage      = '';
				$eventdata->notification      = 1; //this is only set to 0 for personal messages between users
				
				foreach ($_POST['sessions'] as $session){			
				$sessiondate = $DB->get_record('local_attendance_session', array('id'=>$session));
				$attendance_exists = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$session,'userid'=>$fromform->userid));
				$name = (empty($sessiondate->comment)) ? get_string('noname', 'local_attendance') : $sessiondate->comment;
				if (!$attendance_exists){
					$dateData .= date('d/m/Y',$sessiondate->date).' - '.$name.'<br>' ;
					if($DB->insert_record('local_attendance_attendance',array('sessionid'=>$session,'userid'=>$fromform->userid))){
						$row = new html_table_row(array($name, get_string('yes','local_attendance'),'<div class="green">'. get_string('emailsent','local_attendance').'</div>'));
						$table->data[] = $row;
					}
				}
				else{
					$row = new html_table_row(array($name, 'No', '<div class="red">'.get_string('alreadypresent','local_attendance').'</div>'));
					$table->data[] = $row;
				}
			}
			if($dateData != NULL){
			$message  = '<br><br><br>'
					.  get_string('dear','local_attendance').','
					. '<br>'
					. get_string('professor','local_attendance').' '.$USER->firstname.' '.$USER->lastname.' '
					. get_string('changestatus','local_attendance').' '.get_string('attendant','local_attendance').' '
					. get_string('onthecourse','local_attendance').' '.$courseName->fullname.', '
					. get_string('inthesession','local_attendance').':<br><br>'
					. $dataHead
					. $dateData;
			
			$eventdata->fullmessage       = format_text_email ($message, FORMAT_HTML );
			$send = message_send($eventdata);
			}
			}
			$back = new moodle_url('/local/attendance/viewstudentrecord.php', array('action'=>'view_student_details', 'courseid'=>$courseid, 'userid'=>$userid));
			echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));
			echo html_writer::table($table);
			echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));		
		}
		else{
			echo '<div class="alert alert-danger">'.get_string('selectsession','local_attendance').'</div>';
			$action = 'view_student_details';
		}
	}	
}

if($action == 'view_student_details'){
	$userid = required_param('userid', PARAM_INT);
	$username = $DB->get_record('user', array('id'=>$userid));
	$percentage = $DB->get_record_sql('SELECT ROUND(100*(SELECT count(*) FROM {local_attendance_attendance} a WHERE a.sessionid
									   IN (SELECT s.id FROM {local_attendance_session} s WHERE courseid='.$courseid.')
									   AND userid='.$userid.')/(SELECT count(*) FROM {local_attendance_session} s WHERE courseid='.$courseid.')) as total');
	$title = $courseName->fullname.' - '.get_string('recordof', 'local_attendance').' '.$username->firstname.' '. $username->lastname.' ('.get_string('attendance','local_attendance').': '.$percentage->total.'%)';
	echo $OUTPUT->heading($title);
	echo '<br>';

	$actionsForm = new actionsForStudent();
	$actionsForm->display();

	$back = new moodle_url('/local/attendance/viewstudentrecord.php?id='.$courseid, array('action'=>'view_by_student', 'courseid'=>$courseid));
	echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));
}
echo $OUTPUT->footer();

?>
<script src="scripts/tableSorter.js"></script>
<script src="scripts/java.js"></script>
<link rel="stylesheet" type="text/css"  href="scripts/style.css"/>
