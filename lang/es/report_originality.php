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
* Language strings for originality report (Spanish)
*
* @package    report_originality
* @copyright  2025 Mattan Dor (CentricApp)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'Informes de Originalidad';
$string['originality:view'] = 'Ver informes de originalidad';
$string['filters'] = 'Filtros';
$string['graderange'] = 'Rango de calificación';
$string['mingrade'] = 'Calificación mínima';
$string['maxgrade'] = 'Calificación máxima';
$string['startdate'] = 'Fecha de inicio';
$string['enddate'] = 'Fecha de finalización';
$string['applyfilters'] = 'Aplicar filtros';
$string['resetfilters'] = 'Restablecer filtros';
$string['coursename'] = 'Nombre del curso';
$string['enrolledstudents'] = 'Estudiantes matriculados';
$string['averagegrade'] = 'Calificación promedio';
$string['completionrate'] = 'Tasa de finalización';
$string['nocoursesfound'] = 'No se encontraron cursos que coincidan con los criterios';
$string['invalidmingrade'] = 'La calificación mínima debe estar entre 0 y 100';
$string['invalidmaxgrade'] = 'La calificación máxima debe estar entre 0 y 100';
$string['invalidgraderange'] = 'La calificación mínima debe ser menor que la calificación máxima';
$string['invaliddaterange'] = 'La fecha de inicio debe ser anterior a la fecha de finalización';

// Report type strings
$string['reporttype'] = 'Tipo de Informe';
$string['teachers_no_reports'] = 'Profesores que no han abierto informes';
$string['lowest_grade_courses'] = 'Cursos con calificación promedio más baja';
$string['lowest_grade_students'] = 'Estudiantes con calificación promedio más baja';
$string['submission_count'] = 'Número de tareas enviadas para verificación';
$string['average_originality_score'] = 'Puntuación de originalidad promedio';

// Additional strings
$string['department'] = 'Departamento';
$string['lastaccess'] = 'Último acceso';
$string['never'] = 'Nunca';
$string['email'] = 'Correo electrónico';
$string['coursescount'] = 'Número de cursos';
$string['details'] = 'Detalles';
$string['originality_score'] = 'Puntuación de originalidad';
$string['submissions'] = 'Envíos';
$string['modulename'] = 'Nombre del módulo';

// Add a string for overall average
$string['overall_average'] = 'Promedio general';

// PDF Export
$string['exportpdf'] = 'Exportar a PDF';
$string['exportcsv'] = 'Exportar a CSV';
$string['exportreport'] = 'Exportar Informe';
$string['pdfreporttitle'] = 'Informe de Originalidad';
$string['pdfreportsubtitle'] = 'Generado el {$a}';
$string['pdfreportfilters'] = 'Filtros aplicados:';
$string['pdfreportfiltergrade'] = 'Rango de calificación: {$a->min} - {$a->max}';
$string['pdfreportfilterdate'] = 'Rango de fechas: {$a->start} - {$a->end}';

// Settings
$string['settings'] = 'Configuración del Informe';
$string['logo'] = 'Logo del Informe';
$string['logodesc'] = 'Sube un logo para mostrar en el encabezado de los informes PDF. Tamaño recomendado: 300x100px.';
$string['footertext'] = 'Texto del Pie de Página';
$string['footertextdesc'] = 'Ingresa el texto que se mostrará en el pie de página de los informes PDF.';
$string['defaultfooter'] = 'Generado por el Plugin de Informes de Originalidad para Moodle';
