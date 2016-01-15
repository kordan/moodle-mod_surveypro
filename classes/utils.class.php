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

/**
 * The base class representing a field
 */
class mod_surveypro_utility {
    /**
     * Basic necessary essential ingredients
     */
    protected $cm;
    protected $surveypro;

    /**
     * Class constructor
     */
    public function __construct($cm, $surveypro=null) {
        global $DB;

        $this->cm = $cm;
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
        }
        $this->surveypro = $surveypro;
    }

    /**
     * assign_pages
     *
     * @param none
     * @return
     */
    public function assign_pages() {
        global $DB;

        $where = array();
        $where['surveyproid'] = $this->surveypro->id;
        $where['hidden'] = 0;

        $maxassignedpage = 0;
        $lastwaspagebreak = true; // Whether 2 page breaks in line, the second one is ignored.
        $pagenumber = 1;
        $items = $DB->get_recordset('surveypro_item', $where, 'sortindex', 'id, type, plugin, parentid, formpage, sortindex');
        if ($items) {
            foreach ($items as $item) {
                if ($item->plugin == 'pagebreak') { // It is a page break.
                    if (!$lastwaspagebreak) {
                        $pagenumber++;
                    }
                    $lastwaspagebreak = true;
                } else {
                    $lastwaspagebreak = false;
                    if ($this->surveypro->newpageforchild) {
                        if (!empty($item->parentid)) {
                            $parentpage = $DB->get_field('surveypro_item', 'formpage', array('id' => $item->parentid), MUST_EXIST);
                            if ($parentpage == $pagenumber) {
                                $pagenumber++;
                            }
                        }
                    }
                    // echo 'Assigning pages: $DB->set_field(\'surveypro_item\', \'formpage\', '.$pagenumber.', array(\'id\' => '.$item->id.'));<br />';
                    $DB->set_field('surveypro_item', 'formpage', $pagenumber, array('id' => $item->id));
                }
            }
            $items->close();
            $maxassignedpage = $pagenumber;
        }

        return $maxassignedpage;
    }

    /**
     * has_input_items
     *
     * @param $surveyproid
     * @param $formpage
     * @param $includehidden
     * @param $includeadvanced
     * @return bool|int as required by $returncount
     */
    public function has_input_items($formpage=0, $returncount=false, $includehidden=false, $includeadvanced=false) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD);
        if (!empty($formpage)) {
            $whereparams['formpage'] = $formpage;
        }
        if (!$includehidden) {
            $whereclause['hidden'] = 0;
        }
        if (!$includeadvanced) {
            $whereclause['advanced'] = 0;
        }

        if ($returncount) {
            return $DB->count_records('surveypro_item', $whereparams);
        } else {
            return ($DB->count_records('surveypro_item', $whereparams) > 0);
        }
    }

    /**
     * has_search_items
     *
     * @param $surveyproid
     * @return bool|int as required by $returncount
     */
    public function has_search_items($returncount=false) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD, 'hidden' => 0, 'insearchform' => 1);

        if ($returncount) {
            return $DB->count_records('surveypro_item', $whereparams);
        } else {
            return ($DB->count_records('surveypro_item', $whereparams) > 0);
        }
    }

    /**
     * surveypro_count_submissions
     *
     * @param $surveyproid
     * @param $status
     * @return
     */
    public function has_submissions($returncount=false, $status=SURVEYPRO_STATUSALL) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id);
        if ($status != SURVEYPRO_STATUSALL) {
            $params['status'] = $status;
        }

        if ($returncount) {
            return $DB->count_records('surveypro_submission', $whereparams);
        } else {
            return ($DB->count_records('surveypro_submission', $whereparams) > 0);
        }
    }
}
