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
 * The searchmanager class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\utility_item;

/**
 * The class managing the search form for users
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_view_search {

    /**
     * @var object Course module object
     */
    protected $cm;

    /**
     * @var object Context object
     */
    protected $context;

    /**
     * @var object Surveypro object
     */
    protected $surveypro;

    /**
     * @var object Form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $context
     * @param object $surveypro
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
    }

    /**
     * Get the searchparamurl.
     *
     * At the submission time of the seach form, define the $searchparamurl to send to view_submissions.php
     *
     * @return mixed $searchquery if a search was requested, void otherwise
     */
    public function get_searchparamurl() {
        $itemhelperinfo = array();
        foreach ($this->formdata as $elementname => $content) {
            if ($matches = utility_item::get_item_parts($elementname)) {
                // With the introduction of interactive fieldset...
                // those format elements are now equipped with open/close triangle...
                // and they submit their own state.
                // Drop them out.
                $condition = false;
                $condition = $condition || ($matches['prefix'] == SURVEYPRO_DONTSAVEMEPREFIX);
                $condition = $condition || ($matches['type'] == SURVEYPRO_TYPEFORMAT);
                if ($condition) {
                    // Multiselect are always submitted because, at least, they have SURVEYPRO_IGNOREMEVALUE.
                    continue;
                }

                $itemid = $matches['itemid'];
                if (!isset($itemhelperinfo[$itemid])) {
                    $itemhelperinfo[$itemid] = new \stdClass();
                    $itemhelperinfo[$itemid]->type = $matches['type'];
                    $itemhelperinfo[$itemid]->plugin = $matches['plugin'];
                    $itemhelperinfo[$itemid]->itemid = $itemid;
                }
                if (!isset($matches['option'])) {
                    $itemhelperinfo[$itemid]->contentperelement['mainelement'] = $content;
                } else {
                    $itemhelperinfo[$itemid]->contentperelement[$matches['option']] = $content;
                }
            }
        }

        $searchfields = array();
        foreach ($itemhelperinfo as $iteminfo) {
            if (isset($iteminfo->contentperelement['ignoreme'])) {
                if ($iteminfo->contentperelement['ignoreme']) {
                    // Do not waste your time.
                    continue;
                }
            }
            if (isset($iteminfo->contentperelement['mainelement'])) {
                if ($iteminfo->contentperelement['mainelement'] == SURVEYPRO_IGNOREMEVALUE) {
                    // Do not waste your time.
                    continue;
                }
            }
            $item = surveypro_get_item($this->cm, $this->surveypro, $iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

            $userdata = new \stdClass();
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
}
