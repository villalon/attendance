------------------------------------------
attendance for Moodle 2.8.3+ or more
Version: 1.0.0
------------------------------------------

Author: Martin Amestica 
Developers: Juan Pablo Baltra
            Dario Pfeng            

------------------------------------------

- Introduction

Attendance is a tool to check the attendance in a session class.
With this tool the teacher has the posibility of create a time 
session in one moment in the class and the students can be check 
their atttendance whit their smatsphones. The session has a 
period of time aviable determinated for the theacher and after 
this time the session expired and save the attendance that
studients check.

the attendance you can see in two ways, firt you had a list of 
sessions, like a history or in the other way you can see the 
list of studiens in one session.

- Installation:

1.- First is needed Moodle, version 2.8.3+ or more in your sistem.
2.- Then the folder 'attendance' should be copied to /local/ directory in moodle.
3.- Now is needed that login like admin and available plugins are display,
 press "update database now moodle". if the installation is done the pluggin was installed.
 
   
- Settings:

	- Create necessary permissions (are explained in “capabilities types”): 
		site administration->user->permissions->define roles.
 
	- Is needed a block tu use the pluggin in the correct way.
	First is necessary create the configurations of this pluggin in the block, for this 
	you create a function with pluggin name and define the navigation nodes in the block.
	
	for example:
	
		function attendance() {
			global $COURSE, $CFG, $PAGE, $USER, $DB;

			return;
			$context = context_course::instance($COURSE->id);
			$rootnode = navigation_node::create(get_string('attendances', 'local_attendance'));
		
	- Then is necessary config the nodes according the course id, the home page have courseid 1 
	and other course have  id iqual 2 or more.
	
	
	for example:
	
		if($COURSE->id == 1){
			$nodemark = navigation_node::create(
					get_string('markattendance', 'local_attendance'),
					new moodle_url("/local/attendance/markattendance.php",array("courseid"=>$COURSE->id)),
					navigation_node::TYPE_CUSTOM, null, null,
					new pix_icon('i/report', 'markattendance'));
			
			$rootnode->add_node($nodemark);
}

     - Links for specific rol and course:

		Home page:
	
			Teacher/Student: new moodle_url("/local/attendance/markattendance.php",array("courseid"=>$COURSE->id))
	
		Course page:is necesary to config the capabilities restrictions
	
			example: if(has_capability('local/attendance:teacherview', $context))
	
		Now if the user has the capability he will be have this links:
	
			* create attendance session: new moodle_url("/local/attendance/attendance.php",array("courseid"=>$COURSE->id))
			* view students records: new moodle_url("/local/attendance/viewstudentrecord.php",array("courseid"=>$COURSE->id))
	
		else:
	
			*student mark attendence: new moodle_url("/local/attendance/studentrecord.php",array("courseid"=>$COURSE->id))
	

	  - Finally is nedded to add the nodes to the page for see the attendance functions in the block.
	
	
		for example:
	
	 		if($nodeattendance = $this->attendance())
				$root->add_node($nodeattandance);
			
			
			
			
- Capabilities types: 

	* teacherview - teacher can be see the option to create sessions.

- Files contain in this pluggin:

*/local/attendance/attendance.php - create a new session of attendance
*/local/attendance/forms.php -  file containing all the forms used
*/local/attendance/lib.php - file that contains lib
*/local/attendance/markattendance.php - check the attendance
*/local/attendance/studentrecord.php - attendance student record
*/local/attendance/tables.php - file that contains tables
*/local/attendance/version.php - version of script (must be incremented after changes)
*/local/attendance/viewsessionrecord.php - see all sessions created
*/local/attendance/viewstudentrecord.php - see all students attendance in one session
*/local/attendance/db/access.php - definition of capabilities
*/local/attendance/db/install.xml - executed during install (new version.php found)
*/local/attendance/db/messages.php - messaging registration
*/local/attendance/db/upgrade.php - executed after version.php change
*/local/attendance/lang/en/local_attendance.php - english language file
*/local/attendance/lang/es/local_attendance.php - spanish language file
*/local/attendance/webapp/student/index.php - index page
*/local/attendance/webapp/student/markAttendance.php - student web version
*/local/attendance/webapp/teacher/index.php - index page
*/local/attendance/webapp/teacher/markAttendance.php - teacher web version


	