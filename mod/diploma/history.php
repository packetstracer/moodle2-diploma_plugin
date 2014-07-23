<?php

require_once('../../config.php');
require_once($CFG->dirroot .'/mod/diploma/historylib.php');

global $USER;


require_login();


$userid = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

if (has_capability('mod/diploma:manageall', $context)) {
	$userid = (empty($userid)) ? $USER->id : $userid;
}
else {
	$userid = $USER->id;
}
$user = $DB->get_record('user', array('id' => $userid));
$currentuser = ($user->id == $USER->id);
if (!$currentuser) {
    $PAGE->navigation->extend_for_user($user);
}
$url = new moodle_url('/mod/diploma/history.php');
if ($currentuser) {
    $url->params(array('id' => $userid));
}
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('history_heading', 'diploma'));
$PAGE->set_heading(get_string('history_heading', 'diploma'));
$PAGE->set_pagelayout('mydashboard');


echo $OUTPUT->header();
history::update_user_tracking_when_visited($userid);
history::show_page_content($userid);
echo $OUTPUT->footer();