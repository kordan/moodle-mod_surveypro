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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

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
* mod_surveypro data generator class.
*
* @package mod_surveypro
* @category test
* @copyright 2013 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class mod_surveypro_generator extends testing_module_generator {

    /**
    * @var int keep track of how many items have been created,
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

    public function create_instance($record = null, array $options = null) {
        global $CFG;

        require_once($CFG->dirroot.'/mod/surveypro/lib.php');
        $record = (object)(array)$record;

        // Apply defaults.
        $defaults = array(
            'newpageforchild' => 0,
            'saveresume' => 0,
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

            'userstyle_filemanager' => file_get_unused_draft_itemid(),
            'thankshtml_editor' => array(
                'text' => 'Thank you very much for your time on this poll',
                'format' => FORMAT_MOODLE,
                'itemid' => file_get_unused_draft_itemid())
        );
        foreach ($defaults as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }

    /**
    * Apply a template to the surveypro instance.
    *
    * @param $record array|stdClass $record containing course, surveypro and valid template.
    * @return stdClass[] of created items.
    */
    public function apply_template($record = null) {

        $record = (object)(array)$record;

        // Verify course is passed.
        // Verify surveypro is passed.
        // Verify template is passed.
        // Verify template exists.
        // Verify there is not any item created with this generator. Cannot apply template if so.

    }
}