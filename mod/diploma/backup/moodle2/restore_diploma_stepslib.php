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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_diploma_activity_task
 */

/**
 * Structure step to restore one diploma activity
 */
class restore_diploma_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('diploma', '/activity/diploma');

        if ($userinfo) {
            $paths[] = new restore_path_element('diploma_issue', '/activity/diploma/issues/issue');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_diploma($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the diploma record
        $newitemid = $DB->insert_record('diploma', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_diploma_issue($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->diplomaid = $this->get_new_parentid('diploma');
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('diploma_issues', $data);
        $this->set_mapping('diploma_issue', $oldid, $newitemid);
    }

    protected function process_diploma_tracking($data) {
    	global $DB;

    	$data = (object)$data;
    	$oldid = $data->id;

    	//$data->courseid = $this->get_courseid();
    	//$data->timecreated = $this->apply_date_offset($data->timecreated);

		//$DB->execute('TRUNCATE TABLE {diploma_tracking}');

    	$newitemid = $DB->insert_record('diploma_tracking', $data);
    	$this->set_mapping('diploma_tracking', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add diploma related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_diploma', 'issue', 'diploma_issue');
    }
}
