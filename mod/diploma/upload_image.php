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
 * Handles uploading files
 *
 * @package    mod
 * @subpackage diploma
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/diploma/lib.php');
require_once($CFG->dirroot.'/mod/diploma/upload_image_form.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$struploadimage = get_string('uploadimage', 'diploma');

$PAGE->set_url('/admin/settings.php', array('section' => 'modsettingdiploma'));
$PAGE->set_pagetype('admin-setting-modsettingdiploma');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_title($struploadimage);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($struploadimage);

$upload_form = new mod_diploma_upload_image_form();

if ($upload_form->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php?section=modsettingdiploma'));
} else if ($data = $upload_form->get_data()) {
    // Ensure the directory for storing is created
    $uploaddir = "mod/diploma/pix/$data->imagetype";
    $filename = $upload_form->get_new_filename('diplomaimage');
    make_upload_directory($uploaddir);
    $destination = $CFG->dataroot . '/' . $uploaddir . '/' . $filename;
    if (!$upload_form->save_file('diplomaimage', $destination, true)) {
        throw new coding_exception('File upload failed');
    }

    redirect(new moodle_url('/admin/settings.php?section=modsettingdiploma'), get_string('changessaved'));
}

echo $OUTPUT->header();
echo $upload_form->display();
echo $OUTPUT->footer();
?>
