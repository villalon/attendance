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

require_once(dirname(__FILE__) . '/../../../../config.php'); 
global $PAGE, $CFG, $OUTPUT, $DB, $COURSE, $USER;
if (!isset($_SESSION['teacher_webapp'])){header("Location: index.php");}
$user = $_SESSION['teacher_webapp'];
$action = optional_param('action','startpage',PARAM_TEXT);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo get_string('checkattendance', 'local_attendance');?></title>
<link href="../scripts/style.css" rel="stylesheet" type="text/css" />
<script src="../scripts/jquery.js"></script>
<script src="../scripts/java.js"></script>
</head>
<body>
<div id="body">
<div id="header"><img src="https://webcursos.uai.cl/pluginfile.php/1/theme_essential/logo/1421344949/nuevo-logo-wcurso_transp.png" />
<p><?php echo $user->firstname.' '.$user->lastname;?>
<a href="index.php?c"><button class="header_back">Salir</button></a>
</p>
</div>
<h1><?php echo get_string('checkattendance', 'local_attendance');?></h1>
<?php
/////////////// BEGIN CLOSE SESSION ////////////////
if ($action=='close_session'){
	$courseid = required_param('courseid', PARAM_INT);
	$session = $DB->get_record('local_attendance_session',array('courseid'=>$courseid,'open'=>1));
	if (!empty($session)){
		$DB->update_record('local_attendance_session',array("id"=>$session->id,"open"=>0));
	echo '<div class="info">'.get_string('sessionclosed', 'local_attendance').'</div>';
	}
	$action = 'startpage';
}
/////////////// END CLOSE SESSION ////////////////
/////////////// BEGIN CONTINUE SESSION ////////////////
if ($action=='continue_session'){
	$courseid = required_param('courseid', PARAM_INT);
	$session = $DB->get_record('local_attendance_session',array('courseid'=>$courseid,'open'=>1));
	if (!empty($session)){
		$action = 'check_attendance';
	}
	else{
		$action = 'startpage';
	}
}
/////////////// END CONTINUE SESSION ////////////////
/////////////// BEGIN OPEN SESSION ////////////////
if ($action == 'open_session'){
	$courseid = required_param('courseid', PARAM_INT);
	$sessionName = required_param('sessionName', PARAM_TEXT);
	$duration = required_param('duration', PARAM_INT);
	$session = $DB->get_record('local_attendance_session',array('courseid'=>$courseid,'open'=>1));
	if (empty($session)){
	$DB->insert_record('local_attendance_session',array('courseid'=>$courseid,'date'=>time(),'modifierid'=>$user->id,'open'=>1,'comment'=>$sessionName,'duration'=>$duration));
	$action = 'check_attendance';
	}
	else{
	$action = 'startpage';
	}
}
/////////////// END OPEN SESSION ////////////////
/////////////// BEGIN CHECK ATTENDANCE ////////////////
if ($action=='check_attendance'){
echo '
<h2 id="sessionStatus">'.get_string('waitingfor', 'local_attendance') .' <span>. . . </span></h2>
<div class="info">'.get_string('knowledgemessage', 'local_attendance').' <span id="remainingTime"></span> '.get_string('minutes', 'local_attendance').'</div>
<form action="checkAttendance.php" method="post" class="close_open_session"><input type="hidden" name="action" value="close_session"><input type="hidden" name="courseid" value="'.$courseid.'"><input type="submit" value="'.get_string('closesession', 'local_attendance').'"></form>

<div id="lists">
<a href="#" id="attended" class="shadow"></a>
<a href="#" id="all" class="shadow"></a>
<a href="#" id="absent" class="shadow"></a>
</div>

<div id="list_title">
<div id="title_attended">'.get_string('attendant','local_attendance').': <img src="../scripts/images/attended_arrow.png" /> <span id="numberAttended">0</span></div>
<div id="title_all">Todos<img src="../scripts/images/all_arrow.png" /> ('.get_string('attendance','local_attendance').' <span id="percentage">0</span>%)</div>
<div id="title_absent">'.get_string('absent','local_attendance').'<img src="../scripts/images/absent_arrow.png" /></div>
</div>

<div id="list">';
$students = $DB->get_records_sql('SELECT u.id, u.firstname, u.lastname FROM {user} as u 
INNER JOIN {user_enrolments} as ue ON (ue.userid = u.id) 
INNER JOIN {enrol} as e ON (e.id = ue.enrolid) 
INNER JOIN {course} as c ON (e.courseid = c.id) 
WHERE c.id = '.$courseid.' 
AND u.id IN (SELECT u.id FROM {course} c LEFT OUTER JOIN {context} cx ON c.id = cx.instanceid LEFT OUTER JOIN {role_assignments} ra ON cx.id = ra.contextid AND ra.roleid = "5" LEFT OUTER JOIN {user} u ON ra.userid = u.id WHERE cx.contextlevel = "50" and c.id = '.$courseid.') 
ORDER BY u.lastname'
);

foreach ($students as $student){
echo '<p id="'.$student->id.'">'.$student->firstname.' '.$student->lastname.'</p>';
}
echo '</div>';
echo '<script>var courseid = '.$courseid.';</script>';
}

/////////////// END CHECK ATTENDANCE ////////////////
/////////////// BEGIN STARTPAGE ////////////////
if ($action == 'startpage'){
$userCourses = enrol_get_users_courses($user->id);
$hasOpenSessions = false;
foreach($userCourses as $course){
	$isOpen = $DB->record_exists('local_attendance_session',array('courseid'=>$course->id,'open'=>1));
	if ($isOpen){$session = $DB->get_record('local_attendance_session',array('courseid'=>$course->id,'open'=>1));}
	$hasOpenSessions = $hasOpenSessions || $isOpen;
}

if (!$hasOpenSessions){
	echo '
	<form action="checkAttendance.php" method="post" id="newSession">
	<p>'.get_string('course', 'local_attendance').'</p> <select name="courseid">';
	foreach($userCourses as $course){
		$courseContext = context_course::instance($course->id);
		if(has_capability('local/attendance:teacherview', $courseContext, $user->id)){echo '<option value="'.$course->id.'">'.$course->fullname.'</option>';}
	}
	echo '
	</select>
	<p>'.get_string('sessionname','local_attendance').'</p> <input type="text" name="sessionName">
	<p>'.get_string('duration','local_attendance').'</p> 
	<select name="duration">
	<option value="5">5 '.get_string('minutes','local_attendance').'</option>
	<option value="10">10 '.get_string('minutes','local_attendance').'</option>
	<option value="15">15 '.get_string('minutes','local_attendance').'</option>
	<option value="20">20 '.get_string('minutes','local_attendance').'</option>
	<option value="25">25 '.get_string('minutes','local_attendance').'</option>
	<option value="30">30 '.get_string('minutes','local_attendance').'</option>
	<option value="35">35 '.get_string('minutes','local_attendance').'</option>
	<option value="40">40 '.get_string('minutes','local_attendance').'</option>
	</select>
	<input type="hidden" name="action" value="open_session">
	<input type="submit" value="'.get_string('opensession','local_attendance').'">
	</form>';
}
else{
	$course = $DB->get_record('course',array('id'=>$session->courseid));
	echo '<h2>'.get_string('alreadyopen', 'local_attendance').'</h2>';
	echo '<div id="openSession">
	<strong>'.$course->fullname.'</strong>, '.date('d/m/Y',$session->date).'<br />'.
	get_string('sessionname','local_attendance').': '.$session->comment.'	
	<form action="checkAttendance.php" method="post" class="continue"><input type="hidden" name="action" value="continue_session"><input type="hidden" name="courseid" value="'.$session->courseid.'"><input type="submit" value="'.get_string('continue', 'local_attendance').'"></form>
	<form action="checkAttendance.php" method="post" class="close"><input type="hidden" name="action" value="close_session"><input type="hidden" name="courseid" value="'.$session->courseid.'"><input type="submit" value="'.get_string('close', 'local_attendance').'"></form>
	</div>';
}
}
/////////////// END STARTPAGE ////////////////

?>
<p id="error"></p>
</div>
</body>
<div id="loading"><img src="../scripts/images/loading.gif" /></div>
<script>
allfields = '<?php echo get_string('allfields', 'local_attendance');?>';
</script>
</html>