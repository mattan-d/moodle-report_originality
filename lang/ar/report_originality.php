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
* Language strings for originality report (Arabic)
*
* @package    report_originality
* @copyright  2025 Mattan Dor (CentricApp)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'تقارير الأصالة';
$string['originality:view'] = 'عرض تقارير الأصالة';
$string['filters'] = 'المرشحات';
$string['graderange'] = 'نطاق الدرجات';
$string['mingrade'] = 'الحد الأدنى للدرجة';
$string['maxgrade'] = 'الحد الأقصى للدرجة';
$string['startdate'] = 'تاريخ البدء';
$string['enddate'] = 'تاريخ الانتهاء';
$string['applyfilters'] = 'تطبيق المرشحات';
$string['resetfilters'] = 'إعادة تعيين المرشحات';
$string['coursename'] = 'اسم المقرر';
$string['enrolledstudents'] = 'الطلاب المسجلين';
$string['averagegrade'] = 'متوسط الدرجة';
$string['completionrate'] = 'معدل الإكمال';
$string['nocoursesfound'] = 'لم يتم العثور على مقررات تطابق المعايير';
$string['invalidmingrade'] = 'يجب أن تكون الدرجة الدنيا بين 0 و 100';
$string['invalidmaxgrade'] = 'يجب أن تكون الدرجة القصوى بين 0 و 100';
$string['invalidgraderange'] = 'يجب أن تكون الدرجة الدنيا أقل من الدرجة القصوى';
$string['invaliddaterange'] = 'يجب أن يكون تاريخ البدء قبل تاريخ الانتهاء';

// Report type strings
$string['reporttype'] = 'نوع التقرير';
$string['teachers_no_reports'] = 'المعلمون الذين لم يفتحوا التقارير';
$string['lowest_grade_courses'] = 'المقررات ذات متوسط الدرجات الأدنى';
$string['lowest_grade_students'] = 'الطلاب ذوو متوسط الدرجات الأدنى';
$string['submission_count'] = 'عدد المهام المقدمة للتحقق';
$string['average_originality_score'] = 'متوسط درجة الأصالة';

// Additional strings
$string['department'] = 'القسم';
$string['lastaccess'] = 'آخر وصول';
$string['never'] = 'أبدًا';
$string['email'] = 'البريد الإلكتروني';
$string['coursescount'] = 'عدد المقررات';
$string['details'] = 'التفاصيل';
$string['originality_score'] = 'درجة الأصالة';
$string['submissions'] = 'التقديمات';
$string['modulename'] = 'اسم الوحدة';

// Add a string for overall average
$string['overall_average'] = 'المتوسط العام';

// PDF Export
$string['exportpdf'] = 'تصدير إلى PDF';
$string['exportcsv'] = 'تصدير إلى CSV';
$string['exportreport'] = 'تصدير التقرير';
$string['pdfreporttitle'] = 'تقرير الأصالة';
$string['pdfreportsubtitle'] = 'تم إنشاؤه في {$a}';
$string['pdfreportfilters'] = 'المرشحات المطبقة:';
$string['pdfreportfiltergrade'] = 'نطاق الدرجات: {$a->min} - {$a->max}';
$string['pdfreportfilterdate'] = 'نطاق التاريخ: {$a->start} - {$a->end}';

// Settings
$string['settings'] = 'إعدادات التقرير';
$string['logo'] = 'شعار التقرير';
$string['logodesc'] = 'قم بتحميل شعار ليتم عرضه في رأس تقارير PDF. الحجم الموصى به: 300×100 بكسل.';
$string['footertext'] = 'نص تذييل الصفحة';
$string['footertextdesc'] = 'أدخل النص الذي سيتم عرضه في تذييل تقارير PDF.';
$string['defaultfooter'] = 'تم إنشاؤه بواسطة مكون تقارير الأصالة لـ Moodle';
