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

define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/moodlelib.php');      // moodle lib functions
require_once($CFG->libdir.'/datalib.php');      // data lib functions
require_once($CFG->libdir.'/accesslib.php');      // access lib functions
require_once($CFG->dirroot.'/course/lib.php');      // course lib functions
require_once($CFG->dirroot.'/enrol/guest/lib.php');      // guest enrol lib functions

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Closes sessions when its duration time expires

Options:
-h, --help            Print out this help

Example:
\$sudo -u apache /usr/bin/php /local/attendance/cli/closeSessions.php
";

    echo $help;
    die;
}

cli_heading('Closing sessions');

$time=time();

echo "\nStarting at ".date("F j, Y, G:i:s")."\n";

$sessionsToClose = $DB->get_records_sql('SELECT id FROM {local_attendance_session} WHERE CEIL(duration-('.$time.'-date)/60)<=0 AND open=1');

foreach ($sessionsToClose as $session){
	$DB->update_record('local_attendance_session',array("id"=>$session->id,"open"=>0));
}

echo "\n".$k." sessions closed \n";
echo "ok\n";
$timenow=time();
$execute=$time - $timenow;
echo "\nExecute time ".$execute." sec\n";	


exit(0); // 0 means success
