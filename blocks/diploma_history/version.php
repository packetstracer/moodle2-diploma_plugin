<?php

/**
 * Version details
 *
 * @package    block
 * @subpackage diploma_history
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   		= 2012112900;
$plugin->requires  		= 2012112900;					//2012120304.05
$plugin->component 		= 'block_diploma_history';
$plugin->cron 			= 0;
$plugin->maturity 		= MATURITY_STABLE;
$plugin->release 		= '1.0 (Build: 2014031700)';
$plugin->dependencies 	= array(
		'mod_diploma' => ANY_VERSION
);