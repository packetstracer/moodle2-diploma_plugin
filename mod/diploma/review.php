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
 * This page reviews a diploma
 *
 * @package    mod
 * @subpackage diploma
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/pdflib.php");

// Retrieve any variables that are passed
$id = required_param('id', PARAM_INT);    // Course Module ID
$action = optional_param('action', '', PARAM_ALPHA);

if (!$cm = get_coursemodule_from_id('diploma', $id)) {
    print_error('Course Module ID was incorrect');
}

if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');
}

if (!$diploma = $DB->get_record('diploma', array('id'=> $cm->instance))) {
    print_error('course module is incorrect');
}

// Requires a course login
require_course_login($course->id, true, $cm);

// Check the capabilities
$context = context_module::instance($cm->id);
require_capability('mod/diploma:view', $context);

// Initialize $PAGE, compute blocks
$PAGE->set_url('/mod/diploma/review.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($diploma->name));
$PAGE->set_heading(format_string($course->fullname));

// Get previous cert record
if (!$certrecord = $DB->get_record('diploma_issues', array('userid' => $USER->id, 'diplomaid' => $diploma->id))) {
    notice(get_string('nodiplomasissued', 'diploma'), "$CFG->wwwroot/course/view.php?id=$course->id");
    die;
}

// Load the specific diplomatype
require ("$CFG->dirroot/mod/diploma/type/$diploma->diplomatype/diploma.php");

if ($action) {
    // Remove full-stop at the end if it exists, to avoid "..pdf" being created and being filtered by clean_filename
    $certname = rtrim($diploma->name, '.');
    $filename = clean_filename("$certname.pdf");
    $pdf->Output($filename, 'I'); // open in browser
    exit();
}

echo $OUTPUT->header();

if (has_capability('mod/diploma:manage', $context)) {
    $numusers = count(diploma_get_issues($diploma->id, 'ci.timecreated ASC', '', $cm));
    $url = html_writer::tag('a', get_string('viewdiplomaviews', 'diploma', $numusers),
        array('href' => $CFG->wwwroot . '/mod/diploma/report.php?id=' . $cm->id));
    echo html_writer::tag('div', $url, array('class' => 'reportlink'));
}

if (!empty($diploma->intro)) {
    echo $OUTPUT->box(format_module_intro('diploma', $diploma, $cm->id), 'generalbox', 'intro');
}

echo html_writer::tag('p', get_string('viewed', 'diploma'). '<br />' . userdate($certrecord->timecreated), array('style' => 'text-align:center'));

$link = new moodle_url('/mod/diploma/review.php?id='.$cm->id.'&action=get');
$linkname = get_string('reviewdiploma', 'diploma');
$button = new single_button($link, $linkname);
$button->add_action(new popup_action('click', $link, array('height' => 600, 'width' => 800)));

echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));

echo $OUTPUT->footer($course);
