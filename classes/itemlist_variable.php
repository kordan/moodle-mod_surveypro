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
 * Contains class mod_surveypro\mod_surveypro_itemlist_variable
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
class mod_surveypro_itemlist_variable extends \core\output\inplace_editable {
    /**
     * Constructor.
     *
     * @param int $itemid
     * @param string $variablename
     */
    public function __construct($itemid, $variablename) {
        $variablename = format_string($variablename);
        parent::__construct('mod_surveypro', 'itemlist_variable', $itemid, true, $variablename, $variablename);
    }

    /**
     * Updates usertemplate name and returns instance of this object
     *
     * @param int $itemid
     * @param string $newvarname
     * @return static
     */
    public static function update($itemid, $newvarname) {
        global $DB;

        $itemrecord = $DB->get_record('surveypro_item', array('id' => $itemid), 'id, surveyproid, type, plugin', MUST_EXIST);
        $surveypro = $DB->get_record('surveypro', array('id' => $itemrecord->surveyproid), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        \external_api::validate_context($context);

        // Why was I required to move surveypro_get_item from locallib.php to lib.php?
        $item = surveypro_get_item($cm, $surveypro, $itemid, $itemrecord->type, $itemrecord->plugin);

        // Before saving to the the plugin table, validate the variable name.
        $record = new stdClass();
        $record->surveyproid = $surveypro->id;
        $record->variable = $newvarname;
        $record->plugin = $itemrecord->plugin;

        $item->item_validate_variablename($record, $itemid);

        $tablename = 'surveypro'.$itemrecord->type.'_'.$itemrecord->plugin;
        $newvarname = $record->variable;
        $DB->set_field($tablename, 'variable', $newvarname, array('itemid' => $itemid));

        return new static($itemid, $newvarname);
    }
}
