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

/**
 * Surveypro module data generator class
 *
 * @package   mod_surveypro
 * @category  test
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_generator extends testing_module_generator
{
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
     * Create an instance of the module.
     *
     * @param \stdClass|null $record
     * @param array|null $options
     * @return \stdClass
     */
    public function create_instance($record = null, ?array $options = []): \stdClass {
        global $CFG;

        require_once($CFG->dirroot . '/mod/surveypro/tests/behat/lib_behattest.php');

        // Add default values for surveypro.
        $record = (array)$record + [
            'newpageforchild' => 0,
            'neverstartedemail' => 0,
            'pauseresume' => 0,
            'keepinprogress' => 0,
            'captcha' => 0,
            'history' => 0,
            'anonymous' => 0,
            'timeopen' => 0,
            'timeclose' => 0,
            'startyear' => 1970,
            'stopyear' => 2020,
            'maxentries' => 0,
            'thankspage' => '',
            'thankspageformat' => '',
            'mailroles' => null,
            'mailextraaddresses' => null,
            'mailcontent' => 'User {FULLNAME} added a response to "{SURVEYPRONAME}".',
            'mailcontentformat' => FORMAT_HTML,
            'riskyeditdeadline' => 0,
            'template' => null,
            'completionsubmit' => 0,
            'timecreated' => time(),
            'timemodified' => time(),

            'groupmode' => 0,

            'userstyle_filemanager' => file_get_unused_draft_itemid(),
            'mailcontenteditor' => [
                'text' => 'User {FULLNAME} added a response to "{SURVEYPRONAME}".',
                'format' => FORMAT_HTML,
            ],
            'thankspageeditor' => [
                'text' => 'Thank you very much for your commitment on this survey.',
                'format' => FORMAT_HTML,
                'itemid' => file_get_unused_draft_itemid(),
            ],
        ];

        return parent::create_instance($record, (array)$options);
    }

    /**
     * Apply a template to the surveypro instance.
     *
     * @param \stdClass|null $record
     * @param array $options
     * @return \stdClass[] of created items
     */
    public function apply_mastertemplate(?\stdClass $record = null, array $options = []): \stdClass {
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

    // -------------------------------------------------------------------------
    // Private helper
    // -------------------------------------------------------------------------

    /**
     * Build and insert a record in surveypro_item, then return its id.
     * The caller is responsible for inserting the plugin-specific record.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param string    $type      'field' or 'format'.
     * @param string    $plugin    Plugin name (e.g. 'character', 'label').
     * @param array     $options   Optional overrides for any surveypro_item field.
     * @return int The id of the newly created surveypro_item record.
     */
    private function create_item_base(\stdClass $surveypro, string $type, string $plugin, array $options = []): int {
        global $DB;

        $this->itemcount++;

        $item = new \stdClass();
        $item->surveyproid  = $surveypro->id;
        $item->type         = $type;
        $item->plugin       = $plugin;
        $item->sortindex    = $options['sortindex'] ?? $this->itemcount;
        $item->formpage     = $options['formpage'] ?? 1;
        $item->hidden       = $options['hidden'] ?? 0;
        $item->required     = $options['required'] ?? 0;
        $item->reserved     = $options['reserved'] ?? 0;
        $item->parentid     = $options['parentid'] ?? 0;
        $item->parentvalue  = $options['parentvalue'] ?? null;
        $item->timecreated  = $options['timecreated'] ?? time();
        $item->timemodified = $options['timemodified'] ?? time();

        return $DB->insert_record('surveypro_item', $item);
    }

    // -------------------------------------------------------------------------
    // Field items (18)
    // -------------------------------------------------------------------------

    /**
     * Create a field/age item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_age fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_age(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'age', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_age', $plugin);

        return $itemid;
    }

    /**
     * Create a field/autofill item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_autofill fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_autofill(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'autofill', $options);

        $plugin = new \stdClass();
        $plugin->itemid       = $itemid;
        $plugin->hiddenfield  = $options['hiddenfield'] ?? 0;
        $DB->insert_record('surveyprofield_autofill', $plugin);

        return $itemid;
    }

    /**
     * Create a field/boolean item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_boolean fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_boolean(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'boolean', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_boolean', $plugin);

        return $itemid;
    }

    /**
     * Create a field/character item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_character fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_character(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'character', $options);

        $plugin = new \stdClass();
        $plugin->itemid = $itemid;
        $DB->insert_record('surveyprofield_character', $plugin);

        return $itemid;
    }

    /**
     * Create a field/checkbox item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_checkbox fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_checkbox(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'checkbox', $options);

        $plugin = new \stdClass();
        $plugin->itemid           = $itemid;
        $plugin->noanswerdefault  = $options['noanswerdefault'] ?? 0;
        $plugin->minimumrequired  = $options['minimumrequired'] ?? 0;
        $plugin->maximumrequired  = $options['maximumrequired'] ?? 0;
        $plugin->adjustment       = $options['adjustment'] ?? 0;
        $DB->insert_record('surveyprofield_checkbox', $plugin);

        return $itemid;
    }

    /**
     * Create a field/date item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_date fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_date(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'date', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_date', $plugin);

        return $itemid;
    }

    /**
     * Create a field/datetime item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_datetime fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_datetime(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'datetime', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->step          = $options['step'] ?? 1;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_datetime', $plugin);

        return $itemid;
    }

    /**
     * Create a field/fileupload item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_fileupload fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_fileupload(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'fileupload', $options);

        $plugin = new \stdClass();
        $plugin->itemid = $itemid;
        $DB->insert_record('surveyprofield_fileupload', $plugin);

        return $itemid;
    }

    /**
     * Create a field/integer item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_integer fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_integer(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'integer', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_integer', $plugin);

        return $itemid;
    }

    /**
     * Create a field/multiselect item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_multiselect fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_multiselect(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'multiselect', $options);

        $plugin = new \stdClass();
        $plugin->itemid          = $itemid;
        $plugin->noanswerdefault = $options['noanswerdefault'] ?? 0;
        $plugin->minimumrequired = $options['minimumrequired'] ?? 0;
        $plugin->maximumrequired = $options['maximumrequired'] ?? 0;
        $DB->insert_record('surveyprofield_multiselect', $plugin);

        return $itemid;
    }

    /**
     * Create a field/numeric item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_numeric fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_numeric(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'numeric', $options);

        $plugin = new \stdClass();
        $plugin->itemid  = $itemid;
        $plugin->signed  = $options['signed'] ?? 0;
        $DB->insert_record('surveyprofield_numeric', $plugin);

        return $itemid;
    }

    /**
     * Create a field/radiobutton item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_radiobutton fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_radiobutton(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'radiobutton', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $plugin->adjustment    = $options['adjustment'] ?? 0;
        $DB->insert_record('surveyprofield_radiobutton', $plugin);

        return $itemid;
    }

    /**
     * Create a field/rate item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_rate fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_rate(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'rate', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->options       = $options['options'] ?? "Option 1\nOption 2\nOption 3";
        $plugin->rates         = $options['rates'] ?? "1\n2\n3";
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $plugin->style         = $options['style'] ?? 0;
        $plugin->differentrates = $options['differentrates'] ?? 0;
        $DB->insert_record('surveyprofield_rate', $plugin);

        return $itemid;
    }

    /**
     * Create a field/recurrence item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_recurrence fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_recurrence(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'recurrence', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_recurrence', $plugin);

        return $itemid;
    }

    /**
     * Create a field/select item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_select fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_select(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'select', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_select', $plugin);

        return $itemid;
    }

    /**
     * Create a field/shortdate item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_shortdate fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_shortdate(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'shortdate', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_shortdate', $plugin);

        return $itemid;
    }

    /**
     * Create a field/textarea item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_textarea fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_textarea(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'textarea', $options);

        $plugin = new \stdClass();
        $plugin->itemid    = $itemid;
        $plugin->useeditor = $options['useeditor'] ?? 0;
        $plugin->arearows  = $options['arearows'] ?? 0;
        $plugin->areacols  = $options['areacols'] ?? 0;
        $DB->insert_record('surveyprofield_textarea', $plugin);

        return $itemid;
    }

    /**
     * Create a field/time item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyprofield_time fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_time(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'field', 'time', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->step          = $options['step'] ?? 1;
        $plugin->defaultoption = $options['defaultoption'] ?? 2;
        $DB->insert_record('surveyprofield_time', $plugin);

        return $itemid;
    }

    // -------------------------------------------------------------------------
    // Format items (4)
    // -------------------------------------------------------------------------

    /**
     * Create a format/fieldset item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyproformat_fieldset fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_fieldset(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'format', 'fieldset', $options);

        $plugin = new \stdClass();
        $plugin->itemid        = $itemid;
        $plugin->defaultstatus = $options['defaultstatus'] ?? 2;
        $DB->insert_record('surveyproformat_fieldset', $plugin);

        return $itemid;
    }

    /**
     * Create a format/label item.
     *
     * @param \stdClass $surveypro The surveypro instance.
     * @param array     $options   Optional overrides for surveypro_item and surveyproformat_label fields.
     * @return int The id of the newly created surveypro_item record.
     */
    public function create_item_label(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $itemid = $this->create_item_base($surveypro, 'format', 'label', $options);

        $plugin = new \stdClass();
        $plugin->itemid     = $itemid;
        $plugin->fullwidth  = $options['fullwidth'] ?? 0;
        $DB->insert_record('surveyproformat_label', $plugin);

        return $itemid;
    }

    /**
     * Create a pagebreak item for the given surveypro.
     *
     * @param \stdClass $surveypro
     * @param array $options
     * @return int the item id
     */
    public function create_item_pagebreak(\stdClass $surveypro, array $options = []): int {
        global $DB;

        $item = new \stdClass();
        $item->surveyproid = $surveypro->id;
        $item->type = SURVEYPRO_TYPEFORMAT;
        $item->plugin = 'pagebreak';
        $item->sortindex = $options['sortindex'] ?? $DB->count_records('surveypro_item', ['surveyproid' => $surveypro->id]) + 1;
        $item->timecreated = time();
        $item->timemodified = time();

        return $DB->insert_record('surveypro_item', $item);
    }
}
