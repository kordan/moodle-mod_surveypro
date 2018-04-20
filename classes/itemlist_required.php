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
 * Contains class mod_surveypro\mod_surveypro_itemlist_required
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class to prepare an item variable for display and in-place editing
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_itemlist_required extends \core\output\inplace_editable {

    /**
     * @var sortindex
     * to provide an ID to the toggle image
     * needed for behat test
     */
    protected $sortindex;

    /**
     * Constructor.
     *
     * @param int $itemid
     * @param bool $required
     * @param int $sortindex
     */
    public function __construct($itemid, $required, $sortindex) {
        $this->sortindex = $sortindex;

        $required = clean_param($required, PARAM_INT);
        parent::__construct('mod_surveypro', 'itemlist_required', $itemid, true, '', $required);
        $this->set_type_toggle();
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        if ($this->value) {
            $requiredstr = get_string('requireditem_title', 'mod_surveypro');
            $iconparams = array('id' => 'makeoptional_item_'.$this->sortindex);
            $this->edithint = $requiredstr;
            $this->displayvalue = $output->pix_icon('red', $requiredstr, 'mod_surveypro', $iconparams);
        } else {
            $optionalstr = get_string('optionalitem_title', 'mod_surveypro');
            $iconparams = array('id' => 'makerequired_item_'.$this->sortindex);
            $this->edithint = $optionalstr;
            $this->displayvalue = $output->pix_icon('green', $optionalstr, 'mod_surveypro', $iconparams);
        }

        return parent::export_for_template($output);
    }

    /**
     * Updates usertemplate name and returns instance of this object
     *
     * @param int $itemid
     * @param string $newrequired
     * @return static
     */
    public static function update($itemid, $newrequired) {
        global $DB;

        $fields = 'id, surveyproid, type, plugin, sortindex';
        $itemrecord = $DB->get_record('surveypro_item', array('id' => $itemid), $fields, MUST_EXIST);
        $surveypro = $DB->get_record('surveypro', array('id' => $itemrecord->surveyproid), 'id, course', MUST_EXIST);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        \external_api::validate_context($context);

        $tablename = 'surveypro'.$itemrecord->type.'_'.$itemrecord->plugin;
        $newrequired = clean_param($newrequired, PARAM_INT);
        $DB->set_field($tablename, 'required', $newrequired, array('itemid' => $itemid));

        if (!empty($newrequired)) {
            // This item that WAS NOT mandatory IS NOW mandatory.
            $utilityman = new mod_surveypro_utility($cm, $surveypro);
            $utilityman->optional_to_required_followup($itemid);
        }

        return new static($itemid, $newrequired, $itemrecord->sortindex);
    }
}
