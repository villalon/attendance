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
 //a

/**
 * 
 *
 * @package    local
 * @subpackage attendance
 * @copyright  2015 Juan Pablo Baltra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/attendance/tables.php');

class actionsForStudent extends moodleform{
	function definition() {
		global $DB,$userid,$courseid,$OUTPUT,$action;
		$mform =& $this->_form;
		$actionOptions = array('mark_as_attended'=>get_string('markattended', 'local_attendance'),'mark_as_absent'=>get_string('markabsent', 'local_attendance'));
		$actionGroup = array();
		$actionGroup[] =& $mform->createElement('select','action_for_checked','',$actionOptions);
		$actionGroup[] =& $mform->createElement('submit', 'submitbutton', get_string('apply', 'local_attendance'));
		$mform->addGroup($actionGroup, 'actionGroup', get_string('chooseoption', 'local_attendance'), array(' '), false);
		
		$studentsTable = tables::getAssistStudentDetail($courseid, $userid);
		$mform->addElement('html', html_writer::table($studentsTable));
		
		$mform->addElement('hidden','userid',$userid);
		$mform->setType('userid',PARAM_INT);
		$mform->addElement('hidden','courseid',$courseid);
		$mform->setType('courseid',PARAM_INT);
		$mform->addElement('hidden','action','modify_student_attendance');
		$mform->setType('action',PARAM_TEXT);
	}
	function validation($data,$files) {
	
	}
}

class actionsForSession extends moodleform{
	function definition() {
		global $DB,$sessionid,$courseid,$OUTPUT,$action,$userid;
		$mform =& $this->_form;
		
		$actionOptions = array('mark_as_attended'=>get_string('markattended', 'local_attendance'),'mark_as_absent'=>get_string('markabsent', 'local_attendance'));
		$actionGroup = array();
		$actionGroup[] =& $mform->createElement('select','action_for_checked','',$actionOptions);
		$actionGroup[] =& $mform->createElement('submit', 'submitbutton', get_string('apply', 'local_attendance'));
		$actionGroup[] =& $mform->createElement('html','<div id="filter">'.get_string('filter', 'local_attendance').': <input type="text"></div>');
		$mform->addGroup($actionGroup, 'actionGroup', get_string('chooseoption', 'local_attendance'), array(' '), false);
		
		$sessionsTable = tables::getAssistDateDetail($courseid, $sessionid);
		$mform->addElement('html', html_writer::table($sessionsTable));

		$mform->addElement('hidden','sessionid',$sessionid);
		$mform->setType('sessionid',PARAM_INT);
		$mform->addElement('hidden','courseid',$courseid);
		$mform->setType('courseid',PARAM_INT);
		$mform->addElement('hidden','action','modify_session_attendance');
		$mform->setType('action',PARAM_TEXT);
	}
	function validation($data,$files) {

	}
	
}

class openSession extends moodleform{
	function definition() {
		global $DB,$OUTPUT,$courseid;
		$mform =& $this->_form;
		$durationOptions = array_combine(range(5,40,5),range(5,40,5));
		$mform->addElement('text','comment',get_string('sessionname', 'local_attendance'));
		$mform->setType('comment',PARAM_TEXT);
		
		$durationGroup = array();
		$durationGroup[] =& $mform->createElement('select','duration','',$durationOptions);
		$durationGroup[] =& $mform->createElement('static','minutes',get_string('minutes', 'local_attendance'),' '.get_string('minutes', 'local_attendance'));
		$mform->addGroup($durationGroup, 'durationGroup', get_string('duration', 'local_attendance'), array(' '), false);
		$mform->setType('duration',PARAM_INT);

		$mform->addElement('submit', 'submitbutton', get_string('opensession', 'local_attendance'));
		$mform->addElement('hidden','courseid',$courseid);
		$mform->setType('courseid',PARAM_INT);
		$mform->addElement('hidden','action','open_session');
		$mform->setType('action',PARAM_TEXT);

	}
	function validation($data,$files) {
		$errors = array();
	}

}

class webapp extends moodleform{
	function definition(){
		global $DB, $CFG, $OUTPUT;

		$mform =& $this->_form;

		$mform->addElement('text', 'username', 'Usuario');
		$mform->setType('username', PARAM_TEXT);
		$mform->addElement('password', 'password', 'Clave');
		$mform->setType('password', PARAM_ALPHANUM);
		$mform->addElement('html', '<input type="submit" name="entrar" value="Entrar">');
	}
	function validation($data, $files){
		global $DB;
		$errors = array();
		return $errors;
	}
}
