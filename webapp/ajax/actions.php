<?php
require_once(dirname(__FILE__) . '/../../../../config.php'); 
global $PAGE, $CFG, $OUTPUT, $DB, $COURSE, $USER;

$action = required_param ( 'action', PARAM_TEXT );

if ($action == "studentLogin"){
	$username = required_param ( 'user', PARAM_ALPHANUMEXT );
	$password = required_param ( 'pass', PARAM_RAW_TRIMMED );
	if (!($username && $password)){
		echo get_string('allfields', 'local_attendance');
	}
	elseif (! $user = authenticate_user_login ( $username, $password )){
		echo get_string('invalidlogin', 'local_attendance');
	}
	else{
		$_SESSION['user_webapp']=$user;
	}
}

if ($action == "markAttendance"){
$user = $_SESSION['user_webapp'];

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {$ip = $_SERVER['HTTP_CLIENT_IP'];} 
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];} 
else {$ip = $_SERVER['REMOTE_ADDR'];}

$courseid = required_param('courseid',PARAM_INT);
$session = $DB->get_record_sql('SELECT s.id,c.fullname FROM {user} u INNER JOIN {user_enrolments} ue ON (ue.userid = u.id) INNER JOIN {enrol} e ON (e.id = ue.enrolid) INNER JOIN {course} c ON (e.courseid = c.id) INNER JOIN {local_attendance_session} s ON (c.id=s.courseid) WHERE ue.userid = '.$user->id.' AND s.open=1 AND c.id='.$courseid);
$attendanceExists = $DB->record_exists("local_attendance_attendance",array('sessionid'=>$session->id,'userid'=>$user->id));
if (!$attendanceExists){
	$DB->insert_record('local_attendance_attendance',array('sessionid'=>$session->id,'userid'=>$user->id,'ip'=>$ip));
}
else{
	echo get_string('alreadyregistered', 'local_attendance');;	
}
}

if ($action == "teacherLogin"){
	$username = required_param ( 'user', PARAM_ALPHANUMEXT );
	$password = required_param ( 'pass', PARAM_RAW_TRIMMED );
	if (!($username && $password)){
		echo get_string('allfields', 'local_attendance');
	}
	elseif (! $user = authenticate_user_login ( $username, $password )){
		echo get_string('invalidlogin', 'local_attendance');
	}
	else{
		$userCourses = enrol_get_users_courses($user->id);
		$n = 0;		
		foreach($userCourses as $course){
			$courseContext = context_course::instance($course->id);
			if(has_capability('local/attendance:teacherview', $courseContext, $user->id)){$n++;}
		}
		
		if ($n>0){
			$_SESSION['teacher_webapp']=$user;
		}
		else{
			echo 'No eres profesor de ningun ramo';
		}
	}
}


?>