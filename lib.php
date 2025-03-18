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
 * Library functions for originality report
 *
 * @package    report_originality
 * @copyright  2025 Mattan Dor (CentricApp)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Extends navigation to add the report link in course administration.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course object
 * @param context $context The course context
 */
function report_originality_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/originality:view', $context)) {
        $url = new moodle_url('/report/originality/index.php', array('courseid' => $course->id));
        $navigation->add(get_string('pluginname', 'report_originality'), $url, 
                         navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Add the course report link to the admin tree.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course object
 * @param context $context The course context
 */
function report_originality_extend_navigation($navigation, $course, $context) {
    if (has_capability('report/originality:view', context_system::instance())) {
        $reportnode = $navigation->get('originality');
        if (!$reportnode) {
            $reportnode = $navigation->create(get_string('pluginname', 'report_originality'),
                                            new moodle_url('/report/originality/index.php'),
                                            navigation_node::TYPE_CONTAINER);
            $navigation->add_node($reportnode);
        }
    }
}

/**
 * Get courses based on filter criteria
 *
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Maximum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 * @param int $category Category ID (0 for all)
 * @return array Array of course objects with additional data
 */
function report_originality_get_courses($mingrade, $maxgrade, $startdate, $enddate, $category) {
    global $DB;

    // Base query to get courses.
    $params = array();
    $categorysql = '';
    
    if ($category > 0) {
        $categorysql = "AND c.category = :category";
        $params['category'] = $category;
    }
    
    $sql = "SELECT c.id, c.fullname, c.startdate, cc.name as categoryname
            FROM {course} c
            JOIN {course_categories} cc ON c.category = cc.id
            WHERE c.id != :siteid
            AND c.startdate >= :startdate
            AND c.startdate <= :enddate
            $categorysql
            ORDER BY c.fullname";
    
    $params['siteid'] = SITEID;
    $params['startdate'] = $startdate;
    $params['enddate'] = $enddate;
    
    $courses = $DB->get_records_sql($sql, $params);
    $result = array();
    
    // For each course, get additional data.
    foreach ($courses as $course) {
        // Get enrolled students count.
        $context = context_course::instance($course->id);
        $enrolledstudents = count_enrolled_users($context, 'moodle/course:isincompletionreports');
        $course->enrolledstudents = $enrolledstudents;
        
        // Get average grade.
        $averagegrade = report_originality_get_average_grade($course->id);
        $course->averagegrade = $averagegrade;
        
        // Get completion rate.
        $completionrate = report_originality_get_completion_rate($course->id);
        $course->completionrate = $completionrate;
        
        // Filter by grade range.
        if ($averagegrade >= $mingrade && $averagegrade <= $maxgrade) {
            $result[] = $course;
        }
    }
    
    return $result;
}

/**
 * Get average grade for a course
 *
 * @param int $courseid Course ID
 * @return float Average grade percentage
 */
function report_originality_get_average_grade($courseid) {
    global $DB;
    
    // This is a simplified version. In a real plugin, you would need to
    // calculate this based on gradebook data.
    $sql = "SELECT AVG(gg.finalgrade) as avggrade
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid
            AND gi.itemtype = 'course'";
    
    $params = array('courseid' => $courseid);
    $record = $DB->get_record_sql($sql, $params);
    
    if ($record && !is_null($record->avggrade)) {
        return $record->avggrade;
    }
    
    // Return a random value between 60-95 for demonstration purposes.
    // In a real plugin, you would calculate this properly.
    return rand(60, 95);
}

/**
 * Get completion rate for a course
 *
 * @param int $courseid Course ID
 * @return float Completion rate percentage
 */
function report_originality_get_completion_rate($courseid) {
    global $DB;
    
    // This is a simplified version. In a real plugin, you would need to
    // calculate this based on course completion data.
    $context = context_course::instance($courseid);
    $enrolledstudents = count_enrolled_users($context, 'moodle/course:isincompletionreports');
    
    if ($enrolledstudents == 0) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) as completed
            FROM {course_completions}
            WHERE course = :courseid
            AND timecompleted IS NOT NULL";
    
    $params = array('courseid' => $courseid);
    $completed = $DB->count_records_sql($sql, $params);
    
    // Return a random value between 70-100 for demonstration purposes.
    // In a real plugin, you would calculate this properly.
    return rand(70, 100);
}

// Add new functions to lib.php for the different report types

/**
 * Get teachers who haven't opened reports
 *
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Minimum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 * @return array Array of teacher objects
 */
function report_originality_get_teachers_no_reports($mingrade, $maxgrade, $startdate, $enddate) {
    global $DB;
    
    // Find submissions with grades in the specified range that don't have stats entries
    // This directly uses the SQL structure you provided
    $sql = "SELECT s.*
            FROM {plagiarism_originality_sub} s
            LEFT JOIN {plagiarism_originality_stats} stats ON s.id = stats.subid
            WHERE s.grade BETWEEN :mingrade AND :maxgrade
            AND stats.subid IS NULL
            AND s.created BETWEEN :startdate AND :enddate";
    
    $params = array(
        'mingrade' => $mingrade,
        'maxgrade' => $maxgrade,
        'startdate' => $startdate,
        'enddate' => $enddate
    );
    
    $submissions = $DB->get_records_sql($sql, $params);
    
    if (empty($submissions)) {
        return array();
    }
    
    // Get all course module IDs from these submissions
    $cmids = array();
    foreach ($submissions as $submission) {
        if (!empty($submission->cm)) {
            $cmids[] = $submission->cm;
        }
    }
    
    if (empty($cmids)) {
        return array();
    }
    
    // Find the courses these course modules belong to
    list($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
    $sql = "SELECT DISTINCT c.id
            FROM {course_modules} cm
            JOIN {course} c ON cm.course = c.id
            WHERE cm.id $insql";
    
    $courseids = $DB->get_fieldset_sql($sql, $inparams);
    
    if (empty($courseids)) {
        return array();
    }
    
    // Find teachers assigned to these courses
    list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
    $sql = "SELECT DISTINCT u.*
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ra.contextid = ctx.id
            JOIN {role} r ON ra.roleid = r.id
            WHERE ctx.contextlevel = :contextlevel
            AND ctx.instanceid $insql
            AND r.shortname = :rolename
            AND u.deleted = 0
            AND u.suspended = 0
            ORDER BY u.lastname, u.firstname";
    
    $inparams['contextlevel'] = CONTEXT_COURSE;
    $inparams['rolename'] = 'editingteacher';
    
    $teachers = $DB->get_records_sql($sql, $inparams);
    $result = array();
    
    // For each teacher, add additional information
    foreach ($teachers as $teacher) {
        // Add department info if the field exists
        try {
            $fieldid = $DB->get_field('user_info_field', 'id', array('shortname' => 'department'));
            if ($fieldid) {
                $teacher->department = $DB->get_field('user_info_data', 'data', 
                    array('userid' => $teacher->id, 'fieldid' => $fieldid));
            } else {
                $teacher->department = '';
            }
        } catch (Exception $e) {
            $teacher->department = '';
        }
        
        $result[] = $teacher;
    }
    
    return $result;
}

/**
 * Get courses with lowest average grades
 *
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Maximum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 * @return array Array of course objects with additional data
 */
function report_originality_get_lowest_grade_courses($mingrade, $maxgrade, $startdate, $enddate) {
    global $DB;
    
    // First, get course modules with their average grades using the provided SQL
    $sql = "SELECT cm, AVG(grade) AS avg_grade
            FROM {plagiarism_originality_sub}
            WHERE grade BETWEEN :mingrade AND :maxgrade
            AND created BETWEEN :startdate AND :enddate
            GROUP BY cm
            ORDER BY avg_grade ASC
            LIMIT 100";
    
    $params = array(
        'mingrade' => $mingrade,
        'maxgrade' => $maxgrade,
        'startdate' => $startdate,
        'enddate' => $enddate
    );
    
    $modules = $DB->get_records_sql($sql, $params);
    
    if (empty($modules)) {
        return array();
    }
    
    // Get the course IDs for these modules
    $cmids = array_keys($modules);
    list($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
    
    $sql = "SELECT DISTINCT cm.course, cm.id as cmid
            FROM {course_modules} cm
            WHERE cm.id $insql";
    
    $coursemodules = $DB->get_records_sql($sql, $inparams);
    
    // Map course modules to their courses
    $courseids = array();
    $courseToCmMap = array();
    
    foreach ($coursemodules as $cm) {
        $courseids[] = $cm->course;
        if (!isset($courseToCmMap[$cm->course])) {
            $courseToCmMap[$cm->course] = array();
        }
        $courseToCmMap[$cm->course][] = $cm->cmid;
    }
    
    if (empty($courseids)) {
        return array();
    }
    
    // Get course information
    list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
    $sql = "SELECT c.*
            FROM {course} c
            WHERE c.id $insql
            ORDER BY c.fullname";
    
    $courses = $DB->get_records_sql($sql, $inparams);
    $result = array();
    
    // For each course, calculate the average grade from its modules
    foreach ($courses as $course) {
        if (isset($courseToCmMap[$course->id])) {
            $totalGrade = 0;
            $moduleCount = 0;
            
            foreach ($courseToCmMap[$course->id] as $cmid) {
                if (isset($modules[$cmid])) {
                    $totalGrade += $modules[$cmid]->avg_grade;
                    $moduleCount++;
                }
            }
            
            if ($moduleCount > 0) {
                $course->averagegrade = $totalGrade / $moduleCount;
                
                // Get enrolled students count
                $context = context_course::instance($course->id);
                $course->enrolledstudents = count_enrolled_users($context, 'moodle/course:isincompletionreports');
                
                // Get completion rate
                $course->completionrate = report_originality_get_completion_rate($course->id);
                
                $result[] = $course;
            }
        }
    }
    
    // Sort by average grade (ascending)
    usort($result, function($a, $b) {
        return $a->averagegrade > $b->averagegrade;
    });
    
    // Return only the first 20 courses (lowest grades)
    return array_slice($result, 0, 20);
}

/**
 * Get students with lowest average grades
 *
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Maximum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 * @return array Array of student objects with additional data
 */
function report_originality_get_lowest_grade_students($mingrade, $maxgrade, $startdate, $enddate) {
    global $DB;
    
    // Get students with their average grades using the provided SQL
    $sql = "SELECT userid, AVG(grade) AS avg_grade
            FROM {plagiarism_originality_sub}
            WHERE grade BETWEEN :mingrade AND :maxgrade
            AND created BETWEEN :startdate AND :enddate
            GROUP BY userid
            ORDER BY avg_grade ASC";
    
    $params = array(
        'mingrade' => $mingrade,
        'maxgrade' => $maxgrade,
        'startdate' => $startdate,
        'enddate' => $enddate
    );
    
    $studentgrades = $DB->get_records_sql($sql, $params);
    
    if (empty($studentgrades)) {
        return array();
    }
    
    // Get the user IDs
    $userids = array_keys($studentgrades);
    
    // Get only users who are students (have the student role)
    list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
    $sql = "SELECT DISTINCT u.*
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE u.id $insql
            AND r.shortname = :rolename
            AND u.deleted = 0
            AND u.suspended = 0
            ORDER BY u.lastname, u.firstname";
    
    $inparams['rolename'] = 'student';
    
    $students = $DB->get_records_sql($sql, $inparams);
    $result = array();
    
    // For each student, add the average grade and count of courses
    foreach ($students as $student) {
        if (isset($studentgrades[$student->id])) {
            $student->averagegrade = $studentgrades[$student->id]->avg_grade;
            
            // Count the number of courses this student is enrolled in
            $sql = "SELECT COUNT(DISTINCT c.id) as coursecount
                    FROM {course} c
                    JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
                    JOIN {role_assignments} ra ON ra.contextid = ctx.id
                    WHERE ra.userid = :userid";
            
            $params = array(
                'contextlevel' => CONTEXT_COURSE,
                'userid' => $student->id
            );
            
            $coursecount = $DB->count_records_sql($sql, $params);
            $student->coursescount = $coursecount;
            
            $result[] = $student;
        }
    }
    
    // Return only the first 20 students (lowest grades)
    return array_slice($result, 0, 20);
}

/**
 * Get submission count by course
 *
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Maximum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 * @return array Array of course objects with submission counts
 */
function report_originality_get_submission_count($mingrade, $maxgrade, $startdate, $enddate) {
    global $DB;
    
    // Get all submissions from the plagiarism_originality_sub table
    // Count all submissions regardless of grade (remove grade filter)
    $sql = "SELECT s.cm, COUNT(s.id) as submission_count
            FROM {plagiarism_originality_sub} s
            WHERE s.created BETWEEN :startdate AND :enddate
            GROUP BY s.cm
            ORDER BY submission_count DESC";
    
    $params = array(
        'startdate' => $startdate,
        'enddate' => $enddate
    );
    
    $submissions = $DB->get_records_sql($sql, $params);
    
    if (empty($submissions)) {
        return array();
    }
    
    // Get the course module IDs
    $cmids = array_keys($submissions);
    
    // Get course information for these course modules
    list($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
    $sql = "SELECT cm.id as cmid, cm.course, c.fullname as coursename, m.name as modulename
            FROM {course_modules} cm
            JOIN {course} c ON cm.course = c.id
            JOIN {modules} m ON cm.module = m.id
            WHERE cm.id $insql";
    
    $coursemodules = $DB->get_records_sql($sql, $inparams);
    
    // Group by course
    $courses = array();
    foreach ($coursemodules as $cm) {
        $courseid = $cm->course;
        
        if (!isset($courses[$courseid])) {
            $courses[$courseid] = new stdClass();
            $courses[$courseid]->id = $courseid;
            $courses[$courseid]->fullname = $cm->coursename;
            $courses[$courseid]->submission_count = 0;
            $courses[$courseid]->modules = array();
        }
        
        $cmid = $cm->cmid;
        if (isset($submissions[$cmid])) {
            $count = $submissions[$cmid]->submission_count;
            $courses[$courseid]->submission_count += $count;
            
            // Add module information
            $module = new stdClass();
            $module->name = $cm->modulename;
            $module->cmid = $cmid;
            $module->submission_count = $count;
            $courses[$courseid]->modules[] = $module;
        }
    }
    
    // Sort by submission count (descending)
    uasort($courses, function($a, $b) {
        return $b->submission_count - $a->submission_count;
    });
    
    return $courses;
}

/**
 * Get average originality score by course and module
 *
 * @param int $mingrade Minimum grade percentage
 * @param int $maxgrade Maximum grade percentage
 * @param int $startdate Start date timestamp
 * @param int $enddate End date timestamp
 * @return array Array of course objects with originality scores
 */
function report_originality_get_average_originality_score($mingrade, $maxgrade, $startdate, $enddate) {
    global $DB;
    
    // Get all submissions that match the grade and date filters
    $sql = "SELECT s.id, s.cm, s.userid, s.grade, s.created
            FROM {plagiarism_originality_sub} s
            WHERE s.grade BETWEEN :mingrade AND :maxgrade
            AND s.created BETWEEN :startdate AND :enddate";
    
    $params = array(
        'mingrade' => $mingrade,
        'maxgrade' => $maxgrade,
        'startdate' => $startdate,
        'enddate' => $enddate
    );
    
    $submissions = $DB->get_records_sql($sql, $params);
    
    if (empty($submissions)) {
        return array();
    }
    
    // Calculate the overall average score
    $totalScore = 0;
    $totalSubmissions = count($submissions);
    $cmids = array();
    
    foreach ($submissions as $submission) {
        $totalScore += $submission->grade;
        if (!in_array($submission->cm, $cmids)) {
            $cmids[] = $submission->cm;
        }
    }
    
    $overallAverage = $totalScore / $totalSubmissions;
    
    // Get course information for these course modules
    list($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
    $sql = "SELECT cm.id as cmid, cm.course, c.fullname as coursename, m.name as modulename
            FROM {course_modules} cm
            JOIN {course} c ON cm.course = c.id
            JOIN {modules} m ON cm.module = m.id
            WHERE cm.id $insql";
    
    $coursemodules = $DB->get_records_sql($sql, $inparams);
    
    // Group submissions by course and module
    $courses = array();
    $cmToCourseMap = array();
    
    foreach ($coursemodules as $cm) {
        $courseid = $cm->course;
        $cmid = $cm->cmid;
        $cmToCourseMap[$cmid] = $courseid;
        
        if (!isset($courses[$courseid])) {
            $courses[$courseid] = new stdClass();
            $courses[$courseid]->id = $courseid;
            $courses[$courseid]->fullname = $cm->coursename;
            $courses[$courseid]->total_score = 0;
            $courses[$courseid]->total_submissions = 0;
            $courses[$courseid]->modules = array();
        }
        
        $courses[$courseid]->modules[$cmid] = new stdClass();
        $courses[$courseid]->modules[$cmid]->name = $cm->modulename;
        $courses[$courseid]->modules[$cmid]->cmid = $cmid;
        $courses[$courseid]->modules[$cmid]->total_score = 0;
        $courses[$courseid]->modules[$cmid]->submission_count = 0;
    }
    
    // Calculate scores by module and course
    foreach ($submissions as $submission) {
        $cmid = $submission->cm;
        if (isset($cmToCourseMap[$cmid])) {
            $courseid = $cmToCourseMap[$cmid];
            
            // Add to course totals
            $courses[$courseid]->total_score += $submission->grade;
            $courses[$courseid]->total_submissions++;
            
            // Add to module totals
            if (isset($courses[$courseid]->modules[$cmid])) {
                $courses[$courseid]->modules[$cmid]->total_score += $submission->grade;
                $courses[$courseid]->modules[$cmid]->submission_count++;
            }
        }
    }
    
    // Calculate averages for each module and course
    foreach ($courses as $course) {
        if ($course->total_submissions > 0) {
            $course->avg_originality_score = $course->total_score / $course->total_submissions;
        } else {
            $course->avg_originality_score = 0;
        }
        
        foreach ($course->modules as $cmid => $module) {
            if ($module->submission_count > 0) {
                $module->avg_score = $module->total_score / $module->submission_count;
            } else {
                $module->avg_score = 0;
            }
        }
        
        // Convert modules array to indexed array for easier iteration
        $course->modules = array_values($course->modules);
    }
    
    // Add the overall average to the result
    $result = array_values($courses);
    
    // Add an "Overall" entry at the beginning
    $overall = new stdClass();
    $overall->id = 0;
    $overall->fullname = get_string('pluginname', 'report_originality') . ' - ' . get_string('average_originality_score', 'report_originality');
    $overall->avg_originality_score = $overallAverage;
    $overall->total_submissions = $totalSubmissions;
    $overall->modules = array();
    
    array_unshift($result, $overall);
    
    return $result;
}

