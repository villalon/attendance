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
$url = new moodle_url('/local/attendance/viewsessionrecord.php');
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

$userfrom = $USER->id;
//$userfrom->maildisplay = true;

if(!(isset($CFG->local_uai_debug) && $CFG->local_uai_debug==1)) {
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$courseName = $DB->get_record('course', array('id'=>$courseid));
$usersCourse = $DB->get_records_sql('SELECT u.id FROM {user} as u 
				INNER JOIN {user_enrolments} as ue ON (ue.userid = u.id) 
				INNER JOIN {enrol} as e ON (e.id = ue.enrolid) 
				INNER JOIN {course} as c ON (e.courseid = c.id) 
				WHERE c.id = '.$courseid.' 
				AND u.id IN (SELECT u.id FROM {course} c LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid AND ra.roleid = "5" LEFT OUTER JOIN {user} u ON ra.userid = u.id WHERE cx.contextlevel = "50" and c.id = '.$courseid.') 
				ORDER BY u.lastname'
				);

//breadcrumbs
$PAGE->navbar->add($courseName->shortname,'/course/view.php?id='.$courseid);
$PAGE->navbar->add(get_string('attendances', 'local_attendance'));
$PAGE->navbar->add(get_string('viewattendance', 'local_attendance'));
$PAGE->navbar->add(get_string('bysessions', 'local_attendance'),'/local/attendance/viewsessionrecord.php?courseid='.$courseid);

$action = optional_param('action', 'view_by_session', PARAM_TEXT);

echo $OUTPUT->header();
$toprow = array();
$toprow[] = new tabobject('bystudent', new moodle_url('/local/attendance/viewstudentrecord.php', array('courseid'=>$courseid)), get_string('bystudents', 'local_attendance'));
$toprow[] = new tabobject('bysession', new moodle_url('/local/attendance/viewsessionrecord.php', array('courseid'=>$courseid)), get_string('bysessions', 'local_attendance'));
echo $OUTPUT->tabtree($toprow, 'bysession');

if ($action == 'delete_session'){
	$sessionToDelete = required_param('sessionid',PARAM_INT);
	$session = $DB->get_record('local_attendance_session',array('id'=>$sessionToDelete));
	if (!empty($session)){
	$name = (empty($session->comment)) ? get_string('noname', 'local_attendance') : $session->comment;
	$deleteContext = context_course::instance($session->courseid);
		if(!has_capability('local/attendance:teacherview', $deleteContext)){
			print_error(get_string('INVALID_ACCESS','local_attendance'));
		}
	 $DB->delete_records('local_attendance_attendance',array('sessionid'=>$sessionToDelete));
	 $DB->delete_records('local_attendance_session',array('id'=>$sessionToDelete));
	 
	 $subject = get_string('attendancemodified', 'local_attendance');
	 $subjectTosend = $courseName->shortname.': '.$subject;
	 
				    $message = $subject;
					$message .= '<br><br>';
					$message .= get_string('dear','local_attendance').',';
					$message .= '<br>';
					$message .=  get_string('professor','local_attendance').' '.$USER->firstname.' '.$USER->lastname.' ';
					$message .=  get_string('sessioneliminated','local_attendance').' ';
					$message .=  get_string('sessionnamed','local_attendance').' "'.$name.'", ';
					$message .=  get_string('onthecourse','local_attendance').' '.$courseName->fullname .'.';

					$eventdata = new stdClass();
					$eventdata->component 		  = 'local_attendance';
					$eventdata->name              = 'changenotification';
					$eventdata->userfrom		  = $userfrom;
					$eventdata->subject           = $subjectTosend;
					$eventdata->fullmessage       = format_text_email($message,FORMAT_HTML);
					$eventdata->fullmessageformat = FORMAT_HTML;
					$eventdata->fullmessagehtml   = '';
					$eventdata->smallmessage      = '';
					$eventdata->notification      = 1; //this is only set to 0 for personal messages between users
								
	foreach($usersCourse as $email){
		$eventdata->userto            = $email->id;//con id
		$send = message_send($eventdata);
		
	}
		if($send){
			echo '<div class="alert alert-info">'.get_string('alertsessioneliminated','local_attendance').'</div>';
			echo '<div class="alert alert-success">'.get_string('emailsent','local_attendance').'</div>';
		}
	}
	$action = 'view_by_session';
	
}
if($action == 'view_by_session'){
	$title = $courseName->fullname.' - '.get_string('attendancerecord', 'local_attendance');
	echo $OUTPUT->heading($title);
	echo '<br>';
	$table = tables::getAssistByDate($courseid);
	echo html_writer::table($table);
	$back = new moodle_url('/course/view.php?id='.$courseid);
	echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));
}

if($action == 'modify_session_attendance'){
	$sessionid = required_param('sessionid', PARAM_INT);
	$sessionName = $DB->get_record('local_attendance_session', array('id'=>$sessionid));
	
	$subject = get_string('attendancemodified', 'local_attendance');
	$subjectTosend = $courseName->shortname.': '.$subject;
	$actionsForm = new actionsForSession();
	
	if($fromform = $actionsForm->get_data()){
		$table = new html_table();
		$table->head = array(get_string('lastname','local_attendance'),get_string('firstname','local_attendance'),get_string('changes','local_attendance'),get_string('report','local_attendance'));
		$sessiondate = $DB->get_record('local_attendance_session', array('id'=>$fromform->sessionid));
		$name = (empty($sessiondate->comment) || is_null($sessiondate->comment)) ? get_string('noname','local_attendance') : $sessiondate->comment;
		if (isset($_POST['students'])){
			if ($fromform->action_for_checked=='mark_as_absent'){
				$message = '<br><br>'
				. get_string('dear','local_attendance').','
				. '<br>'
				. get_string('professor','local_attendance').' '.$USER->firstname.' '.$USER->lastname.' '
				. get_string('changestatus','local_attendance').' '.get_string('absent','local_attendance').' '
				. get_string('onthecourse','local_attendance').' '.$courseName->fullname.', '
				. get_string('inthesession','local_attendance').' "'.$name.'" ('.date('d/m/Y',$sessiondate->date).').';
	
				$eventdata = new stdClass();
				$eventdata->component 		  = 'local_attendance';
				$eventdata->name              = 'changenotification';
				$eventdata->userfrom		  = $USER->id;
				$eventdata->subject           = $subjectTosend;
				$eventdata->fullmessage       = format_text_email ($message, FORMAT_HTML );
				$eventdata->fullmessageformat = FORMAT_HTML;
				$eventdata->fullmessagehtml   = '';
				$eventdata->smallmessage      = '';
				$eventdata->notification      = 1; //this is only set to 0 for personal messages between users
	
				foreach ($_POST['students'] as $student){
					$username = $DB->get_record('user', array('id'=>$student));
					$attendanceExists = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$fromform->sessionid,'userid'=>$student));
					if ($attendanceExists){
						$DB->delete_records('local_attendance_attendance',array('sessionid'=>$fromform->sessionid,'userid'=>$student));
						$eventdata->userto            = $student;
						$send = message_send($eventdata);
						if($send){
							$row = new html_table_row(array($username->lastname,$username->firstname, get_string('yes','local_attendance'), '<div class="green">'.get_string('emailsent','local_attendance').'</div>'));
							$table->data[] = $row;
						}
					}
					else{
						$row = new html_table_row(array($username->lastname,$username->firstname, 'No', '<div class="red">'.get_string('alreadyabsent','local_attendance').'</div>'));
						$table->data[] = $row;
					}
						
				}
	
			}
			elseif ($fromform->action_for_checked=='mark_as_attended'){
				$message = '<br><br>'
				. get_string('dear','local_attendance').','
				. '<br>'
				. get_string('professor','local_attendance').' '.$USER->firstname.' '.$USER->lastname.' '
				. get_string('changestatus','local_attendance').' '.get_string('attendant','local_attendance').' '
				. get_string('onthecourse','local_attendance').' '.$courseName->fullname.', '
				. get_string('inthesession','local_attendance').' "'.$name.'" ('.date('d/m/Y',$sessiondate->date).').';
				
	
				$eventdata = new stdClass();
				$eventdata->component 		  = 'local_attendance';
				$eventdata->name              = 'changenotification';
				$eventdata->userfrom		  = $userfrom;
				$eventdata->subject           = $subjectTosend;
				$eventdata->fullmessage       = format_text_email ($message, FORMAT_HTML );
				$eventdata->fullmessageformat = FORMAT_HTML;
				$eventdata->fullmessagehtml   = '';
				$eventdata->smallmessage      = '';
				$eventdata->notification      = 1; //this is only set to 0 for personal messages between users
	
				foreach ($_POST['students'] as $student){
					$username = $DB->get_record('user', array('id'=>$student));
					$attendanceExists = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$fromform->sessionid,'userid'=>$student));
					if (!$attendanceExists){
						$DB->insert_record('local_attendance_attendance',array('sessionid'=>$fromform->sessionid,'userid'=>$student));
						$sessiondate = $DB->get_record('local_attendance_session', array('id'=>$fromform->sessionid));
						$eventdata->userto            = $student;
						$send = message_send($eventdata);
	
						if($send){
							$row = new html_table_row(array($username->lastname,$username->firstname, get_string('yes','local_attendance') , '<div class="green">'.get_string('emailsent','local_attendance').'</div>'));
							$table->data[] = $row;
						}
	
					}
					else{
						$row = new html_table_row(array($username->lastname,$username->firstname, 'No', '<div class="red">'.get_string('alreadypresent','local_attendance').'</div>'));
						$table->data[] = $row;
					}
						
				}
			}
	$back = new moodle_url('/local/attendance/viewsessionrecord.php', array('action'=>'view_session_details', 'courseid'=>$courseid, 'sessionid'=>$sessionid));
	echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));
	echo html_writer::table($table);
	echo $OUTPUT->single_button($back, get_string('back', 'local_attendance'));
	}
	else {
		echo '<div class="alert alert-danger">'.get_string('selectstudent','local_attendance').'</div>';
		$action = 'view_session_details';
	}
		
	}

}

if($action == 'view_session_details'){
	$sessionid = required_param('sessionid', PARAM_INT);
	$sessionName = $DB->get_record('local_attendance_session', array('id'=>$sessionid));
	$modifierName = $DB->get_record('user', array('id'=>$sessionName->modifierid));
	$attendedStudents = $DB->count_records('local_attendance_attendance',array("sessionid"=>$sessionid));
	$percentage = round(100*$attendedStudents/count($usersCourse));
	if($sessionName->comment != NULL){
		$name = $sessionName->comment;
	}else{
		$name = get_string('noname', 'local_attendance');
	}
	$title = $courseName->fullname.' - '.$name.' ('.get_string('attendance','local_attendance').': '.$percentage.'%)';
	echo $OUTPUT->heading($title);
	echo '<h3>'.get_string('openby','local_attendance').': '.' '.$modifierName->firstname.' '.$modifierName->lastname.'</h3>';
	echo '<br>';

	$actionsForm = new actionsForSession();
	$actionsForm->display();

}
echo $OUTPUT->footer();

?>
<script src="scripts/tableSorter.js"></script>
<script type="text/javascript" src="scripts/java.js"></script>
<link rel="stylesheet" type="text/css"  href="scripts/style.css"/>
