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
 * Define all the backup steps that will be used by the backup_diploma_activity_task
 */

/**
 * Define the complete diploma structure for backup, with file and id annotations
 */
class backup_diploma_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');


        // Define each element separated
        $diploma = new backup_nested_element('diploma', array('id'), array(
        		'coursealtname', 'name', 'intro', 'introformat', 'emailteachers',
        		'delivery', 'diplomatype', 'timecreated', 'timemodified', 'deleted',
        		'metadata'
        ));

        $issues = new backup_nested_element('issues');

        $issue = new backup_nested_element('issue', array('id'), array(
        		'userid', 'courseid', 'diplomaid', 'trackingid', 'coursename',
        		'timecreated', 'grade', 'serialnumber'));

        $trackings = new backup_nested_element('trackings');

        $tracking = new backup_nested_element('tracking', array('id'), array(
        		'userid', 'courseid', 'coursename', 'enroldate', 'unenroldate',
        		'grade', 'time'
        ));


        // Build the tree
        $diploma->add_child($issues);
        $issues->add_child($issue);

        $diploma->add_child($trackings);
        $trackings->add_child($tracking);


        // Define sources
        $diploma->set_source_table('diploma', array('id' => backup::VAR_ACTIVITYID));


        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
        	////7777// Comment this lines to disable backup of issues and tracking tables
            $issue->set_source_table('diploma_issues', array('diplomaid' => backup::VAR_PARENTID));
            $tracking->set_source_table('diploma_tracking', array('courseid' => backup::VAR_COURSEID));
        }


        // Annotate the user id's where required.
        $issue->annotate_ids('user', 'userid');
        $tracking->annotate_ids('user', 'userid');


        // Define file annotations
        $diploma->annotate_files('mod_diploma', 'intro', null); // This file area hasn't itemid
        //$diploma->annotate_files('mod_diploma', 'pix', null); // This file area hasn't itemid
        $issue->annotate_files('mod_diploma', 'issues', 'id');


        // Return the root element (diploma), wrapped into standard activity structure
        return $this->prepare_activity_structure($diploma);
    }
}

