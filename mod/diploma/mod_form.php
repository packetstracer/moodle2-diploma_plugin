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
* Instance add/edit form
*
* @package    mod
* @subpackage diploma
* @copyright  Mark Nelson <markn@moodle.com>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/diploma/lib.php');

class mod_diploma_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('diplomaname', 'diploma'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(false, get_string('intro', 'diploma'));

        // Issue options
        $mform->addElement('header', 'issueoptions', get_string('issueoptions', 'diploma'));
        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
        $mform->addElement('select', 'emailteachers', get_string('emailteachers', 'diploma'), $ynoptions);
        $mform->setDefault('emailteachers', 0);
        $mform->addHelpButton('emailteachers', 'emailteachers', 'diploma');

        $mform->addElement('text', 'emailothers', get_string('emailothers', 'diploma'), array('size'=>'64', 'maxsize'=>'200'));
        $mform->setType('emailothers', PARAM_TEXT);
        $mform->addHelpButton('emailothers', 'emailothers', 'diploma');

        $deliveryoptions = array( 0 => get_string('openbrowser', 'diploma'), 1 => get_string('download', 'diploma'), 2 => get_string('emaildiploma', 'diploma'));
        $mform->addElement('select', 'delivery', get_string('delivery', 'diploma'), $deliveryoptions);
        $mform->setDefault('delivery', 0);
        $mform->addHelpButton('delivery', 'delivery', 'diploma');

        $mform->addElement('text', 'requiredtime', get_string('coursetimereq', 'diploma'), array('size'=>6));
        $mform->setType('requiredtime', PARAM_INT);
        $mform->addHelpButton('requiredtime', 'coursetimereq', 'diploma');

        // Text Options
        $mform->addElement('header', 'textoptions', get_string('textoptions', 'diploma'));

        $mform->addElement('text', 'coursealtname', get_string('coursealtname', 'diploma'), array('size'=>'64', 'maxsize'=>'256'));
        $mform->setType('coursealtname', PARAM_TEXT);
        $mform->addHelpButton('coursealtname', 'coursealtname', 'diploma');

        $mform->addElement('text', 'program', get_string('program', 'diploma'), array('size'=>'64', 'maxlength' => '256'));
        $mform->setType('program', PARAM_TEXT);
        $mform->addHelpButton('program', 'program', 'diploma');

        $mform->addElement('text', 'edition', get_string('edition', 'diploma'), array('size'=>'64', 'maxlength' => '256'));
        $mform->setType('edition', PARAM_TEXT);
        $mform->addHelpButton('edition', 'edition', 'diploma');

        $mform->addElement('text', 'organization', get_string('organization', 'diploma'), array('size'=>'64', 'maxlength' => '256'));
        $mform->setType('organization', PARAM_TEXT);
        $mform->addHelpButton('organization', 'organization', 'diploma');

        $modules = diploma_get_mods();
        $dateoptions = diploma_get_date_options() + $modules;

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'diploma'));
        $mform->addHelpButton('startdate', 'startdate', 'diploma');
        $mform->setDefault('startdate', time() + 3600 * 24);

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'diploma'));
        $mform->addHelpButton('enddate', 'enddate', 'diploma');
        $mform->setDefault('enddate', time() + 3600 * 24);

        $dateformatoptions = array( 1 => 'January 1, 2000', 2 => 'January 1st, 2000', 3 => '1 January 2000',
        		4 => 'January 2000', 5 => get_string('userdateformat', 'diploma'));
        $mform->addElement('select', 'startenddatefmt', get_string('startenddatefmt', 'diploma'), $dateformatoptions);
        $mform->setDefault('startenddatefmt', 0);
        $mform->addHelpButton('startenddatefmt', 'startenddatefmt', 'diploma');

        $mform->addElement('select', 'deliverydatefmt', get_string('deliverydatefmt', 'diploma'), $dateformatoptions);
        $mform->setDefault('deliverydatefmt', 'N');
        $mform->addHelpButton('deliverydatefmt', 'deliverydatefmt', 'diploma');

        $mform->addElement('select', 'completiondatefmt', get_string('completiondatefmt', 'diploma'), $dateformatoptions);
        $mform->setDefault('completiondatefmt', 'N');
        $mform->addHelpButton('completiondatefmt', 'completiondatefmt', 'diploma');

        $mform->addElement('text', 'duration', get_string('duration', 'diploma'), array('size'=>'32', 'maxlength' => '128'));
        $mform->setType('duration', PARAM_TEXT);
        $mform->addHelpButton('duration', 'duration', 'diploma');

        $gradeformatoptions = array( 1 => get_string('gradepercent', 'diploma'), 2 => get_string('gradepoints', 'diploma'),
        		3 => get_string('gradeletter', 'diploma'));
        $mform->addElement('select', 'gradefmt', get_string('gradefmt', 'diploma'), $gradeformatoptions);
        $mform->setDefault('gradefmt', 0);
        $mform->addHelpButton('gradefmt', 'gradefmt', 'diploma');


        // Design Options
        $mform->addElement('header', 'designoptions', get_string('designoptions', 'diploma'));
        $mform->addElement('select', 'diplomatype', get_string('diplomatype', 'diploma'), diploma_types());
        $mform->setDefault('diplomatype', 'A4_non_embedded');
        $mform->addHelpButton('diplomatype', 'diplomatype', 'diploma');

        $mform->addElement('select', 'printheader', get_string('printheader', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_HEADER));
        $mform->setDefault('printheader', '0');
        $mform->addHelpButton('printheader', 'printheader', 'diploma');

        $mform->addElement('select', 'printwmark', get_string('printwmark', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_WATERMARK));
        $mform->setDefault('printwmark', '0');
        $mform->addHelpButton('printwmark', 'printwmark', 'diploma');

        // Left Signature
        $mform->addElement('header', 'signatureleft', get_string('signatureleft', 'diploma'));
        $mform->addElement('select', 'printsignatureleft', get_string('printsignatureleft', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_SIGNATURE));
        $mform->setDefault('printsignatureleft', '0');
        $mform->addHelpButton('printsignatureleft', 'printsignatureleft', 'diploma');

        $mform->addElement('text', 'signaturenameleft', get_string('signaturenameleft', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signaturenameleft', PARAM_TEXT);
        $mform->addHelpButton('signaturenameleft', 'signaturenameleft', 'diploma');

        $mform->addElement('text', 'signaturejobpositionleft', get_string('signaturejobpositionleft', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signaturejobpositionleft', PARAM_TEXT);
        $mform->addHelpButton('signaturejobpositionleft', 'signaturejobpositionleft', 'diploma');

        $mform->addElement('text', 'signatureinstitutionleft', get_string('signatureinstitutionleft', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signatureinstitutionleft', PARAM_TEXT);
        $mform->addHelpButton('signatureinstitutionleft', 'signatureinstitutionleft', 'diploma');

        // Center Signature
        $mform->addElement('header', 'signaturecenter', get_string('signaturecenter', 'diploma'));
        $mform->addElement('select', 'printsignaturecenter', get_string('printsignaturecenter', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_SIGNATURE));
        $mform->setDefault('printsignaturecenter', '0');
        $mform->addHelpButton('printsignaturecenter', 'printsignaturecenter', 'diploma');

        $mform->addElement('text', 'signaturenamecenter', get_string('signaturenamecenter', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signaturenamecenter', PARAM_TEXT);
        $mform->addHelpButton('signaturenamecenter', 'signaturenamecenter', 'diploma');

        $mform->addElement('text', 'signaturejobpositioncenter', get_string('signaturejobpositioncenter', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signaturejobpositioncenter', PARAM_TEXT);
        $mform->addHelpButton('signaturejobpositioncenter', 'signaturejobpositioncenter', 'diploma');

        $mform->addElement('text', 'signatureinstitutioncenter', get_string('signatureinstitutioncenter', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signatureinstitutioncenter', PARAM_TEXT);
        $mform->addHelpButton('signatureinstitutioncenter', 'signatureinstitutioncenter', 'diploma');

        // Right Signature
        $mform->addElement('header', 'signatureright', get_string('signatureright', 'diploma'));
        $mform->addElement('select', 'printsignatureright', get_string('printsignatureright', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_SIGNATURE));
        $mform->setDefault('printsignatureright', '0');
        $mform->addHelpButton('printsignatureright', 'printsignatureright', 'diploma');

        $mform->addElement('text', 'signaturenameright', get_string('signaturenameright', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signaturenameright', PARAM_TEXT);
        $mform->addHelpButton('signaturenameright', 'signaturenameright', 'diploma');

        $mform->addElement('text', 'signaturejobpositionright', get_string('signaturejobpositionright', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signaturejobpositionright', PARAM_TEXT);
        $mform->addHelpButton('signaturejobpositionright', 'signaturejobpositionright', 'diploma');

        $mform->addElement('text', 'signatureinstitutionright', get_string('signatureinstitutionright', 'diploma'), array('size'=>'48', 'maxlength' => '255'));
        $mform->setType('signatureinstitutionright', PARAM_TEXT);
        $mform->addHelpButton('signatureinstitutionright', 'signatureinstitutionright', 'diploma');

        // Seals / Logos
        $mform->addElement('header', 'sealoptions', get_string('sealoptions', 'diploma'));
        $mform->addElement('select', 'printsealleft', get_string('printsealleft', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_SEAL));
        $mform->setDefault('printsealleft', '0');
        $mform->addHelpButton('printsealleft', 'printsealleft', 'diploma');

        $mform->addElement('select', 'printsealcenter', get_string('printsealcenter', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_SEAL));
        $mform->setDefault('printsealcenter', '0');
        $mform->addHelpButton('printsealcenter', 'printsealcenter', 'diploma');

        $mform->addElement('select', 'printsealright', get_string('printsealright', 'diploma'), diploma_get_images(DIPLOMA_IMAGE_SEAL));
        $mform->setDefault('printsealright', '0');
        $mform->addHelpButton('printsealright', 'printsealright', 'diploma');

        // Standard course elements
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Some basic validation
     *
     * @param $data
     * @param $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check that the required time entered is valid
        if ((!is_number($data['requiredtime']) || $data['requiredtime'] < 0)) {
            $errors['requiredtime'] = get_string('requiredtimenotvalid', 'diploma');
        }

        return $errors;
    }


    public function set_data($default_values) {
    	parent::set_data(diploma_format_out_data($default_values));
    }

}