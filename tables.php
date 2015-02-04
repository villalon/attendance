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

defined('MOODLE_INTERNAL') || die();

class tables{

	public function __construct(){

	}
	//show student list to start checking attendance
	public static function getCourseStudents($courseid){ 
		global $DB, $OUTPUT;
		$students = $DB->get_records_sql('SELECT u.id, u.firstname, u.lastname FROM {user} as u 
				INNER JOIN {user_enrolments} as ue ON (ue.userid = u.id) 
				INNER JOIN {enrol} as e ON (e.id = ue.enrolid) 
				INNER JOIN {course} as c ON (e.courseid = c.id) 
				WHERE c.id = '.$courseid.' 
				AND u.id IN (SELECT u.id FROM {course} c LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid AND ra.roleid = "5" LEFT OUTER JOIN {user} u ON ra.userid = u.id WHERE cx.contextlevel = "50" and c.id = '.$courseid.') 
				ORDER BY u.lastname'
				);
		$table = new html_table();
		$table->head = array(get_string('lastname', 'local_attendance'),get_string('firstname', 'local_attendance'), get_string('attendance', 'local_attendance'));
		foreach($students as $student){
			$table->data[]= array($student->lastname, $student->firstname, get_string('absent', 'local_attendance').'<input type="hidden" value="'.$student->id.'">');
		}
		return $table;

	}	
	//shows list of open sessions
	public static function getOpenSessions($sessions){
		global $DB, $OUTPUT;
		$table = new html_table();
		$table->head = array(get_string('course','local_attendance'),"");
		foreach($sessions as $session){
			$sessionContext = context_course::instance($session->id);
			if(!has_capability('local/attendance:teacherview', $sessionContext)){
			$attended = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$session->sessionid,'userid'=>$session->userid));
			$button = ($attended) ? get_string('attendancemarked', 'local_attendance') : $OUTPUT->single_button(new moodle_url('markattendance.php',array('action'=>'mark_attendance','courseid'=>$session->id)), get_string('markattendance', 'local_attendance'));
			$table->data[]= array($session->fullname,$button);
		}
		}
		return $table;
	
	}
	
	//show every session in the course and the attendance of the current user for each one
	public static function getStudentHistory($courseid){
		global $DB, $OUTPUT, $USER;
		$table = new html_table();
		$table->head = array(get_string('date', 'local_attendance'),get_string('hour', 'local_attendance'),get_string('sessionname', 'local_attendance'),get_string('attendance', 'local_attendance'));
		$sessions = $DB->get_records('local_attendance_session',array('courseid'=>$courseid,'open'=>0));
		foreach($sessions as $session){
		$attended = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$session->id,'userid'=>$USER->id));
		$attendance = ($attended) ? get_string('attendant', 'local_attendance') : get_string('absent', 'local_attendance'); 
		$cellClass = ($attended) ? 'green' : 'red';
		$attendanceCell = new html_table_cell($attendance);
		$attendanceCell->attributes['class'] = $cellClass;
		if($session->comment != NULL){
			$name = $session->comment;
		}else{
			$name = get_string('noname', 'local_attendance');
		}
		$table->data[]= array('<input type="hidden" value="'.$session->date.'">'.date('d/m/Y',$session->date),date('H:i',$session->date),$name, $attendanceCell);
	}
		return $table;
	
	}
	
	//shows
	public static function getAssistByStudent($courseid){
		global $DB, $OUTPUT;
		$students = $DB->get_records_sql('SELECT u.id, u.firstname, u.lastname FROM {user} as u 
				INNER JOIN {user_enrolments} as ue ON (ue.userid = u.id) 
				INNER JOIN {enrol} as e ON (e.id = ue.enrolid) 
				INNER JOIN {course} as c ON (e.courseid = c.id) 
				WHERE c.id = '.$courseid.' 
				AND u.id IN (SELECT u.id FROM {course} c LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid AND ra.roleid = "5" LEFT OUTER JOIN {user} u ON ra.userid = u.id WHERE cx.contextlevel = "50" and c.id = '.$courseid.')
				ORDER BY u.lastname');
		$table = new html_table();
		$table->attributes['sort_by']='0 1';
		$table->head = array(get_string('lastname', 'local_attendance'), get_string('firstname', 'local_attendance'), get_string('attendance', 'local_attendance'), get_string('detail', 'local_attendance'));
		$icono = new pix_icon('i/preview', get_string('detail','local_attendance'));
		foreach($students as $student){
			$url =  new moodle_url('/local/attendance/viewstudentrecord.php', array('action'=>'view_student_details', 'courseid'=>$courseid, 'userid'=>$student->id));
			$btn = $OUTPUT->action_icon($url, $icono);
			$percentage = $DB->get_record_sql('SELECT ROUND(100*(SELECT count(*) FROM {local_attendance_attendance} a WHERE a.sessionid
									   IN (SELECT s.id FROM {local_attendance_session} s WHERE courseid='.$courseid.')
									   AND userid='.$student->id.')/(SELECT count(*) FROM {local_attendance_session} s WHERE courseid='.$courseid.')) as total');
			$table->data[]= array($student->lastname, $student->firstname, $percentage->total.'%', $btn);
		}
	return $table;
	}
	
	
	//shows attendance by session date for a course
	public static function getAssistByDate($courseid){
		global $DB, $OUTPUT;
		$sessions = $DB->get_records('local_attendance_session',array('courseid'=>$courseid,'open'=>0));
		$table = new html_table();
		$table->head = array(get_string('date', 'local_attendance'),get_string('hour','local_attendance'),get_string('sessionname','local_attendance'), get_string('attendance', 'local_attendance'), get_string('options', 'local_attendance'));
		$totalStudents = $DB->get_record_sql('SELECT count(u.id) as total FROM {user} as u 
				INNER JOIN {user_enrolments} as ue ON (ue.userid = u.id) 
				INNER JOIN {enrol} as e ON (e.id = ue.enrolid) 
				INNER JOIN {course} as c ON (e.courseid = c.id) 
				WHERE c.id = '.$courseid.' 
				AND u.id IN (SELECT u.id FROM {course} c LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid AND ra.roleid = "5" LEFT OUTER JOIN {user} u ON ra.userid = u.id WHERE cx.contextlevel = "50" and c.id = '.$courseid.')
				ORDER BY u.lastname'
				);
		$detailIcon = new pix_icon('i/preview', get_string('detail','local_attendance'));
		$deleteIcon = new pix_icon('t/delete', get_string('delete','local_attendance'));
		foreach($sessions as $session){
			$deleteUrl =  new moodle_url('/local/attendance/viewsessionrecord.php', array('action'=>'delete_session', 'courseid'=>$courseid, 'sessionid'=>$session->id));
			$detailUrl =  new moodle_url('/local/attendance/viewsessionrecord.php', array('action'=>'view_session_details', 'courseid'=>$courseid, 'sessionid'=>$session->id));
			$detailButton = $OUTPUT->action_icon($detailUrl, $detailIcon);
			$deleteButton = $OUTPUT->action_icon($deleteUrl ,$deleteIcon, new confirm_action(get_string('deletesession', 'local_attendance')));
			$attendedStudents = $DB->count_records('local_attendance_attendance',array("sessionid"=>$session->id));
			$percentage = round(100*$attendedStudents/$totalStudents->total);
			if($session->comment != NULL){
				$name = $session->comment;
			}else{
				$name = get_string('noname', 'local_attendance');
			}
			$table->data[]= array('<input type="hidden" value="'.$session->date.'">'.date('d/m/Y',$session->date),date('H:i', $session->date), $name, $percentage.'%', $detailButton . $deleteButton);
		}
	return $table;
	}
	
	//shows every session in the course and the attendance of the selected student in each one
	public static function getAssistStudentDetail($courseid, $userid){
		global $DB, $OUTPUT;
		$table = new html_table();
		$table->head = array(get_string('date', 'local_attendance'),get_string('hour', 'local_attendance'),get_string('sessionname', 'local_attendance'),get_string('attendance', 'local_attendance'), '<input type="checkbox" id="select_all">'.get_string('selectall', 'local_attendance'));
		$sessions = $DB->get_records('local_attendance_session',array('courseid'=>$courseid,'open'=>0));
		foreach($sessions as $session){
			$attended = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$session->id,'userid'=>$userid));
			$attendance = ($attended) ? get_string('attendant', 'local_attendance') : get_string('absent', 'local_attendance');
			$cellClass = ($attended) ? 'green' : 'red';
			$checkbox = '<input type="checkbox" name="sessions[]" value="'.$session->id.'">';
			if($session->comment != NULL){
				$name = $session->comment;
			}else{
				$name = get_string('noname','local_attendance');
			}
			$attendanceCell = new html_table_cell($attendance);
			$attendanceCell->attributes['class'] = $cellClass;
			$table->data[]= array('<input type="hidden" value="'.$session->date.'">'.date('d/m/Y',$session->date),date('H:i',$session->date),$name, $attendanceCell, $checkbox);
		}
	return $table;
	}
	
	//shows every student and the attendance of each one in the selected session
	public static function getAssistDateDetail($courseid, $sessionid){
		global $DB, $OUTPUT;
		$table = new html_table();
		$table->attributes['sort_by']='0 1';
		$table->head = array(get_string('lastname', 'local_attendance'),get_string('firstname', 'local_attendance'),get_string('attendance', 'local_attendance'),'<input type="checkbox" id="select_all">'.get_string('selectall', 'local_attendance'));
		$students = $DB->get_records_sql('SELECT u.id, u.firstname, u.lastname FROM {user} as u 
			INNER JOIN {user_enrolments} as ue ON (ue.userid = u.id) 
			INNER JOIN {enrol} as e ON (e.id = ue.enrolid) 
			INNER JOIN {course} as c ON (e.courseid = c.id) 
			WHERE c.id = '.$courseid.' 
			AND u.id IN (SELECT u.id FROM {course} c LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid AND ra.roleid = "5" LEFT OUTER JOIN {user} u ON ra.userid = u.id WHERE cx.contextlevel = "50" and c.id = '.$courseid.')
			ORDER BY u.lastname'
		);
		foreach($students as $student){
			$attended = $DB->record_exists('local_attendance_attendance', array('sessionid'=>$sessionid,'userid'=>$student->id));
			$attendance = ($attended) ? get_string('attendant', 'local_attendance') : get_string('absent', 'local_attendance');
			$cellClass = ($attended) ? 'green' : 'red';
			$checkbox = '<input type="checkbox" name="students[]" value="'.$student->id.'">';
			$attendanceCell = new html_table_cell($attendance);
			$attendanceCell->attributes['class'] = $cellClass;
			$table->data[]= array($student->lastname,$student->firstname, $attendanceCell,$checkbox);
		}
	return $table;
	
	}
	
	//shows list of open sessions for webapp
	public static function getOpenSessionsWebapp($sessions){
		global $DB, $OUTPUT;
		$table = new html_table();
		$table->head = array(get_string('course','local_attendance'),"");
		foreach($sessions as $session){
			$sessionContext = context_course::instance($session->id);
			$loadImage = '<img class="loader" src="scripts/images/loader.gif">';
			if(!has_capability('local/attendance:teacherview', $sessionContext, $_SESSION['user_webapp']->id)){
				$attended = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$session->sessionid,'userid'=>$session->userid));
				$button = ($attended) ? get_string('attendancemarked', 'local_attendance') : $loadImage.$OUTPUT->single_button(new moodle_url('markattendance.php',array('action'=>'mark_attendance','courseid'=>$session->id)), get_string('markattendance', 'local_attendance'));
				$table->data[]= array($session->fullname,$button);
			}
		}
		return $table;
	
	}
}