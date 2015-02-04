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


//page that lets a student view his own attendance record

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
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$url = new moodle_url('/local/attendance/studentrecord.php'); 
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

if(has_capability('local/attendance:teacherview', $context)){
	print_error(get_string('INVALID_ACCESS','local_attendance'));
}

$courseName = $DB->get_record('course', array('id'=>$courseid));

//breadcurmbs
$PAGE->navbar->add($courseName->shortname,'/course/view.php?id='.$courseid);
$PAGE->navbar->add(get_string('attendances', 'local_attendance'));
$PAGE->navbar->add(get_string('record', 'local_attendance'),'/local/attendance/studentrecord.php?courseid='.$courseid);

$percentage = $DB->get_record_sql('SELECT ROUND(100*(SELECT count(*) FROM {local_attendance_attendance} a WHERE a.sessionid
								  IN (SELECT s.id from {local_attendance_session} s WHERE s.courseid='.$courseid.') AND a.userid='.$USER->id.')/
								  (SELECT count(*) FROM {local_attendance_session} s WHERE s.courseid='.$courseid.')) as total');

$title = $courseName->fullname.' - '.get_string('attendancerecord', 'local_attendance').' ('.get_string('attendance','local_attendance').': '.$percentage->total.'%)';
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo '<br>';
$table = tables::getStudentHistory($courseid);
echo html_writer::table($table);

echo $OUTPUT->single_button(new moodle_url('/course/view.php?id='.$courseid),get_string('back', 'local_attendance'));

global $local_attendance;
$local_attendance = true;
echo $OUTPUT->footer();
?>