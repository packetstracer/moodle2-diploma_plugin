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
 * A4_embedded diploma type
 *
 * @package    mod
 * @subpackage diploma
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from view.php
}

// We use vertical orientation (P = portrait, size in cm)
$pagelayout = array(279.4, 215.9);
//$pdf = new PDF('P', 'mm', 'Letter', true, 'UTF-8', false);
$pdf = new PDF('P', 'mm', $pagelayout, true, 'UTF-8', false);


$pdf->SetTitle($diploma->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();



//////////////////////
// Define variables //
//////////////////////

// Positions (portrait)
$sealleftx = 12.5;
$seallefty = 32;
$sealcenterx = 77.5;
$sealcentery = 32;
$sealrightx = 142.5;
$sealrighty = 32;
$sigleftx = 12.5;
$siglefty = 205;
$sigcenterx = 77.5;
$sigcentery = 205;
$sigrightx = 142.5;
$sigrighty = 205;
$wmarkx = 0;
$wmarky = 0;
$wmarkw = 215.9;
$wmarkh = 279.4;
$brdrx = 0;
$brdry = 0;
$brdrw = 215.9;
$brdrh = 279.4;
$headerx = 0;
$headery = 0;
$headerw = 215.9;
$headerh = '';
$colegex = 0;
$colegey = 65;
$certificatex = 0;
$certificatey = $colegey + 25;
$usernamex = 0;
$usernamey = $certificatey + 5;
$participationx = 25;
$participationy = $usernamey + 16;
$gradingx = 25;
$gradingy = $participationy + 20;
$datex = 25;
$datey = $gradingy + 20;
$programx = 25;
$programy = $datey + 20;
$expeditionx = 0;
$expeditiony = $siglefty - 14;
$documentx = 0;
$documenty = 258;
$validationx = 0;
$validationy = $documenty + 5;

//texts
$start_date_text 	= diploma_format_date($diploma->startdate, $diploma->startenddatefmt);
$end_date_text	 	= diploma_format_date($diploma->enddate, $diploma->startenddatefmt);
$delivery_date_text = diploma_get_date($diploma, $certrecord, $course, $userid = null, 1);
$grade_date_text 	= diploma_get_date($diploma, $certrecord, $course, $userid = null, 2);

// needed when the tutor is issuing a diploma for a student (that its not him)
if (empty($USER) && !empty($user)) {
	$diploma_user = $user;
}
else {
	$diploma_user = $USER;
}

$participation_texts = array(
	'course_name' 	=> ($diploma->coursealtname) ? $diploma->coursealtname : $course->fullname,
	'program_name' 	=> $diploma->program,
	'edition' 		=> $diploma->edition
);
$grading_texts = array(
	'user_name' => fullname($diploma_user),
	'grade' 	=>
		(diploma_get_course_grade($diploma, $course)) ?
			diploma_get_course_grade($diploma, $course) :
			$certrecord->grade
);
$date_texts = array(
	'start_date' 	=> $start_date_text,
	'end_date' 		=> $end_date_text,
	'duration' 		=> $diploma->duration
);
$program_texts = array(
	'program_name' => $diploma->program
);
$expedition_texts = array(
	'expedition_date' => $grade_date_text
);
$validation_texts = array(
	'link' => $CFG->wwwroot .'/mod/diploma/validation.php?serialnumber='. $certrecord->serialnumber
);

//sentences
$colege_sentence 		= get_string('colege_sentence', 'diploma');
$certificates_sentence 	= get_string('certificates_sentence', 'diploma');
$participation_sentence	= get_string('pariticipation_sentence', 'diploma', $participation_texts);
$grading_sentence 		= get_string('grading_sentence', 'diploma', $grading_texts);
$date_sentence 			= get_string('date_sentence', 'diploma', $date_texts);
$program_sentence 		= get_string('program_sentence', 'diploma', $program_texts);
$expedition_sentence 	= get_string('expedition_sentence', 'diploma', $expedition_texts);
$document_sentence 		= get_string('document_sentence', 'diploma');
$validation_sentence 	= get_string('validation_sentence', 'diploma', $validation_texts);



//////////////////////////
// Add images and lines //
//////////////////////////

// watermark: Set alpha (not used)
//$pdf->SetAlpha(0.2);
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);

// border: not used but can be
//diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);

// header
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_HEADER, $headerx, $headery, $headerw, $headerh);

// seals / logos
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SEAL, $sealleftx, $seallefty, 60, '', 'left');
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SEAL, $sealcenterx, $sealcentery, 60, '', 'center');
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SEAL, $sealrightx, $sealrighty, 60, '', 'right');

// signatures
$signatureleft_text = diploma_get_signature_text($diploma, 'left');
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SIGNATURE, $sigleftx, $siglefty, 60, '', 'left');

$signaturecenter_text = diploma_get_signature_text($diploma, 'center');
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SIGNATURE, $sigcenterx, $sigcentery, 60, '', 'center');

$signatureright_text = diploma_get_signature_text($diploma, 'right');
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SIGNATURE, $sigrightx, $sigrighty, 60, '', 'right');

//signatures text must be html in order to be able to set text centered
$html =
	'<table>
		<tr>
			<td align="center">'. $signatureleft_text .'</td>
			<td align="center">'. $signaturecenter_text .'</td>
			<td align="center">'. $signatureright_text .'</td>
		</tr>
	</table>';

//$pdf->writeHTML($html, true, false, true, false, '');
diploma_print_text($pdf, 7.5, $siglefty + 25, '', 'freeserif', '', 10, $html);



//////////////
// Add text //
//////////////

diploma_print_text($pdf, $colegex, $colegey, 'C', 'freesans', '', 18, $colege_sentence);
diploma_print_text($pdf, $certificatex, $certificatey, 'C', 'freesans', '', 12, $certificates_sentence);
$pdf->SetTextColor(0, 0, 60);
diploma_print_text($pdf, $usernamex, $usernamey, 'C', 'freesans', '', 18, fullname($diploma_user));
$pdf->SetTextColor(0, 0, 0);
diploma_print_text($pdf, $participationx, $participationy, 'L', 'freesans', '', 12, $participation_sentence);
diploma_print_text($pdf, $gradingx, $gradingy, 'L', 'freesans', '', 12, $grading_sentence);
diploma_print_text($pdf, $datex, $datey, 'L', 'freesans', '', 12, $date_sentence);
diploma_print_text($pdf, $programx, $programy, 'L', 'freesans', '', 12, $program_sentence);
diploma_print_text($pdf, $expeditionx, $expeditiony, 'C', 'freesans', '', 10, $expedition_sentence);
diploma_print_text($pdf, $documentx, $documenty, 'C', 'freesans', '', 10, $document_sentence);
diploma_print_text($pdf, $validationx, $validationy, 'C', 'freesans', '', 10, $validation_sentence);



?>