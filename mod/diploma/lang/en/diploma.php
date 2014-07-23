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
 * Language strings for the diploma module
 *
 * @package    mod
 * @subpackage diploma
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addlinklabel'] = 'Add another linked activity option';
$string['addlinktitle'] = 'Click to add another linked activity option';
$string['areaintro'] = 'diploma introduction';
$string['awarded'] = 'Awarded';
$string['awardedto'] = 'Awarded To';
$string['back'] = 'Back';
$string['border'] = 'Border';
$string['borderblack'] = 'Black';
$string['borderblue'] = 'Blue';
$string['borderbrown'] = 'Brown';
$string['bordercolor'] = 'Border Lines';
$string['bordercolor_help'] = 'Since images can substantially increase the size of the pdf file, you may choose to print a border of lines instead of using a border image (be sure the Border Image option is set to No).  The Border Lines option will print a nice border of three lines of varying widths in the chosen color.';
$string['bordergreen'] = 'Green';
$string['borderlines'] = 'Lines';
$string['borderstyle'] = 'Border Image';
$string['borderstyle_help'] = 'The Border Image option allows you to choose a border image from the diploma/pix/borders folder.  Select the border image that you want around the diploma edges or select no border.';
$string['diploma'] = 'Verification for diploma code:';
$string['diploma:addinstance'] = 'Add a diploma instance';
$string['diploma:manage'] = 'Manage a diploma instance';
$string['diploma:printteacher'] = 'Be listed as a teacher on the diploma if the print teacher setting is on';
$string['diploma:student'] = 'Retrieve a diploma';
$string['diploma:view'] = 'View a diploma';
$string['diplomaname'] = 'diploma Name';
$string['diplomareport'] = 'diplomas Report';
$string['diplomasfor'] = 'diplomas for';
$string['diplomatype'] = 'diploma Type';
$string['diplomatype_help'] = 'This is where you determine the layout of the diploma. The diploma type folder includes four default diplomas:
A4 Embedded prints on A4 size paper with embedded font.
A4 Non-Embedded prints on A4 size paper without embedded fonts.
Letter Embedded prints on letter size paper with embedded font.
Letter Non-Embedded prints on letter size paper without embedded fonts.

The non-embedded types use the Helvetica and Times fonts.  If you feel your users will not have these fonts on their computer, or if your language uses characters or symbols that are not accommodated by the Helvetica and Times fonts, then choose an embedded type.  The embedded types use the Dejavusans and Dejavuserif fonts.  This will make the pdf files rather large; thus it is not recommended to use an embedded type unless you must.

New type folders can be added to the diploma/type folder. The name of the folder and any new language strings for the new type must be added to the diploma language file.';
$string['certify'] = 'This is to certify that';
$string['code'] = 'Code';
$string['completiondate'] = 'Course Completion';
$string['course'] = 'For';
$string['coursegrade'] = 'Course Grade';
$string['coursename'] = 'Course';
$string['coursetimereq'] = 'Required minutes in course';
$string['coursetimereq_help'] = 'Enter here the minimum amount of time, in minutes, that a student must be logged into the course before they will be able to receive the diploma.';
$string['credithours'] = 'Credit Hours';
$string['customtext'] = 'Custom Text';
$string['customtext_help'] = 'If you want the diploma to print different names for the teacher than those who are assigned
the role of teacher, do not select Print Teacher or any signature image except for the line image.  Enter the teacher names in this text box as you would like them to appear.  By default, this text is placed in the lower left of the diploma. The following html tags are available: &lt;br&gt;, &lt;p&gt;, &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;img&gt; (src and width (or height) are mandatory), &lt;a&gt; (href is mandatory), &lt;font&gt; (possible attributes are: color, (hex color code), face, (arial, times, courier, helvetica, symbol)).';
$string['date'] = 'On';
$string['startenddatefmt'] = 'Start and End Dates Format';
$string['startenddatefmt_help'] = 'Choose a start date and end date format to print on the diploma. Or, choose the last option to have the start and end dates printed in the format of the user\'s chosen language.';
$string['deliverydatefmt'] = 'Delivery Date Format';
$string['deliverydatefmt_help'] = 'Choose a delivery date format to print on the diploma. Or, choose the last option to have the delivery date printed in the format of the user\'s chosen language.';
$string['completiondatefmt'] = 'Completion Date Format';
$string['completiondatefmt_help'] = 'Choose a completion date format to print on the diploma. Or, choose the last option to have the completion date printed in the format of the user\'s chosen language.';
$string['datehelp'] = 'Date';
$string['deletissueddiplomas'] = 'Delete issued diplomas';
$string['delivery'] = 'Delivery';
$string['delivery_help'] = 'Choose here how you would like your students to get their diploma.
Open in Browser: Opens the diploma in a new browser window.
Force Download: Opens the browser file download window.
Email diploma: Choosing this option sends the diploma to the student as an email attachment.
After a user receives their diploma, if they click on the diploma link from the course homepage, they will see the date they received their diploma and will be able to review their received diploma.';
$string['designoptions'] = 'Design Options';
$string['download'] = 'Force download';
$string['emaildiploma'] = 'Email (Must also choose save!)';
$string['emailothers'] = 'Email Others';
$string['emailothers_help'] = 'Enter the email addresses here, separated by a comma, of those who should be alerted with an email whenever students receive a diploma.';
$string['emailstudenttext'] = 'Attached is your diploma for {$a->course}.';
$string['emailteachers'] = 'Email Teachers';
$string['emailteachers_help'] = 'If enabled, then teachers are alerted with an email whenever students receive a diploma.';
$string['emailteachermail'] = '
{$a->student} has received their diploma: \'{$a->diploma}\'
for {$a->course}.

You can review it here:

    {$a->url}';
$string['emailteachermailhtml'] = '
{$a->student} has received their diploma: \'<i>{$a->diploma}</i>\'
for {$a->course}.

You can review it here:

    <a href="{$a->url}">diploma Report</a>.';
$string['entercode'] = 'Enter diploma code to verify:';
$string['getdiploma'] = 'Get your diploma';
$string['grade'] = 'Grade';
$string['gradedate'] = 'Grade Date';
$string['gradefmt'] = 'Grade Format';
$string['gradefmt_help'] = 'There are three available formats if you choose to print a grade on the diploma:

Percentage Grade: Prints the grade as a percentage.
Points Grade: Prints the point value of the grade.
Letter Grade: Prints the percentage grade as a letter.';
$string['gradeletter'] = 'Letter Grade';
$string['gradepercent'] = 'Percentage Grade';
$string['gradepoints'] = 'Points Grade';
$string['header'] = 'Header';
$string['imagetype'] = 'Image Type';
$string['incompletemessage'] = 'In order to download your diploma, you must first complete all required '.'activities. Please return to the course to complete your coursework.';
$string['intro'] = 'Introduction';
$string['issueoptions'] = 'Issue Options';
$string['issued'] = 'Issued';
$string['issueddate'] = 'Date Issued';
$string['landscape'] = 'Landscape';
$string['lastviewed'] = 'You last received this diploma on:';
$string['letter'] = 'Letter';
$string['lockingoptions'] = 'Locking Options';
$string['modulename'] = 'Diploma';
$string['modulenameplural'] = 'Diplomas';
$string['mydiplomas'] = 'My diplomas';
$string['nodiplomas'] = 'There are no diplomas';
$string['nodiplomasissued'] = 'There are no diplomas that have been issued';
$string['nodiplomasreceived'] = 'has not received any course diplomas.';
$string['nofileselected'] = 'Must choose a file to upload!';
$string['nogrades'] = 'No grades available';
$string['notapplicable'] = 'N/A';
$string['notfound'] = 'The diploma number could not be validated.';
$string['notissued'] = 'Not Issued';
$string['notissuedyet'] = 'Not issued yet';
$string['notreceived'] = 'You have not received this diploma';
$string['openbrowser'] = 'Open in new window';
$string['opendownload'] = 'Click the button below to save your diploma to your computer.';
$string['openemail'] = 'Click the button below and your diploma will be sent to you as an email attachment.';
$string['openwindow'] = 'Click the button below to open your diploma in a new browser window.';
$string['or'] = 'Or';
$string['orientation'] = 'Orientation';
$string['orientation_help'] = 'Choose whether you want your diploma orientation to be portrait or landscape.';
$string['pluginadministration'] = 'diploma administration';
$string['pluginname'] = 'diploma';
$string['portrait'] = 'Portrait';
$string['startdate'] = 'Print Start Date';
$string['startdate_help'] = 'This is the course start date that will be printed, if a print start date is selected. If the course completion date is selected but the student has not completed the course, the start date received will be printed. You can also choose to print the date based on when an activity was graded. If a diploma is issued before that activity is graded, the date received will be printed.';
$string['enddate'] = 'Print End Date';
$string['enddate_help'] = 'This is the course end date that will be printed, if a print end date is selected. If the course completion date is selected but the student has not completed the course, the end date received will be printed. You can also choose to print the date based on when an activity was graded. If a diploma is issued before that activity is graded, the date received will be printed.';
$string['printerfriendly'] = 'Printer-friendly page';
$string['printhours'] = 'Print Credit Hours';
$string['printhours_help'] = 'Enter here the number of credit hours to be printed on the diploma.';
$string['printgrade'] = 'Print Grade';
$string['printgrade_help'] = 'You can choose any available course grade items from the gradebook to print the user\'s grade received for that item on the diploma.  The grade items are listed in the order in which they appear in the gradebook. Choose the format of the grade below.';
$string['printnumber'] = 'Print Code';
$string['printnumber_help'] = 'A unique 10-digit code of random letters and numbers can be printed on the diploma. This number can then be verified by comparing it to the code number displayed in the diplomas report.';
$string['printoutcome'] = 'Print Outcome';
$string['printoutcome_help'] = 'You can choose any course outcome to print the name of the outcome and the user\'s received outcome on the diploma.  An example might be: Assignment Outcome: Proficient.';
$string['sealoptions'] = 'Logos';
$string['printsealleft'] = 'Left Image';
$string['printsealleft_help'] = 'This option allows you to select a left seal or logo to print on the diploma from the diploma/pix/seals folder.';
$string['printsealcenter'] = 'Center Image';
$string['printsealcenter_help'] = 'This option allows you to select a center seal or logo to print on the diploma from the diploma/pix/seals folder.';
$string['printsealright'] = 'Right Image';
$string['printsealright_help'] = 'This option allows you to select a right seal or logo to print on the diploma from the diploma/pix/seals folder.';
$string['signatureleft'] = 'Left Signature';
$string['printsignatureleft'] = 'Image';
$string['printsignatureleft_help'] = 'This option allows you to print the left signature from the diploma/pix/signatures folder. You can print a graphic representation of a signature, or print a line for a written signature. By default, this image is placed in the lower left of the diploma.';
$string['signaturenameleft'] = 'Name';
$string['signaturenameleft_help'] = 'This option allows you to print a left signature name that the signature belongs to, this name is printed below the signature image.';
$string['signaturejobpositionleft'] = 'Job Position';
$string['signaturejobpositionleft_help'] = 'This option allows you to print the left job position of the person that the signature belongs to, this job position is printed below the signature name.';
$string['signatureinstitutionleft'] = 'Institution';
$string['signatureinstitutionleft_help'] = 'This option allows you to print the left institution name of the person that the signature belongs to, this institution name is printed below the signature name';
$string['signaturecenter'] = 'Center Signature';
$string['printsignaturecenter'] = 'Image';
$string['printsignaturecenter_help'] = 'This option allows you to print a center signature  from the diploma/pix/signatures folder.  You can print a graphic representation of a signature, or print a line for a written signature. By default, this image is placed in the lower left of the diploma.';
$string['signaturenamecenter'] = 'Name';
$string['signaturenamecenter_help'] = 'This option allows you to print a center signature name that the center signature belongs to, this name is printed below the second signature image.';
$string['signaturejobpositioncenter'] = 'Job Position';
$string['signaturejobpositioncenter_help'] = 'This option allows you to print the job position of the person that the center signature belongs to, this job position is printed below the second signature name.';
$string['signatureinstitutioncenter'] = 'Institution';
$string['signatureinstitutioncenter_help'] = 'This option allows you to print the institution name of the person that the center signature belongs to, this institution name is printed below the second signature name';
$string['signatureright'] = 'Right Signature';
$string['printsignatureright'] = 'Image';
$string['printsignatureright_help'] = 'This option allows you to print a right signature  from the diploma/pix/signatures folder.  You can print a graphic representation of a signature, or print a line for a written signature. By default, this image is placed in the lower left of the diploma.';
$string['signaturenameright'] = 'Name';
$string['signaturenameright_help'] = 'This option allows you to print a right signature name that the right signature belongs to, this name is printed below the third signature image.';
$string['signaturejobpositionright'] = 'Job Position';
$string['signaturejobpositionright_help'] = 'This option allows you to print the right job position of the person that the right signature belongs to, this job position is printed below the third signature name.';
$string['signatureinstitutionright'] = 'Institution';
$string['signatureinstitutionright_help'] = 'This option allows you to print the right institution name of the person that the right signature belongs to, this institution name is printed below the third signature name';
$string['printteacher'] = 'Print Teacher Name(s)';
$string['printteacher_help'] = 'For printing the teacher name on the diploma, set the role of teacher at the module level.  Do this if, for example, you have more than one teacher for the course or you have more than one diploma in the course and you want to print different teacher names on each diploma.  Click to edit the diploma, then click on the Locally assigned roles tab.  Then assign the role of Teacher (editing teacher) to the diploma (they do not HAVE to be a teacher in the course--you can assign that role to anyone).  Those names will be printed on the diploma for teacher.';
$string['printheader'] = 'Header Image';
$string['printheader_help'] = 'A header file can be placed in the top of the diploma.';
$string['printwmark'] = 'Watermark Image';
$string['printwmark_help'] = 'A watermark file can be placed in the background of the diploma. A watermark is a faded graphic. A watermark could be a logo, seal, crest, wording, or whatever you want to use as a graphic background.';
$string['receivedcerts'] = 'Received diplomas';
$string['receiveddate'] = 'Date Received';
$string['removecert'] = 'Issued diplomas removed';
$string['report'] = 'Report';
$string['reportcert'] = 'Report diplomas';
$string['reportcert_help'] = 'If you choose yes here, then this diploma\'s date received, code number, and the course name will be shown on the user diploma reports.  If you choose to print a grade on this diploma, then that grade will also be shown on the diploma report.';
$string['requiredtimenotmet'] = 'You must spend at least a minimum of {$a->requiredtime} minutes in the course before you can access this diploma';
$string['requiredtimenotvalid'] = 'The required time must be a valid number greater than 0';
$string['reviewdiploma'] = 'Review your diploma';
$string['savecert'] = 'Save diplomas';
$string['savecert_help'] = 'If you choose this option, then a copy of each user\'s diploma pdf file is saved in the course files moddata folder for that diploma. A link to each user\'s saved diploma will be displayed in the diploma report.';
$string['seal'] = 'Seal';
$string['sigline'] = 'line';
$string['signature'] = 'Signature';
$string['statement'] = 'has completed the course';
$string['summaryofattempts'] = 'Summary of Previously Received diplomas';
$string['textoptions'] = 'Text Options';
$string['title'] = 'diploma of ACHIEVEMENT';
$string['to'] = 'Awarded to';
$string['typeA4_embedded'] = 'A4 Embedded';
$string['typeA4_non_embedded'] = 'A4 Non-Embedded';
$string['typeletter_embedded'] = 'Letter Embedded';
$string['typeletter_non_embedded'] = 'Letter Non-Embedded';
$string['typeCOLAM_aprobado'] = 'COLAM aprobado';
$string['typeCOLAM_participacion'] = 'COLAM participación';
$string['unsupportedfiletype'] = 'File must be a jpeg or png file';
$string['uploadimage'] = 'Upload image';
$string['uploadimagedesc'] = 'This button will take you to a new screen where you will be able to upload images.';
$string['userdateformat'] = 'User\'s Language Date Format';
$string['validate'] = 'Verify';
$string['verifydiploma'] = 'Verify diploma';
$string['viewdiplomaviews'] = 'View {$a} issued diplomas';
$string['viewed'] = 'You received this diploma on:';
$string['viewtranscript'] = 'View diplomas';
$string['watermark'] = 'Watermark';

$string['coursealtname'] = 'Course alternative name';
$string['coursealtname_help'] = 'Set an alternative course name, if empty course name use instead';
$string['duration'] = 'Duration Text';
$string['duration_help'] = 'Free text field for custom course duration text';
$string['program'] = 'The program name';
$string['program_help'] = 'The program name printed in the diploma';
$string['edition'] = 'The edition name';
$string['edition_help'] = 'The edition name printed in the diploma';
$string['organization'] = 'The organization name';
$string['organization_help'] = 'The organization name printed in the diploma';

$string['left'] = 'Left';
$string['center'] = 'Center';
$string['right'] = 'Right';

$string['download_diploma'] = 'Download Diploma';


// Colam participation
$string['colege_sentence'] = '<b>El Colegio de las Américas de la</b><br/><b>Organización Universitaria Interamericana</b>';
$string['certificates_sentence'] = 'Certifica que:';
$string['pariticipation_sentence'] = '<span style="text-align:justify;">Participó como estudiante regular del Curso / Módulo {$a->course_name} del Programa en {$a->program_name}, impartido en su {$a->edition} edición.</span>';
$string['grading_sentence'] = '<span style="text-align:justify;">{$a->user_name} obtuvo una calificación aprobatoria de {$a->grade} en una escala de 1/100</span>';
$string['date_sentence'] = '<span style="text-align:justify;">Esta formación fue impartida en modalidad virtual del {$a->start_date} al {$a->end_date}, con una duración total de {$a->duration}.</span>';
$string['program_sentence'] = '<span style="text-align:justify;">El programa {$a->program_name} hace parte de la oferta formativa del Colegio de las Américas - COLAM de la Organización Universitaria Interamericana - OUI</span>';
$string['expedition_sentence'] = 'Dado en Montreal, Canadá, el {$a->expedition_date}.';
$string['document_sentence'] = 'Este documento es fiel copia del original que reposa en nuestros archivos';
$string['validation_sentence'] = 'Puede consultar su validez o descargar una copia ingresando al siguiente enlace:<br/> <a href="{$a->link}">{$a->link}</a>';

// Colam aprobado
$string['grants_sentence2'] = 'Otorga la presente constancia a:';
$string['passing_sentence2'] = 'Por haber aprobado el:';
$string['date_sentence2'] = 'Impartido en la modalidad en línea del {$a->start_date} al {$a->end_date}, con una duración de {$a->duration}.';
$string['expedition_sentence2'] = 'Quebec, Canadá {$a->expedition_date}';

//Tabs String
$string['standardview'] = 'Issue a test certificate';
$string['issuedview'] = 'Issued certificates';
$string['bulkview'] = 'Bulk operations';
$string['cantissue'] = 'The certificate can\'t be issued, because the user hasn\'t reached the course objectives';

//Issue list
$string['nocertificatesissued'] = 'There are no issued diplomas';

//Bulk texts
$string['onepdf'] = 'Download certificates in a one pdf file';
$string['multipdf'] = 'Download certificates in a zip file';
$string['sendtoemail'] = 'Send to user\'s email';
$string['showusers'] = 'Show';
$string['completedusers'] = 'Users that met the course objectives';
$string['allusers'] = 'All users';
$string['bulkaction'] = 'Choose a Bulk Operation';
$string['bulkbuttonlabel'] = 'Send';
$string['emailsent'] = 'The emails have been sent';
$string['noaction_specified'] = 'No action was specified';

$string['keywords'] = 'cetificate, course, pdf, moodle';

$string['issueddownload'] = 'Issued certificate [id: {$a}] downloaded';

$string['defaultperpage'] = 'Per page';
$string['defaultperpage_help'] = 'Number of certificate to show per page (Max. 200)';

//Emails text
$string['emailstudentsubject'] = 'Your certificate for {$a->course}';
$string['emailstudenttext'] = '
Hello {$a->username},

Below is your certificate download link for {$a->course}.

{$a->download_link}

THIS IS AN AUTOMATED MESSAGE - PLEASE DO NOT REPLY';

$string['emailteachermail'] = '
{$a->student} has received their certificate: \'{$a->certificate}\'
for {$a->course}.

You can review it here:

{$a->url}';

$string['emailteachermailhtml'] = '
{$a->student} has received their certificate: \'<i>{$a->certificate}</i>\'
for {$a->course}.

You can review it here:

<a href="{$a->url}">Certificate Report</a>.';

//Erros
$string['filenotfound'] = 'File not Found: {$a}';
$string['invalidcode'] = 'Invalid certificate code';
$string['cantdeleteissue'] = 'Error removing issued certificates';
$string['requiredtimenotmet'] = 'You must have at least {$a->requiredtime} minutes in this course to issue this certificate';


//Validation form
$string['page_heading'] = 'Verify Diploma';
$string['validation_code'] = 'Validation Code';
$string['validation_code_help'] = 'Enter the validation code located on the bottom side of your diploma.';
$string['enter_validation_code'] = 'Please enter diploma validation code';
$string['issue_notvalid'] = 'Diploma is not valid';
$string['issue_valid'] = 'Diploma is valid';
$string['go_back'] = 'Go Back';
$string['course_name'] = 'Course Name';
$string['startdate_heading'] = 'Start Date';
$string['completiondate_heading'] = 'Completion Date';
$string['issuedate_heading'] = 'Issue Date';


//History
$string['menu_user_diplomas'] = 'Diplomas';
$string['menu_user_history'] = 'History';
$string['history_heading'] = 'Course History';

$string['currentcourses_heading'] = 'Current Courses';
$string['pastcourses_heading'] = 'Past Courses';

$string['no_courses'] = 'There are no courses';

$string['th_coursename'] = 'Course Name';
$string['th_startdate'] = 'Start Date';
$string['th_enddate'] = 'End Date';
$string['th_currentgrade'] = 'Current Grade';
$string['th_endgrade'] = 'End Grade';
$string['th_timespent'] = 'Time Spent';
$string['th_achieveddiplomas'] = 'Diplomas';


