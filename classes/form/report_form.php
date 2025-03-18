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
 * Originality report filter form
 *
 * @package    report_originality
 * @copyright  2025 Mattan Dor (CentricApp)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for filtering originality report
 */
class report_originality_form extends moodleform {
    /**
     * Form definition
     */
    public function definition() {
        global $DB, $CFG;

        $mform = $this->_form;

        // Add a header.
        $mform->addElement('header', 'filterheader', get_string('filters', 'report_originality'));

        // Grade range.
        $gradegroup = array();
        $gradegroup[] = $mform->createElement('text', 'mingrade', get_string('mingrade', 'report_originality'), array('size' => 5));
        $gradegroup[] = $mform->createElement('text', 'maxgrade', get_string('maxgrade', 'report_originality'), array('size' => 5));
        $mform->addGroup($gradegroup, 'gradegroup', get_string('graderange', 'report_originality'), ' - ', false);
        $mform->setType('mingrade', PARAM_INT);
        $mform->setType('maxgrade', PARAM_INT);
        $mform->setDefault('mingrade', 0);
        $mform->setDefault('maxgrade', 100);
        $mform->addRule('mingrade', get_string('required'), 'required', null, 'client');
        $mform->addRule('maxgrade', get_string('required'), 'required', null, 'client');
        $mform->addRule('maxgrade', get_string('required'), 'required', null, 'client');

        // Date range.
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'report_originality'));
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'report_originality'));
        $mform->setDefault('startdate', strtotime('-1 year'));
        $mform->setDefault('enddate', time());

        // Report type selector.
        $reporttypes = array(
                'teachers_no_reports' => get_string('teachers_no_reports', 'report_originality'),
                'lowest_grade_courses' => get_string('lowest_grade_courses', 'report_originality'),
                'lowest_grade_students' => get_string('lowest_grade_students', 'report_originality'),
                'submission_count' => get_string('submission_count', 'report_originality'),
                'average_originality_score' => get_string('average_originality_score', 'report_originality')
        );
        $mform->addElement('select', 'reporttype', get_string('reporttype', 'report_originality'), $reporttypes);
        $mform->setDefault('reporttype', 'teachers_no_reports');

        // Add action buttons - Apply filters and Reset
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('applyfilters', 'report_originality'));
        $buttonarray[] = $mform->createElement('button', 'resetbutton', get_string('resetfilters', 'report_originality'),
                array('onclick' => 'window.location.href="' . $CFG->wwwroot . '/report/originality/index.php"; return false;'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    /**
     * Form validation
     *
     * @param array $data Data from the form
     * @param array $files Files uploaded
     * @return array of errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate grade range.
        if ($data['mingrade'] < 0 || $data['mingrade'] > 100) {
            $errors['gradegroup'] = get_string('invalidmingrade', 'report_originality');
        }

        if ($data['maxgrade'] < 0 || $data['maxgrade'] > 100) {
            $errors['gradegroup'] = get_string('invalidmaxgrade', 'report_originality');
        }

        if ($data['mingrade'] > $data['maxgrade']) {
            $errors['gradegroup'] = get_string('invalidgraderange', 'report_originality');
        }

        // Validate date range.
        if ($data['startdate'] > $data['enddate']) {
            $errors['enddate'] = get_string('invaliddaterange', 'report_originality');
        }

        return $errors;
    }
}
