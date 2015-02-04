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
if (isset($_GET["c"])){unset($_SESSION["user_webapp"]);}
if (isset($_SESSION["user_webapp"])){header("Location: markAttendance.php");}
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
<div id="header"><img src="https://webcursos.uai.cl/pluginfile.php/1/theme_essential/logo/1421344949/nuevo-logo-wcurso_transp.png" /></div>
<h1><?php echo get_string('markattendance', 'local_attendance');?></h1>
<form action="#" id="studentLogin" class="login">
<p><?php echo get_string('username', 'local_attendance');?></p>
<input type="text" /><br />
<p><?php echo get_string('password', 'local_attendance');?></p>
<input type="password" /><br />
<input type="submit" value="Entrar" />
<p id="error"></p>
</form>
</div>
</body>
<div id="loading"><img src="../scripts/images/loading.gif" /></div>
<script>
allfields = '<?php echo get_string('allfields', 'local_attendance');?>';
</script>
</html>
