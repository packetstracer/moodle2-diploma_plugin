<?php

defined('MOODLE_INTERNAL') || die();    ///  It must be included after moodle config.php

require_once($CFG->dirroot .'/mod/diploma/lib.php');


class history
{

	const TYPE_CURRENT 	= 'current';
	const TYPE_PAST		= 'past';


	/**
	 * Generates page html code.
	 * @param int $user_id : if specified, show content for that user
	 * @return string : html code
	 */
	public static function show_page_content($user_id = false) {
		global $OUTPUT;

		echo $OUTPUT->heading(get_string('currentcourses_heading', 'diploma'));
		echo self::get_courses_table(self::TYPE_CURRENT, $user_id);
		echo $OUTPUT->heading(get_string('pastcourses_heading', 'diploma'));
		echo self::get_courses_table(self::TYPE_PAST, $user_id);
	}


	/**
	 * Generates html code for a history section specified by history type.
	 * @param string $type : history type
	 * @param int $user_id : if specified, show content for that user
	 * @return string : section html code
	 */
	public static function get_courses_table($type = self::TYPE_CURRENT, $user_id = false) {
		global $USER;

		// if user is not specified, use the current one
		if (!$user_id) {
			$user_id = $USER->id;
		}

		$courses = self::get_courses($user_id, $type);

		if (empty($courses)) {
			return html_writer::tag(
						'h5',
						get_string('no_courses', 'diploma'),
						array('style' => 'text-align:center;')
					);
		}

		return self::generate_courses_table($courses, $type);
	}


	/**
	 * Generates html code for course table specified by history type.
	 * @param string $courses : course data
	 * @param string $type : history type
	 * @return string : table html code
	 */
	private static function generate_courses_table(array $courses, $type) {
		$table = new html_table();

		self::set_table_attributes($table, $type);

		foreach ($courses as $course) {
			$table->data[] = self::get_table_row($course, $type);
		}

		return html_writer::table($table);
	}


	/**
	 * Set table object attributes depending of its history type.
	 * @param stdClass $table : table object
	 * @param string $type : history type
	 */
	private static function set_table_attributes(html_table &$table, $type) {
		$table->width 		= '95%';
		$table->tablealign 	= 'center';

		switch ($type) {
			case self::TYPE_CURRENT:
				$table->head  = array(
					get_string('th_coursename', 'diploma'),
					get_string('th_startdate', 'diploma'),
					get_string('th_currentgrade', 'diploma'),
					get_string('th_timespent', 'diploma'),
					get_string('th_achieveddiplomas', 'diploma')
				);
				$table->align = array('center', 'center', 'center', 'center', 'center');
				$table->size  = array ('20%', '20%', '20%', '20%', '20%');
				break;

			case self::TYPE_PAST:
				$table->head  = array(
					get_string('th_coursename', 'diploma'),
					get_string('th_startdate', 'diploma'),
					get_string('th_enddate', 'diploma'),
					get_string('th_endgrade', 'diploma'),
					get_string('th_timespent', 'diploma'),
					get_string('th_achieveddiplomas', 'diploma')
				);
				$table->align = array('center', 'center', 'center', 'center', 'center', 'center');
				$table->size  = array ('16%', '16%', '16%', '16%', '16%', '16%');
				break;
		}
	}


	/**
	 * Get table cell array to fill table object's data.
	 * @param stdClass $course : course object
	 * @param string $type : history type
	 * @return array : table cell
	 */
	private static function get_table_row($course, $type) {
		$cell = array();

		switch ($type) {
			case self::TYPE_CURRENT:
				$cell[] = $course->coursename;
				$cell[] = userdate($course->enroldate);
				$cell[] = self::get_grade_cell($course->courseid, $course->grade);
				$cell[] = self::format_timespent_output($course->time);
				$cell[] = self::get_diplomas_cell($course->diplomas);
				break;

			case self::TYPE_PAST:
				$cell[] = $course->coursename;
				$cell[] = userdate($course->enroldate);
				$cell[] = userdate($course->unenroldate);
				$cell[] = (is_numeric($course->grade)) ? number_format($course->grade, 2) : $course->grade;
				$cell[] = self::format_timespent_output($course->time);
				$cell[] = self::get_diplomas_cell($course->diplomas);
				break;
		}

		return $cell;
	}


	/**
	 * Get content of the grade cell, that's the graded linked to the user's
	 * grade report.
	 * @param integer $course_id : course id
	 * @param string $grade : the grade string
	 * @return string : html code of the cell
	 */
	private static function get_grade_cell($course_id, $grade) {
		global $CFG;

		$url = $CFG->wwwroot .'/grade/report/index.php?id='. $course_id;

		if (empty($grade)) {
			return $grade;
		}

		return html_writer::link($url, number_format($grade, 2));
	}


	/**
	 * Generates the html content of diploma cell for the courses table.
	 * @param array $diplomas : diploma issue objects
	 * @return boolean|string : html cell code, false if no diplomas
	 */
	private static function get_diplomas_cell(array $diplomas) {
		global $OUTPUT;

		if (empty($diplomas)) {
			return false;
		}

		$html = '';

		foreach ($diplomas as $diploma) {
			$url  = diploma_get_pdf_download_url($diploma);
			$icon =
				'<img src="'. $OUTPUT->pix_url(file_mimetype_icon('application/pdf')) .'"
					  height="16" width="16" alt="application/pdf" />&nbsp;';

			$html .= $icon . html_writer::link($url, $diploma->diplomaname) .'<br/>';
		}

		return $html;
	}


	/**
	 * Get user current/past courses including its diplomas data for
	 * creating the history table data.
	 * @param integer $user_id : user id
	 * @param string $type : courses type (current or past)
	 * @return boolean|array : courses data, false if none
	 */
	private static function get_courses($user_id, $type = self::TYPE_CURRENT) {
		$courses = self::get_user_tracking($user_id, $type);

		if (empty($courses)) {
			return false;
		}

		foreach ($courses as $course) {
			$course->diplomas = self::get_user_course_diplomas($user_id, $course->courseid);
		}

		return $courses;
	}


	/**
	 * Get diplomas objects array for a specified user and course from
	 * the database.
	 * @param integer $user_id : user id
	 * @param integer $course_id : course id
	 * @return array : diplomas
	 */
	private static function get_user_course_diplomas($user_id, $course_id) {
		global $DB;

		$sql =
			"SELECT di.courseid, di.userid,
					di.trackingid, di.coursename, di.timecreated, di.grade, di.serialnumber,
					d.id AS diplomaid, d.name AS diplomaname
				FROM {diploma_issues} AS di
				INNER JOIN {diploma} AS d ON d.id = di.diplomaid
			 WHERE di.userid = ? AND di.courseid = ? AND d.deleted = 0";

		return $DB->get_records_sql($sql, array($user_id, $course_id));
	}


	/**
	 * Formats a number of seconds into date units and form a text
	 * string for output.
	 * @param integer $timespent : seconds
	 * @return string : date output string
	 */
	private static function format_timespent_output($timespent) {
		if (empty($timespent)) {
			return false;
		}

		$units = self::convert_seconds_to_date_units($timespent);

		$output =
			$units['days'] 		.' '. get_string('days') 	.', '.
			$units['hours'] 	.' '. get_string('hours') 	.', '.
			$units['minutes'] 	.' '. get_string('minutes') .', '.
			$units['seconds'] 	.' '. get_string('seconds')
		;

		return $output;
	}


	/**
	 * Converts seconds to an array consisting of date units (days, hours,
	 * minutes and seconds) of duration.
	 * @param integer $milliseconds : number of seconds to convert
	 * @return array : number of days, hours, minutes and seconds
	 */
	private static function convert_seconds_to_date_units($time) {
		$units = array();
		$units['days'] 	 	= floor($time / (24 * 60 * 60));
		$units['hours'] 	= floor(($time - ($units['days'] * 24 * 60 * 60)) / (60 * 60));
		$units['minutes'] 	= floor(($time - ($units['days'] * 24 * 60 * 60) - ($units['hours'] * 60 * 60)) / 60);
		$units['seconds'] 	= ($time - ($units['days'] * 24 * 60 * 60) - ($units['hours'] * 60 * 60) - ($units['minutes'] * 60)) % 60;

		return $units;
	}


	/**
	 * Catches user_enrolled system event and updates the diploma_tracking table.
	 * If the tracking record exists update it, if not insert a new one.
	 * @param stdClass $event : cought event
	 * @return boolean|number : id inserted/updated, false if error
	 */
	public static function user_enrolled(stdClass $event) {
		if (self::exists_user_course_tracking($event->userid, $event->courseid)) {
			$tracking = self::format_enrol_event_to_tracking($event, 'enrol', false);
			$res = self::update_user_course_tracking($tracking);
		}
		else {
			$tracking = self::format_enrol_event_to_tracking($event, 'enrol', true);
			$res = self::insert_user_course_tracking($tracking);
		}

		return $res;
	}


	/**
	 * Catches user_unenrolled system event and updates the diploma_tracking table.
	 * @param stdClass $event : cought event
	 * @return boolean|number : id updated, false if error
	 */
	public static function user_unenrolled(stdClass $event) {
		if (!self::exists_user_course_tracking($event->userid, $event->courseid)) {
			return true;
		}

		$tracking = self::format_enrol_event_to_tracking($event, 'unenrol');

		return self::update_user_course_tracking($tracking);
	}


	/**
	 * Formats the event object into a diploma tracking object, in order to
	 * insert/update the database.
	 * @param stdClass $event : cought event
	 * @param string $type : type of formatted object (enrol | unenrol)
	 * @param string $new : for enrol type if new or existing record
	 * @return stdClass : tracking object
	 */
	private static function format_enrol_event_to_tracking(stdClass $event, $type = 'enrol', $new = false) {
		$tracking = new stdClass();

		if ($type === 'enrol') {
			// if new enrolment insert all data, if reenrolment just update unenroldate to 0
			if ($new) {
				$tracking->userid 		= $event->userid;
				$tracking->courseid 	= $event->courseid;
				$tracking->coursename 	= self::get_course_name($event->courseid);
				$tracking->enroldate 	= $event->timecreated;
				$tracking->time 		= 0;
				$tracking->unenroldate 	= 0;
			}
			else {
				$tracking->id = self::get_user_course_tracking_id($event->userid, $event->courseid);
				$tracking->unenroldate = 0;
			}
		}
		else {
			$tracking->id = self::get_user_course_tracking_id($event->userid, $event->courseid);
			$tracking->unenroldate = $event->timecreated;
			$tracking->time = diploma_get_course_time($event->courseid);
			$tracking->grade = self::get_user_course_grade($event->userid, $event->courseid);
		}

		return $tracking;
	}


	/**
	 * Get the user's course grade value specified by its user id and course id.
	 * @param integer $user_id : user id
	 * @param integer $course_id : course id
	 * @return null | float : grade value, null if no value
	 */
	private static function get_user_course_grade($user_id, $course_id) {
		$course_item = grade_item::fetch_course_item($course_id);
		$course_item->gradetype = GRADE_TYPE_VALUE;

		//$grade = new grade_grade(array('itemid' => $course_id, 'userid' => $user_id));
		$grade = new grade_grade(array('itemid' => $course_item->id, 'userid' => $user_id));
		$grade_value = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_REAL, $decimals = 2);

		$res = (is_numeric($grade_value)) ? $grade_value : null;

		return $res;
	}

	/**
	 * Gets from the database the final grade for user's course, it's not used
	 * but could be.
	 * @param integer $user_id : user id
	 * @param integer $course_id : course id
	 * @return boolean|float : final grade if exists, false on the contrary
	 */
	private static function get_user_course_grade_from_db($user_id, $course_id) {
		global $DB, $CFG;

		$sql =
			'SELECT u.id AS userid, u.username,
					gi.id AS itemid, gi.itemname AS itemname,
					gi.grademax AS itemgrademax, gi.aggregationcoef AS itemaggregation,
					g.finalgrade AS finalgrade
				FROM {user} u
				INNER JOIN {grade_grades} g ON g.userid = u.id
				INNER JOIN {grade_items} gi ON g.itemid =  gi.id
				INNER JOIN {course} c ON c.id = gi.courseid
			WHERE gi.courseid = ? AND u.id = ?
				LIMIT 1 ';

		$row = $DB->get_record_sql($sql, array($course_id, $user_id));

		if (empty($row)) {
			return false;
		}

		return $row->finalgrade;
	}





	/**
	 * Get tracking id for a diploma tracking given by its user id and course id.
	 * @param integer $user_id : user id
	 * @param integer $course_id : course id
	 * @return stdClass : course tracking object
	 */
	public static function get_user_course_tracking_id($user_id, $course_id) {
		global $DB;

		$params = array(
				'userid' => $user_id,
				'courseid' => $course_id,
		);

		$res = $DB->get_record('diploma_tracking', $params, 'id');

		if (empty($res->id)) {
			return 0;
		}

		return $res->id;
	}


	/**
	 * Inserts a course tracking object into diploma_tracking table.
	 * @param stdClass $tracking : tracking object
	 * @return boolean|integer : false if error inserting, id number if success
	 */
	private static function insert_user_course_tracking(stdClass $tracking) {
		global $DB;

		return $DB->insert_record('diploma_tracking', $tracking);
	}


	/**
	 * Updates a course tracking object into diploma_tracking table.
	 * @param stdClass $tracking : tracking object
	 * @return boolean|integer : false if error updating, id number if success
	 */
	private static function update_user_course_tracking(stdClass $tracking) {
		global $DB;

		return $DB->update_record('diploma_tracking', $tracking);
	}


	/**
	 * Gets user courses tracking by type (all, current courses,
	 * past courses).
	 * @param integer $user_id : user id
	 * @param string $type : type name, null if none
	 * @return array: tracking objects
	 */
	private static function get_user_tracking($user_id, $type = null) {
		global $DB;

		$unenroldate_condition = '';

		if (!empty($type)) {
			if ($type === self::TYPE_CURRENT) {
				$unenroldate_condition = ' AND dt.unenroldate = 0 ';
			}
			else if ($type === self::TYPE_PAST) {
				$unenroldate_condition = ' AND dt.unenroldate > 0 ';
			}
		}

		$sql =
			"SELECT dt.id, dt.userid, dt.courseid, dt.coursename,
					dt.enroldate, dt.unenroldate, dt.grade, dt.time
				FROM {diploma_tracking} dt
			 WHERE dt.userid = ? $unenroldate_condition
			 	ORDER BY dt.enroldate ";

		return $DB->get_records_sql($sql, array($user_id));
	}


	/**
	 * Checks if a user course tracking record exists, specified by user id
	 * and course id.
	 * @param integer $user_id : user id
	 * @param integer $course_id : course id
	 * @return boolean : true if tracking record exists, false on the contrary
	 */
	private static function exists_user_course_tracking($user_id, $course_id) {
		$res = self::get_user_course_tracking($user_id, $course_id);

		return !empty($res);
	}


	/**
	 * Gets a user course tracking specified by user id and course id.
	 * @param integer $user_id : user id
	 * @param integer $course_id : course id
	 * @return array : tracking object
	 */
	private static function get_user_course_tracking($user_id, $course_id) {
		global $DB;

		$sql =
			"SELECT dt.id, dt.userid, dt.courseid, dt.coursename,
					dt.enroldate, dt.unenroldate, dt.time
				FROM {diploma_tracking} dt
			WHERE dt.userid = ? AND dt.courseid = ?
				LIMIT 1 ";

		return $DB->get_record_sql($sql, array($user_id, $course_id));
	}


	/**
	 * Returns the fullname of a course given its it.
	 * @param integer $course_id : course id
	 * @return boolean|string : course fullname, false if not found
	 */
	private static function get_course_name($course_id) {
		global $DB;

		$res = $DB->get_record('course', array('id' => $course_id), 'fullname');

		if (empty($res)) {
			return false;
		}

		return $res->fullname;
	}


	/**
	 * Updates the user tracking for all current courses when history page is
	 * visited, this function name is used instead of update_user_tracking to
	 * avoid name colliding between existing functions.
	 * @param string $update_diplomas
	 * @param int $user_id : if specified, show content for that user
	 * @return boolean : false if any of the update sentences fail, true on the
	 * contratry
	 */
	public static function update_user_tracking_when_visited($user_id = false) {
		global $USER, $DB;

		// if user is not specified, use the current one
		if (!$user_id) {
			$user_id = $USER->id;
		}

		$res = true;
		$courses = self::get_user_tracking($user_id, self::TYPE_CURRENT);

		if (empty($courses)) {
			return true;
		}

		foreach ($courses as $course) {
			$tracking = new stdClass();
			$tracking->id = $course->id;
			$tracking->userid = $course->userid;
			$tracking->courseid = $course->courseid;
			$tracking->time = diploma_get_course_time($course->courseid, $user_id);
			$tracking->grade = grade_get_course_grade($user_id, $course->courseid)->grade;
			//$tracking->grade = self::get_user_course_grade($user_id, $course->courseid);
			//$tracking->grade = self::get_user_course_grade_from_db($user_id, $course->courseid);

			$res &= $DB->update_record('diploma_tracking', $tracking);
		}

		return (bool) $res;
	}


	/**
	 * Update a user course tracking specified by its course_id when a diploma is
	 * issued, don't check if the course is current coz this function should only
	 * be called when a diploma is issued show we take for granted that the course
	 * is a current one. This function name is used instead of update_user_course_tracking
	 * to avoid name colliding between existing functions.
	 * @param integer $course_id : course id
	 * @param int $user_id : if specified, show content for that user
	 * @return boolean : true if updated, false if an error when updating
	 */
	public static function update_user_course_tracking_when_diploma_issued($course_id, $user_id = false) {
		global $USER, $DB;

		// if user is not specified, use the current one
		if (!$user_id) {
			$user_id = $USER->id;
		}

		$course = self::get_user_course_tracking($user_id, $course_id);

		if (empty($course)) {
			return false;
		}

		$tracking = new stdClass();
		$tracking->id = $course->id;
		$tracking->time = diploma_get_course_time($course_id, $user_id);
		$tracking->grade = self::get_user_course_grade($user_id, $course_id);

		return $DB->update_record('diploma_tracking', $tracking);
	}


}