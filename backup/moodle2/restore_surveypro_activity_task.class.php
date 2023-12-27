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
 * Define all the backup steps that will be used by the backup_assign_activity_task
 *
 * @package    mod_surveypro
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/backup/moodle2/restore_surveypro_stepslib.php'); // Because it exists (must).

/**
 * surveypro restore task that provides all the settings and steps to perform one complete restore of the activity
 *
 * @package   mod_surveypro
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_surveypro_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     *
     * @return void
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     *
     * @return void
     */
    protected function define_my_steps() {
        // Surveypro only has one structure step.
        $this->add_step(new restore_surveypro_activity_structure_step('surveypro_structure', 'surveypro.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     *
     * @return array
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('surveypro', ['intro'], 'surveypro');
        // $contents[] = new restore_decode_content('surveypro', ['thankspage'], 'surveypro');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule('SURVEYPROVIEWBYID', '/mod/surveypro/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SURVEYPROINDEX', '/mod/surveypro/index.php?id=$1', 'course');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@see restore_logs_processor} when restoring
     * surveypro logs. It must return one array
     * of {@see restore_log_rule} objects
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('surveypro', 'add', 'view.php?id={course_module}', '{surveypro}');
        $rules[] = new restore_log_rule('surveypro', 'update', 'view.php?id={course_module}', '{surveypro}');
        $rules[] = new restore_log_rule('surveypro', 'view', 'view.php?id={course_module}', '{surveypro}');
        $rules[] = new restore_log_rule('surveypro', 'choose', 'view.php?id={course_module}', '{surveypro}');
        $rules[] = new restore_log_rule('surveypro', 'choose again', 'view.php?id={course_module}', '{surveypro}');
        $rules[] = new restore_log_rule('surveypro', 'report', 'reports.php?id={course_module}', '{surveypro}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@see restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@see restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        // Fix old wrong uses (missing extension).
        $rules[] = new restore_log_rule('surveypro', 'view all', 'index?id={course}', null,
                                        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('surveypro', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
