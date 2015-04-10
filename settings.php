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
 * This file adds the settings pages to the navigation menu
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/surveypro/adminlib.php');

$ADMIN->add('modsettings', new admin_category('modsurveyprofolder', new lang_string('pluginname', 'mod_surveypro'), !$module->is_enabled()));

$settings = new admin_settingpage($section, get_string('settings', 'mod_surveypro'), 'moodle/site:config', !$module->is_enabled());

if ($ADMIN->fulltree) {
    $name = new lang_string('requiremodintro', 'admin');
    $description = new lang_string('requiremodintro', 'admin');
    $settings->add(new admin_setting_configcheckbox('surveypro/requiremodintro', $name, $description, 0));

    $name = new lang_string('maxinputdelay', 'mod_surveypro');
    $description = new lang_string('maxinputdelay_descr', 'mod_surveypro');
    $settings->add(new admin_setting_configtext('surveypro/maxinputdelay', $name, $description, 168, PARAM_INT)); // alias: 7*24 hours == 1 week

    $name = new lang_string('extranoteinsearch', 'mod_surveypro');
    $description = new lang_string('extranoteinsearch_descr', 'mod_surveypro');
    $settings->add(new admin_setting_configcheckbox('surveypro/extranoteinsearch', $name, $description, 0));

    $name = new lang_string('fillinginstructioninsearch', 'mod_surveypro');
    $description = new lang_string('fillinginstructioninsearch_descr', 'mod_surveypro');
    $settings->add(new admin_setting_configcheckbox('surveypro/fillinginstructioninsearch', $name, $description, 0));
}

$ADMIN->add('modsurveyprofolder', $settings);

// Tell core we already added the settings structure.
$settings = null;

// folder 'surveypro field'
$ADMIN->add('modsurveyprofolder', new admin_category('surveyprofieldplugins',
                new lang_string('fieldplugins', 'surveypro'), !$module->is_enabled()));
$ADMIN->add('surveyprofieldplugins', new mod_surveypro_admin_page_manage_surveypro_plugins('surveyprofield'));

// folder 'surveypro format'
$ADMIN->add('modsurveyprofolder', new admin_category('surveyproformatplugins',
                new lang_string('formatplugins', 'surveypro'), !$module->is_enabled()));
$ADMIN->add('surveyproformatplugins', new mod_surveypro_admin_page_manage_surveypro_plugins('surveyproformat'));

// folder 'surveypro (master) templates'
$ADMIN->add('modsurveyprofolder', new admin_category('surveyprotemplateplugins',
                new lang_string('mastertemplateplugins', 'surveypro'), !$module->is_enabled()));
$ADMIN->add('surveyprotemplateplugins', new mod_surveypro_admin_page_manage_surveypro_plugins('surveyprotemplate'));

// folder 'surveypro reports'
$ADMIN->add('modsurveyprofolder', new admin_category('surveyproreportplugins',
                new lang_string('reportplugins', 'surveypro'), !$module->is_enabled()));
$ADMIN->add('surveyproreportplugins', new mod_surveypro_admin_page_manage_surveypro_plugins('surveyproreport'));

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveyprofield') as $plugin) {
    // @var \mod_surveypro\plugininfo\surveyprofield $plugin
    $plugin->load_settings($ADMIN, 'surveyprofieldplugins', $hassiteconfig);
}

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveyproformat') as $plugin) {
    // @var \mod_surveypro\plugininfo\surveyproformat $plugin
    $plugin->load_settings($ADMIN, 'surveyproformatplugins', $hassiteconfig);
}

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveyprotemplate') as $plugin) {
    // @var \mod_surveypro\plugininfo\surveyprotemplate $plugin
    $plugin->load_settings($ADMIN, 'surveyprotemplateplugins', $hassiteconfig);
}

foreach (core_plugin_manager::instance()->get_plugins_of_type('surveyproreport') as $plugin) {
    // @var \mod_surveypro\plugininfo\surveyproreport $plugin
    $plugin->load_settings($ADMIN, 'surveyproreportplugins', $hassiteconfig);
}

