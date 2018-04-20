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
 * Contains class mod_surveypro\mod_surveypro_itemlist_reserved
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
class mod_surveypro_itemlist_reserved extends \core\output\inplace_editable {

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
     * @param bool $reserved
     * @param int $sortindex
     */
    public function __construct($itemid, $reserved, $sortindex) {
        $this->sortindex = $sortindex;

        $reserved = clean_param($reserved, PARAM_INT);
        parent::__construct('mod_surveypro', 'itemlist_reserved', $itemid, true, '', $reserved);
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
            $reservedstr = get_string('reserved_title', 'mod_surveypro');
            $iconparams = array('id' => 'makeavailable_item_'.$this->sortindex);
            $this->edithint = $reservedstr;
            $this->displayvalue = $output->pix_icon('reserved', $reservedstr, 'surveypro', $iconparams);
        } else {
            $availablestr = get_string('available_title', 'mod_surveypro');
            $iconparams = array('id' => 'makereserved_item_'.$this->sortindex);
            $this->edithint = $availablestr;
            $this->displayvalue = $output->pix_icon('free', $availablestr, 'surveypro', $iconparams);
        }

        return parent::export_for_template($output);
    }

    /**
     * Updates usertemplate name and returns instance of this object
     *
     * @param int $itemid
     * @param string $newreserved
     * @return static
     */
    public static function update($itemid, $newreserved) {
        global $DB;

        $fields = 'id, surveyproid, type, plugin, sortindex';
        $itemrecord = $DB->get_record('surveypro_item', array('id' => $itemid), $fields, MUST_EXIST);
        $surveypro = $DB->get_record('surveypro', array('id' => $itemrecord->surveyproid), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        \external_api::validate_context($context);

        $newreserved = clean_param($newreserved, PARAM_INT);
        $DB->set_field('surveypro_item', 'reserved', $newreserved, array('id' => $itemid));

        return new static($itemid, $newreserved, $itemrecord->sortindex);
    }
}
