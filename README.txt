------------------------------------------
attendance for Moodle 2.8.3+ or more
Version: 1.0.0
------------------------------------------

Author: Juan Pablo Baltra
Developers: Juan Pablo Baltra
            Martin Amestica Montenegro

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
 
	- Is needed the UAI block to see the pluggin in the home page, 
	in this point we have to visualizations, first that the teacher saw and this
	is the link to create a new session or close old sessions and the posibility of
	see the records of other sessions, and the second visualization is
	the student vision and in this way he see the option to check his attendance.
	
- Capabilities types: 

	* teacherview - teacher can be see the option to create sessions.

- Files contain in this pluggin:

*/local/attendance/attendance.php - create a new session of attendance
*/local/attendance/forms.php -  file containing all the forms used
*/local/attendance/lib.php - file that contains lib
*/local/attendance/markattendance.php - check the attendance
*/local/attendance/studentrecord.php
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
*/local/attendance/webapp/student/index.php
*/local/attendance/webapp/student/markAttendance.php
*/local/attendance/webapp/teacher/index.php
*/local/attendance/webapp/teacher/markAttendance.php


	