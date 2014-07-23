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

require_once($CFG->dirroot . '/mod/diploma/backup/moodle2/backup_diploma_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/mod/diploma/backup/moodle2/backup_diploma_settingslib.php'); // Because it exists (optional)

/**
 * diploma backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_diploma_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // diploma only has one structure step
        $this->add_step(new backup_diploma_activity_structure_step('diploma_structure', 'diploma.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of diplomas
        $search="/(".$base."\/mod\/diploma\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DIPLOMAINDEX*$2@$', $content);

        // Link to diploma view by moduleid
        $search="/(".$base."\/mod\/diploma\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DIPLOMAVIEWBYID*$2@$', $content);

        return $content;
    }
}
