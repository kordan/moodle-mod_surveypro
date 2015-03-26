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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_surveypro_itembaseform extends moodleform {

    /*
     * definition
     *
     * @param none
     * @return none
     */
    public function definition() {
        global $DB, $CFG;
        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        $item = $this->_customdata->item;
        $surveypro = $this->_customdata->surveypro;

        // ----------------------------------------
        // itembase: itemid
        // ----------------------------------------
        $fieldname = 'itemid';
        $mform->addElement('hidden', $fieldname, '');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // itembase: pluginid
        // ----------------------------------------
        $fieldname = 'pluginid';
        $mform->addElement('hidden', $fieldname, '');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // itembase: type
        // ----------------------------------------
        $fieldname = 'type';
        $mform->addElement('hidden', $fieldname, 'dummytype');
        $mform->setType($fieldname, PARAM_RAW);

        // ----------------------------------------
        // itembase: plugin
        // ----------------------------------------
        $fieldname = 'plugin';
        $mform->addElement('hidden', $fieldname, 'dummyplugin');
        $mform->setType($fieldname, PARAM_RAW);

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'common_fs';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));
        }

        // ----------------------------------------
        // itembase: content & contentformat
        // ----------------------------------------
        if ($item->get_isinitemform('content')) {
            $editors = $item->get_editorlist();
            if (array_key_exists('content', $editors)) {
                $fieldname = 'content_editor';
                $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES);
                $mform->addElement('editor', $fieldname, get_string($fieldname, 'surveypro'), null, $editoroptions);
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
                $mform->setType($fieldname, PARAM_CLEANHTML);
            } else {
                $fieldname = 'content';
                $mform->addElement('text', $fieldname, get_string($fieldname, 'surveypro'), array('maxlength' => '128', 'size' => '50'));
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
                $mform->setType($fieldname, PARAM_TEXT);
            }
        }

        // ----------------------------------------
        // itembase: required
        // ----------------------------------------
        $fieldname = 'required';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // itembase: indent
        // ----------------------------------------
        $fieldname = 'indent';
        if ($item->get_isinitemform($fieldname)) {
            $options = array_combine(range(0, 9), range(0, 9));
            $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setDefault($fieldname, '0');
        }

        // ----------------------------------------
        // itembase: position
        // ----------------------------------------
        $fieldname = 'position';
        if ($position = $item->get_isinitemform($fieldname)) {
            $options = array(SURVEYPRO_POSITIONTOP => get_string('top', 'surveypro'),
                            SURVEYPRO_POSITIONFULLWIDTH => get_string('fullwidth', 'surveypro'));
            if ($item->item_left_position_allowed()) { // position can even be SURVEYPRO_POSITIONLEFT
                $options = array(SURVEYPRO_POSITIONLEFT => get_string('left', 'surveypro')) + $options;
            }
            $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setDefault($fieldname, $position);
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // itembase: customnumber
        // ----------------------------------------
        $fieldname = 'customnumber';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // itembase: hideinstructions
        // ----------------------------------------
        $fieldname = 'hideinstructions';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // itembase: variable
        // ----------------------------------------
        // for SURVEYPRO_TYPEFIELD only
        $fieldname = 'variable';
        if ($item->get_isinitemform($fieldname)) {
            $options = array('maxlength' => 64, 'size' => 12, 'class' => 'longfield');

            $mform->addElement('text', $fieldname, get_string($fieldname, 'surveypro'), $options);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // ----------------------------------------
        // itembase: extranote
        // ----------------------------------------
        $fieldname = 'extranote';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('text', $fieldname, get_string($fieldname, 'surveypro'), array('class' => 'longfield'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_TEXT);
        }

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'availability_fs';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // ----------------------------------------
        // itembase: hidden
        // ----------------------------------------
        $fieldname = 'hidden';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // itembase: insearchform
        // ----------------------------------------
        $fieldname = 'insearchform';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        // ----------------------------------------
        // itembase: advanced
        // ----------------------------------------
        $fieldname = 'advanced';
        if ($item->get_isinitemform($fieldname)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);
        }

        if ($item->get_isinitemform('parentid')) {
            // -----------------------------
            // here I open a new fieldset
            // -----------------------------
            $fieldname = 'branching_fs';
            $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

            // ----------------------------------------
            // itembase::parentid
            // ----------------------------------------
            $fieldname = 'parentid';
            // create the list of each item with:
            //     sortindex lower than mine (whether already exists)
            //     $classname::get_canbeparent() == true
            //     advanced == my one <-- I omit this verification because the surveypro creator can, at every time, change the basicform of the current item
            //                            So I move the verification of the holding form at the form verification time.

            // build the list only for searchable plugins
            $pluginlist = surveypro_get_plugin_list(SURVEYPRO_TYPEFIELD);
            foreach ($pluginlist as $plugin) {
                require_once($CFG->dirroot.'/mod/surveypro/'.SURVEYPRO_TYPEFIELD.'/'.$plugin.'/plugin.class.php');
                $classname = 'mod_surveypro_'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
                if (!$classname::get_canbeparent()) {
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
            $select = $quickform->createElement('select', $fieldname, get_string($fieldname, 'surveypro'));
            $select->addOption(get_string('choosedots'), 0);
            foreach ($parentsseeds as $parentsseed) {
                $parentitem = surveypro_get_item($parentsseed->id, $parentsseed->type, $parentsseed->plugin);
                $star = ($parentitem->get_advanced()) ? '(*) ' : '';

                // I do not need to take care of contents of items of master templates because if I am here, $parent is a standard item and not a multilang one
                $content = $star;
                $content .= get_string('pluginname', 'surveyprofield_'.$parentitem->get_plugin());
                $content .= ' ['.$parentitem->get_sortindex().']: '.strip_tags($parentitem->get_content());
                $content = surveypro_fixlength($content, 60);

                $condition = ($parentitem->get_hidden() == 1);
                $condition = $condition && ($item->parentid != $parentitem->itemid);
                $disabled = $condition ? array('disabled' => 'disabled') : null;
                $select->addOption($content, $parentitem->itemid, $disabled);
            }
            $parentsseeds->close();

            $mform->addElement($select);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_INT);

            // ----------------------------------------
            // itembase::parentcontent
            // ----------------------------------------
            $fieldname = 'parentcontent';
            $params = array('wrap' => 'virtual', 'rows' => '5', 'cols' => '45');
            $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveypro'), $params);
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
            $mform->setType($fieldname, PARAM_RAW);

            // ----------------------------------------
            // itembase::parentformat
            // ----------------------------------------
            $fieldname = 'parentformat';
            $a = html_writer::start_tag('ul');
            foreach ($pluginlist as $plugin) {
                $a .= html_writer::start_tag('li');
                $a .= html_writer::start_tag('div');
                $a .= html_writer::tag('div', get_string('pluginname', 'surveyprofield_'.$plugin), array('class' => 'pluginname'));
                $a .= html_writer::tag('div', get_string('parentformat', 'surveyprofield_'.$plugin), array('class' => 'inputformat'));
                $a .= html_writer::end_tag('div');
                $a .= html_writer::end_tag('li');
                $a .= "\n";
            }
            $a .= html_writer::end_tag('ul');
            $mform->addElement('static', $fieldname, get_string('note', 'surveypro'), get_string($fieldname, 'surveypro', $a));
        }

        if ($item->get_type() == SURVEYPRO_TYPEFIELD) {
            // -----------------------------
            // here I open a new fieldset
            // -----------------------------
            $fieldname = 'specializations';
            $typename = get_string('pluginname', 'surveyprofield_'.$item->get_plugin());
            $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro', $typename));
        }
    }

    /*
     * add_item_buttons
     *
     * @param none
     * @return none
     */
    public function add_item_buttons() {
        global $CFG;

        $mform = $this->_form;

        // ----------------------------------------
        $item = $this->_customdata->item;
        $surveypro = $this->_customdata->surveypro;

        $hassubmissions = surveypro_count_submissions($surveypro->id);
        $riskyediting = ($surveypro->riskyeditdeadline > time());

        // ----------------------------------------
        // buttons
        $itemid = $item->get_itemid();
        if (!empty($itemid)) {
            $fieldname = 'buttons';
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('submit', 'save', get_string('savechanges'));
            if (!$hassubmissions || $riskyediting) {
                $elementgroup[] = $mform->createElement('submit', 'saveasnew', get_string('saveasnew', 'surveypro'));
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
        global $CFG, $DB;

        // ----------------------------------------
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = array();

        // if (default == noanswer) but item is required => error
        if ( isset($data['defaultvalue_check']) && isset($data['required']) ) {
            $a = get_string('noanswer', 'surveypro');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'surveypro', $a);
        }

        if (empty($data['parentid']) && empty($data['parentcontent'])) {
            // stop verification here
            return $errors;
        }

        // you choosed a parentid but you are missing the parentcontent
        if (empty($data['parentid']) && (strlen($data['parentcontent']) > 0)) { // $data['parentcontent'] can be = '0'
            $a = get_string('parentcontent', 'surveypro');
            $errors['parentid'] = get_string('missingparentid_err', 'surveypro', $a);
        }

        // you did not choose a parent item but you entered an answer
        if ( !empty($data['parentid']) && (strlen($data['parentcontent']) == 0) ) { // $data['parentcontent'] can be = '0'
            $a = get_string('parentid', 'surveypro');
            $errors['parentcontent'] = get_string('missingparentcontent_err', 'surveypro', $a);
        }

        return $errors;
    }
}
