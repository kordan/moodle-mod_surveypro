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
 * Surveypro surveyprofield info class.
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_surveypro\plugininfo;

use core\plugininfo\base, core_plugin_manager, moodle_url;

defined('MOODLE_INTERNAL') || die();

class surveyprofield extends base {
    /**
     * Finds all enabled plugins, the result may include missing plugins.
     *
     * @param none
     * @return array|null of enabled plugins $pluginname=>$pluginname, null means unknown
     */
    public static function get_enabled_plugins() {
        global $DB;

        $plugins = core_plugin_manager::instance()->get_installed_plugins('surveyprofield');
        if (!$plugins) {
            return array();
        }
        $installed = array();
        foreach ($plugins as $plugin => $version) {
            $installed[] = 'surveyprofield_'.$plugin;
        }

        list($installed, $params) = $DB->get_in_or_equal($installed, SQL_PARAMS_NAMED);
        $disabled = $DB->get_records_select('config_plugins', "plugin $installed AND name = 'disabled'", $params, 'plugin ASC');
        foreach ($disabled as $conf) {
            if (empty($conf->value)) {
                continue;
            }
            list($type, $name) = explode('_', $conf->plugin, 2);
            unset($plugins[$name]);
        }

        $enabled = array();
        foreach ($plugins as $plugin => $version) {
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }

    /*
     * is_uninstall_allowed
     *
     * @param none
     * @return bool: false if the corrsponding record exists
     */
    public function is_uninstall_allowed() {
        global $DB;

        return !$DB->record_exists('surveypro_item', array('type' => 'field', 'plugin' => $this->name));
    }

    /**
     * Return URL used for management of plugins of this type.
     *
     * @paran none
     * @return moodle_url
     */
    public static function get_manage_url() {
        return new moodle_url('/mod/surveypro/adminmanageplugins.php', array('subtype' => 'surveyprofield'));
    }

    /*
     * get_settings_section_name
     *
     * @param none
     * @return settings section name
     */
    public function get_settings_section_name() {
        return $this->type.'_'.$this->name;
    }

    /**
     * Loads plugin settings to the settings tree
     *
     * This function usually includes settings.php file in plugins folder.
     * Alternatively it can create a link to some settings page (instance of admin_externalpage)
     *
     * @param \part_of_admin_tree $adminroot
     * @param string $parentnodename
     * @param bool $hassiteconfig whether the current user has moodle/site:config capability
     * @return void
     */
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.

        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);

        if ($adminroot->fulltree) {
            $shortsubtype = substr($this->type, strlen('surveypro'));
            include($this->full_path('settings.php'));
        }

        $adminroot->add($this->type.'plugins', $settings);
    }
}
