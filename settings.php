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
 * Settings for originality report
 *
 * @package    report_originality
 * @copyright  2025 Mattan Dor (CentricApp)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Add the report to the admin reports menu.
$ADMIN->add('reports', new admin_externalpage('reportoriginality',
        get_string('pluginname', 'report_originality'),
        new moodle_url('/report/originality/index.php')));

// Add settings for the report.
$settings = new admin_settingpage('reportoriginalitysettings', get_string('settings', 'report_originality'));

if ($ADMIN->fulltree) {
    // Logo upload setting
    $name = 'report_originality/logo';
    $title = get_string('logo', 'report_originality');
    $description = get_string('logodesc', 'report_originality');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo', 0, 
        array('maxfiles' => 1, 'accepted_types' => array('image')));
    $settings->add($setting);

    // Footer text setting
    $name = 'report_originality/footertext';
    $title = get_string('footertext', 'report_originality');
    $description = get_string('footertextdesc', 'report_originality');
    $default = get_string('defaultfooter', 'report_originality');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
}

$ADMIN->add('reports', $settings);
