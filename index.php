<?php
// This file is part of Moodle - http://moodle.org/
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
 * Originality reports main page
 *
 * @package    report_originality
 * @copyright  2025 Mattan Dor (CentricApp)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/originality/lib.php');
require_once($CFG->dirroot.'/report/originality/classes/form/report_form.php');
require_once($CFG->libdir.'/pdflib.php');

// Check permissions.
require_login();
$context = context_system::instance();
require_capability('report/originality:view', $context);

// Setup page.
$PAGE->set_context($context);
$PAGE->set_url('/report/originality/index.php');
$PAGE->set_title(get_string('pluginname', 'report_originality'));
$PAGE->set_heading(get_string('pluginname', 'report_originality'));
$PAGE->set_pagelayout('report');

// Get filter form.
$mform = new report_originality_form();

// Process form data.
if ($formdata = $mform->get_data()) {
    $mingrade = isset($formdata->mingrade) ? $formdata->mingrade : 0;
    $maxgrade = isset($formdata->maxgrade) ? $formdata->maxgrade : 100;
    $startdate = isset($formdata->startdate) ? $formdata->startdate : 0;
    $enddate = isset($formdata->enddate) ? $formdata->enddate : time();
    $reporttype = isset($formdata->reporttype) ? $formdata->reporttype : 'teachers_no_reports';
} else {
    // Default values.
    $mingrade = optional_param('mingrade', 0, PARAM_INT);
    $maxgrade = optional_param('maxgrade', 100, PARAM_INT);
    $startdate = optional_param('startdate', 0, PARAM_INT);
    $enddate = optional_param('enddate', time(), PARAM_INT);
    $reporttype = optional_param('reporttype', 'teachers_no_reports', PARAM_RAW);
}

// Check if we need to export
$export = optional_param('export', '', PARAM_ALPHA);
if ($export === 'pdf') {
    export_report_to_pdf($reporttype, $mingrade, $maxgrade, $startdate, $enddate);
    exit;
} else if ($export === 'csv') {
    export_report_to_csv($reporttype, $mingrade, $maxgrade, $startdate, $enddate);
    exit;
}

// Output starts here.
echo $OUTPUT->header();

// Add settings link for administrators
if (has_capability('moodle/site:config', context_system::instance())) {
    $settingsurl = new moodle_url('/admin/settings.php', array('section' => 'reportoriginalitysettings'));
    echo html_writer::div(
        html_writer::link(
            $settingsurl,
            get_string('settings', 'report_originality'),
            array('class' => 'btn btn-secondary mb-3')
        ),
        'text-right'
    );
}

// Display the filter form.
$mform->display();

// Get data based on report type
switch ($reporttype) {
    case 'teachers_no_reports':
        $data = report_originality_get_teachers_no_reports($mingrade, $maxgrade, $startdate, $enddate);
        display_teachers_report($data);
        break;
    case 'lowest_grade_courses':
        $data = report_originality_get_lowest_grade_courses($mingrade, $maxgrade, $startdate, $enddate);
        display_courses_report($data);
        break;
    case 'lowest_grade_students':
        $data = report_originality_get_lowest_grade_students($mingrade, $maxgrade, $startdate, $enddate);
        display_students_report($data);
        break;
    case 'submission_count':
        $data = report_originality_get_submission_count($mingrade, $maxgrade, $startdate, $enddate);
        display_submission_count_report($data);
        break;
    case 'average_originality_score':
        $data = report_originality_get_average_originality_score($mingrade, $maxgrade, $startdate, $enddate);
        display_average_originality_score_report($data);
        break;
    default:
        // Default to teachers_no_reports if an invalid report type is provided
        $data = report_originality_get_teachers_no_reports($mingrade, $maxgrade, $startdate, $enddate);
        display_teachers_report($data);
        break;
}

// Add export to PDF button
$exportpdfurl = new moodle_url('/report/originality/index.php', array(
    'export' => 'pdf',
    'reporttype' => $reporttype,
    'mingrade' => $mingrade,
    'maxgrade' => $maxgrade,
    'startdate' => $startdate,
    'enddate' => $enddate
));

// Add export to CSV button
$exportcsvurl = new moodle_url('/report/originality/index.php', array(
    'export' => 'csv',
    'reporttype' => $reporttype,
    'mingrade' => $mingrade,
    'maxgrade' => $maxgrade,
    'startdate' => $startdate,
    'enddate' => $enddate
));

echo html_writer::div(
    html_writer::link(
        $exportpdfurl,
        get_string('exportpdf', 'report_originality'),
        array('class' => 'btn btn-secondary mr-2', 'target' => '_blank')
    ) .
    html_writer::link(
        $exportcsvurl,
        get_string('exportcsv', 'report_originality'),
        array('class' => 'btn btn-secondary', 'target' => '_blank')
    ),
    'mt-3'
);

echo $OUTPUT->footer();

/**
 * Display teachers report
 *
 * @param array $teachers Array of teacher objects
 */
function display_teachers_report($teachers) {
    global $OUTPUT;
    
    if (empty($teachers)) {
        echo $OUTPUT->notification(get_string('nocoursesfound', 'report_originality'), 'notifymessage');
        return;
    }
    
    $table = new html_table();
    $table->head = array(
        get_string('fullname'),
        get_string('email', 'report_originality'),
        get_string('department', 'report_originality'),
        get_string('lastaccess', 'report_originality')
    );
    $table->attributes['class'] = 'generaltable';
    $table->data = array();
    
    foreach ($teachers as $teacher) {
        $row = array();
        $row[] = fullname($teacher);
        $row[] = $teacher->email;
        $row[] = $teacher->department;
        $row[] = $teacher->lastaccess ? userdate($teacher->lastaccess) : get_string('never', 'report_originality');
        $table->data[] = $row;
    }
    
    echo html_writer::table($table);
}

/**
 * Display courses report
 *
 * @param array $courses Array of course objects
 */
function display_courses_report($courses) {
    global $OUTPUT;
    
    if (empty($courses)) {
        echo $OUTPUT->notification(get_string('nocoursesfound', 'report_originality'), 'notifymessage');
        return;
    }
    
    $table = new html_table();
    $table->head = array(
        get_string('coursename', 'report_originality'),
        get_string('enrolledstudents', 'report_originality'),
        get_string('averagegrade', 'report_originality'),
        get_string('completionrate', 'report_originality'),
        get_string('startdate', 'report_originality')
    );
    $table->attributes['class'] = 'generaltable';
    $table->data = array();
    
    foreach ($courses as $course) {
        $row = array();
        $row[] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname);
        $row[] = $course->enrolledstudents;
        $row[] = number_format($course->averagegrade, 2);
        $row[] = number_format($course->completionrate, 2);
        $row[] = userdate($course->startdate, get_string('strftimedatefullshort', 'core_langconfig'));
        $table->data[] = $row;
    }
    
    echo html_writer::table($table);
}

/**
 * Display students report
 *
 * @param array $students Array of student objects
 */
function display_students_report($students) {
    global $OUTPUT;
    
    if (empty($students)) {
        echo $OUTPUT->notification(get_string('nocoursesfound', 'report_originality'), 'notifymessage');
        return;
    }
    
    $table = new html_table();
    $table->head = array(
        get_string('fullname'),
        get_string('email', 'report_originality'),
        get_string('averagegrade', 'report_originality'),
        get_string('coursescount', 'report_originality'),
        get_string('lastaccess', 'report_originality')
    );
    $table->attributes['class'] = 'generaltable';
    $table->data = array();
    
    foreach ($students as $student) {
        $row = array();
        $row[] = fullname($student);
        $row[] = $student->email;
        $row[] = number_format($student->averagegrade, 2);
        $row[] = $student->coursescount;
        $row[] = $student->lastaccess ? userdate($student->lastaccess) : get_string('never', 'report_originality');
        $table->data[] = $row;
    }
    
    echo html_writer::table($table);
}

/**
 * Display submission count report
 *
 * @param array $courses Array of course objects with submission counts
 */
function display_submission_count_report($courses) {
    global $OUTPUT;
    
    if (empty($courses)) {
        echo $OUTPUT->notification(get_string('nocoursesfound', 'report_originality'), 'notifymessage');
        return;
    }
    
    // Display total submission count across all courses
    $totalSubmissions = 0;
    foreach ($courses as $course) {
        $totalSubmissions += $course->submission_count;
    }
    
    echo html_writer::tag('div', 
        html_writer::tag('h3', get_string('submission_count', 'report_originality') . ': ' . $totalSubmissions) .
        html_writer::tag('p', get_string('coursescount', 'report_originality') . ': ' . count($courses)),
        array('class' => 'alert alert-info')
    );
    
    $table = new html_table();
    $table->head = array(
        get_string('coursename', 'report_originality'),
        get_string('submission_count', 'report_originality'),
        get_string('details', 'report_originality')
    );
    $table->attributes['class'] = 'generaltable';
    $table->data = array();
    
    foreach ($courses as $course) {
        $row = array();
        $row[] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname);
        $row[] = $course->submission_count;
        
        // Create a details list of modules and their submission counts
        $moduledetails = '';
        if (!empty($course->modules)) {
            $moduledetails = html_writer::start_tag('ul', array('class' => 'module-details'));
            foreach ($course->modules as $module) {
                $moduledetails .= html_writer::tag('li', 
                    $module->name . ': ' . $module->submission_count);
            }
            $moduledetails .= html_writer::end_tag('ul');
        }
        $row[] = $moduledetails;
        
        $table->data[] = $row;
    }
    
    echo html_writer::table($table);
}

/**
 * Display average originality score report
 *
 * @param array $courses Array of course objects with originality scores
 */
function display_average_originality_score_report($courses) {
    global $OUTPUT;
    
    if (empty($courses)) {
        echo $OUTPUT->notification(get_string('nocoursesfound', 'report_originality'), 'notifymessage');
        return;
    }
    
    // Display the overall average first
    if (isset($courses[0]) && $courses[0]->id === 0) {
        $overall = $courses[0];
        echo html_writer::tag('div', 
            html_writer::tag('h3', get_string('average_originality_score', 'report_originality') . ': ' . 
            number_format($overall->avg_originality_score, 2)) . 
            html_writer::tag('p', get_string('submissions', 'report_originality') . ': ' . $overall->total_submissions),
            array('class' => 'alert alert-info')
        );
        
        // Remove the overall entry from the courses array
        array_shift($courses);
    }
    
    // If there are no courses left after removing the overall entry
    if (empty($courses)) {
        return;
    }
    
    $table = new html_table();
    $table->head = array(
        get_string('coursename', 'report_originality'),
        get_string('originality_score', 'report_originality'),
        get_string('submissions', 'report_originality'),
        get_string('details', 'report_originality')
    );
    $table->attributes['class'] = 'generaltable';
    $table->data = array();
    
    foreach ($courses as $course) {
        $row = array();
        $row[] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname);
        $row[] = number_format($course->avg_originality_score, 2);
        $row[] = $course->total_submissions;
        
        // Create a details list of modules and their originality scores
        $moduledetails = '';
        if (!empty($course->modules)) {
            $moduledetails = html_writer::start_tag('ul', array('class' => 'module-details'));
            foreach ($course->modules as $module) {
                $moduledetails .= html_writer::tag('li', 
                    $module->name . ': ' . number_format($module->avg_score, 2) . ' (' . 
                    get_string('submissions', 'report_originality') . ': ' . $module->submission_count . ')');
            }
            $moduledetails .= html_writer::end_tag('ul');
        }
        $row[] = $moduledetails;
        
        $table->data[] = $row;
    }
    
    echo html_writer::table($table);
}

/**
 * Export the current report to PDF
 *
 * @param string $reporttype The type of report to export
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Maximum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 */
function export_report_to_pdf($reporttype, $mingrade, $maxgrade, $startdate, $enddate) {
    global $CFG, $USER, $SESSION, $DB;
    
    // Validate report type to ensure it's one of the allowed types
    $validReportTypes = array(
        'teachers_no_reports',
        'lowest_grade_courses',
        'lowest_grade_students',
        'submission_count',
        'average_originality_score'
    );
    
    if (!in_array($reporttype, $validReportTypes)) {
        $reporttype = 'teachers_no_reports'; // Default to a safe option if invalid
    }
    
    // Get report data
    $data = array();
    switch ($reporttype) {
        case 'teachers_no_reports':
            $data = report_originality_get_teachers_no_reports($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('teachers_no_reports', 'report_originality');
            break;
        case 'lowest_grade_courses':
            $data = report_originality_get_lowest_grade_courses($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('lowest_grade_courses', 'report_originality');
            break;
        case 'lowest_grade_students':
            $data = report_originality_get_lowest_grade_students($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('lowest_grade_students', 'report_originality');
            break;
        case 'submission_count':
            $data = report_originality_get_submission_count($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('submission_count', 'report_originality');
            break;
        case 'average_originality_score':
            $data = report_originality_get_average_originality_score($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('average_originality_score', 'report_originality');
            break;
    }
    
    // If no data, return empty PDF with message
    if (empty($data)) {
        $pdf = new pdf();
        $pdf->SetTitle(get_string('pdfreporttitle', 'report_originality'));
        $pdf->SetAuthor(fullname($USER));
        $pdf->SetCreator(get_string('pluginname', 'report_originality'));
        
        // Check if current language is Hebrew or Arabic and set RTL mode
        $currentlang = current_language();
        if ($currentlang == 'he' || $currentlang == 'ar') {
            $pdf->setRTL(true);
        } else {
            $pdf->setRTL(false);
        }

        // Always use freesans font
        $pdf->SetFont('freesans', '', 12);
        
        $pdf->AddPage();
        $pdf->Cell(0, 10, get_string('nocoursesfound', 'report_originality'), 0, 1, 'C');
        
        // Add footer
        $footertext = get_config('report_originality', 'footertext');
        if (!empty($footertext)) {
            $pdf->SetY(-15);
            $pdf->SetFont('freesans', 'I', 8);
            $pdf->Cell(0, 10, $footertext, 0, 0, 'C');
        }
        
        // Generate filename with date and time
        $filename = get_string('pdfreporttitle', 'report_originality') . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
    
    // Create PDF document
    $pdf = new pdf();
    $pdf->SetTitle(get_string('pdfreporttitle', 'report_originality'));
    $pdf->SetAuthor(fullname($USER));
    $pdf->SetCreator(get_string('pluginname', 'report_originality'));
    
    // Check if current language is Hebrew or Arabic and set RTL mode
    $currentlang = current_language();
    $isRTL = ($currentlang == 'he' || $currentlang == 'ar');
    if ($isRTL) {
        $pdf->setRTL(true);
    } else {
        $pdf->setRTL(false);
    }
    
    // Always use freesans font with bold style for title
    $pdf->SetFont('freesans', 'B', 16);
    
    // Set up custom footer
    $footertext = get_config('report_originality', 'footertext');
    // We'll add the footer manually at the end of each page since SetFooterCallback is not available
    
    $pdf->AddPage();
    
    // Add logo if available
    $fs = get_file_storage();
    $context = context_system::instance();
    $files = $fs->get_area_files($context->id, 'report_originality', 'logo', 0, 'sortorder', false);
    
    if (!empty($files)) {
        $file = reset($files);
        $logopath = $CFG->tempdir . '/' . $file->get_filename();
        $file->copy_content_to($logopath);
        
        // Add logo to PDF
        $pdf->Image($logopath, 10, 10, 0, 20); // Adjust size as needed
        $pdf->Ln(25); // Add space after logo
    }
    
    // Add title and subtitle
    $pdf->Cell(0, 10, get_string('pdfreporttitle', 'report_originality') . ': ' . $reporttitle, 0, 1, 'C');
    
    // Use freesans with italic style for subtitle
    $pdf->SetFont('freesans', 'I', 10);
    
    $pdf->Cell(0, 10, get_string('pdfreportsubtitle', 'report_originality', userdate(time())), 0, 1, 'C');
    
    // Add filter information
    // Use freesans with bold style for filter header
    $pdf->SetFont('freesans', 'B', 12);
    
    $pdf->Cell(0, 10, get_string('pdfreportfilters', 'report_originality'), 0, 1);
    
    // Use freesans with normal style for filter details
    $pdf->SetFont('freesans', '', 10);
    
    $gradefilter = new stdClass();
    $gradefilter->min = $mingrade;
    $gradefilter->max = $maxgrade;
    $pdf->Cell(0, 6, get_string('pdfreportfiltergrade', 'report_originality', $gradefilter), 0, 1);
    
    $datefilter = new stdClass();
    $datefilter->start = userdate($startdate);
    $datefilter->end = userdate($enddate);
    $pdf->Cell(0, 6, get_string('pdfreportfilterdate', 'report_originality', $datefilter), 0, 1);
    
    $pdf->Ln(10);
    
    // Generate table based on report type
    try {
        switch ($reporttype) {
            case 'teachers_no_reports':
                export_teachers_report_to_pdf($pdf, $data, $isRTL);
                break;
            case 'lowest_grade_courses':
                export_courses_report_to_pdf($pdf, $data, $isRTL);
                break;
            case 'lowest_grade_students':
                export_students_report_to_pdf($pdf, $data, $isRTL);
                break;
            case 'submission_count':
                export_submission_count_report_to_pdf($pdf, $data, $isRTL);
                break;
            case 'average_originality_score':
                export_average_originality_score_report_to_pdf($pdf, $data, $isRTL);
                break;
        }
    } catch (Exception $e) {
        // If there's an error during PDF generation, add an error message
        $pdf->SetFont('freesans', 'B', 12);
        $pdf->Cell(0, 10, 'Error generating report: ' . $e->getMessage(), 0, 1, 'C');
    }

    // Add footer
    $footertext = get_config('report_originality', 'footertext');
    if (!empty($footertext)) {
        $pdf->SetY(-15);
        $pdf->SetFont('freesans', 'I', 8);
        $pdf->Cell(0, 10, $footertext, 0, 0, 'C');
    }
    
    // Generate filename with date and time
    $filename = get_string('pdfreporttitle', 'report_originality') . '_' . $reporttype . '_' . date('Y-m-d_H-i-s') . '.pdf';
    
    // Output PDF
    $pdf->Output($filename, 'D');
    exit;
}

// Update all export functions to use freesans font
function export_teachers_report_to_pdf($pdf, $teachers, $isRTL) {
    // Set up table headers
    $pdf->SetFont('freesans', 'B', 10);
    
    $pdf->Cell(60, 7, get_string('fullname'), 1, 0, 'C');
    $pdf->Cell(70, 7, get_string('email', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('department', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('lastaccess', 'report_originality'), 1, 1, 'C');
    
    // Add data rows
    $pdf->SetFont('freesans', '', 9);
    
    foreach ($teachers as $teacher) {
        $pdf->Cell(60, 6, fullname($teacher), 1, 0);
        $pdf->Cell(70, 6, $teacher->email, 1, 0);
        $pdf->Cell(30, 6, $teacher->department, 1, 0);
        // Use short date format for last access
        $lastaccess = $teacher->lastaccess ? userdate($teacher->lastaccess, '%d/%m/%y') : get_string('never', 'report_originality');
        $pdf->Cell(30, 6, $lastaccess, 1, 1);
    }
}

function export_courses_report_to_pdf($pdf, $courses, $isRTL) {
    // Set up table headers
    $pdf->SetFont('freesans', 'B', 10);
    
    $pdf->Cell(70, 7, get_string('coursename', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('enrolledstudents', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('averagegrade', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('completionrate', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('startdate', 'report_originality'), 1, 1, 'C');
    
    // Add data rows
    $pdf->SetFont('freesans', '', 9);
    
    foreach ($courses as $course) {
        $pdf->Cell(70, 6, $course->fullname, 1, 0);
        $pdf->Cell(30, 6, $course->enrolledstudents, 1, 0, 'C');
        $pdf->Cell(30, 6, number_format($course->averagegrade, 2), 1, 0, 'C');
        $pdf->Cell(30, 6, number_format($course->completionrate, 2), 1, 0, 'C');
        // Use short date format for start date
        $pdf->Cell(30, 6, userdate($course->startdate, '%d/%m/%y'), 1, 1);
    }
}

function export_students_report_to_pdf($pdf, $students, $isRTL) {
    // Set up table headers
    $pdf->SetFont('freesans', 'B', 10);
    
    $pdf->Cell(60, 7, get_string('fullname'), 1, 0, 'C');
    $pdf->Cell(70, 7, get_string('email', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(20, 7, get_string('averagegrade', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(20, 7, get_string('coursescount', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(20, 7, get_string('lastaccess', 'report_originality'), 1, 1, 'C');
    
    // Add data rows
    $pdf->SetFont('freesans', '', 9);
    
    foreach ($students as $student) {
        $pdf->Cell(60, 6, fullname($student), 1, 0);
        $pdf->Cell(70, 6, $student->email, 1, 0);
        $pdf->Cell(20, 6, number_format($student->averagegrade, 2), 1, 0, 'C');
        $pdf->Cell(20, 6, $student->coursescount, 1, 0, 'C');
        // Use short date format for last access
        $lastaccess = $student->lastaccess ? userdate($student->lastaccess, '%d/%m/%y') : get_string('never', 'report_originality');
        $pdf->Cell(20, 6, $lastaccess, 1, 1);
    }
}

function export_submission_count_report_to_pdf($pdf, $courses, $isRTL) {
    // Calculate total submissions
    $totalSubmissions = 0;
    foreach ($courses as $course) {
        $totalSubmissions += $course->submission_count;
    }
    
    // Add summary information
    $pdf->SetFont('freesans', 'B', 12);
    
    $pdf->Cell(0, 10, get_string('submission_count', 'report_originality') . ': ' . $totalSubmissions, 0, 1);
    $pdf->Cell(0, 10, get_string('coursescount', 'report_originality') . ': ' . count($courses), 0, 1);
    $pdf->Ln(5);
    
    // Set up table headers
    $pdf->SetFont('freesans', 'B', 10);
    
    $pdf->Cell(120, 7, get_string('coursename', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(70, 7, get_string('submission_count', 'report_originality'), 1, 1, 'C');
    
    // Add data rows
    $pdf->SetFont('freesans', '', 9);
    
    foreach ($courses as $course) {
        $pdf->Cell(120, 6, $course->fullname, 1, 0);
        $pdf->Cell(70, 6, $course->submission_count, 1, 1, 'C');
    }
}

function export_average_originality_score_report_to_pdf($pdf, $courses, $isRTL) {
    // Display the overall average first if available
    if (isset($courses[0]) && $courses[0]->id === 0) {
        $overall = $courses[0];
        
        $pdf->SetFont('freesans', 'B', 12);
        
        $pdf->Cell(0, 10, get_string('average_originality_score', 'report_originality') . ': ' . 
                  number_format($overall->avg_originality_score, 2), 0, 1);
        $pdf->Cell(0, 10, get_string('submissions', 'report_originality') . ': ' . $overall->total_submissions, 0, 1);
        $pdf->Ln(5);
        
        // Remove the overall entry from the courses array
        array_shift($courses);
    }
    
    // If no courses left, return
    if (empty($courses)) {
        return;
    }
    
    // Set up table headers
    $pdf->SetFont('freesans', 'B', 10);
    
    $pdf->Cell(100, 7, get_string('coursename', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(45, 7, get_string('originality_score', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(45, 7, get_string('submissions', 'report_originality'), 1, 1, 'C');
    
    // Add data rows
    $pdf->SetFont('freesans', '', 9);
    
    foreach ($courses as $course) {
        $pdf->Cell(100, 6, $course->fullname, 1, 0);
        $pdf->Cell(45, 6, number_format($course->avg_originality_score, 2), 1, 0, 'C');
        $pdf->Cell(45, 6, $course->total_submissions, 1, 1, 'C');
    }
}

/**
 * Export the current report to CSV
 *
 * @param string $reporttype The type of report to export
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Maximum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 */
function export_report_to_csv($reporttype, $mingrade, $maxgrade, $startdate, $enddate) {
    global $CFG, $USER;
    
    // Validate report type to ensure it's one of the allowed types
    $validReportTypes = array(
        'teachers_no_reports',
        'lowest_grade_courses',
        'lowest_grade_students',
        'submission_count',
        'average_originality_score'
    );
    
    if (!in_array($reporttype, $validReportTypes)) {
        $reporttype = 'teachers_no_reports'; // Default to a safe option if invalid
    }
    
    // Get report data
    $data = array();
    switch ($reporttype) {
        case 'teachers_no_reports':
            $data = report_originality_get_teachers_no_reports($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('teachers_no_reports', 'report_originality');
            break;
        case 'lowest_grade_courses':
            $data = report_originality_get_lowest_grade_courses($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('lowest_grade_courses', 'report_originality');
            break;
        case 'lowest_grade_students':
            $data = report_originality_get_lowest_grade_students($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('lowest_grade_students', 'report_originality');
            break;
        case 'submission_count':
            $data = report_originality_get_submission_count($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('submission_count', 'report_originality');
            break;
        case 'average_originality_score':
            $data = report_originality_get_average_originality_score($mingrade, $maxgrade, $startdate, $enddate);
            $reporttitle = get_string('average_originality_score', 'report_originality');
            break;
    }
    
    // Generate filename with date and time
    $filename = clean_filename(get_string('pdfreporttitle', 'report_originality') . '_' . $reporttype . '_' . date('Y-m-d_H-i-s') . '.csv');
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    // Create a file handle for output
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Generate CSV based on report type
    switch ($reporttype) {
        case 'teachers_no_reports':
            export_teachers_report_to_csv($output, $data);
            break;
        case 'lowest_grade_courses':
            export_courses_report_to_csv($output, $data);
            break;
        case 'lowest_grade_students':
            export_students_report_to_csv($output, $data);
            break;
        case 'submission_count':
            export_submission_count_report_to_csv($output, $data);
            break;
        case 'average_originality_score':
            export_average_originality_score_report_to_csv($output, $data);
            break;
    }
    
    fclose($output);
    exit;
}

/**
 * Export teachers report to CSV
 *
 * @param resource $output File handle for output
 * @param array $teachers Array of teacher objects
 */
function export_teachers_report_to_csv($output, $teachers) {
    // Write headers
    fputcsv($output, array(
        get_string('fullname'),
        get_string('email', 'report_originality'),
        get_string('department', 'report_originality'),
        get_string('lastaccess', 'report_originality')
    ));
    
    // Write data rows
    foreach ($teachers as $teacher) {
        // Use short date format for last access
        $lastaccess = $teacher->lastaccess ? userdate($teacher->lastaccess, '%d/%m/%y') : get_string('never', 'report_originality');
        fputcsv($output, array(
            fullname($teacher),
            $teacher->email,
            $teacher->department,
            $lastaccess
        ));
    }
}

/**
 * Export courses report to CSV
 *
 * @param resource $output File handle for output
 * @param array $courses Array of course objects
 */
function export_courses_report_to_csv($output, $courses) {
    // Write headers
    fputcsv($output, array(
        get_string('coursename', 'report_originality'),
        get_string('enrolledstudents', 'report_originality'),
        get_string('averagegrade', 'report_originality'),
        get_string('completionrate', 'report_originality'),
        get_string('startdate', 'report_originality')
    ));
    
    // Write data rows
    foreach ($courses as $course) {
        fputcsv($output, array(
            $course->fullname,
            $course->enrolledstudents,
            number_format($course->averagegrade, 2),
            number_format($course->completionrate, 2),
            userdate($course->startdate, '%d/%m/%y')
        ));
    }
}

/**
 * Export students report to CSV
 *
 * @param resource $output File handle for output
 * @param array $students Array of student objects
 */
function export_students_report_to_csv($output, $students) {
    // Write headers
    fputcsv($output, array(
        get_string('fullname'),
        get_string('email', 'report_originality'),
        get_string('averagegrade', 'report_originality'),
        get_string('coursescount', 'report_originality'),
        get_string('lastaccess', 'report_originality')
    ));
    
    // Write data rows
    foreach ($students as $student) {
        // Use short date format for last access
        $lastaccess = $student->lastaccess ? userdate($student->lastaccess, '%d/%m/%y') : get_string('never', 'report_originality');
        fputcsv($output, array(
            fullname($student),
            $student->email,
            number_format($student->averagegrade, 2),
            $student->coursescount,
            $lastaccess
        ));
    }
}

/**
 * Export submission count report to CSV
 *
 * @param resource $output File handle for output
 * @param array $courses Array of course objects with submission counts
 */
function export_submission_count_report_to_csv($output, $courses) {
    // Calculate total submissions
    $totalSubmissions = 0;
    foreach ($courses as $course) {
        $totalSubmissions += $course->submission_count;
    }
    
    // Write summary information
    fputcsv($output, array(get_string('submission_count', 'report_originality'), $totalSubmissions));
    fputcsv($output, array(get_string('coursescount', 'report_originality'), count($courses)));
    fputcsv($output, array()); // Empty line
    
    // Write headers
    fputcsv($output, array(
        get_string('coursename', 'report_originality'),
        get_string('submission_count', 'report_originality')
    ));
    
    // Write data rows
    foreach ($courses as $course) {
        fputcsv($output, array(
            $course->fullname,
            $course->submission_count
        ));
    }
}

/**
 * Export average originality score report to CSV
 *
 * @param resource $output File handle for output
 * @param array $courses Array of course objects with originality scores
 */
function export_average_originality_score_report_to_csv($output, $courses) {
    // Display the overall average first if available
    if (isset($courses[0]) && $courses[0]->id === 0) {
        $overall = $courses[0];
        fputcsv($output, array(
            get_string('average_originality_score', 'report_originality'),
            number_format($overall->avg_originality_score, 2)
        ));
        fputcsv($output, array(
            get_string('submissions', 'report_originality'),
            $overall->total_submissions
        ));
        fputcsv($output, array()); // Empty line
        
        // Remove the overall entry from the courses array
        array_shift($courses);
    }
    
    // If no courses left, return
    if (empty($courses)) {
        return;
    }
    
    // Write headers
    fputcsv($output, array(
        get_string('coursename', 'report_originality'),
        get_string('originality_score', 'report_originality'),
        get_string('submissions', 'report_originality')
    ));
    
    // Write data rows
    foreach ($courses as $course) {
        fputcsv($output, array(
            $course->fullname,
            number_format($course->avg_originality_score, 2),
            $course->total_submissions
        ));
    }
}
