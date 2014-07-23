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
 * Handles viewing a diploma
 *
 * @package    mod
 * @subpackage diploma
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("$CFG->dirroot/mod/diploma/deprecatedlib.php");
require_once("$CFG->dirroot/mod/diploma/lib.php");
require_once("$CFG->libdir/pdflib.php");
require_once('diplomalib.php');


$edit = optional_param('edit', -1, PARAM_BOOL);
$id = required_param('id', PARAM_INT); // Course Module ID
$action = optional_param('action', '', PARAM_ALPHA);
$tab = optional_param('tab', simple::DEFAULT_VIEW, PARAM_INT);
$sort = optional_param('sort', '', PARAM_RAW);
$type = optional_param('type', '', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 50, PARAM_INT);
$issuelist = optional_param('issuelist', null, PARAM_ALPHA);
$selectedusers = optional_param_array('selectedusers', null, PARAM_INT);


if (!$cm = get_coursemodule_from_id('diploma', $id)) {
    print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');
}
if (!$diploma = $DB->get_record('diploma', array('id'=> $cm->instance))) {
    print_error('course module is incorrect');
}

// format diploma metadata
$diploma = diploma_format_out_data($diploma);


require_login($course->id, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/diploma:view', $context);
$canmanage = has_capability('mod/diploma:manage', $context);

$url = new moodle_url('/mod/diploma/view.php', array (
		'id' => $cm->id,
		'tab' => $tab,
		'page' => $page,
		'perpage' => $perpage,
));
if ($type) {
	$url->param('type', $type);
}
if ($sort) {
	$url->param ('sort', $sort);
}
if ($action) {
	$url->param ('action', $action);
}
if ($issuelist) {
	$url->param ('issuelist', $issuelist);
}


// log update
add_to_log($course->id, 'diploma', 'view', "view.php?id=$cm->id", $diploma->id, $cm->id);
$completion=new completion_info($course);
$completion->set_module_viewed($cm);

// Initialize $PAGE, compute blocks
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($diploma->name));
$PAGE->set_heading(format_string($course->fullname));


// Is manager, else student
if ($canmanage) {
	switch ($tab) {
		case simple::ISSUED_CERTIFCADES_VIEW :
			simple::view_issued_diplomas($diploma, $cm, $course, $context, $url);
			break;

		case simple::BULK_ISSUE_CERTIFCADES_VIEW :
			simple::view_bulk_diplomas($diploma, $cm, $course, $url, $selectedusers);
			break;

		default :
			simple::view_default($diploma, $cm, $course, $url, $canmanage);
			break;
	}
}
else {
	simple::view_student_diploma($action, $diploma, $cm, $course, $context);
}

