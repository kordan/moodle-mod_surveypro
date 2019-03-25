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
 * The mod_surveypro_utility_mform class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class managing mform classes
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_utility_mform {

    /**
     * Register classes extending mform classes
     *
     * @return void
     */
    static public function register_form_elements() {
        global $CFG;

        $basepath = $CFG->dirroot.'/mod/surveypro';
        $types = array(SURVEYPRO_TYPEFIELD, SURVEYPRO_TYPEFORMAT);

        foreach ($types as $type) {
            $plugins = surveypro_get_plugin_list($type);
            foreach ($plugins as $plugin) {
                $filepath = $basepath.'/'.$type.'/'.$plugin.'/mform';
                if (file_exists($filepath) && is_dir($filepath)) {
                    $classfiles = scandir($filepath);
                    foreach ($classfiles as $classfile) {
                        if ($classfile{0} == '.') { // Hidden files, '.' and '..'.
                            continue;
                        }
                        $basename = basename($classfile, '.php');
                        $extendingclass = 'mod_surveypro_'.$basename;
                        $parentpath = $filepath.'/'.$classfile;
                        MoodleQuickForm::registerElementType($extendingclass, $parentpath, 'surveypromform_'.$basename);
                    }
                }
            }
        }
    }
}
