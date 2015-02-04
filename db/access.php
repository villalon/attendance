<?php
$capabilities = array('local/attendance:teacherview' => array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_COURSE,
				'legacy' => array(
					'student'=>CAP_PROHIBIT,
					'teacher' => CAP_ALLOW,
					'editingteacher' => CAP_ALLOW
		)));
?>