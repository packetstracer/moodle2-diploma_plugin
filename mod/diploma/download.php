<?php

define('READFILE_CHUNK_SIZE', 1048576);  // 1MB
define('READFILE_TIME_LIMIT', 3600);     // 1 hour


/**
 * Send pdf file to requester.
 *
 * @return void
 */
function response_file($filename, $path) {
	$lastmodified  = filemtime($path);
	$filesize      = filesize($path);

	if (($info === false) || ($filesize == 0)) {
		show_404($filename);
	}

	header('Last-Modified: '. gmdate('D, d M Y H:i:s', $lastmodified) .' GMT');
	header('Content-Type: application/pdf');
	header('Content-Disposition: attachment; filename=' . $filename);
	header('Content-Length: '.$filesize);
	header('Cache-Control: max-age=604800, public');

	sendfile_chunked($path);
}


/**
 * Send a file reading and send it in chunked blocks
 *
 * @return integer
 */
function sendfile_chunked($filename, $retbytes = true) {
	if (empty($filename)) {
		return false;
	}

	$buffer = '';
	$cnt = 0;
	$handle = fopen($filename, 'rb');
	if ($handle === false) {
		return false;
	}

	// clean buffer to avoid problems
	ob_clean();
	flush();

	while (!feof($handle)) {
		@set_time_limit(READFILE_TIME_LIMIT); //reset time limit to 60 min - should be enough for 1 MB chunk
		$buffer = fread($handle, READFILE_CHUNK_SIZE);
		echo $buffer;
		flush();
		if ($retbytes) {
			$cnt += strlen($buffer);
		}
	}
	$status = fclose($handle);
	if ($retbytes && $status) {
		return $cnt; // return num. bytes delivered like readfile() does.
	}
	return $status;
}


/**
 * Shows a not found error.
 * @param string $filename : filename accessed
 */
function show_404($filename) {
	print_error($filename .'diploma file does not exist');
	//header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
}


require_once("../../config.php");
require_once("$CFG->dirroot/mod/diploma/lib.php");


$user_id 	= required_param('userid', PARAM_INT);
$diploma_id = required_param('diplomaid', PARAM_INT);

require_login();

if (!$diploma = $DB->get_record('diploma', array('id' => $diploma_id))) {
	print_error('diploma module is incorrect');
}

if (!$diploma_issue = $DB->get_record(
						'diploma_issues',
						array('diplomaid' => $diploma_id, 'userid' => $user_id)
					)) {
	print_error('diploma issue is incorrect');
}


$cm = get_coursemodule_from_instance('diploma', $diploma->id);

// course or activity module has been deleted: manager can download diplomas
if (!$cm) {
	$context = context_system::instance();
	require_capability('mod/diploma:manageall', $context);
}
// course or activity exists
else {
	$context = context_module::instance($cm->id);

	if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
		print_error('course is misconfigured');
	}

	// logged user is the same as diploma user: need view capabilities on the module
	if ($USER->id == $user_id) {
		require_capability('mod/diploma:view', $context);
	}
	// logged user is different from diploma user: need manage capabilites on the module
	else {
		require_capability('mod/diploma:manage', $context);
	}
}


// send file to user
response_file(
		diploma_get_pdf_filename($diploma_issue),
		diploma_get_pdf_path($diploma_issue)
);



