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
 * This page lists all the instances of diploma in a particular course
 *
 * @package    mod
 * @subpackage diploma
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);           // Course Module ID

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

// Requires a login
require_course_login($course);

// Declare variables
$currentsection = "";
$printsection = "";
$timenow = time();

// Strings used multiple times
$strdiplomas = get_string('modulenameplural', 'diploma');
$strissued  = get_string('issued', 'diploma');
$strname  = get_string("name");
$strsectionname = get_string('sectionname', 'format_'.$course->format);

// Print the header
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/diploma/index.php', array('id'=>$course->id));
$PAGE->navbar->add($strdiplomas);
$PAGE->set_title($strdiplomas);
$PAGE->set_heading($course->fullname);

// Add the page view to the Moodle log
add_to_log($course->id, 'diploma', 'view all', 'index.php?id='.$course->id, '');

// Get the diplomas, if there are none display a notice
if (!$diplomas = get_all_instances_in_course('diploma', $course)) {
    echo $OUTPUT->header();
    notice(get_string('nodiplomas', 'diploma'), "$CFG->wwwroot/course/view.php?id=$course->id");
    echo $OUTPUT->footer();
    exit();
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strissued);
} else {
    $table->head  = array ($strname, $strissued);
}

foreach ($diplomas as $diploma) {
    if (!$diploma->visible) {
        // Show dimmed if the mod is hidden
        $link = html_writer::tag('a', $diploma->name, array('class' => 'dimmed',
            'href' => $CFG->wwwroot . '/mod/diploma/view.php?id=' . $diploma->coursemodule));
    } else {
        // Show normal if the mod is visible
        $link = html_writer::tag('a', $diploma->name, array('class' => 'dimmed',
            'href' => $CFG->wwwroot . '/mod/diploma/view.php?id=' . $diploma->coursemodule));
    }

    $strsection = '';
    if ($diploma->section != $currentsection) {
        if ($diploma->section) {
            $strsection = get_section_name($course, $diploma->section);
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $diploma->section;
    }

    // Get the latest diploma issue
    if ($certrecord = $DB->get_record('diploma_issues', array('userid' => $USER->id, 'diplomaid' => $diploma->id))) {
        $issued = userdate($certrecord->timecreated);
    } else {
        $issued = get_string('notreceived', 'diploma');
    }

    if ($usesections) {
        $table->data[] = array ($strsection, $link, $issued);
    } else {
        $table->data[] = array ($link, $issued);
    }
}

echo $OUTPUT->header();
echo '<br />';
echo html_writer::table($table);
echo $OUTPUT->footer();