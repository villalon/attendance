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
if (!isset($_SESSION['user_webapp'])){header("Location: index.php");}
$user = $_SESSION['user_webapp'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo get_string('markattendance', 'local_attendance');?></title>
<link href="../scripts/style.css" rel="stylesheet" type="text/css" />
<script src="../scripts/jquery.js"></script>
<script src="../scripts/java.js"></script>
</head>
<body>
<div id="body">
<div id="header"><img src="https://webcursos.uai.cl/pluginfile.php/1/theme_essential/logo/1421344949/nuevo-logo-wcurso_transp.png" />
<p><?php echo $user->firstname.' '.$user->lastname;?></p>
</div>
<h1><?php echo get_string('markattendance', 'local_attendance');?></h1>
<?php
$sessions = $DB->get_records_sql('SELECT c.id,c.fullname,u.id as userid,s.id as sessionid FROM {user} u INNER JOIN {user_enrolments} ue ON (ue.userid = u.id) INNER JOIN {enrol} e ON (e.id = ue.enrolid) INNER JOIN {course} c ON (e.courseid = c.id) INNER JOIN {local_attendance_session} s ON (c.id=s.courseid) WHERE ue.userid = '.$user->id.' AND s.open=1');

if (empty($sessions)){
	echo '<h2>'.get_string('nosessions', 'local_attendance').'</h2>';
}
else{
	$n = 0;
	foreach($sessions as $session){
		$sessionContext = context_course::instance($session->id);
		if(!has_capability('local/attendance:teacherview', $sessionContext, $user->id)){
			$n++;
		}
	}
	if($n > 0){
	echo '<h2>'.get_string('followingopensessions', 'local_attendance').'</h2>';
	echo '<div id="openSessions">';
	foreach ($sessions as $session){
	if(!has_capability('local/attendance:teacherview', $sessionContext, $user->id)){
	$attended = $DB->record_exists('local_attendance_attendance',array('sessionid'=>$session->sessionid,'userid'=>$session->userid));
	$button = ($attended) ? '<span>OK <img src="../scripts/images/ok.png"></span>' : '<a href="#">'.get_string('markattendance', 'local_attendance').' <img src="../scripts/images/check.png"></a>';
	echo '<div class="openSession" id="'.$session->id.'">'.$session->fullname . $button . '</div>';	
	}
	}
	echo '</div>';
	}else{
		echo '<h2>'.get_string('nosessions', 'local_attendance').'</h2>';
	}
}

?>
<p id="error"></p>
<a href="index.php?c"><button class="back"><?php echo get_string('exit', 'local_attendance');?></button></a>
</div>
</body>
<div id="loading"><img src="../scripts/images/loading.gif" /></div>
<script>
allfields = '<?php echo get_string('allfields', 'local_attendance');?>';
</script>
</html>