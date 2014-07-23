<?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");


class diplomavalidation_form extends moodleform
{
	
	public function definition() {
		global $CFG;

		$mform = $this->_form;

		$mform->addElement('header', 'validation', get_string('page_heading', 'diploma'));
		
		$mform->addElement(
				'text', 
				'serialnumber', 
				get_string('validation_code', 'diploma'), 
				array('size' => '64', 'maxsize' => '128')
		);
		$mform->addHelpButton('serialnumber', 'validation_code', 'diploma');
		$mform->setType('serialnumber', PARAM_ALPHANUMEXT);
		$mform->setDefault('serialnumber', '');
		$mform->addRule('serialnumber', null, 'required', null, 'client');
		
		$this->add_action_buttons(false, get_string('validate', 'diploma'));
	}


	function validation($data, $files) {
		return array();
	}
	
}
