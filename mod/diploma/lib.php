<?php

// This file is part of the diploma module for Moodle - http://moodle.org/
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
 * diploma module core interaction API
 *
 * @package    mod
 * @subpackage diploma
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
require_once('historylib.php');


/** The border image folder */
define('DIPLOMA_IMAGE_BORDER', 'borders');
/** The header image folder */
define('DIPLOMA_IMAGE_HEADER', 'headers');
/** The watermark image folder */
define('DIPLOMA_IMAGE_WATERMARK', 'watermarks');
/** The signature image folder */
define('DIPLOMA_IMAGE_SIGNATURE', 'signatures');
/** The seal image folder */
define('DIPLOMA_IMAGE_SEAL', 'seals');

/** Set DIPLOMA_PER_PAGE to 0 if you wish to display all diplomas on the report page */
define('DIPLOMA_PER_PAGE', 30);
define('DIPLOMA_MAX_PER_PAGE', 200);

define('DIPLOMA_MAX_SEALS', 3);
define('DIPLOMA_MAX_SIGNATURES', 3);

define('DIPLOMA_ALIGN_TYPE_1', 'left');
define('DIPLOMA_ALIGN_TYPE_2', 'center');
define('DIPLOMA_ALIGN_TYPE_3', 'right');


/**
 * Formats a diploma object submitted by the form in order
 * to fit the diploma table structure. We gather some of the
 * fields values into a json metadata field.
 * @param stdClass $diploma : diploma created by the create/edit form
 * @return stdClass : formatted diploma object
 */
function diploma_format_in_data($diploma) {
	$formatted = diploma_get_regular_data($diploma);

	$metadata 				= diploma_get_regular_metadata($diploma);
	$metadata->logos 		= diploma_format_logos_metadata($diploma);
	$metadata->signatures 	= diploma_format_signatures_metadata($diploma);

	$formatted->metadata = json_encode($metadata);

	return $formatted;
}


/**
 * Gets diploma regular fields, those which are not stored into the
 * metadata field.
 * @param stdClass $diploma : source diploma object
 * @return stdClass : object including only regular fields
 */
function diploma_get_regular_data($diploma) {
	$regular_fields = array(
			'id', 'course', 'name', 'coursealtname', 'emailteachers',
			'emailothers', 'delivery', 'requiredtime', 'diplomatype',
			'intro', 'introformat', 'timecreated', 'timemodified',
			'deleted'
	);

	$regular = new stdClass();

	foreach ($diploma as $key => $value) {
		if (in_array($key, $regular_fields)) {
			$regular->$key = $value;
		}
	}

	return $regular;
}


/**
 * Get an object containning only the regular fields from the diploma object
 * parameter.
 * @param stdClass $diploma : source diploma object
 * @return stdClass : object with regular fields
 */
function diploma_get_regular_metadata($diploma) {
	$metadata_fields = array(
			'program', 'edition', 'organization', 'startdate', 'enddate',
			'startenddatefmt', 'deliverydatefmt', 'completiondatefmt',
			'duration', 'gradefmt', 'printheader', 'printwmark'
	);

	$regular = new stdClass();

	foreach ($diploma as $key => $value) {
		if (in_array($key, $metadata_fields)) {
			$regular->$key = (!empty($value)) ? $value : false;
		}
	}

	return $regular;
}


/**
 * Format seal/logo fields into an associative array where the keys are
 * the align of the logo and the value is the image file of the logo.
 * @param stdClass $diploma : source diploma object
 * @return array : seal fields formatted
 */
function diploma_format_logos_metadata($diploma) {
	$logos = array();

	for ($i = 1; $i <= DIPLOMA_MAX_SEALS; $i++) {
		$align = constant('DIPLOMA_ALIGN_TYPE_'. $i);

		if (!empty($diploma->{'printseal'.$align})) {
			$logos[$align] = $diploma->{'printseal'.$align};
		}
	}

	return $logos;
}


/**
 * Format signature fields into an associative array where the keys are
 * the align of the logo and the value is an array with the image file of the
 * signature, the signature name, the signature institution and signature
 * position name.
 * @param stdClass $diploma : source diploma object
 * @return array : seal fields formatted
 */
function diploma_format_signatures_metadata($diploma) {
	$signatures = array();

	for ($i = 1; $i <= DIPLOMA_MAX_SIGNATURES; $i++) {
		$align = constant('DIPLOMA_ALIGN_TYPE_'. $i);

		if (!empty($diploma->{'printsignature'.$align})) {
			$signatures[$align]['image'] 		= $diploma->{'printsignature'.$align};
			$signatures[$align]['name'] 		= $diploma->{'signaturename'.$align};
			$signatures[$align]['institution'] 	= $diploma->{'signatureinstitution'.$align};
			$signatures[$align]['position'] 	= $diploma->{'signaturejobposition'.$align};
		}
	}

	return $signatures;
}


/**
 * Get formatted diploma data for output (form set data, pdf print, etc...),
 * extracts the metadata into the formatted object.
 * @param stdClass $diploma : source diploma object
 * @return stdClass : formatted object
 */
function diploma_format_out_data($diploma) {
	$metadata = array();

	if (!empty($diploma->metadata)) {
		$metadata = json_decode($diploma->metadata);

		$regular 	= diploma_extract_regular_metadata($metadata);
		$logos 		= diploma_extract_logos_metadata($metadata);
		$signatures = diploma_extract_signatures_metadata($metadata);

		$metadata = array_merge($regular, $logos, $signatures);
	}

	$formatted = (object) array_merge((array) $diploma, $metadata);
	unset($formatted->metadata);

	return $formatted;
}


/**
 * Gets the regular data from the metadata field.
 * @param stdClass $metadata : metadata values
 * @return array : regular fields from metadata
 */
function diploma_extract_regular_metadata($metadata) {
	$metadata_fields = array(
			'program', 'edition', 'organization', 'startdate', 'enddate',
			'startenddatefmt', 'deliverydatefmt', 'completiondatefmt',
			'duration', 'gradefmt', 'printheader', 'printwmark'
	);

	$regular = array();

	foreach ($metadata as $key => $value) {
		if (in_array($key, $metadata_fields)) {
			$regular[$key] = (!empty($value)) ? $value : false;
		}
	}

	return $regular;
}


/**
 * Gets logo fields from the metadata field.
 * @param stdClass $metadata : metadata values
 * @return array : logo fields from metadata
 */
function diploma_extract_logos_metadata($metadata) {
	$logos = array();

	foreach ($metadata->logos as $align => $image) {
		if (!empty($image)) {
			$logos['printseal'.$align] = $image;
		}
	}

	return $logos;
}


/**
 * Gets signatures fields from the metadata field.
 * @param stdClass $metadata : metadata values
 * @return array : signature fields from metadata
 */
function diploma_extract_signatures_metadata($metadata) {
	$signatures = array();

	foreach ($metadata->signatures as $align => $values) {
		if (!empty($values) && !empty($values)) {
			$signatures['printsignature'.$align] 		= $values->image;
			$signatures['signaturename'.$align] 		= $values->name;
			$signatures['signatureinstitution'.$align] 	= $values->institution;
			$signatures['signaturejobposition'.$align] 	= $values->position;
		}
	}

	return $signatures;
}


/**
 * Add diploma instance.
 *
 * @param stdClass $diploma
 * @return int new diploma instance id
 */
function diploma_add_instance($diploma) {
    global $DB;

    // Create the diploma.
    $diploma->timecreated = time();
    $diploma->timemodified = $diploma->timecreated;
    $diploma->deleted = 0;

    return $DB->insert_record('diploma', diploma_format_in_data($diploma));
}

/**
 * Update diploma instance.
 *
 * @param stdClass $diploma
 * @return bool true
 */
function diploma_update_instance($diploma) {
    global $DB;

    // Update the diploma.
    $diploma->timemodified = time();
    $diploma->id = $diploma->instance;

    return $DB->update_record('diploma', diploma_format_in_data($diploma));
}

/**
 * Given an ID of an instance of this module,
 * this function will logically delete the instance
 * and won't delete any data that depends on it, in order
 * to be able to keep track of diplomas once the course,
 * activity or related user are deleted.
 *
 * @param int $id
 * @return bool true if successful
 */
function diploma_delete_instance($id) {
    global $DB;

    // Ensure the diploma exists
    if (!$diploma = $DB->get_record('diploma', array('id' => $id))) {
        return false;
    }

    // Prepare file record object
    if (!$cm = get_coursemodule_from_instance('diploma', $id)) {
        return false;
    }

	// logical deletion
    $result = true;
    $delete = new stdClass();
    $delete->id = $id;
    $delete->deleted = 1;
    $DB->update_record('diploma', $delete);

    return $result;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all posts from the specified diploma
 * and clean up any related data.
 *
 * Written by Jean-Michel Vedrine
 *
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function diploma_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'diploma');
    $status = array();

    if (!empty($data->reset_diploma)) {
        $sql = "SELECT cert.id
                FROM {diploma} cert
                WHERE cert.course = :courseid";
        $DB->delete_records_select('diploma_issues', "diplomaid IN ($sql)", array('courseid' => $data->courseid));
        $status[] = array('component' => $componentstr, 'item' => get_string('diplomaremoved', 'diploma'), 'error' => false);
    }

    // Updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('diploma', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);
    }

    return $status;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the diploma.
 *
 * Written by Jean-Michel Vedrine
 *
 * @param $mform form passed by reference
 */
function diploma_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'diplomaheader', get_string('modulenameplural', 'diploma'));
    $mform->addElement('advcheckbox', 'reset_diploma', get_string('deletissueddiplomas', 'diploma'));
}

/**
 * Course reset form defaults.
 *
 * Written by Jean-Michel Vedrine
 *
 * @param stdClass $course
 * @return array
 */
function diploma_reset_course_form_defaults($course) {
    return array('reset_diploma' => 1);
}

/**
 * Returns information about received diploma.
 * Used for user activity reports.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $diploma
 * @return stdClass the user outline object
 */
function diploma_user_outline($course, $user, $mod, $diploma) {
    global $DB;

    $result = new stdClass;
    if ($issue = $DB->get_record('diploma_issues', array('diplomaid' => $diploma->id, 'userid' => $user->id))) {
        $result->info = get_string('issued', 'diploma');
        $result->time = $issue->timecreated;
    } else {
        $result->info = get_string('notissued', 'diploma');
    }

    return $result;
}

/**
 * Returns information about received diploma.
 * Used for user activity reports.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $page
 * @return string the user complete information
 */
function diploma_user_complete($course, $user, $mod, $diploma) {
   global $DB, $OUTPUT;

   if ($issue = $DB->get_record('diploma_issues', array('diplomaid' => $diploma->id, 'userid' => $user->id))) {
        echo $OUTPUT->box_start();
        echo get_string('issued', 'diploma') . ": ";
        echo userdate($issue->timecreated);
        diploma_print_user_files($diploma->id, $user->id);
        echo '<br />';
        echo $OUTPUT->box_end();
    } else {
        print_string('notissuedyet', 'diploma');
    }
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of diploma.
 *
 * @param int $diplomaid
 * @return stdClass list of participants
 */
function diploma_get_participants($diplomaid) {
    global $DB;

    $sql = "SELECT DISTINCT u.id, u.id
            FROM {user} u, {diploma_issues} a
            WHERE a.diplomaid = :diplomaid
            AND u.id = a.userid";
    return  $DB->get_records_sql($sql, array('diplomaid' => $diplomaid));
}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function diploma_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}

/**
 * Function to be run periodically according to the moodle cron
 * TODO:This needs to be done
 */
function diploma_cron () {
    return true;
}

/**
 * Returns a list of teachers by group
 * for sending email alerts to teachers
 *
 * @param stdClass $diploma
 * @param stdClass $user
 * @param stdClass $course
 * @param stdClass $cm
 * @return array the teacher array
 */
function diploma_get_teachers($diploma, $user, $course, $cm) {
    global $USER, $DB;

    $context = context_module::instance($cm->id);
    $potteachers = get_users_by_capability($context, 'mod/diploma:manage',
        '', '', '', '', '', '', false, false);
    if (empty($potteachers)) {
        return array();
    }
    $teachers = array();
    if (groups_get_activity_groupmode($cm, $course) == SEPARATEGROUPS) {   // Separate groups are being used
        if ($groups = groups_get_all_groups($course->id, $user->id)) {  // Try to find all groups
            foreach ($groups as $group) {
                foreach ($potteachers as $t) {
                    if ($t->id == $user->id) {
                        continue; // do not send self
                    }
                    if (groups_is_member($group->id, $t->id)) {
                        $teachers[$t->id] = $t;
                    }
                }
            }
        } else {
            // user not in group, try to find teachers without group
            foreach ($potteachers as $t) {
                if ($t->id == $USER->id) {
                    continue; // do not send self
                }
                if (!groups_get_all_groups($course->id, $t->id)) { //ugly hack
                    $teachers[$t->id] = $t;
                }
            }
        }
    } else {
        foreach ($potteachers as $t) {
            if ($t->id == $USER->id) {
                continue; // do not send self
            }
            $teachers[$t->id] = $t;
        }
    }

    return $teachers;
}

/**
 * Alerts teachers by email of received diplomas. First checks
 * whether the option to email teachers is set for this diploma.
 *
 * @param stdClass $course
 * @param stdClass $diploma
 * @param stdClass $certrecord
 * @param stdClass $cm course module
 */
function diploma_email_teachers($course, $diploma, $certrecord, $cm) {
    global $USER, $CFG, $DB;

    if ($diploma->emailteachers == 0) {          // No need to do anything
        return;
    }

    $user = $DB->get_record('user', array('id' => $certrecord->userid));

    if ($teachers = diploma_get_teachers($diploma, $user, $course, $cm)) {
        $strawarded = get_string('awarded', 'diploma');
        foreach ($teachers as $teacher) {
            $info = new stdClass;
            $info->student = fullname($USER);
            $info->course = format_string($course->fullname,true);
            $info->diploma = format_string($diploma->name,true);
            $info->url = $CFG->wwwroot.'/mod/diploma/report.php?id='.$cm->id;
            $from = $USER;
            $postsubject = $strawarded . ': ' . $info->student . ' -> ' . $diploma->name;
            $posttext = diploma_email_teachers_text($info);
            $posthtml = ($teacher->mailformat == 1) ? diploma_email_teachers_html($info) : '';

            @email_to_user($teacher, $from, $postsubject, $posttext, $posthtml);  // If it fails, oh well, too bad.
        }
    }
}

/**
 * Alerts others by email of received diplomas. First checks
 * whether the option to email others is set for this diploma.
 * Uses the email_teachers info.
 * Code suggested by Eloy Lafuente
 *
 * @param stdClass $course
 * @param stdClass $diploma
 * @param stdClass $certrecord
 * @param stdClass $cm course module
 */
function diploma_email_others($course, $diploma, $certrecord, $cm) {
    global $USER, $CFG, $DB;

    if ($diploma->emailothers) {
       $others = explode(',', $diploma->emailothers);
        if ($others) {
            $strawarded = get_string('awarded', 'diploma');
            foreach ($others as $other) {
                $other = trim($other);
                if (validate_email($other)) {
                    $destination = new stdClass;
                    $destination->email = $other;
                    $info = new stdClass;
                    $info->student = fullname($USER);
                    $info->course = format_string($course->fullname, true);
                    $info->diploma = format_string($diploma->name, true);
                    $info->url = $CFG->wwwroot.'/mod/diploma/report.php?id='.$cm->id;
                    $from = $USER;
                    $postsubject = $strawarded . ': ' . $info->student . ' -> ' . $diploma->name;
                    $posttext = diploma_email_teachers_text($info);
                    $posthtml = diploma_email_teachers_html($info);

                    @email_to_user($destination, $from, $postsubject, $posttext, $posthtml);  // If it fails, oh well, too bad.
                }
            }
        }
    }
}

/**
 * Creates the text content for emails to teachers -- needs to be finished with cron
 *
 * @param $info object The info used by the 'emailteachermail' language string
 * @return string
 */
function diploma_email_teachers_text($info) {
    $posttext = get_string('emailteachermail', 'diploma', $info) . "\n";

    return $posttext;
}

/**
 * Creates the html content for emails to teachers
 *
 * @param $info object The info used by the 'emailteachermailhtml' language string
 * @return string
 */
function diploma_email_teachers_html($info) {
    $posthtml  = '<font face="sans-serif">';
    $posthtml .= '<p>' . get_string('emailteachermailhtml', 'diploma', $info) . '</p>';
    $posthtml .= '</font>';

    return $posthtml;
}

/**
 * Sends the student their issued diploma from moddata as an email
 * attachment.
 *
 * @param stdClass $course
 * @param stdClass $diploma
 * @param stdClass $certrecord
 * @param stdClass $context
 */
function diploma_email_student($course, $diploma, $certrecord, $context) {
    global $DB, $USER;

    // Get teachers
    if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
        '', '', '', '', false, true)) {
        $users = sort_by_roleassignment_authority($users, $context);
        $teacher = array_shift($users);
    }

    // If we haven't found a teacher yet, look for a non-editing teacher in this course.
    if (empty($teacher) && $users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
        '', '', '', '', false, true)) {
        $users = sort_by_roleassignment_authority($users, $context);
        $teacher = array_shift($users);
    }

    // Ok, no teachers, use administrator name
    if (empty($teacher)) {
        $teacher = fullname(get_admin());
    }

    $info = new stdClass;
    $info->username = fullname($USER);
    $info->diploma = format_string($diploma->name, true);
    $info->course = format_string($course->fullname, true);
    $from = fullname($teacher);
    $subject = $info->course . ': ' . $info->diploma;
    $message = get_string('emailstudenttext', 'diploma', $info) . "\n";

    // Make the HTML version more XHTML happy  (&amp;)
    $messagehtml = text_to_html(get_string('emailstudenttext', 'diploma', $info));

    // Remove full-stop at the end if it exists, to avoid "..pdf" being created and being filtered by clean_filename
    $certname = rtrim($diploma->name, '.');
    $filename = clean_filename("$certname.pdf");

    // Get hashed pathname
    $fs = get_file_storage();

    $component = 'mod_diploma';
    $filearea = 'issue';
    $filepath = '/';
    $files = $fs->get_area_files($context->id, $component, $filearea, $certrecord->id);
    foreach ($files as $f) {
        $filepathname = $f->get_contenthash();
    }
    $attachment = 'filedir/'.diploma_path_from_hash($filepathname).'/'.$filepathname;
    $attachname = $filename;

    return email_to_user($USER, $from, $subject, $message, $messagehtml, $attachment, $attachname);
}

/**
 * Retrieve diploma path from hash
 *
 * @param array $contenthash
 * @return string the path
 */
function diploma_path_from_hash($contenthash) {
    $l1 = $contenthash[0].$contenthash[1];
    $l2 = $contenthash[2].$contenthash[3];
    return "$l1/$l2";
}

/**
 * Serves diploma issues and other files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool|nothing false if file not found, does not return anything if found - just send the file
 */
function diploma_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    if (!$diploma = $DB->get_record('diploma', array('id' => $cm->instance))) {
        return false;
    }

    require_login($course, false, $cm);

    require_once($CFG->libdir.'/filelib.php');

    if ($filearea === 'issue') {
        $certrecord = (int)array_shift($args);

        if (!$certrecord = $DB->get_record('diploma_issues', array('id' => $certrecord))) {
            return false;
        }

        if ($USER->id != $certrecord->userid and !has_capability('mod/diploma:manage', $context)) {
            return false;
        }

        $relativepath = implode('/', $args);
        $fullpath = "/{$context->id}/mod_diploma/issue/$certrecord->id/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }
        send_stored_file($file, 0, 0, true); // download MUST be forced - security!
    }
}

/**
 * This function returns success or failure of file save
 *
 * @param string $pdf is the string contents of the pdf
 * @param int $certrecordid the diploma issue record id
 * @param string $filename pdf filename
 * @param int $contextid context id
 * @return bool return true if successful, false otherwise
 */
function diploma_save_pdf($pdf, $certrecordid, $filename, $contextid) {
    global $DB, $USER;

    if (empty($certrecordid)) {
        return false;
    }

    if (empty($pdf)) {
        return false;
    }

    $fs = get_file_storage();

    // Prepare file record object
    $component = 'mod_diploma';
    $filearea = 'issue';
    $filepath = '/';
    $fileinfo = array(
        'contextid' => $contextid,   // ID of context
        'component' => $component,   // usually = table name
        'filearea'  => $filearea,     // usually = table name
        'itemid'    => $certrecordid,  // usually = ID of row in table
        'filepath'  => $filepath,     // any path beginning and ending in /
        'filename'  => $filename,    // any filename
        'mimetype'  => 'application/pdf',    // any filename
        'userid'    => $USER->id);

    // If the file exists, delete it and recreate it. This is to ensure that the
    // latest diploma is saved on the server. For example, the student's grade
    // may have been updated. This is a quick dirty hack.
    if ($fs->file_exists($contextid, $component, $filearea, $certrecordid, $filepath, $filename)) {
        $fs->delete_area_files($contextid, $component, $filearea, $certrecordid);
    }

    $fs->create_file_from_string($fileinfo, $pdf);

    return true;
}


/**
 * Saves pdf file to custom directory in moodledata, if the directory
 * does not exist, create it.
 * @param string $pdf : pdf content to be stored into a file
 * @param stdClass $certrecord : diploma issue object
 * @param stdClass $user : if specify save the file in the folder of
 * this user instead
 * @return booelan : result of file storage
 */
function diploma_custom_save_pdf($pdf, $certrecord, $test = false, $user = null) {
	global $CFG, $USER;

	$user_to_issue = (!empty($user)) ? $user : $USER;

	if (empty($certrecord)) {
		return false;
	}
	if (empty($pdf)) {
		return false;
	}

	$diplomas_directory = "{$CFG->dataroot}/diplomas/";
	$user_directory = "{$user_to_issue->id}/";
	$filename = diploma_get_pdf_filename($certrecord);
	$path = $diplomas_directory . $user_directory . $filename;

	if (file_exists($path) || $test) {
		return true;
	}

	// create directories if don't exist
	if (!is_dir($diplomas_directory) && !file_exists($diplomas_directory)) {
		mkdir($diplomas_directory);
	}
	chdir($diplomas_directory);

	if (!is_dir($user_directory) && !file_exists($user_directory)) {
		mkdir($user_directory);
	}
	chdir($user_directory);

	return file_put_contents($path, $pdf);
}


/**
 * Produces a list of links to the issued diplomas.  Used for report.
 *
 * @param stdClass $diploma
 * @param int $userid
 * @param stdClass $context
 * @return string return the user files
 */
function diploma_print_user_files($diploma, $userid, $context) {
    global $CFG, $DB, $OUTPUT;

    $output = '';

    $certrecord = $DB->get_record('diploma_issues', array('userid' => $userid, 'diplomaid' => $diploma->id));
    $fs = get_file_storage();
    $browser = get_file_browser();

    $component = 'mod_diploma';
    $filearea = 'issue';
    $files = $fs->get_area_files($context, $component, $filearea, $certrecord->id);

    foreach ($files as $file) {
        $filename = $file->get_filename();
        $mimetype = $file->get_mimetype();
        $link = diploma_get_pdf_download_url($certrecord);

        $output = '<img src="'.$OUTPUT->pix_url(file_mimetype_icon($file->get_mimetype())).'" height="16" width="16" alt="'.$file->get_mimetype().'" />&nbsp;'.
                  '<a href="'.$link.'" >'.s($filename).'</a>';
    }

    $output .= '<br />';
    $output = '<div class="files">'.$output.'</div>';

    return $output;
}

/**
 * Inserts preliminary user data when a diploma is viewed.
 * Prevents form from issuing a diploma upon browser refresh.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $diploma
 * @param stdClass $cm
 * @param booblean $test : just cr
 * @return stdClass the newly created diploma issue
 */
function diploma_get_issue($course, $user, $diploma, $cm, $test = false) {
    global $DB;

    // Check if there is an issue already, should only ever be one
    if ($certissue = $DB->get_record('diploma_issues', array('userid' => $user->id, 'diplomaid' => $diploma->id))) {
        return $certissue;
    }

    // Create new diploma issue record
    $certissue = new stdClass();
    $certissue->diplomaid = $diploma->id;
    $certissue->userid = $user->id;
    $certissue->serialnumber = diploma_generate_code();
    $certissue->timecreated =  time();
    $certissue->courseid = $course->id;
    $certissue->coursename = $course->fullname;
    $certissue->trackingid = history::get_user_course_tracking_id($user->id, $course->id);

    if (is_int(diploma_get_grade($diploma, $course, $user->id))) {
    	$certissue->grade = diploma_get_grade($diploma, $course, $user->id);
    }
    else {
    	$certissue->grade = 0;
    }

    // if test don't create the diploma issue record, and don't email it
    if (!$test) {
    	$certissue->id = $DB->insert_record('diploma_issues', $certissue);

    	// Email to the teachers and anyone else
    	diploma_email_teachers($course, $diploma, $certissue, $cm);
    	diploma_email_others($course, $diploma, $certissue, $cm);
    }
    else {
    	$certissue->id = rand(0, 4);
    }

    return $certissue;
}


/**
 * Returns a list of issued diplomas - sorted for report.
 *
 * @param int $diplomaid
 * @param string $sort the sort order
 * @param bool $groupmode are we in group mode ?
 * @param stdClass $cm the course module
 * @param int $page offset
 * @param int $perpage total per page
 * @return stdClass the users
 */
function diploma_get_issues($diplomaid, $sort="ci.timecreated ASC", $groupmode, $cm, $page = 0, $perpage = 0) {
    global $CFG, $DB;

    // get all users that can manage this diploma to exclude them from the report.
    $context = context_module::instance($cm->id);

    $conditionssql = '';
    $conditionsparams = array();
    if ($certmanagers = array_keys(get_users_by_capability($context, 'mod/diploma:manage', 'u.id'))) {
        list($sql, $params) = $DB->get_in_or_equal($certmanagers, SQL_PARAMS_NAMED, 'cert');
        $conditionssql .= "AND NOT u.id $sql \n";
        $conditionsparams += $params;
    }



    $restricttogroup = false;
    if ($groupmode) {
        $currentgroup = groups_get_activity_group($cm);
        if ($currentgroup) {
            $restricttogroup = true;
            $groupusers = array_keys(groups_get_members($currentgroup, 'u.*'));
            if (empty($groupusers)) {
                return array();
            }
        }
    }

    $restricttogrouping = false;

    // if groupmembersonly used, remove users who are not in any group
    if (!empty($CFG->enablegroupings) and $cm->groupmembersonly) {
        if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
            $restricttogrouping = true;
        } else {
            return array();
        }
    }

    if ($restricttogroup || $restricttogrouping) {
        if ($restricttogroup) {
            $allowedusers = $groupusers;
        } else if ($restricttogroup && $restricttogrouping) {
            $allowedusers = array_intersect($groupusers, $groupingusers);
        } else  {
            $allowedusers = $groupingusers;
        }

        list($sql, $params) = $DB->get_in_or_equal($allowedusers, SQL_PARAMS_NAMED, 'grp');
        $conditionssql .= "AND u.id $sql \n";
        $conditionsparams += $params;
    }


    $page = (int) $page;
    $perpage = (int) $perpage;

    // Get all the users that have diplomas issued, should only be one issue per user for a diploma
    $allparams = $conditionsparams + array('diplomaid' => $diplomaid);

    $users = $DB->get_records_sql("SELECT u.*, ci.serialnumber, ci.timecreated
                                   FROM {user} u
                                   INNER JOIN {diploma_issues} ci
                                   ON u.id = ci.userid
                                   WHERE u.deleted = 0
                                   AND ci.diplomaid = :diplomaid
                                   $conditionssql
                                   ORDER BY {$sort}",
                                   $allparams,
                                   $page * $perpage,
                                   $perpage);


    return $users;
}


/**
 * Returns a list of previously issued diplomas--used for reissue.
 *
 * @param int $diplomaid
 * @return stdClass the attempts else false if none found
 */
function diploma_get_attempts($diplomaid) {
    global $DB, $USER;

    $sql = "SELECT *
            FROM {diploma_issues} i
            WHERE diplomaid = :diplomaid
            AND userid = :userid";
    if ($issues = $DB->get_records_sql($sql, array('diplomaid' => $diplomaid, 'userid' => $USER->id))) {
        return $issues;
    }

    return false;
}

/**
 * Prints a table of previously issued diplomas--used for reissue.
 *
 * @param stdClass $course
 * @param stdClass $diploma
 * @param stdClass $attempts
 * @return string the attempt table
 */
function diploma_print_attempts($course, $diploma, $attempts) {
    global $OUTPUT, $DB;

    echo $OUTPUT->heading(get_string('summaryofattempts', 'diploma'));

    // Prepare table header
    $table = new html_table();
    $table->class = 'generaltable';
    $table->head = array(get_string('issued', 'diploma'));
    $table->align = array('left');
    $table->attributes = array("style" => "width:20%; margin:auto");

    $table->head[] = get_string('grade');
    $table->align[] = 'center';
    $table->size[] = '';

    // One row for each attempt
    foreach ($attempts as $attempt) {
        $row = array();

        // prepare strings for time taken and date completed
        $datecompleted = userdate($attempt->timecreated);
        $row[] = $datecompleted;

        $attemptgrade = diploma_get_grade($diploma, $course);
        $row[] = $attemptgrade;

        $table->data[$attempt->id] = $row;
    }

    echo html_writer::table($table);
}

/**
 * Get the time the user has spent in the course
 *
 * @param int $courseid
 * @return int the total time spent in seconds
 */
function diploma_get_course_time($courseid, $userid = false) {
    global $CFG, $USER;

    set_time_limit(0);

    if (!$userid) {
        $userid = $USER->id;
    }

    $totaltime = 0;
    $sql = "l.course = :courseid AND l.userid = :userid";
    if ($logs = get_logs($sql, array('courseid' => $courseid, 'userid' => $userid), 'l.time ASC', '', '', $totalcount)) {
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

/**
 * Get all the modules
 *
 * @return array
 */
function diploma_get_mods() {
    global $COURSE, $DB;

    $strtopic = get_string("topic");
    $strweek = get_string("week");
    $strsection = get_string("section");

    // Collect modules data
    $modinfo = get_fast_modinfo($COURSE);
    $mods = $modinfo->get_cms();

    $modules = array();
    $sections = $modinfo->get_section_info_all();
    for ($i = 0; $i <= count($sections) - 1; $i++) {
        // should always be true
        if (isset($sections[$i])) {
            $section = $sections[$i];
            if ($section->sequence) {
                switch ($COURSE->format) {
                    case "topics":
                        $sectionlabel = $strtopic;
                    break;
                    case "weeks":
                        $sectionlabel = $strweek;
                    break;
                    default:
                        $sectionlabel = $strsection;
                }

                $sectionmods = explode(",", $section->sequence);
                foreach ($sectionmods as $sectionmod) {
                    if (empty($mods[$sectionmod])) {
                        continue;
                    }
                    $mod = $mods[$sectionmod];
                    $instance = $DB->get_record($mod->modname, array('id' => $mod->instance));
                    if ($grade_items = grade_get_grade_items_for_activity($mod)) {
                        $mod_item = grade_get_grades($COURSE->id, 'mod', $mod->modname, $mod->instance);
                        $item = reset($mod_item->items);
                        if (isset($item->grademax)){
                            $modules[$mod->id] = $sectionlabel . ' ' . $section->section . ' : ' . $instance->name;
                        }
                    }
                }
            }
        }
    }

    return $modules;
}

/**
 * Search through all the modules for grade data for mod_form.
 *
 * @return array
 */
function diploma_get_grade_options() {
    $gradeoptions['0'] = get_string('no');
    $gradeoptions['1'] = get_string('coursegrade', 'diploma');

    return $gradeoptions;
}

/**
 * Search through all the modules for grade dates for mod_form.
 *
 * @return array
 */
function diploma_get_date_options() {
    $dateoptions['0'] = get_string('no');
    $dateoptions['1'] = get_string('issueddate', 'diploma');
    $dateoptions['2'] = get_string('completiondate', 'diploma');

    return $dateoptions;
}

/**
 * Fetch all grade categories from the specified course.
 *
 * @param int $courseid the course id
 * @return array
 */
function diploma_get_grade_categories($courseid) {
    $grade_category_options = array();

    if ($grade_categories = grade_category::fetch_all(array('courseid' => $courseid))) {
        foreach ($grade_categories as $grade_category) {
            if (!$grade_category->is_course_category()) {
                $grade_category_options[-$grade_category->id] = get_string('category') . ' : ' . $grade_category->get_name();
            }
        }
    }

    return $grade_category_options;
}

/**
 * Get the course outcomes for for mod_form print outcome.
 *
 * @return array
 */
function diploma_get_outcomes() {
    global $COURSE, $DB;

    // get all outcomes in course
    $grade_seq = new grade_tree($COURSE->id, false, true, '', false);
    if ($grade_items = $grade_seq->items) {
        // list of item for menu
        $printoutcome = array();
        foreach ($grade_items as $grade_item) {
            if (isset($grade_item->outcomeid)){
                $itemmodule = $grade_item->itemmodule;
                $printoutcome[$grade_item->id] = $itemmodule . ': ' . $grade_item->get_name();
            }
        }
    }
    if (isset($printoutcome)) {
        $outcomeoptions['0'] = get_string('no');
        foreach ($printoutcome as $key => $value) {
            $outcomeoptions[$key] = $value;
        }
    } else {
        $outcomeoptions['0'] = get_string('nooutcomes', 'diploma');
    }

    return $outcomeoptions;
}

/**
 * Used for course participation report (in case diploma is added).
 *
 * @return array
 */
function diploma_get_view_actions() {
    return array('view', 'view all', 'view report');
}

/**
 * Used for course participation report (in case diploma is added).
 *
 * @return array
 */
function diploma_get_post_actions() {
    return array('received');
}

/**
 * Get logo and signature elements types of alignment
 */
function diploma_get_alignments() {
	return array(
		'1' => get_string('left', 	'diploma'),
		'2' => get_string('center', 'diploma'),
		'3' => get_string('right', 	'diploma'),
	);
}

/**
 * Get diploma types indexed and sorted by name for mod_form.
 *
 * @return array containing the diploma type
 */
function diploma_types() {
    $types = array();
    $names = get_list_of_plugins('mod/diploma/type');
    $sm = get_string_manager();
    foreach ($names as $name) {
        if ($sm->string_exists('type'.$name, 'diploma')) {
            $types[$name] = get_string('type'.$name, 'diploma');
        } else {
            $types[$name] = ucfirst($name);
        }
    }
    asort($types);
    return $types;
}

/**
 * Get images for mod_form.
 *
 * @param string $type the image type
 * @return array
 */
function diploma_get_images($type) {
    global $CFG, $DB;

    switch($type) {
        case DIPLOMA_IMAGE_BORDER :
        	$path = "$CFG->dataroot/mod/diploma/pix/borders";
            $uploadpath = "$CFG->dataroot/mod/diploma/pix/borders";
            break;

        case DIPLOMA_IMAGE_SEAL :
        	$path = "$CFG->dataroot/mod/diploma/pix/seals";
            $uploadpath = "$CFG->dataroot/mod/diploma/pix/seals";
            break;

        case DIPLOMA_IMAGE_SIGNATURE :
        	$path = "$CFG->dataroot/mod/diploma/pix/signatures";
            $uploadpath = "$CFG->dataroot/mod/diploma/pix/signatures";
            break;

		case DIPLOMA_IMAGE_HEADER :
			$path = "$CFG->dataroot/mod/diploma/pix/headers";
			$uploadpath = "$CFG->dataroot/mod/diploma/pix/headers";
			break;

        case DIPLOMA_IMAGE_WATERMARK :
        	$path = "$CFG->dataroot/mod/diploma/pix/watermarks";
            $uploadpath = "$CFG->dataroot/mod/diploma/pix/watermarks";
            break;
    }

    // If valid path
    if (!empty($path)) {
        $options = array();
        $options += diploma_scan_image_dir($path);
        $options += diploma_scan_image_dir($uploadpath);

        // Sort images
        ksort($options);

        // Add the 'no' option to the top of the array
        $options = array_merge(array('0' => get_string('no')), $options);

        return $options;
    }
    else {
        return array();
    }
}

/**
 * Prepare to print an activity grade.
 *
 * @param stdClass $course
 * @param int $moduleid
 * @param int $userid
 * @return stdClass|bool return the mod object if it exists, false otherwise
 */
function diploma_get_mod_grade($course, $moduleid, $userid) {
    global $DB;

    $cm = $DB->get_record('course_modules', array('id' => $moduleid));
    $module = $DB->get_record('modules', array('id' => $cm->module));

    if ($grade_item = grade_get_grades($course->id, 'mod', $module->name, $cm->instance, $userid)) {
        $item = new grade_item();
        $itemproperties = reset($grade_item->items);
        foreach ($itemproperties as $key => $value) {
            $item->$key = $value;
        }
        $modinfo = new stdClass;
        $modinfo->name = utf8_decode($DB->get_field($module->name, 'name', array('id' => $cm->instance)));
        $grade = $item->grades[$userid]->grade;
        $item->gradetype = GRADE_TYPE_VALUE;
        $item->courseid = $course->id;

        $modinfo->points = grade_format_gradevalue($grade, $item, true, GRADE_DISPLAY_TYPE_REAL, $decimals = 2);
        $modinfo->percentage = grade_format_gradevalue($grade, $item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, $decimals = 2);
        $modinfo->letter = grade_format_gradevalue($grade, $item, true, GRADE_DISPLAY_TYPE_LETTER, $decimals = 0);

        if ($grade) {
            $modinfo->dategraded = $item->grades[$userid]->dategraded;
        } else {
            $modinfo->dategraded = time();
        }
        return $modinfo;
    }

    return false;
}

/**
 * Returns the date to display for the diploma.
 *
 * @param stdClass $diploma
 * @param stdClass $certrecord
 * @param stdClass $course
 * @param int $userid
 * @return string the date
 */
function diploma_get_date($diploma, $certrecord, $course, $userid = null, $date_type = 1) {
    global $DB, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    // Set diploma date to current time, can be overwritten later
    $date = $certrecord->timecreated;

    // 1: delivery date - 2: course completion date - 3: grade date

    // Set the date type as parameter, so we can choose what type of format for the date we need coz it's not provided in the activity form
    if ($date_type === 2) {
        // Get the enrolment end date
        $sql = "SELECT MAX(c.timecompleted) as timecompleted
                FROM {course_completions} c
                WHERE c.userid = :userid
                AND c.course = :courseid";
        if ($timecompleted = $DB->get_record_sql($sql, array('userid' => $userid, 'courseid' => $course->id))) {
            if (!empty($timecompleted->timecompleted)) {
                $date = $timecompleted->timecompleted;
            }
        }
    }
    else if ($date_type > 2) {
        if ($modinfo = diploma_get_mod_grade($course, $diploma->printdate, $userid)) {
            $date = $modinfo->dategraded;
        }
    }

    if ($date_type > 0) {
    	if ($diploma->deliverydatefmt == 1) {
            $diplomadate = userdate($date, '%B %d, %Y');
    	}
    	else if ($diploma->deliverydatefmt == 2) {
            $suffix = diploma_get_ordinal_number_suffix(userdate($date, '%d'));
            $diplomadate = userdate($date, '%B %d' . $suffix . ', %Y');
        }
        else if ($diploma->deliverydatefmt == 3) {
            $diplomadate = userdate($date, '%d %B %Y');
        }
        else if ($diploma->deliverydatefmt == 4) {
            $diplomadate = userdate($date, '%B %Y');
       	}
       	else if ($diploma->deliverydatefmt == 5) {
            $diplomadate = userdate($date, get_string('strftimedate', 'langconfig'));
        }

        return $diplomadate;
    }

    return '';
}


/**
 * Formats a date with the specified format.
 * @param string $date : date to forma
 * @param int $format : format type for the date
 * @return string : date formatted
 */
function diploma_format_date($date, $format) {
	switch ($format) {
		case '1':
			$diplomadate = userdate($date, '%B %d, %Y');
			break;

		case '2':
			$suffix = diploma_get_ordinal_number_suffix(userdate($date, '%d'));
			$diplomadate = userdate($date, '%B %d' . $suffix . ', %Y');
			break;

		case '3':
			$diplomadate = userdate($date, '%d %B %Y');
			break;

		case '4':
			$diplomadate = userdate($date, '%B %Y');
			break;

		case '5':
			$diplomadate = userdate($date, get_string('strftimedate', 'langconfig'));
			break;

		default:
			$suffix = diploma_get_ordinal_number_suffix(userdate($date, '%d'));
			$diplomadate = userdate($date, '%B %d' . $suffix . ', %Y');
			break;
	}

	return $diplomadate;
}


/**
 * Helper function to return the suffix of the day of
 * the month, eg 'st' if it is the 1st of the month.
 *
 * @param int the day of the month
 * @return string the suffix.
 */
function diploma_get_ordinal_number_suffix($day) {
    if (!in_array(($day % 100), array(11, 12, 13))) {
        switch ($day % 10) {
            // Handle 1st, 2nd, 3rd
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
        }
    }
    return 'th';
}

/**
 * Returns the grade to display for the diploma.
 *
 * @param stdClass $diploma
 * @param stdClass $course
 * @param int $userid
 * @return string the grade result
 */
function diploma_get_grade($diploma, $course, $userid = null) {
    global $USER, $DB;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    if ($course_item = grade_item::fetch_course_item($course->id)) {
    	// String used
    	$strcoursegrade = get_string('coursegrade', 'diploma');

    	$grade = new grade_grade(array('itemid' => $course_item->id, 'userid' => $userid));
    	$course_item->gradetype = GRADE_TYPE_VALUE;
    	$coursegrade = new stdClass;
    	$coursegrade->points = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_REAL, $decimals = 2);
    	$coursegrade->percentage = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, $decimals = 2);
    	$coursegrade->letter = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_LETTER, $decimals = 0);

    	if ($diploma->gradefmt == 1) {
    		$grade = $coursegrade->percentage;
    	}
    	else if ($diploma->gradefmt == 2) {
    		$grade = $coursegrade->points;
    	}
    	else if ($diploma->gradefmt == 3) {
    		$grade = $coursegrade->letter;
    	}

    	return $grade;
    }

    return '';
}


/**
 * Returns the course grade to display in the diploma (replacement of diploma_get_grade())
 * @param stdClass $diploma
 * @param stdClass $course
 * @param int $userid
 * @return string the grade result
 */
function diploma_get_course_grade($diploma, $course, $userid = null) {
	global $USER, $DB;

	if (empty($userid)) {
		$userid = $USER->id;
	}

	if ($course_item = grade_item::fetch_course_item($course->id)) {
		$strcoursegrade = get_string('coursegrade', 'diploma');

		$grade = new grade_grade(array('itemid' => $course_item->id, 'userid' => $userid));

		$course_item->gradetype = GRADE_TYPE_VALUE;
		$coursegrade = new stdClass;
		$coursegrade->points = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_REAL, $decimals = 2);
		$coursegrade->percentage = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, $decimals = 2);
		$coursegrade->letter = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_LETTER, $decimals = 0);

		if ($diploma->gradefmt == 1) {
			$grade = $coursegrade->percentage;
		}
		else if ($diploma->gradefmt == 2) {
			$grade = $coursegrade->points;
		}
		else if ($diploma->gradefmt == 3) {
			$grade = $coursegrade->letter;
		}

		return $grade;
	}
}


/**
 * Creates de html string for the text of a diploma signature
 * specified by its alignament.
 * @param stdClass $diploma : the diploma
 * @param string $align : align provided to fetch fields from the
 * diploma
 */
function diploma_get_signature_text($diploma, $align = 'left') {
	if (empty($diploma->{'printsignature'.$align})) {
		return false;
	}

	$html =
		'<b>'. $diploma->{'signaturename'.$align} .'</b> <br/>'.
		 $diploma->{'signaturejobposition'.$align} .'<br/>'.
		 $diploma->{'signatureinstitution'.$align};

	return $html;
}



/**
 * Returns the outcome to display on the diploma
 *
 * @param stdClass $diploma
 * @param stdClass $course
 * @return string the outcome
 */
function diploma_get_outcome($diploma, $course) {
    global $USER, $DB;

    if ($diploma->printoutcome > 0) {
        if ($grade_item = new grade_item(array('id' => $diploma->printoutcome))) {
            $outcomeinfo = new stdClass;
            $outcomeinfo->name = $grade_item->get_name();
            $outcome = new grade_grade(array('itemid' => $grade_item->id, 'userid' => $USER->id));
            $outcomeinfo->grade = grade_format_gradevalue($outcome->finalgrade, $grade_item, true, GRADE_DISPLAY_TYPE_REAL);

            return $outcomeinfo->name . ': ' . $outcomeinfo->grade;
        }
    }

    return '';
}

/**
 * Returns the code to display on the diploma.
 *
 * @param stdClass $course
 * @param stdClass $certrecord
 * @return string the code
 */
function diploma_get_code($diploma, $certrecord) {
    if ($diploma->printnumber) {
        return $certrecord->code;
    }

    return '';
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 * @param int $width horizontal dimension of text block
 */
function diploma_print_text($pdf, $x, $y, $align, $font='freeserif', $style, $size = 10, $text, $width = 0) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell($width, 0, '', '', $text, 0, 0, 0, true, $align);
}

/**
 * Creates rectangles for line border for A4 size paper.
 *
 * @param stdClass $pdf
 * @param stdClass $diploma
 */
function diploma_draw_frame($pdf, $diploma) {
    if ($diploma->bordercolor > 0) {
        if ($diploma->bordercolor == 1) {
            $color = array(0, 0, 0); // black
        }
        if ($diploma->bordercolor == 2) {
            $color = array(153, 102, 51); // brown
        }
        if ($diploma->bordercolor == 3) {
            $color = array(0, 51, 204); // blue
        }
        if ($diploma->bordercolor == 4) {
            $color = array(0, 180, 0); // green
        }
        switch ($diploma->orientation) {
            case 'L':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(10, 10, 277, 190);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(13, 13, 271, 184);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(16, 16, 265, 178);
            break;
            case 'P':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(10, 10, 190, 277);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(13, 13, 184, 271);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(16, 16, 178, 265);
            break;
        }
    }
}

/**
 * Creates rectangles for line border for letter size paper.
 *
 * @param stdClass $pdf
 * @param stdClass $diploma
 */
function diploma_draw_frame_letter($pdf, $diploma) {
    if ($diploma->bordercolor > 0) {
        if ($diploma->bordercolor == 1)    {
            $color = array(0, 0, 0); //black
        }
        if ($diploma->bordercolor == 2)    {
            $color = array(153, 102, 51); //brown
        }
        if ($diploma->bordercolor == 3)    {
            $color = array(0, 51, 204); //blue
        }
        if ($diploma->bordercolor == 4)    {
            $color = array(0, 180, 0); //green
        }
        switch ($diploma->orientation) {
            case 'L':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 4.25, 'color' => $color));
                $pdf->Rect(28, 28, 736, 556);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(37, 37, 718, 538);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 2.8, 'color' => $color));
                $pdf->Rect(46, 46, 700, 520);
                break;
            case 'P':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(25, 20, 561, 751);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(40, 35, 531, 721);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(51, 46, 509, 699);
            break;
        }
    }
}

/**
 * Prints border images from the borders folder in PNG or JPG formats.
 *
 * @param stdClass $pdf;
 * @param stdClass $diploma
 * @param int $x x position
 * @param int $y y position
 * @param int $w the width
 * @param int $h the height
 * @param string $align imagen alignment for seals/logos and signatures
 */
function diploma_print_image($pdf, $diploma, $type, $x, $y, $w, $h, $align = "left") {
    global $CFG;

    switch($type) {
        case DIPLOMA_IMAGE_BORDER :
            $attr = 'borderstyle';
            $path = "$CFG->dataroot/mod/diploma/pix/$type/$diploma->borderstyle";
            $uploadpath = "$CFG->dataroot/mod/diploma/pix/$type/$diploma->borderstyle";
            break;

        case DIPLOMA_IMAGE_SEAL :
        	$attr = 'printseal'. $align;
        	if (!empty($diploma->$attr)) {
        		$path = "$CFG->dataroot/mod/diploma/pix/$type/". $diploma->$attr;
        		$uploadpath = "$CFG->dataroot/mod/diploma/pix/$type/". $diploma->$attr;
        	}
        	break;

        case DIPLOMA_IMAGE_SIGNATURE :
            $attr = 'printsignature'. $align;
            if (!empty($diploma->$attr)) {
            	$path = "$CFG->dataroot/mod/diploma/pix/$type/". $diploma->$attr;
            	$uploadpath = "$CFG->dataroot/mod/diploma/pix/$type/". $diploma->$attr;
            }
            break;

        case DIPLOMA_IMAGE_HEADER :
        	$attr = 'printheader';
        	$path = "$CFG->dataroot/mod/diploma/pix/$type/$diploma->printheader";
           	$uploadpath = "$CFG->dataroot/mod/diploma/pix/$type/$diploma->printheader";
           	break;

        case DIPLOMA_IMAGE_WATERMARK :
            $attr = 'printwmark';
            $path = "$CFG->dataroot/mod/diploma/pix/$type/$diploma->printwmark";
            $uploadpath = "$CFG->dataroot/mod/diploma/pix/$type/$diploma->printwmark";
            break;
    }

    // Has to be valid
    if (!empty($path)) {
        switch ($diploma->$attr) {
            case '0' :
            case '' :
            break;

            default :
                if (file_exists($path)) {
                    $pdf->Image($path, $x, $y, $w, $h);
                }
                if (file_exists($uploadpath)) {
                    $pdf->Image($uploadpath, $x, $y, $w, $h);
                }
            break;
        }
    }
}


/**
 * Generates UUIDv4 code.
 *
 * @return string
 */
function diploma_generate_code() {
	global $DB, $CFG;
	require_once (dirname(__FILE__) . '/lib.uuid.php');

	$uniquecodefound = false;

	while (!$uniquecodefound) {
		$UUID = UUID::mint(UUID::VERSION_4);
		$code = $UUID->__toString();

		if (!$DB->record_exists('diploma_issues', array('serialnumber' => $code))) {
			$uniquecodefound = true;
		}
	}

	return $code;
}


/**
 * Scans directory for valid images
 *
 * @param string the path
 * @return array
 */
function diploma_scan_image_dir($path) {
    // Array to store the images
    $options = array();

    // Start to scan directory
    if (is_dir($path)) {
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.png', 1) || strpos($file, '.jpg', 1) ) {
                    $i = strpos($file, '.');
                    if ($i > 1) {
                        // Set the name
                        $options[$file] = substr($file, 0, $i);
                    }
                }
            }
            closedir($handle);
        }
    }

    return $options;
}


/**
 * Returns the filename corresponding to a diploma_issue object.
 * @param stdClass $certrecord : diploma issue object
 * @return string : pdf filename
 */
function diploma_get_pdf_filename($certrecord) {
	return "{$certrecord->timecreated}_{$certrecord->serialnumber}.pdf";
}


/**
 * Returns the path corresponding to a diploma issue object.
 * @param stdClass $certrecord : diploma issue object
 * @return string : pdf path
 */
function diploma_get_pdf_path($certrecord) {
	global $CFG;

	$folder   = "{$CFG->dataroot}/diplomas/{$certrecord->userid}/";
	$filename = diploma_get_pdf_filename($certrecord);

	return $folder . $filename;
}


/**
 * Get download url for the pdf file associated to the diploma
 * issue. This file is delivered by download.php script inside
 * the module folder.
 * @param stdClass $certrecord : diploma issue
 * @param boolean $printpath : preppend path to script url
 * @return string : download url
 */
function diploma_get_pdf_download_url($certrecord, $print_path = true) {
	global $CFG;

	$host	= "{$CFG->wwwroot}/";
	$path 	= "mod/diploma/";
	$script = "download.php?userid={$certrecord->userid}&diplomaid={$certrecord->diplomaid}";

	if ($print_path) {
		return $host . $path . $script;
	}
	else {
		return $script;
	}


}


/**
 * Checks if a diploma_issue file exists in moodledata folder.
 * @param stdClass $certrecord : diploma issue object
 * @return true if exist, false on the contrary
 *
 */
function diploma_issue_file_exists($certrecord) {
	return file_exists(diploma_get_pdf_path($certrecord));
}


/**
 * Gets a diploma issue by its serial number, it also fetches
 * diploma activity data, course and user names if records exist
 * in their respective tables.
 * @param string $serial_number : diploma issue serial number
 * @return stdClass : object retrieved by database, false if no
 * diploma with that serial number exists
 */
function diploma_get_issue_by_serialnumber($serial_number) {
	global $DB;

	if (!$serial_number) {
		return false;
	}

	$sql =
		"SELECT d.*, di.*,
				u.firstname, u.lastname,
				c.fullname AS coursename,
				cc.timestarted, cc.timecompleted
			FROM {diploma_issues} AS di
			LEFT JOIN {diploma} AS d ON di.diplomaid = d.id
			LEFT JOIN {user} AS u ON di.userid = u.id
			LEFT JOIN {course} AS c ON di.courseid = c.id
			LEFT JOIN {course_completions} AS cc ON c.id = cc.course AND di.userid = cc.userid
		WHERE di.serialnumber = ?
			LIMIT 1 ";

	return $DB->get_record_sql($sql, array($serial_number));
}


/**
 * Prints an html with the validated diploma issue data.
 * @param stdClass $diploma : diploma issue object
 */
function diploma_show_validated_diploma_issue($diploma) {
	$username = $diploma->firstname .' '. $diploma->lastname;

	$table = new html_table();
	$table->width = "95%";
	$table->tablealign = "center";
	$table->head  = array(
			get_string('course_name', 'diploma'),
			get_string('fullname'),
			get_string('issuedate_heading', 'diploma')
	);
	$table->align = array('center', 'center', 'center');
	$table->size  = array ('40%', '32%', '26%');
	$table->data[] = array(
			$diploma->coursename,
			$username,
			(!empty($diploma->timecreated)) ? userdate($diploma->timecreated) : '-'
	);

	echo html_writer::tag('h3', get_string('issue_valid', 'diploma'), array('style' => 'color:green; text-align:center;'));
	echo html_writer::table($table);
	echo html_writer::link('validation.php', get_string('go_back', 'diploma'));
}


/**
 * Prints an html error, warning that the diploma issue is
 * not validated.
 */
function diploma_show_not_validated_diploma_issue() {
	echo html_writer::tag('h3', get_string('issue_notvalid', 'diploma'), array('style' => 'color:red; text-align:center;'));
	echo html_writer::link('validation.php', get_string('go_back', 'diploma'));
}


