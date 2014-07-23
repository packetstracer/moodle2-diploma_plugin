<?php


class block_diploma_history extends block_base
{

	public function init() {
		$this->title = get_string('history', 'block_diploma_history');
	}


	public function get_content() {
		global $CFG;

		if ($this->content !== null) {
			return $this->content;
		}

		$this->content 		 	= new stdClass();
		$this->content->text 	=
			'<a href="'. $CFG->wwwroot .'/mod/diploma/history.php">'.
				get_string('link_text', 'block_diploma_history') .
			'</a>';
		$this->content->footer 	= '';

		return $this->content;
	}

}