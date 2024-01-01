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
 * Surveypro utility class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_page;

/**
 * The utility class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility_page {

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
     * Class constructor.
     *
     * @param object $cm
     * @param object $surveypro
     */
    public function __construct($cm, $surveypro=null) {
        global $DB;

        $this->cm = $cm;
        $this->context = \context_module::instance($cm->id);
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
        }
        $this->surveypro = $surveypro;
    }

    /**
     * Display the Blocks editing on/Blocks editing off in each page
     *
     * @param boolean $edit
     * @return array $pluginlist;
     */
    public function manage_editbutton($edit) {
        global $PAGE, $OUTPUT, $USER;

        $pageparams = $PAGE->url->params();
        if (!isset($pageparams['s']) && !isset($pageparams['id'])) {
            $message = '<br>It seems you have not defined either \'s\' or \'id\' in the URL.';
            $message .= '<br>Maybe you forget to define $PAGE->set_url before calling $PAGE->manage_editbutton()';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        if (($edit != -1) && $PAGE->user_allowed_editing()) {
            $USER->editing = $edit;
        }
        if ($PAGE->user_allowed_editing() && !$PAGE->theme->haseditswitch) {
            // Change URL parameter and block display string value depending on whether editing is enabled or not.
            if ($PAGE->user_is_editing()) {
                $urlediting = 'off';
                $strediting = get_string('blockseditoff');
            } else {
                $urlediting = 'on';
                $strediting = get_string('blocksediton');
            }
            $pageparams = ['edit' => $urlediting];
            $editurl = new \moodle_url($PAGE->url->out(false), $pageparams);

            $PAGE->set_button($OUTPUT->single_button($editurl, $strediting));
        }
    }
}
