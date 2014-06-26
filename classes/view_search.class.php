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

/*
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * The base class representing a field
 */
class mod_surveypro_searchmanager {
    /*
     * $context
     */
    public $context = null;

    /*
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /*
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

    /*
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /*
     * Class constructor
     */
    public function __construct($cm, $surveypro) {
        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
        $this->surveypro = $surveypro;
        $this->canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);
    }

    /*
     * get_searchparamurl
     *
     * @return
     */
    public function get_searchparamurl() {
        $regexp = '~('.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_PLACEHOLDERPREFIX.')_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';

        $itemhelperinfo = array();
        foreach ($this->formdata as $elementname => $content) {

            if (preg_match($regexp, $elementname, $matches)) {
                $itemid = $matches[4]; // itemid of the search_form element (or of the search_form family element)
                if (!isset($itemhelperinfo[$itemid])) {
                    $itemhelperinfo[$itemid] = new stdClass();
                    $itemhelperinfo[$itemid]->type = $matches[2];
                    $itemhelperinfo[$itemid]->plugin = $matches[3];
                    $itemhelperinfo[$itemid]->itemid = $itemid;
                }
                if (!isset($matches[5])) {
                    $itemhelperinfo[$itemid]->contentperelement['mainelement'] = $content;
                } else {
                    $itemhelperinfo[$itemid]->contentperelement[$matches[5]] = $content;
                }
            }
        }

        $searchfields = array();
        foreach ($itemhelperinfo as $iteminfo) {
            if ( isset($iteminfo->contentperelement['ignoreme']) && $iteminfo->contentperelement['ignoreme'] ) {
                // do not waste your time
                continue;
            }
            if ( isset($iteminfo->contentperelement['mainelement']) && ($iteminfo->contentperelement['mainelement'] == SURVEYPRO_IGNOREME)) {
                // do not waste your time
                continue;
            }
            $item = surveypro_get_item($iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

            $userdata = new stdClass();
            $item->userform_save_preprocessing($iteminfo->contentperelement, $userdata, true);

            if (!is_null($userdata->content)) {
                $searchfields[$iteminfo->itemid] = $userdata->content;
            }
        }

        if ($searchfields) {
            return serialize($searchfields);
        } else {
            return;
        }
    }

    /*
     * count_input_items
     *
     * @return
     */
    public function count_search_items() {
        global $DB;

        // if no items are available, stop the intervention here
        $whereparams = array('surveyproid' => $this->surveypro->id);
        $whereclause = 'surveyproid = :surveyproid AND hidden = 0 AND insearchform = 1';

        return $DB->count_records_select('surveypro_item', $whereclause, $whereparams);
    }

    /*
     * noitem_stopexecution
     *
     * @return
     */
    public function noitem_stopexecution() {
        global $COURSE, $OUTPUT;

        echo $OUTPUT->notification(get_string('emptysearchform', 'surveypro'), 'notifyproblem');

        $continueurl = new moodle_url('/mod/surveypro/view_manage.php', array('s' => $this->surveypro->id));
        echo $OUTPUT->continue_button($continueurl);

        echo $OUTPUT->footer();
        die();
    }
}