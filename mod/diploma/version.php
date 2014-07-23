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
 * Code fragment to define the version of the diploma module
 *
 * @package    mod
 * @subpackage dploma
 * @copyright  www.cohaerentis.com (Antonio Espinosa and Iván Merín)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */

$module->version   = 2014030405; // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2012120300; // Requires this Moodle version
$module->cron      = 0; // Period for cron to check this module (secs)
$module->component = 'mod_diploma';

$module->maturity  = MATURITY_STABLE;
$module->release   = "Stable"; // User-friendly version number
