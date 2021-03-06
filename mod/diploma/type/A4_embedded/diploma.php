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

$pdf = new PDF($diploma->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($diploma->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// Landscape
if ($diploma->orientation == 'L') {
    $x = 10;
    $y = 30;
    $sealx = 230;
    $sealy = 150;
    $sigx = 47;
    $sigy = 155;
    $custx = 47;
    $custy = 155;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = 175;
} else { // Portrait
    $x = 10;
    $y = 40;
    $sealx = 150;
    $sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;
}

// Add images and lines
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
diploma_draw_frame($pdf, $diploma);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SEAL, $sealx, $sealy, '', '');
diploma_print_image($pdf, $diploma, DIPLOMA_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 120);
diploma_print_text($pdf, $x, $y, 'C', 'freesans', '', 30, get_string('title', 'diploma'));
$pdf->SetTextColor(0, 0, 0);
diploma_print_text($pdf, $x, $y + 20, 'C', 'freeserif', '', 20, get_string('certify', 'diploma'));
diploma_print_text($pdf, $x, $y + 36, 'C', 'freesans', '', 30, fullname($USER));
diploma_print_text($pdf, $x, $y + 55, 'C', 'freesans', '', 20, get_string('statement', 'diploma'));
diploma_print_text($pdf, $x, $y + 72, 'C', 'freesans', '', 20, $course->fullname);
diploma_print_text($pdf, $x, $y + 92, 'C', 'freesans', '', 14,  diploma_get_date($diploma, $certrecord, $course));
diploma_print_text($pdf, $x, $y + 102, 'C', 'freeserif', '', 10, diploma_get_grade($diploma, $course));
diploma_print_text($pdf, $x, $y + 112, 'C', 'freeserif', '', 10, diploma_get_outcome($diploma, $course));
if ($diploma->printhours) {
    diploma_print_text($pdf, $x, $y + 122, 'C', 'freeserif', '', 10, get_string('credithours', 'diploma') . ': ' . $diploma->printhours);
}
diploma_print_text($pdf, $x, $codey, 'C', 'freeserif', '', 10, diploma_get_code($diploma, $certrecord));
$i = 0;
if ($diploma->printteacher) {
    $context = context_module::instance($cm->id);
    if ($teachers = get_users_by_capability($context, 'mod/diploma:printteacher', '', $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $i++;
            diploma_print_text($pdf, $sigx, $sigy + ($i * 4), 'L', 'freeserif', '', 12, fullname($teacher));
        }
    }
}

diploma_print_text($pdf, $custx, $custy, 'L', null, null, null, $diploma->customtext);
?>