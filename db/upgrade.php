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
 * This file keeps track of upgrades to the asistencias block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since 2.0
 * @package blocks
 * @copyright 2015 Juan Pablo Baltra
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 */


function xmldb_local_attendance_upgrade($oldversion) {
	global $CFG, $DB;

	$dbman = $DB->get_manager();
	if ($oldversion < 2015011203) {
	
		// Define table local_attendance_attendance to be created.
		$table = new xmldb_table('local_attendance_attendance');
	
		// Adding fields to table local_attendance_attendance.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('sessionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('comment', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
	
		// Adding keys to table local_attendance_attendance.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('fk_sessionid', XMLDB_KEY_FOREIGN, array('sessionid'), 'local_attendance_session', array('id'));
		$table->add_key('fk_userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
	
		// Conditionally launch create table for local_attendance_attendance.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
	   // Define table local_attendance_session to be created.
        $table = new xmldb_table('local_attendance_session');

        // Adding fields to table local_attendance_session.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('open', XMLDB_TYPE_INTEGER, '1', null, null, null, null);

        // Adding keys to table local_attendance_session.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

        // Conditionally launch create table for local_attendance_session.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Attendance savepoint reached.
        upgrade_plugin_savepoint(true, 2015011203, 'local', 'attendance');
    }
    //creacion de los campos comment y time
    if ($oldversion < 2015011600) {
    
    	// Define field comment to be added to local_attendance_session.
    	$table = new xmldb_table('local_attendance_session');
    	$field = new xmldb_field('comment', XMLDB_TYPE_CHAR, '1000', null, null, null, null, 'open');
    
    	// Conditionally launch add field comment.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    	
    	// Define field duration to be added to local_attendance_session.
    	$table = new xmldb_table('local_attendance_session');
    	$field = new xmldb_field('duration', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '30', 'comment');
    	
    	// Conditionally launch add field duration.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    	
    	// Attendance savepoint reached.
    	upgrade_plugin_savepoint(true, 2015011600, 'local', 'attendance');
    }
    
    if ($oldversion < 2015011602) {

        // Define field modifierid to be added to local_attendance_session.
        $table = new xmldb_table('local_attendance_session');
        $field = new xmldb_field('modifierid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'id');

        // Conditionally launch add field modifierid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Attendance savepoint reached.
        upgrade_plugin_savepoint(true, 2015011602, 'local', 'attendance');
    }
    
    if ($oldversion < 2015012202) {
    
    	// Define field ip to be added to local_attendance_attendance.
    	$table = new xmldb_table('local_attendance_attendance');
    	$field = new xmldb_field('ip', XMLDB_TYPE_CHAR, '250', null, null, null, null, 'comment');
    
    	// Conditionally launch add field ip.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Attendance savepoint reached.
    	upgrade_plugin_savepoint(true, 2015012202, 'local', 'attendance');
    }
     
	
	return true;
}