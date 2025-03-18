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
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/report/originality/lib.php');
require_once($CFG->dirroot . '/report/originality/classes/form/report_form.php');
require_once($CFG->libdir . '/pdflib.php');

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

// Check if we need to export to PDF
$export = optional_param('export', 0, PARAM_BOOL);
if ($export) {
    export_report_to_pdf($reporttype, $mingrade, $maxgrade, $startdate, $enddate);
    exit;
}

// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_originality'));

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
$exporturl = new moodle_url('/report/originality/index.php', array(
        'export' => 1,
        'reporttype' => $reporttype,
        'mingrade' => $mingrade,
        'maxgrade' => $maxgrade,
        'startdate' => $startdate,
        'enddate' => $enddate
));
echo html_writer::div(
        html_writer::link(
                $exporturl,
                get_string('exportpdf', 'report_originality'),
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
    global $CFG, $USER, $SESSION;

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

        // Check if current language is Hebrew and set RTL mode
        $currentlang = current_language();
        if ($currentlang == 'he') {
            $pdf->setRTL(true);
            $pdf->SetFont('freesans', '', 12); // FreeSans supports Hebrew
        } else {
            $pdf->setRTL(false);
            $pdf->SetFont('helvetica', '', 12);
        }

        $pdf->AddPage();
        $pdf->Cell(0, 10, get_string('nocoursesfound', 'report_originality'), 0, 1, 'C');
        $pdf->Output(get_string('pdfreporttitle', 'report_originality') . '.pdf', 'D');
        exit;
    }

    // Create PDF document
    $pdf = new pdf();
    $pdf->SetTitle(get_string('pdfreporttitle', 'report_originality'));
    $pdf->SetAuthor(fullname($USER));
    $pdf->SetCreator(get_string('pluginname', 'report_originality'));

    // Check if current language is Hebrew and set RTL mode
    $currentlang = current_language();
    $isRTL = ($currentlang == 'he');
    if ($isRTL) {
        $pdf->setRTL(true);
        $pdf->SetFont('freesans', 'B', 16); // FreeSans supports Hebrew
    } else {
        $pdf->setRTL(false);
        $pdf->SetFont('helvetica', 'B', 16);
    }

    $pdf->AddPage();

    // Add title and subtitle
    $pdf->Cell(0, 10, get_string('pdfreporttitle', 'report_originality') . ': ' . $reporttitle, 0, 1, 'C');

    if ($isRTL) {
        $pdf->SetFont('freesans', 'I', 10);
    } else {
        $pdf->SetFont('helvetica', 'I', 10);
    }

    $pdf->Cell(0, 10, get_string('pdfreportsubtitle', 'report_originality', userdate(time())), 0, 1, 'C');

    // Add filter information
    if ($isRTL) {
        $pdf->SetFont('freesans', 'B', 12);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
    }

    $pdf->Cell(0, 10, get_string('pdfreportfilters', 'report_originality'), 0, 1);

    if ($isRTL) {
        $pdf->SetFont('freesans', '', 10);
    } else {
        $pdf->SetFont('helvetica', '', 10);
    }

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
        if ($isRTL) {
            $pdf->SetFont('freesans', 'B', 12);
        } else {
            $pdf->SetFont('helvetica', 'B', 12);
        }
        $pdf->Cell(0, 10, 'Error generating report: ' . $e->getMessage(), 0, 1, 'C');
    }

    // Output PDF
    $pdf->Output(get_string('pdfreporttitle', 'report_originality') . '_' . $reporttype . '.pdf', 'D');
    exit;
}

// Update all export functions to use FreeSans for Hebrew
function export_teachers_report_to_pdf($pdf, $teachers, $isRTL) {
    // Set up table headers
    if ($isRTL) {
        $pdf->SetFont('freesans', 'B', 10);
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
    }

    $pdf->Cell(60, 7, get_string('fullname'), 1, 0, 'C');
    $pdf->Cell(70, 7, get_string('email', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('department', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('lastaccess', 'report_originality'), 1, 1, 'C');

    // Add data rows
    if ($isRTL) {
        $pdf->SetFont('freesans', '', 9);
    } else {
        $pdf->SetFont('helvetica', '', 9);
    }

    foreach ($teachers as $teacher) {
        $pdf->Cell(60, 6, fullname($teacher), 1, 0);
        $pdf->Cell(70, 6, $teacher->email, 1, 0);
        $pdf->Cell(30, 6, $teacher->department, 1, 0);
        $lastaccess = $teacher->lastaccess ? userdate($teacher->lastaccess) : get_string('never', 'report_originality');
        $pdf->Cell(30, 6, $lastaccess, 1, 1);
    }
}

function export_courses_report_to_pdf($pdf, $courses, $isRTL) {
    // Set up table headers
    if ($isRTL) {
        $pdf->SetFont('freesans', 'B', 10);
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
    }

    $pdf->Cell(70, 7, get_string('coursename', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('enrolledstudents', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('averagegrade', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('completionrate', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(30, 7, get_string('startdate', 'report_originality'), 1, 1, 'C');

    // Add data rows
    if ($isRTL) {
        $pdf->SetFont('freesans', '', 9);
    } else {
        $pdf->SetFont('helvetica', '', 9);
    }

    foreach ($courses as $course) {
        $pdf->Cell(70, 6, $course->fullname, 1, 0);
        $pdf->Cell(30, 6, $course->enrolledstudents, 1, 0, 'C');
        $pdf->Cell(30, 6, number_format($course->averagegrade, 2), 1, 0, 'C');
        $pdf->Cell(30, 6, number_format($course->completionrate, 2), 1, 0, 'C');
        $pdf->Cell(30, 6, userdate($course->startdate, get_string('strftimedatefullshort', 'core_langconfig')), 1, 1);
    }
}

function export_students_report_to_pdf($pdf, $students, $isRTL) {
    // Set up table headers
    if ($isRTL) {
        $pdf->SetFont('freesans', 'B', 10);
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
    }

    $pdf->Cell(60, 7, get_string('fullname'), 1, 0, 'C');
    $pdf->Cell(70, 7, get_string('email', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(20, 7, get_string('averagegrade', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(20, 7, get_string('coursescount', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(20, 7, get_string('lastaccess', 'report_originality'), 1, 1, 'C');

    // Add data rows
    if ($isRTL) {
        $pdf->SetFont('freesans', '', 9);
    } else {
        $pdf->SetFont('helvetica', '', 9);
    }

    foreach ($students as $student) {
        $pdf->Cell(60, 6, fullname($student), 1, 0);
        $pdf->Cell(70, 6, $student->email, 1, 0);
        $pdf->Cell(20, 6, number_format($student->averagegrade, 2), 1, 0, 'C');
        $pdf->Cell(20, 6, $student->coursescount, 1, 0, 'C');
        $lastaccess = $student->lastaccess ? userdate($student->lastaccess) : get_string('never', 'report_originality');
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
    if ($isRTL) {
        $pdf->SetFont('freesans', 'B', 12);
    } else {
        $pdf->SetFont('helvetica', 'B', 12);
    }

    $pdf->Cell(0, 10, get_string('submission_count', 'report_originality') . ': ' . $totalSubmissions, 0, 1);
    $pdf->Cell(0, 10, get_string('coursescount', 'report_originality') . ': ' . count($courses), 0, 1);
    $pdf->Ln(5);

    // Set up table headers
    if ($isRTL) {
        $pdf->SetFont('freesans', 'B', 10);
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
    }

    $pdf->Cell(120, 7, get_string('coursename', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(70, 7, get_string('submission_count', 'report_originality'), 1, 1, 'C');

    // Add data rows
    if ($isRTL) {
        $pdf->SetFont('freesans', '', 9);
    } else {
        $pdf->SetFont('helvetica', '', 9);
    }

    foreach ($courses as $course) {
        $pdf->Cell(120, 6, $course->fullname, 1, 0);
        $pdf->Cell(70, 6, $course->submission_count, 1, 1, 'C');
    }
}

function export_average_originality_score_report_to_pdf($pdf, $courses, $isRTL) {
    // Display the overall average first if available
    if (isset($courses[0]) && $courses[0]->id === 0) {
        $overall = $courses[0];

        if ($isRTL) {
            $pdf->SetFont('freesans', 'B', 12);
        } else {
            $pdf->SetFont('helvetica', 'B', 12);
        }

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
    if ($isRTL) {
        $pdf->SetFont('freesans', 'B', 10);
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
    }

    $pdf->Cell(100, 7, get_string('coursename', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(45, 7, get_string('originality_score', 'report_originality'), 1, 0, 'C');
    $pdf->Cell(45, 7, get_string('submissions', 'report_originality'), 1, 1, 'C');

    // Add data rows
    if ($isRTL) {
        $pdf->SetFont('freesans', '', 9);
    } else {
        $pdf->SetFont('helvetica', '', 9);
    }

    foreach ($courses as $course) {
        $pdf->Cell(100, 6, $course->fullname, 1, 0);
        $pdf->Cell(45, 6, number_format($course->avg_originality_score, 2), 1, 0, 'C');
        $pdf->Cell(45, 6, $course->total_submissions, 1, 1, 'C');
    }
}

