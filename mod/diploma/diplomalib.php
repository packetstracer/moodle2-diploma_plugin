<?php

class simple
{
    const CERTIFICATE_IMAGE_FILE_AREA = 'image';
    const CERTIFICATE_ISSUES_FILE_AREA = 'issues';
    const CERTIFICATE_COMPONENT_NAME = 'mod_diploma';
    const OUTPUT_OPEN_IN_BROWSER = 0;
    const OUTPUT_FORCE_DOWNLOAD = 1;
    const OUTPUT_SEND_EMAIL = 2;

    //View const
    const  DEFAULT_VIEW = 0;
    const  ISSUED_CERTIFCADES_VIEW = 1;
    const  BULK_ISSUE_CERTIFCADES_VIEW = 2;

    //pagination
    const SIMPLECERT_MAX_PER_PAGE = 200;

    //Temporary moodledata directory for zipping files
	const SIMPLECERT_ZIP_TEMP_FOLDER = 'mod_diploma/zip/';


    public static function view_issued_diplomas($diploma, $cm, $course, $context, moodle_url $url) {
    	global $OUTPUT, $DB, $CFG;

    	// Declare some variables
    	$strcertificates = get_string('modulenameplural', 'diploma');
    	$strcertificate  = get_string('modulename', 'diploma');
    	$strto = get_string('awardedto', 'diploma');
    	$strdate = get_string('receiveddate', 'diploma');
    	$strgrade = get_string('grade','diploma');
    	$strcode = get_string('code', 'diploma');
    	$strreport= get_string('report', 'diploma');
    	$groupmode = groups_get_activity_groupmode($cm);
    	$page = $url->get_param('page');
    	$perpage = $url->get_param('perpage');

    	$users = self::get_issued_diploma_users($diploma, $cm, $context, $DB->sql_fullname(), $groupmode, $page, $perpage);


    	if (!$url->get_param('action')) {
    		echo $OUTPUT->header();
    		self::show_tabs($url);

    		if ($groupmode) {
    			groups_get_activity_group($cm, true);
    		}

    		groups_print_activity_menu($cm, $url);

    		if (!$users) {
    			notify(get_string('nocertificatesissued', 'diploma'));
    			echo $OUTPUT->footer($course);
    			exit();
    		}

    		$usercount = count($users);

    		// Create the table for the users
    		$table = new html_table();
    		$table->width = "95%";
    		$table->tablealign = "center";
    		$table->head  = array($strto, $strdate, $strgrade, $strcode);
    		$table->align = array("left", "left", "center", "center");

    		foreach ($users as $user) {
    			$name = $OUTPUT->user_picture($user) . fullname($user);
    			$date = userdate($user->timecreated) . self::print_issue_diploma_file(diploma_get_issue($course, $user, $diploma, $cm), $context);
    			$code = $user->code;
    			$table->data[] = array ($name, $date, diploma_get_grade($diploma, $course, $user->id), $code);
    		}

    		// Create table to store buttons
    		$tablebutton = new html_table();
    		$tablebutton->attributes['class'] = 'downloadreport';
    		$btndownloadods = $OUTPUT->single_button($url->out_as_local_url(false, array('action'=>'download', 'type'=>'ods')), get_string("downloadods"));
    		$btndownloadxls = $OUTPUT->single_button($url->out_as_local_url(false, array('action'=>'download', 'type'=>'xls')), get_string("downloadexcel"));
    		$btndownloadtxt = $OUTPUT->single_button($url->out_as_local_url(false, array('action'=>'download', 'type'=>'txt')), get_string("downloadtext"));
    		$tablebutton->data[] = array($btndownloadods, $btndownloadxls, $btndownloadtxt);

    		//echo $OUTPUT->heading(get_string('modulenameplural', 'diploma'));
    		echo $OUTPUT->paging_bar($usercount, $page, $perpage, $url);
    		echo '<br />';
    		echo html_writer::table($table);
    		echo html_writer::tag('div', html_writer::table($tablebutton), array('style' => 'margin:auto; width:50%'));
    	}
    	else if ($url->get_param('action') == 'download') {
    		$page = $perpage = 0;
    		$type = $url->get_param('type');

    		// Calculate file name
    		$course_name = (!empty($course->shortname)) ?
    							strip_tags(format_string($course->shortname .'-')) :
    							''
    		;
    		$filename = clean_filename($course_name .'-'. strip_tags(format_string($diploma->name, true)). '.' .strip_tags(format_string($type, true)));

    		switch ($type) {
    		    case 'ods':
    		    	require_once("$CFG->libdir/odslib.class.php");

    		    	// Creating a workbook
    		    	$workbook = new MoodleODSWorkbook("-");
    		    	// Send HTTP headers
    		    	$workbook->send($filename);
    		    	// Creating the first worksheet
    		    	$myxls = $workbook->add_worksheet($strreport);

    		    	// Print names of all the fields
    		    	$myxls->write_string(0, 0, get_string("fullname"));
    		    	$myxls->write_string(0, 1, get_string("idnumber"));
    		    	$myxls->write_string(0, 2, get_string("group"));
    		    	$myxls->write_string(0, 3, $strdate);
    		    	$myxls->write_string(0, 4, $strgrade);
    		    	$myxls->write_string(0, 5, $strcode);

    		    	// Generate the data for the body of the spreadsheet
    		    	$i = 0;
    		    	$row = 1;

    		    	if ($users) {
    		    		foreach ($users as $user) {
    		    			$myxls->write_string($row, 0, fullname($user));
    		    			$studentid = (!empty($user->idnumber)) ? $user->idnumber : " ";
    		    			$myxls->write_string($row, 1, $studentid);
    		    			$ug2 = '';

    		    			if ($usergrps = groups_get_all_groups($diploma->course, $user->id)) {
    		    				foreach ($usergrps as $ug) {
    		    					$ug2 = $ug2. $ug->name;
    		    				}
    		    			}

    		    			$myxls->write_string($row, 2, $ug2);
    		    			$myxls->write_string($row, 3, userdate($user->timecreated));
    		    			$myxls->write_string($row, 4, diploma_get_grade($diploma, $course, $user->id));
    		    			$myxls->write_string($row, 5, $user->code);
    		    			$row++;
    		    		}

    		    		$pos = 5;
    		    	}

    		    	// Close the workbook
    		    	$workbook->close();
    		    break;

    		    case 'xls':
    		    	require_once("$CFG->libdir/excellib.class.php");

    		    	// Creating a workbook
    		    	$workbook = new MoodleExcelWorkbook("-");
    		    	// Send HTTP headers
    		    	$workbook->send($filename);
    		    	// Creating the first worksheet
    		    	$myxls = $workbook->add_worksheet($strreport);

    		    	// Print names of all the fields
    		    	$myxls->write_string(0, 0, get_string("fullname"));
    		    	$myxls->write_string(0, 1, get_string("idnumber"));
    		    	$myxls->write_string(0, 2, get_string("group"));
    		    	$myxls->write_string(0, 3, $strdate);
    		    	$myxls->write_string(0, 4, $strgrade);
    		    	$myxls->write_string(0, 5, $strcode);

    		    	// Generate the data for the body of the spreadsheet
    		    	$i = 0;
    		    	$row = 1;

    		    	if ($users) {
    		    		foreach ($users as $user) {
    		    			$myxls->write_string($row, 0, fullname($user));
    		    			$studentid = (!empty($user->idnumber)) ? $user->idnumber : " ";
    		    			$myxls->write_string($row, 1, $studentid);
    		    			$ug2 = '';

    		    			if ($usergrps = groups_get_all_groups($diploma->course, $user->id)) {
    		    				foreach ($usergrps as $ug) {
    		    					$ug2 = $ug2 . $ug->name;
    		    				}
    		    			}

    		    			$myxls->write_string($row, 2, $ug2);
    		    			$myxls->write_string($row, 3, userdate($user->timecreated));
    		    			$myxls->write_string($row, 4, diploma_get_grade($diploma, $course, $user->id));
    		    			$myxls->write_string($row, 5, $user->code);
    		    			$row++;
    		    		}

    		    		$pos = 5;
    		    	}

    		    	// Close the workbook
    		    	$workbook->close();
    		    break;

    		    case 'txt':
    		    	header("Content-Type: application/download\n");
    		    	header("Content-Disposition: attachment; filename=\"$filename\"");
    		    	header("Expires: 0");
    		    	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    		    	header("Pragma: public");

    		    	// Print names of all the fields
    		    	echo get_string("fullname"). "\t" . get_string("idnumber") . "\t";
    		    	echo get_string("group"). "\t";
    		    	echo $strdate. "\t";
    		    	echo $strgrade. "\t";
    		    	echo $strcode. "\n";

    		    	// Generate the data for the body of the spreadsheet
    		    	$i=0;
    		    	$row=1;

    		    	if ($users) foreach ($users as $user) {
    		    		echo fullname($user);
    		    		$studentid = " ";

    		    		if (!empty($user->idnumber)) {
    		    			$studentid = $user->idnumber;
    		    		}

    		    		echo "\t" . $studentid . "\t";
    		    		$ug2 = '';

    		    		if ($usergrps = groups_get_all_groups($diploma->course, $user->id)) {
    		    			foreach ($usergrps as $ug) {
    		    				$ug2 = $ug2. $ug->name;
    		    			}
    		    		}

    		    		echo $ug2 . "\t";
    		    		echo userdate($user->timecreated) . "\t";
    		    		echo diploma_get_grade($diploma, $course, $user->id). "\t";
    		    		echo $user->code . "\n";
    		    		$row++;
    		    	}
    		    break;
    		}

    		exit;
    	}

    	echo $OUTPUT->footer($course);
    }


    public static function view_bulk_diplomas($diploma, $cm, $course, moodle_url $url, array $selectedusers = null) {
    	global $OUTPUT, $CFG, $DB;

    	$course_context = context_course::instance($diploma->course);

    	$page 		= $url->get_param('page');
    	$perpage 	= $url->get_param('perpage');
    	//$issuelist 	= $url->get_param('issuelist');
    	$issuelist  = 'completed';
    	$action 	= $url->get_param('action');
    	$groupid 	= 0;
    	$groupmode 	= groups_get_activity_groupmode($cm);

    	if ($groupmode) {
    		$groupid = groups_get_activity_group($cm, true);
    	}

    	if (!$selectedusers) {
    		$users = get_enrolled_users($course_context, '', $groupid);
    	}
    	else {
    		list($sqluserids, $params) = $DB->get_in_or_equal($selectedusers);

    		$sql = "SELECT * FROM {user} WHERE id $sqluserids";
    		$users = $DB->get_records_sql($sql, $params);
    	}

    	if (!$action) {
    		$usercount = count($users);
    		echo $OUTPUT->header();
    		self::show_tabs($url);

    		groups_print_activity_menu($cm, $url);

    		//$select = new single_select($url, 'issuelist', array('completed' => get_string('completedusers','diploma'), 'allusers' => get_string('allusers','diploma')), $issuelist);
    		//$select = new single_select($url, 'issuelist', array('completed' => get_string('completedusers','diploma')), $issuelist);
    		//$select->label = get_string('showusers','diploma');
    		//echo $OUTPUT->render($select);
    		echo '<br>';
    		echo '<form id="bulkissue" name="bulkissue" method="post" action="view.php">';

    		echo html_writer::label(get_string('bulkaction','diploma'), 'menutype', true);
    		echo '&nbsp;';
    		//echo html_writer::select(array('pdf' => get_string('onepdf','diploma'), 'zip'=> get_string('multipdf','diploma'), 'email'=>get_string('sendtoemail','diploma')),'type','pdf');
    		echo html_writer::select(array('zip'=> get_string('multipdf','diploma'), 'email'=>get_string('sendtoemail','diploma')),'type','pdf');
    		$table = new html_table();
    		$table->width = "95%";
    		$table->tablealign = "center";

    		//strgrade
    		$table->head  = array(' ', get_string('fullname'), get_string('grade'));
    		$table->align = array("left", "left", "center");
    		$table->size = array ('1%','89%','10%');

    		foreach ($users as $user) {
    			$canissue = self::can_issue($user, $issuelist != 'allusers', $cm, $diploma, $course);

    			if (empty($canissue)) {
    				$chkbox = html_writer::checkbox('selectedusers[]', $user->id, false);
    				$name = $OUTPUT->user_picture($user) . fullname($user);
    				$table->data[] = array ($chkbox ,$name, diploma_get_grade($diploma, $course, $user->id));
    			}
    		}

    		$downloadbutton = $OUTPUT->single_button($url->out_as_local_url(false, array('action'=>'download')), get_string('bulkbuttonlabel','diploma'));

    		echo $OUTPUT->paging_bar($usercount, $page, $perpage, $url);
    		echo '<br />';
    		echo html_writer::table($table);
    		echo html_writer::tag('div', $downloadbutton, array('style' => 'text-align: center'));
    		echo '</form>';

    	}
    	else if ($action == 'download') {
    		// needed to update user course tracking (diploma_tracking)
    		require_once 'historylib.php';

    		$type = $url->get_param('type');

    		// Calculate zip file name
    		$filename = str_replace(' ', '_', clean_filename($diploma->coursealtname.' '.get_string('modulenameplural','diploma').' '.strip_tags(format_string($diploma->name,true)).'.'.strip_tags(format_string($type, true))));

    		switch ($type) {
    			//One pdf with all certificates
    			case 'pdf':
    				/*
    				$pdf = $this->create_pdf_object();

    				foreach ($users as $user) {
    					$canissue = self::can_issue($user, $issuelist != 'allusers', $cm, $diploma, $course);

    					if (empty($canissue)) {
    						$this->create_pdf(self::get_issue($user), $pdf);
    					}
    				}

    				$pdf->Output($filename, 'D');
    				*/
    				break;

    			//One zip with all certificates in separated files
    			case 'zip':
    				$filesforzipping = array();
    				self::delete_files_from_zip_dir();

    				foreach ($users as $user) {
    					$canissue = self::can_issue($user, $issuelist != 'allusers', $cm, $diploma, $course);

    					if (empty($canissue)) {
    						$issuecert = diploma_get_issue($course, $user, $diploma, $cm, true);

    						if(!diploma_issue_file_exists($issuecert)) {
    							// Save diploma and update user course tracking
    							$issuecert = self::issue_diploma_for_user($diploma, $cm, $course, $user);
    							history::update_user_course_tracking_when_diploma_issued($course->id, $user->id);
    						}

    						if (self::copy_diploma_file_to_zip_dir($issuecert)) {
    							$pdf_filename = diploma_get_pdf_filename($issuecert);
    							$filesforzipping[$pdf_filename] = self::get_zip_folder_path() . $pdf_filename;
    						}
    					}
    				}

    				$tempzip = self::create_temp_file('issuedcertificate_');

    				//zipping files
    				$zipper = new zip_packer();

    				if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
    					//send file and delete after sending.
    					send_temp_file($tempzip, $filename);
    				}
    				else {
    					$url->remove_params('action', 'type');
    					redirect($url, get_string('no_diplomas_generated', 'diploma'), 5);
    				}

    				break;

    			case 'email':
    				foreach ($users as $user) {
    					$canissue = self::can_issue($user, $issuelist != 'allusers', $cm, $diploma, $course);

    					if (empty($canissue)) {
    						$issuecert = diploma_get_issue($course, $user, $diploma, $cm, true);

    						if(!diploma_issue_file_exists($issuecert)) {
    							// Save diploma and update user course tracking
    							$issuecert = self::issue_diploma_for_user($diploma, $cm, $course, $user);
    							history::update_user_course_tracking_when_diploma_issued($course->id, $user->id);
    						}

    						self::send_certificade_email($issuecert, context_module::instance($cm->id));
    					}
    				}

    				$url->remove_params('action','type');
    				redirect($url, get_string('emailsent','diploma'), 5);
    				break;

    			default:
    				$url->remove_params('action','type');
    				redirect($url, get_string('noaction_specified','diploma'), 5);
    				break;
    		}

    		exit;
    	}

    	echo $OUTPUT->footer($diploma->course);
    }


    public static function view_default($diploma, $cm, $course, moodle_url $url, $canmanage) {
    	global $OUTPUT, $USER, $CFG;

    	// Create new diploma record, or return existing record
    	$certrecord = diploma_get_issue($course, $USER, $diploma, $cm, $canmanage);

    	// Load the specific diploma type.
    	make_cache_directory('tcpdf');
    	require("$CFG->dirroot/mod/diploma/type/$diploma->diplomatype/diploma.php");

    	if (!$url->get_param('action')) {
    		echo $OUTPUT->header();

    		if ($canmanage) {
    			self::show_tabs($url);
    		}

    		// Check if the user can view the diploma
    		if ($diploma->requiredtime && !$canmanage) {
    			if (diploma_get_course_time($course->id, $course) < ($diploma->requiredtime * 60)) {
    				$a = new stdClass;
    				$a->requiredtime = $diploma->requiredtime;
    				notice(get_string('requiredtimenotmet', 'diploma', $a), "$CFG->wwwroot/course/view.php?id=$course->id");
    				die;
    			}
    		}

    		if (!empty($diploma->intro)) {
    			echo $OUTPUT->box(format_module_intro('diploma', $diploma, $cm->id), 'generalbox', 'intro');
    		}

    		if ($diploma->delivery != 3 || $canmanage) {
    			// if diploma has not been generate show button to generate,
    			// or if is a manager let him generate a test certificate,
    			// else show a download link
    			if (!$certrecord || $canmanage) {
	    			switch ($diploma->delivery) {
	    				case self::OUTPUT_FORCE_DOWNLOAD:
	    					$str = get_string('opendownload', 'diploma');
	    					break;

	    				case self::OUTPUT_SEND_EMAIL:
	    					$str = get_string('openemail', 'diploma');
	    					break;

	    				default:
	    					$str = get_string('openwindow', 'diploma');
	    					break;
	    			}

	    			echo html_writer::tag('p', $str, array('style' => 'text-align:center'));
	    			$linkname = get_string('getdiploma', 'diploma');

	    			$link = new moodle_url('/mod/diploma/view.php?id='.$cm->id.'&action=get');
	    			$button = new single_button($link, $linkname);
		    		$button->add_action(new popup_action('click', $link, 'view'.$cm->id, array('height' => 600, 'width' => 800)));

	    			echo html_writer::tag ( 'div', $OUTPUT->render ( $button ), array ('style' => 'text-align:center'));
    			}
    			else {
    				$url  = new moodle_url(diploma_get_pdf_download_url($certrecord, false));
    				$link = html_writer::link($url, get_string('download_diploma', 'diploma'));
    				$date =  userdate($certrecord->timecreated);
    				echo html_writer::tag('div', $date .' - '.  $link, array('style' => 'text-align:center'));
    			}
    		}

    		// Add to log, only if we are reissuing
    		add_to_log($course->id, 'diploma', 'view', "view.php?id=$cm->id", $diploma->id, $cm->id);

    		echo $OUTPUT->footer($course);
    	}
    	else { // Output to pdf
    		// Always save diploma, PDF contents are now in $file_contents as a string
    		// don't save if user can manage (is a test)
    		$file_contents = $pdf->Output('', 'S');
    		diploma_custom_save_pdf($file_contents, $certrecord, $canmanage);

    		$filename = diploma_get_pdf_filename($certrecord);

    		if ($diploma->delivery == 0) {
    			$pdf->Output($filename, 'I'); // open in browser
    		}
    		elseif ($diploma->delivery == 1) {
    			$pdf->Output($filename, 'D'); // force download when create
    		}
    		elseif ($diploma->delivery == 2) {
    			diploma_email_student($course, $diploma, $certrecord, $context);

    			$pdf->Output($filename, 'I'); // open in browser
    			$pdf->Output('', 'S'); // send
    		}
    	}
    }


    /**
     * Shows student activity view. If diploma has not been issued yet
     * show a generate button, else show a download link and issue date
     * @param string $action : action to do (show activity / generate diploma)
     * @param stdClass $diploma : diploma object
     * @param stdClass $cm : course module object
     * @param stdClass $course : course object
     * @param stdClass $context : context object
     */
    public static function view_student_diploma($action, $diploma, $cm, $course, $context) {
    	global $CFG, $USER, $OUTPUT, $DB;

    	// Check if the user can view the diploma
    	if ($diploma->requiredtime) {
    		if (diploma_get_course_time($course->id) < ($diploma->requiredtime * 60)) {
    			$a = new stdClass;
    			$a->requiredtime = $diploma->requiredtime;
    			notice(get_string('requiredtimenotmet', 'diploma', $a), "$CFG->wwwroot/course/view.php?id=$course->id");
    			die;
    		}
    	}

    	make_cache_directory('tcpdf');

    	if (empty($action)) { // Not displaying PDF
    		echo $OUTPUT->header();

    		/// find out current groups mode
    		groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/diploma/view.php?id=' . $cm->id);
    		$currentgroup = groups_get_activity_group($cm);
    		$groupmode = groups_get_activity_groupmode($cm);

    		if (!empty($diploma->intro)) {
    			echo $OUTPUT->box(format_module_intro('diploma', $diploma, $cm->id), 'generalbox', 'intro');
    		}

    		// if diploma has not been generated show button to generate, else show a download link
    		if (!$certrecord = $DB->get_record('diploma_issues', array('diplomaid' => $diploma->id, 'userid' => $USER->id))) {
    			if ($attempts = diploma_get_attempts($diploma->id)) {
    				echo diploma_print_attempts($course, $diploma, $attempts);
    			}
    			if ($diploma->delivery == 0)    {
    				$str = get_string('openwindow', 'diploma');
    			}
    			elseif ($diploma->delivery == 1)    {
    				$str = get_string('opendownload', 'diploma');
    			}
    			elseif ($diploma->delivery == 2)    {
    				$str = get_string('openemail', 'diploma');
    			}

    			echo html_writer::tag('p', $str, array('style' => 'text-align:center'));
    			$linkname = get_string('getdiploma', 'diploma');

    			$link = new moodle_url('/mod/diploma/view.php?id='.$cm->id.'&action=get');
    			$button = new single_button($link, $linkname);
    			$button->add_action(new popup_action('click', $link, 'view'.$cm->id, array('height' => 600, 'width' => 800)));

    			echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));
    		}
    		else {
    			$url  = new moodle_url(diploma_get_pdf_download_url($certrecord, false));
    			$link = html_writer::link($url, get_string('download_diploma', 'diploma'));
    			$date =  userdate($certrecord->timecreated);
    			echo html_writer::tag('div', $date .' - '.  $link, array('style' => 'text-align:center'));
    		}

    		// Add to log, only if we are reissuing
    		add_to_log($course->id, 'diploma', 'view', "view.php?id=$cm->id", $diploma->id, $cm->id);

    		echo $OUTPUT->footer($course);
    		exit;
    	}
    	else { // Output to pdf
    		require_once 'historylib.php';

    		// Update user course tracking
    		$tracking_id = history::update_user_course_tracking_when_diploma_issued($course->id);

    		// Create new diploma record and save file
    		$certrecord = diploma_get_issue($course, $USER, $diploma, $cm);

    		// Load the specific diploma type.
    		require("$CFG->dirroot/mod/diploma/type/$diploma->diplomatype/diploma.php");

    		// Generate diploma and save file
    		$file_contents = $pdf->Output('', 'S');
    		diploma_custom_save_pdf($file_contents, $certrecord);

    		//Remove full-stop at the end if it exists, to avoid "..pdf" being created and being filtered by clean_filename
    		$certname = rtrim($diploma->name, '.');
    		$filename = clean_filename("$certname.pdf");

    		// Generate diploma and send output to browser
    		if ($diploma->delivery == 0) {
    			$pdf->Output($filename, 'I'); // open in browser
    		}
    		elseif ($diploma->delivery == 1) {
    			$pdf->Output($filename, 'D'); // force download when create
    		}
    		elseif ($diploma->delivery == 2) {
    			diploma_email_student($course, $diploma, $certrecord, $context);
    			$pdf->Output($filename, 'I'); // open in browser
    			$pdf->Output('', 'S'); // send
    		}
    	}
    }


    /**
     * Issues a diploma for a specified user, this action is done by managers.
     * @param stdClass $diploma : diploma object
     * @param stdClass $cm : course module object
     * @param stdClass $course : course object
     * @param stdClass $user : user object
     * @return stdClass : diploma issue object generated
     */
    private static function issue_diploma_for_user($diploma, $cm, $course, $user) {
    	global $OUTPUT, $CFG;

    	// Create new diploma record, or return existing record
    	$certrecord = diploma_get_issue($course, $user, $diploma, $cm, false);

    	// Load the specific diploma type.
    	make_cache_directory('tcpdf');
    	require("$CFG->dirroot/mod/diploma/type/$diploma->diplomatype/diploma.php");

    	// Always save diploma, PDF contents are now in $file_contents as a string
    	// don't save if user can manage (is a test)
    	$file_contents = $pdf->Output('', 'S');
    	diploma_custom_save_pdf($file_contents, $certrecord, false, $user);

    	return $certrecord;
    }


    /**
     * Show tabs for manager
     * @param moodle_url $url
     */
    private static function show_tabs(moodle_url $url) {
    	global $OUTPUT, $CFG;

    	$tabs [] = new tabobject(self::DEFAULT_VIEW,
    			$url->out(false, array('tab' => self::DEFAULT_VIEW)),
    			get_string('standardview', 'diploma')
    	);
    	$tabs [] = new tabobject(self::ISSUED_CERTIFCADES_VIEW,
    			$url->out(false, array('tab' => self::ISSUED_CERTIFCADES_VIEW)),
    			get_string('issuedview', 'diploma')
    	);
    	$tabs [] = new tabobject(self::BULK_ISSUE_CERTIFCADES_VIEW,
    			$url->out(false, array('tab' => self::BULK_ISSUE_CERTIFCADES_VIEW)),
    			get_string('bulkview', 'diploma')
    	);

    	if (!$url->get_param('tab')) {
    		$tab = self::DEFAULT_VIEW;
    	}
    	else {
    		$tab = $url->get_param('tab');
    	}

    	$tabrows = array();
    	$tabrows[] = $tabs;
    	print_tabs($tabrows, $tab);
    }


    private static function get_issued_diploma_users($diploma, $cm, $context, $sort="ci.timecreated ASC", $groupmode=0, $page = 0, $perpage = self::SIMPLECERT_MAX_PER_PAGE) {
    	global $CFG, $DB;

    	// get all users that can manage this certificate to exclude them from the report.
    	$certmanagers = get_users_by_capability($context, 'mod/diploma:manage', 'u.id');
    	$limitsql = '';
    	$page = (int) $page;
    	$perpage = (int) $perpage;

    	// Setup pagination - when both $page and $perpage = 0, get all results
    	if ($page || $perpage) {
    		if ($page < 0) {
    			$page = 0;
    		}

    		if ($perpage > self::SIMPLECERT_MAX_PER_PAGE) {
    			$perpage = self::SIMPLECERT_MAX_PER_PAGE;
    		}

    		$limitsql = " LIMIT $perpage" . " OFFSET " . $page * $perpage ;
    	}

    	// Get all the users that have certificates issued, should only be one issue per user for a certificate
    	$issedusers = $DB->get_records_sql(
    		"SELECT u.*, ci.serialnumber AS code, ci.timecreated
    			FROM {user} u
    			INNER JOIN {diploma_issues} ci ON u.id = ci.userid
    		WHERE u.deleted = 0 AND ci.diplomaid = :diplomaid
    			ORDER BY {$sort}
    		{$limitsql}",
    		array('diplomaid' => $diploma->id));

    	// now exclude all the certmanagers.
		foreach ($issedusers as $id => $user) {
			if (isset($certmanagers[$id])) { //exclude certmanagers.
				unset($issedusers[$id]);
			}
		}

		// if groupmembersonly used, remove users who are not in any group
		if (!empty($issedusers) and !empty($CFG->enablegroupings) and $cm->groupmembersonly) {
			if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
				$issedusers = array_intersect($issedusers, array_keys($groupingusers));
			}
		}

		if ($groupmode) {
			$currentgroup = groups_get_activity_group($cm);

			if ($currentgroup) {
				$groupusers = groups_get_members($currentgroup, 'u.*');

				if (empty($groupusers)) {
					return array();
				}

				foreach($issedusers as $id => $unused) {
					if (!isset($groupusers[$id])) {
						// remove this user as it isn't in the group!
						unset($issedusers[$id]);
					}
				}
			}
		}

		return $issedusers;
    }


    /**
     * Return diploma download link if file exists, if not return empty string.
     * @param stdClass $issuecert : diploma issue object
     * @param stdClass $context : context object
     * @return string : html download link
     */
    public static function print_issue_diploma_file($issuecert, $context = null) {
    	global $CFG, $OUTPUT;

    	$output = '';

    	if (!$context) {
    		try {
    			if ($cm = get_coursemodule_from_instance('diploma', $issuecert->diplomaid)) {
    				$context = get_context_instance(CONTEXT_MODULE, $cm->id);
    			}
    		}
    		catch (Exception $e) {
    			return $output;
    		}
    	}

    	// if file does not exist return empty output
    	if (!file_exists(diploma_get_pdf_path($issuecert))) {
    		return $output;
    	}

    	// generate download link
    	$mimetype = 'application/pdf';
    	$filename = diploma_get_pdf_filename($issuecert);

    	$output  = 	'<img src="'. $OUTPUT->pix_url(file_mimetype_icon($mimetype)) .'" height="16" width="16" alt="'. $mimetype .'" />&nbsp;'.
    				'<a href="'. diploma_get_pdf_download_url($issuecert) .'" target="_blank" >'. s($filename) .'</a>';
    	$output .= 	'<br />';
    	$output  = 	'<div class="files">'. $output .'</div>';

    	return $output;
    }


    public static function get_diploma_issue_fileinfo($issuecert, $context) {
    	global $DB;

    	if (is_object($context)) {
    		$contextid = $context->id;
    	}
    	else {
    		$contextid = $context;
    	}

    	$filename = diploma_get_pdf_filename($issuecert);

    	$fileinfo = array(
    			'contextid' => $contextid, // ID of context
    			'component' => self::CERTIFICATE_COMPONENT_NAME, // usually = table name
    			'filearea' => self::CERTIFICATE_ISSUES_FILE_AREA, // usually = table name
    			'itemid' => $issuecert->id, // usually = ID of row in table
    			'filepath' => '/', // any path beginning and ending in /
    			'mimetype' => 'application/pdf', // any filename
    			'userid' => $issuecert->userid,
    			'filename' => $filename
    	);

    	return $fileinfo;
    }


    /**
     * Verify if user meet issue conditions
     *
     * @param int $userid User id
     * @return string null if user meet issued conditions, or an text with erro
     */
    public static function can_issue($user = null, $chkcompletation = true, $cm = null, $diploma = null, $course) {
    	global $DB, $USER, $CFG;

    	if (empty($user)) {
    		$user = $USER;
    	}

    	if (has_capability('mod/diploma:manage', context_module::instance($cm->id), $user)) {
    		return get_string('cantissue', 'diploma');
    	}

    	if ($chkcompletation) {
    		if ($diploma->requiredtime) {
    			if (self::get_course_time($user, $course) < $diploma->requiredtime) {
    				$a = new stdClass;
    				$a->requiredtime = $diploma->requiredtime;
    				return get_string('requiredtimenotmet', 'diploma', $a);
    			}
    		}

    		if (completion_info::is_enabled_for_site()) {
    			require_once("{$CFG->libdir}/completionlib.php");

    			if (!$course = $DB->get_record('course', array('id' => $diploma->course))) {
    				print_error('cannotfindcourse');
    			}

    			$info = new completion_info($course);

    			if ($info->is_enabled($cm) && !$info->is_course_complete($user->id)) {
    				return get_string('cantissue', 'diploma');
    			}
    		}

/* AEA - Provocate warning in line 1221 of lib/conditionlib.php
Warning: strpos(): Empty delimiter in /home/www/test/aula-moodle/current/lib/conditionlib.php on line 1221
TCPDF ERROR: Some data has already been output, can't send PDF file
*/
    		if ($CFG->enableavailability) {
    			require_once("{$CFG->libdir}/conditionlib.php");
    			$condition_info = new condition_info($cm, CONDITION_MISSING_EVERYTHING);

    			if (!$condition_info->is_available($msg, false, $user->id)) {
    				return $msg;
    			}
    		}
/* */
    	}

    	return null;
    }


    /**
     * Creates a temporary file
     * @param string $file : filename
     * @return string :
     */
    public static function create_temp_file($file) {
    	global $CFG;

    	$path = make_temp_directory(self::CERTIFICATE_COMPONENT_NAME);
    	return tempnam($path, $file);
    }


    /**
     * Copy the diploma issue pdf file to the module temp directory. Creates
     * the temp directory if not exists
     * @param stdClass $issuecert : diploma issue object
     * @return boolean : true if file was copied, false on the contrary
     */
    private static function copy_diploma_file_to_zip_dir($issuecert) {
    	$temp_dir	 = self::SIMPLECERT_ZIP_TEMP_FOLDER;
        $zip_dir     = self::get_zip_folder_path();
    	$dest_path	 = $zip_dir . diploma_get_pdf_filename($issuecert);
    	$source_path = diploma_get_pdf_path($issuecert);

    	if (!file_exists($zip_dir)) {
    		make_temp_directory($temp_dir, $exceptiononerror = true);
    	}

    	return copy($source_path, $dest_path);
    }


    /**
     * Delete files from the temp dir, where diplomas are placed.
     */
    private static function delete_files_from_zip_dir() {
    	$res = true;

    	$files = glob(self::get_zip_folder_path() .'*');

    	if (empty($files)) {
    		return false;
    	}

    	foreach ($files as $file) {
    		$res &= unlink($file);
    	}

    	return $res;
    }


    /**
     * Gets full path of the diploma zip temp folder.
     * @return string : path
     */
    private static function get_zip_folder_path() {
    	global $CFG;

    	return $CFG->tempdir .'/'. self::SIMPLECERT_ZIP_TEMP_FOLDER;
    }


    /**
     * Sends the student their issued certificate from moddata as an email
     * attachment.
     *
     * @param stdClass $course
     * @param stdClass $certificate
     * @param stdClass $certrecord
     * @param stdClass $context
     */
    private static function send_certificade_email($issuecert, $context) {
    	global $DB, $CFG, $USER;

    	if (!$user = $DB->get_record('user', array('id' => $issuecert->userid))) {
    		print_error('nousersfound', 'moodle');
    	}

    	$filelink = diploma_get_pdf_download_url($issuecert);

    	$info = new stdClass;
    	$info->username = format_string(fullname($user), true);
    	$info->certificate = format_string($issuecert->coursename, true);
    	$info->course = format_string($issuecert->coursename, true);
    	$info->download_link = html_writer::link($filelink, $filelink);


    	$subject = get_string('emailstudentsubject', 'diploma', $info);
    	$message = get_string('emailstudenttext', 'diploma', $info) . "\n";

    	// Make the HTML version more XHTML happy  (&amp;)
    	$messagehtml = text_to_html($message);
    	$ret = email_to_user($user, format_string($USER->email, true), $subject, $message, $messagehtml);

    	return $ret;
    }


    /**
     * Get the time the user has spent in the course
     *
     * @param int $userid User ID  (default= $USER->id)
     * @return int the total time spent in seconds
     */
    public static function get_course_time($user = null, $course = null) {
    	global $CFG, $USER;

    	if (empty($user)) {
    		$user = $USER;
    	}
    	set_time_limit(0);

    	$totaltime = 0;
    	$sql = "l.course = :courseid AND l.userid = :userid";
    	if ($logs = get_logs($sql, array('courseid' => $course->id, 'userid' => $user->id), 'l.time ASC', '', '', $totalcount)) {
    		foreach ($logs as $log) {
    			if (!isset($login)) {
    				// For the first time $login is not set so the first log is also the first login
    				$login = $log->time;
    				$lasthit = $log->time;
    				$totaltime = 0;
    			}
    			$delay = $log->time - $lasthit;
    			if ($delay > ($CFG->sessiontimeout * 60)) {
    				// The difference between the last log and the current log is more than
    				// the timeout Register session value so that we have found a session!
    				$login = $log->time;
    			} else {
    				$totaltime += $delay;
    			}
    			// Now the actual log became the previous log for the next cycle
    			$lasthit = $log->time;
    		}
    		return $totaltime;
    	}
    	return 0;
    }

}