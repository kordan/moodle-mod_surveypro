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
 * Surveypro layout_itemsetup class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;
use mod_surveypro\local\ipe\layout_customnumber;
use mod_surveypro\local\ipe\layout_insearchform;
use mod_surveypro\local\ipe\layout_required;
use mod_surveypro\local\ipe\layout_reserved;
use mod_surveypro\local\ipe\layout_variable;
use mod_surveypro\utility_layout;

/**
 * The base class representing the list of elements of this surveypro
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class layout_itemsetup {

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
     * @var string Type of the leading item
     */
    protected $type;

    /**
     * @var string Plugin of the leading item
     */
    protected $plugin;

    /**
     * @var int Id of the leading item
     */
    protected $rootitemid;

    /**
     * @var int Sortindex of the leading item
     */
    protected $sortindex;

    /**
     * @var int Required action
     */
    protected $action;

    /**
     * @var int Required mode
     */
    protected $mode;

    /**
     * @var int Id of the item to move
     */
    protected $itemtomove;

    /**
     * @var int Id of the last item before the moving one
     */
    protected $lastitembefore;

    /**
     * @var int User confirmation to actions
     */
    protected $confirm;

    /**
     * @var int New indent requested for $itemid
     */
    protected $nextindent;

    /**
     * @var int Id of the parent item of $itemid
     */
    protected $parentid;

    /**
     * @var bool True if this surveypro has submissions; false otherwise
     */
    protected $hassubmissions;

    /**
     * @var int Count of the items of this surveypro
     */
    protected $itemcount;

    /**
     * @var int Binary number providing a mask for the feedback of the item editing
     */
    protected $itemeditingfeedback;

    /**
     * @var int Feedback of the performed action
     */
    protected $actionfeedback;

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

        $utilitylayoutman = new utility_layout($cm, $surveypro);
        $itemcount = $utilitylayoutman->has_items(0, null, true, true, true);
        $this->set_itemcount($itemcount);
    }

    /**
     * Setup.
     *
     * @return void
     */
    public function setup() {
        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $itemcount = $utilitylayoutman->has_items(0, null, true, true, true);
        $this->set_itemcount($itemcount);
    }

    /**
     * Set typeplugin.
     *
     * @param string $typeplugin
     * @return void
     */
    public function set_typeplugin($typeplugin) {
        if (preg_match('~^('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_(\w+)$~', $typeplugin, $match)) {
            // Execution comes from /form/items/selectitemform.php.
            $this->type = $match[1]; // Field or format.
            $this->plugin = $match[2]; // Boolean or char ... or fieldset ...
        } else {
            $message = 'Malformed typeplugin parameter passed to set_typeplugin';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    // MARK set.

    /**
     * Set type.
     *
     * @param string $type
     * @return void
     */
    public function set_type($type) {
        $this->type = $type;
    }

    /**
     * Set plugin.
     *
     * @param string $plugin
     * @return void
     */
    public function set_plugin($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Set itemid.
     *
     * @param int $itemid
     * @return void
     */
    public function set_itemid($itemid) {
        $this->rootitemid = $itemid;
    }

    /**
     * Set action.
     *
     * @param int $action
     * @return void
     */
    public function set_action($action) {
        $this->action = $action;
    }

    /**
     * Set itemcount.
     *
     * @param int $itemcount
     * @return void
     */
    public function set_itemcount($itemcount) {
        $this->itemcount = $itemcount;
    }

    /**
     * Set mode.
     *
     * @param int $mode
     * @return void
     */
    public function set_mode($mode) {
        $this->mode = $mode;
    }

    /**
     * Set hassubmissions.
     *
     * @param int $hassubmissions
     * @return void
     */
    public function set_hassubmissions($hassubmissions) {
        $this->hassubmissions = $hassubmissions;
    }

    // MARK get.

    /**
     * Get type.
     *
     * @return void
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get plugin.
     *
     * @return void
     */
    public function get_plugin() {
        return $this->plugin;
    }

    // MARK other.

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    public function prevent_direct_user_input() {
        if ($this->surveypro->template) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Display the identity card of the item going to be created/edited just before the beginning of the item form.
     *
     * @return void
     */
    public function item_identitycard() {
        global $OUTPUT;

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '..
        $friendlyname = get_string('userfriendlypluginname', 'surveypro'.$this->type.'_'.$this->plugin);

        $iconparams = ['title' => $friendlyname, 'class' => 'icon'];
        $message = $OUTPUT->pix_icon('icon', $friendlyname, 'surveypro'.$this->type.'_'.$this->plugin, $iconparams);
        $message .= get_string($this->type, 'mod_surveypro').$labelsep.$friendlyname;

        echo $OUTPUT->box($message);
    }

}
