<?php

require_once('../../config.php');
require_once('lib.php');
require_once('validation_form.php');


$serial_number = optional_param('serialnumber', false, PARAM_ALPHANUMEXT);


$context = context_system::instance();
$url = new moodle_url('/mod/diploma/validation.php', array());

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('page_heading', 'diploma'));
$PAGE->set_heading(get_string('page_heading', 'diploma'));
$PAGE->set_pagelayout('standard');


echo $OUTPUT->header();

$mform = new diplomavalidation_form();

if ($serial_number) {
	$diploma = diploma_get_issue_by_serialnumber($serial_number);

	if ($diploma) {
		diploma_show_validated_diploma_issue($diploma);
	}
	else {
		diploma_show_not_validated_diploma_issue();
	}
}
else {
	$mform->display();
}

echo $OUTPUT->footer();