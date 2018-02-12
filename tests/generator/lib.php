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
 * mod_surveypro data generator.
 *
 * @package mod_surveypro
 * @category test
 * @copyright 2013 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Surveypro module data generator class
 *
 * @package   mod_surveypro
 * @category  test
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_generator extends testing_module_generator {

    /**
     * @var int Keep track of how many items have been created,
     */
    protected $itemcount = 0;

    /**
     * Reset generator counters.
     *
     * NOTE: To be called from data reset code only, do not use in tests!
     */
    public function reset() {
        $this->itemcount = 0;
        parent::reset();
    }

    /**
     * Create_instance.
     *
     * @param array $record
     * @param array $options
     * @return void
     */
    public function create_instance($record = null, array $options = null) {
        global $CFG;

        require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
        require_once($CFG->dirroot.'/mod/surveypro/tests/behat/lib_behattest.php');

        // Add default values for surveypro.
        $record = (array)$record + array(
            'newpageforchild' => 0,
            'saveresume' => 0,
            'keepinprogress' => 0,
            'captcha' => 0,
            'history' => 0,
            'anonymous' => 0,
            'timeopen' => 0,
            'timeclose' => 0,
            'startyear' => 1970,
            'stopyear' => 2020,
            'maxentries' => 0,
            'notifyrole' => null,
            'notifymore' => null,
            'thankshtml' => null,
            'thankshtmlformat' => FORMAT_MOODLE,
            'riskyeditdeadline' => 0,
            'template' => null,
            'completionsubmit' => 0,
            'timecreated' => time(),
            'timemodified' => time(),

            'groupmode' => 0,

            'userstyle_filemanager' => file_get_unused_draft_itemid(),
            'thankshtml_editor' => array(
                'text' => 'Thank you very much for your commitment on this survey.',
                'format' => FORMAT_HTML,
                'itemid' => file_get_unused_draft_itemid()
            )
        );

        return parent::create_instance($record, (array)$options);
    }

    /**
     * Apply a template to the surveypro instance.
     *
     * @param array $record array|stdClass $record containing course, surveypro and valid template
     * @param array $options
     * @return stdClass[] of created items
     */
    public function apply_mastertemplate($record = null, array $options = null) {

        $record = (object)(array)$record;
        $options = (array) $options;

        if (empty($record->mastertemplatename)) {
            throw new coding_exception('Master template application requires $record->mastertemplatename');
        }

        // Verify course is passed.
        // Verify surveypro is passed.
        // Verify template is passed.
        // Verify template exists.
        // Verify there is not any item created with this generator. Cannot apply template if so.
    }
}
