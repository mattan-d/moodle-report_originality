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
 * Language strings for originality report (Hebrew)
 *
 * @package    report_originality
 * @copyright  2025 Mattan Dor (CentricApp)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'דוחות מקוריות';
$string['originality:view'] = 'צפייה בדוחות מקוריות';
$string['filters'] = 'סינון';
$string['graderange'] = 'טווח ציונים';
$string['mingrade'] = 'ציון מינימלי';
$string['maxgrade'] = 'ציון מקסימלי';
$string['startdate'] = 'תאריך התחלה';
$string['enddate'] = 'תאריך סיום';
$string['applyfilters'] = 'החל סינון';
$string['resetfilters'] = 'אפס סינון';
$string['coursename'] = 'שם הקורס';
$string['enrolledstudents'] = 'סטודנטים רשומים';
$string['averagegrade'] = 'ציון ממוצע';
$string['completionrate'] = 'שיעור השלמה';
$string['nocoursesfound'] = 'לא נמצאו קורסים התואמים את הקריטריונים';
$string['invalidmingrade'] = 'ציון מינימלי חייב להיות בין 0 ל-100';
$string['invalidmaxgrade'] = 'ציון מקסימלי חייב להיות בין 0 ל-100';
$string['invalidgraderange'] = 'ציון מינימלי חייב להיות נמוך מציון מקסימלי';
$string['invaliddaterange'] = 'תאריך התחלה חייב להיות לפני תאריך סיום';

// Report type strings
$string['reporttype'] = 'סוג דוח';
$string['teachers_no_reports'] = 'המרצים שלא פתחו את דוחות';
$string['lowest_grade_courses'] = 'הקורסים עם ציון הממוצע הנמוך ביותר';
$string['lowest_grade_students'] = 'הסטודנטים עם ציון הממוצע הנמוך ביותר';
$string['submission_count'] = 'כמות עבודות הוגשו לבדיקת';
$string['average_originality_score'] = 'ציון מקוריות ממוצע';

// Additional strings
$string['department'] = 'מחלקה';
$string['lastaccess'] = 'תאריך';
$string['never'] = 'אף פעם';
$string['email'] = 'דוא"ל';
$string['coursescount'] = 'מס׳ קורסים';
$string['details'] = 'פרטים';
$string['originality_score'] = 'ציון מקוריות';
$string['submissions'] = 'הגשות';
$string['modulename'] = 'שם המודול';

// Add a string for overall average
$string['overall_average'] = 'ממוצע כללי';

// PDF Export
$string['exportpdf'] = 'ייצוא ל-PDF';
$string['exportcsv'] = 'ייצוא ל-CSV';
$string['exportreport'] = 'ייצוא דוח';
$string['pdfreporttitle'] = 'דוח מקוריות';
$string['pdfreportsubtitle'] = 'נוצר בתאריך {$a}';
$string['pdfreportfilters'] = 'סינון שהוחל:';
$string['pdfreportfiltergrade'] = 'טווח ציונים: {$a->min} - {$a->max}';
$string['pdfreportfilterdate'] = 'טווח תאריכים: {$a->start} - {$a->end}';

// Settings
$string['settings'] = 'הגדרות דוח';
$string['logo'] = 'לוגו דוח';
$string['logodesc'] = 'העלה לוגו שיוצג בכותרת דוחות PDF. גודל מומלץ: 300x100 פיקסלים.';
$string['footertext'] = 'טקסט כותרת תחתונה';
$string['footertextdesc'] = 'הזן את הטקסט שיוצג בכותרת התחתונה של דוחות PDF.';
$string['defaultfooter'] = 'נוצר על ידי תוסף דוחות מקוריות עבור Moodle';
