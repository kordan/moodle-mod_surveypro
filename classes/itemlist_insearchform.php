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
 * Contains class mod_surveypro\mod_surveypro_itemlist_insearchform
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
class mod_surveypro_itemlist_insearchform extends \core\output\inplace_editable {

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
     * @param bool $insearchform
     * @param int $sortindex
     */
    public function __construct($itemid, $insearchform, $sortindex) {
        $this->sortindex = $sortindex;

        $insearchform = clean_param($insearchform, PARAM_INT);
        parent::__construct('mod_surveypro', 'itemlist_insearchform', $itemid, true, '', $insearchform);
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
            $insearchformstr = get_string('insearchform_title', 'mod_surveypro');
            $iconparams = array('id' => 'removefromsearch_item_'.$this->sortindex);
            $this->edithint = $insearchformstr;
            $this->displayvalue = $output->pix_icon('insearch', $insearchformstr, 'mod_surveypro', $iconparams);
        } else {
            $notinsearchformstr = get_string('notinsearchform_title', 'mod_surveypro');
            $iconparams = array('id' => 'addtosearch_item_'.$this->sortindex);
            $this->edithint = $notinsearchformstr;
            $this->displayvalue = $output->pix_icon('notinsearch', $notinsearchformstr, 'mod_surveypro', $iconparams);
        }

        return parent::export_for_template($output);
    }

    /**
     * Updates usertemplate name and returns instance of this object
     *
     * @param int $itemid
     * @param string $newinsearchform
     * @return static
     */
    public static function update($itemid, $newinsearchform) {
        global $DB;

        $fields = 'id, surveyproid, type, plugin, sortindex';
        $itemrecord = $DB->get_record('surveypro_item', array('id' => $itemid), $fields, MUST_EXIST);
        $surveypro = $DB->get_record('surveypro', array('id' => $itemrecord->surveyproid), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        \external_api::validate_context($context);

        $newinsearchform = clean_param($newinsearchform, PARAM_INT);
        $DB->set_field('surveypro_item', 'insearchform', $newinsearchform, array('id' => $itemid));

        return new static($itemid, $newinsearchform, $itemrecord->sortindex);
    }
}
