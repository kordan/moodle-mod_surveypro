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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');

class mod_surveypro_itembaseform extends moodleform {

    /*
     * definition
     *
     * @param none
     * @return void
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Get _customdata.
        $item = $this->_customdata->item;
        $surveypro = $this->_customdata->surveypro;
        $cm = $this->_customdata->item->get_cm();

        // Itembase: itemid.
        $fieldname = 'itemid';
        $mform->addElement('hidden', $fieldname, 0);
        $mform->setType($fieldname, PARAM_INT);

        // Itembase: pluginid.
        $fieldname = 'pluginid';
        $mform->addElement('hidden', $fieldname, 0);
        $mform->setType($fieldname, PARAM_INT);

        // Itembase: type.
        $fieldname = 'type';
        $mform->addElement('hidden', $fieldname, 'dummytype');
        $mform->setType($fieldname, PARAM_RAW);

        // Itembase: plugin.
        $fieldname = 'plugin';
        $mform->addElement('hidden', $fieldname, 'dummyplugin');
        $mform->setType($fieldname, PARAM_RAW);

        // Here I open a new fieldset.
        $fieldname = 'common_fs';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));
        }

        // Itembase: content & contentformat.
        if ($item->get_insetupform('content')) {
            $editors = $item->get_editorlist();
            if (array_key_exists('content', $editors)) {
                $fieldname = 'content_editor';
                $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES);
                $mform->addElement('editor', $fieldname, get_string($fieldname, 'mod_surveypro'), null, $editoroptions);
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
                $mform->setType($fieldname, PARAM_CLEANHTML);
            } else {
                $fieldname = 'content';
                $mform->addElement('text', $fieldname, get_string($fieldname, 'mod_surveypro'), array('maxlength' => '128', 'size' => '50'));
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
                $mform->setType($fieldname, PARAM_TEXT);
            }
        }

        // Itembase: required.
        $fieldname = 'required';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // Itembase: indent.
        $fieldname = 'indent';
        if ($item->get_insetupform($fieldname)) {
            $options = array_combine(range(0, 9), range(0, 9));
            $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setDefault($fieldname, '0');
        }

        // Itembase: position.
        $fieldname = 'position';
        if ($position = $item->get_insetupform($fieldname)) {
            $options = array();
            $default = SURVEYPRO_POSITIONTOP;
            if ($item->item_left_position_allowed()) { // Position can even be SURVEYPRO_POSITIONLEFT.
                $options[SURVEYPRO_POSITIONLEFT] =  get_string('left', 'mod_surveypro');
                $default = SURVEYPRO_POSITIONLEFT;
            }
            $options[SURVEYPRO_POSITIONTOP] = get_string('top', 'mod_surveypro');
            $options[SURVEYPRO_POSITIONFULLWIDTH] = get_string('fullwidth', 'mod_surveypro');
            $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setDefault($fieldname, $default);
            $mform->setType($fieldname, PARAM_INT);
        }

        // Itembase: customnumber.
        $fieldname = 'customnumber';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // Itembase: hideinstructions.
        $fieldname = 'hideinstructions';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // Itembase: variable.
        // For SURVEYPRO_TYPEFIELD only.
        $fieldname = 'variable';
        if ($item->get_insetupform($fieldname)) {
            $options = array('maxlength' => 64, 'size' => 12, 'class' => 'longfield');

            $mform->addElement('text', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // Itembase: extranote.
        $fieldname = 'extranote';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'mod_surveypro'), array('class' => 'longfield'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // Here I open a new fieldset.
        $fieldname = 'availability_fs';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Itembase: hidden.
        $fieldname = 'hidden';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // Itembase: insearchform.
        $fieldname = 'insearchform';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // Itembase: advanced.
        $fieldname = 'advanced';
        if ($item->get_insetupform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        if ($item->get_insetupform('parentid')) {
            // Here I open a new fieldset.
            $fieldname = 'branching';
            $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

            // Itembase::parentid.
            $fieldname = 'parentid';
            // Create the list of each item with:
            //     sortindex lower than mine (whether already exists);
            //     $classname::item_get_canbeparent() == true;
            //     advanced == my one <-- I omit this verification because the surveypro creator can, at every time, change the availability of the current item
            //                            So I move the verification of the holding form at the form verification time.

            // Build the list only for searchable plugins.
            $pluginlist = surveypro_get_plugin_list(SURVEYPRO_TYPEFIELD);
            foreach ($pluginlist as $plugin) {
                require_once($CFG->dirroot.'/mod/surveypro/'.SURVEYPRO_TYPEFIELD.'/'.$plugin.'/classes/plugin.class.php');
                $classname = 'mod_surveypro_'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
                if (!$classname::item_get_canbeparent()) {
                    unset($pluginlist[$plugin]);
                }
            }

            $sql = 'SELECT *
                    FROM {surveypro_item}
                    WHERE surveyproid = :surveyproid';
            $whereparams = array('surveyproid' => $surveypro->id);
            if ($item->get_sortindex()) {
                $sql .= ' AND sortindex < :sortindex';
                $whereparams['sortindex'] = $item->get_sortindex();
            }
            $sql .= ' AND plugin IN (\''.implode("','", $pluginlist).'\')
                        ORDER BY sortindex';
            $parentsseeds = $DB->get_recordset_sql($sql, $whereparams);

            $quickform = new HTML_QuickForm();
            $select = $quickform->createElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $select->addOption(get_string('choosedots'), 0);
            foreach ($parentsseeds as $parentsseed) {
                $parentitem = surveypro_get_item($cm, $surveypro, $parentsseed->id, $parentsseed->type, $parentsseed->plugin);
                $star = ($parentitem->get_advanced()) ? '(*) ' : '';

                // I do not need to take care of contents of items of master templates because if I am here, $parent is a standard item and not a multilang one
                $content = $star;
                $content .= get_string('pluginname', 'surveyprofield_'.$parentitem->get_plugin());
                $content .= ' ['.$parentitem->get_sortindex().']: '.strip_tags($parentitem->get_content());
                $content = surveypro_cutdownstring($content);

                $condition = ($parentitem->get_hidden() == 1);
                $condition = $condition && ($item->get_parentid() != $parentitem->get_itemid());
                $disabled = $condition ? array('disabled' => 'disabled') : null;
                $select->addOption($content, $parentitem->get_itemid(), $disabled);
            }
            $parentsseeds->close();

            $mform->addElement($select);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);

            // Itembase::parentcontent.
            $fieldname = 'parentcontent';
            $params = array('wrap' => 'virtual', 'rows' => '5', 'cols' => '45');
            $mform->addElement('textarea', $fieldname, get_string($fieldname, 'mod_surveypro'), $params);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_RAW);

            // Itembase::parentformat.
            $fieldname = 'parentformat';
            $a = new stdClass();
            $a->fieldname = get_string('parentcontent', 'mod_surveypro');
            $a->examples = html_writer::start_tag('ul');
            foreach ($pluginlist as $plugin) {
                $a->examples .= html_writer::start_tag('li');
                $a->examples .= html_writer::start_tag('div');
                $a->examples .= html_writer::tag('div', get_string('pluginname', 'surveyprofield_'.$plugin), array('class' => 'pluginname'));
                $a->examples .= html_writer::tag('div', get_string('parentformat', 'surveyprofield_'.$plugin), array('class' => 'inputformat'));
                $a->examples .= html_writer::end_tag('div');
                $a->examples .= html_writer::end_tag('li');
                $a->examples .= "\n";
            }
            $a->examples .= html_writer::end_tag('ul');
            $mform->addElement('static', $fieldname, get_string('note', 'mod_surveypro'), get_string($fieldname, 'mod_surveypro', $a));
        }

        if ($item->get_type() == SURVEYPRO_TYPEFIELD) {
            // Here I open a new fieldset.
            $fieldname = 'specializations';
            $typename = get_string('pluginname', 'surveyprofield_'.$item->get_plugin());
            $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro', $typename));
        }
    }

    /*
     * add_item_buttons
     *
     * @param none
     * @return void
     */
    public function add_item_buttons() {
        $mform = $this->_form;

        // Get _customdata.
        $item = $this->_customdata->item;
        $surveypro = $this->_customdata->surveypro;
        $cm = $this->_customdata->item->get_cm();

        $utilityman = new mod_surveypro_utility($cm, $surveypro);
        $hassubmissions = $utilityman->has_submissions();
        $riskyediting = ($surveypro->riskyeditdeadline > time());

        // Buttons.
        $itemid = $item->get_itemid();
        if (!empty($itemid)) {
            $fieldname = 'buttons';
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('submit', 'save', get_string('savechanges'));
            if (!$hassubmissions || $riskyediting) {
                $elementgroup[] = $mform->createElement('submit', 'saveasnew', get_string('saveasnew', 'mod_surveypro'));
            }
            $elementgroup[] = $mform->createElement('cancel');
            $mform->addGroup($elementgroup, $fieldname.'_group', '', ' ', false);
            $mform->closeHeaderBefore($fieldname.'_group');
        } else {
            $this->add_action_buttons(true, get_string('add'));
        }
    }

    /*
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        // Get _customdata.
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;
        // $cm = $this->_customdata->item->get_cm();

        $errors = array();

        // If (default == noanswer) but item is required => error.
        if ( isset($data['defaultvalue_check']) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultvalue_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        if (empty($data['parentid']) && empty($data['parentcontent'])) {
            // Stop verification here.
            return $errors;
        }

        // You choosed a parentid but you are missing the parentcontent.
        if (empty($data['parentid']) && (strlen($data['parentcontent']) > 0)) { // $data['parentcontent'] can be = '0'
            $a = get_string('parentcontent', 'mod_surveypro');
            $errors['parentid'] = get_string('ierr_missingparentid', 'mod_surveypro', $a);
        }

        // You did not choose a parent item but you entered an answer.
        if ( !empty($data['parentid']) && (strlen($data['parentcontent']) == 0) ) { // $data['parentcontent'] can be = '0'
            $a = get_string('parentid', 'mod_surveypro');
            $errors['parentcontent'] = get_string('ierr_missingparentcontent', 'mod_surveypro', $a);
        }

        return $errors;
    }
}
