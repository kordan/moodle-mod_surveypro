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
class mod_surveypro_itemlist {
    /**
     * $cm
     */
    public $cm = null;

    /**
     * $context
     */
    public $context = null;

    /**
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /**
     * $type
     */
    public $type = '';

    /**
     * $plugin
     */
    public $plugin = '';

    /**
     * $itemid
     */
    public $itemid = 0;

    /**
     * $sortindex
     */
    public $sortindex = 0;

    /**
     * $action
     */
    public $action = SURVEYPRO_NOACTION;

    /**
     * $action
     */
    public $view = SURVEYPRO_NEWRESPONSE;

    /**
     * $itemtomove
     */
    public $itemtomove = 0;

    /**
     * $lastitembefore
     */
    public $lastitembefore = 0;

    /**
     * $confirm
     */
    public $confirm = SURVEYPRO_UNCONFIRMED;

    /**
     * $nextindent
     */
    public $nextindent = 0;

    /**
     * $parentid
     */
    public $parentid = 0;

    /**
     * $userfeedbackmask
     */
    public $userfeedbackmask = SURVEYPRO_NOFEEDBACK;

    /**
     * $saveasnew
     */
    public $saveasnew = 0;

    /**
     * $hassubmissions
     */
    public $hassubmissions = null;

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;

        $this->hassubmissions = surveypro_count_submissions($surveypro->id, SURVEYPRO_STATUSALL);
    }

    /**
     * drop_multilang
     *
     * @param none
     * @return none
     */
    public function drop_multilang() {
        if ($this->surveypro->template) {
            $this->action = SURVEYPRO_DROPMULTILANG;
            if ($this->confirm != SURVEYPRO_UNCONFIRMED) {
                $this->manage_actions();
            }
        }
    }

    /**
     * trigger_event
     *
     * @param $itemcount
     * @return none
     */
    public function trigger_event($itemcount) {
        if (!empty($itemcount)) {
            $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
            $event = \mod_surveypro\event\all_items_viewed::create($eventdata);
            $event->trigger();
        }
    }

    /**
     * manage_actions
     *
     * @param none
     * @return
     */
    public function manage_actions() {
        global $DB;

        switch ($this->action) {
            case SURVEYPRO_NOACTION:
                break;
            case SURVEYPRO_HIDEITEM:
                $this->manage_item_hide();
                break;
            case SURVEYPRO_SHOWITEM:
                $this->manage_item_show();
                break;
            case SURVEYPRO_DELETEITEM:
                $this->manage_item_deletion();
                break;
            case SURVEYPRO_DROPMULTILANG:
                $this->manage_item_dropmultilang();
                break;
            case SURVEYPRO_CHANGEORDER:
                // it was required to move the item $this->itemid
                $this->reorder_items();
                break;
            case SURVEYPRO_REQUIREDON:
                $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);
                $item->set_required(1);
                break;
            case SURVEYPRO_REQUIREDOFF:
                $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);
                $item->set_required(0);
                break;
            case SURVEYPRO_CHANGEINDENT:
                $DB->set_field('surveypro'.$this->type.'_'.$this->plugin, 'indent', $this->nextindent, array('itemid' => $this->itemid));
                break;
            case SURVEYPRO_ADDTOSEARCH:
                $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);
                if ($item->get_isinitemform('insearchform')) {
                    $DB->set_field('surveypro_item', 'insearchform', 1, array('id' => $this->itemid));
                }
                break;
            case SURVEYPRO_OUTOFSEARCH:
                $DB->set_field('surveypro_item', 'insearchform', 0, array('id' => $this->itemid));
                break;
            case SURVEYPRO_MAKEADVANCED:
                $this->manage_item_makeadvanced();
                break;
            case SURVEYPRO_MAKESTANDARD:
                $this->manage_item_makestandard();
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->action = '.$this->action, DEBUG_DEVELOPER);
        }
    }

    /**
     * display_items_table
     *
     * @param none
     * @return
     */
    public function display_items_table() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $table = new flexible_table('itemslist');

        $paramurl = array('id' => $this->cm->id);
        $baseurl = new moodle_url('/mod/surveypro/items_manage.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'plugin';
        $tablecolumns[] = 'sortindex';
        $tablecolumns[] = 'parentid';
        $tablecolumns[] = 'customnumber';
        $tablecolumns[] = 'content';
        $tablecolumns[] = 'variable';
        $tablecolumns[] = 'formpage';
        $tablecolumns[] = 'availability';
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('plugin', 'surveypro');
        $tableheaders[] = get_string('sortindex', 'surveypro');
        $tableheaders[] = get_string('branching', 'surveypro');
        $tableheaders[] = get_string('customnumber_header', 'surveypro');
        $tableheaders[] = get_string('content', 'surveypro');
        $tableheaders[] = get_string('variable', 'surveypro');
        $tableheaders[] = get_string('page');
        $tableheaders[] = get_string('availability', 'surveypro');
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        // $table->collapsible(true);
        $table->sortable(true, 'sortindex'); // sorted by sortindex by default
        $table->no_sorting('content');
        $table->no_sorting('variable');
        $table->no_sorting('availability');
        $table->no_sorting('actions');

        $table->column_class('plugin', 'plugin');
        $table->column_class('sortindex', 'sortindex');
        $table->column_class('parentid', 'parentitem');
        $table->column_class('availability', 'availability');
        $table->column_class('formpage', 'formpage');

        $table->column_class('content', 'content');
        $table->column_class('customnumber', 'customnumber');
        $table->column_class('actions', 'actions');

        // $table->initialbars(true);

        // hide the same info whether in two consecutive rows
        // $table->column_suppress('picture');
        // $table->column_suppress('fullname');

        // general properties for the whole table
        // $table->set_attribute('cellpadding', '5');
        if ($this->view == SURVEYPRO_CHANGEORDERASK) {
            $table->set_attribute('id', 'sortitems');
        } else {
            $table->set_attribute('id', 'manageitems');
        }
        $table->set_attribute('class', 'generaltable');
        // $table->set_attribute('width', '90%');
        $table->setup();

        // -----------------------------
        $edittitle = get_string('edit');
        $requiredtitle = get_string('switchrequired', 'surveypro');
        $optionaltitle = get_string('switchoptional', 'surveypro');
        $onlyoptionaltitle = get_string('onlyoptional', 'surveypro');
        $changetitle = get_string('changeorder', 'surveypro');
        $hidetitle = get_string('hidefield', 'surveypro');
        $showtitle = get_string('showfield', 'surveypro');
        $deletetitle = get_string('delete');
        $indenttitle = get_string('indent', 'surveypro');
        $moveheretitle = get_string('movehere');
        $namenotset = get_string('namenotset', 'surveypro');

        // -----------------------------
        // $paramurlmove definition
        $paramurlmove = array();
        $paramurlmove['id'] = $this->cm->id;
        $paramurlmove['act'] = SURVEYPRO_CHANGEORDER;
        $paramurlmove['itm'] = $this->itemtomove;
        // end of $paramurlmove definition
        // -----------------------------

        $where = array('surveyproid' => $this->surveypro->id);
        if ($this->view == SURVEYPRO_CHANGEORDERASK) { // if you are reordering, force ordering to...
            $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex ASC', '*, id as itemid');
        } else {
            $itemseeds = $DB->get_records('surveypro_item', $where, $table->get_sql_sort(), '*, id as itemid');
        }
        $drawmovearrow = (count($itemseeds) > 1);

        // this is the very first position, so if the item has a parent, no "moveherebox" must appear
        if (($this->view == SURVEYPRO_CHANGEORDERASK) && (!$this->parentid)) {
            $drawmoveherebox = true;
            $paramurl = $paramurlmove;
            $paramurl['lib'] = 0; // move just after this sortindex (lib == last item before)
            $paramurl['sesskey'] = sesskey();

            $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php', $paramurl),
                new pix_icon('movehere', $moveheretitle, 'moodle', array('title' => $moveheretitle)),
                null, array('id' => 'moveafter_0', 'title' => $moveheretitle));

            $tablerow = array();
            $tablerow[] = $icons;
            $tablerow = array_pad($tablerow, count($table->columns), '');

            $table->add_data($tablerow);
        } else {
            $drawmoveherebox = false;
        }

        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin, true);

            $sortindex = $item->get_sortindex();

            // -----------------------------
            // $paramurlbase definition
            $paramurlbase = array();
            $paramurlbase['id'] = $this->cm->id;
            $paramurlbase['itemid'] = $item->get_itemid();
            $paramurlbase['type'] = $item->get_type();
            $paramurlbase['plugin'] = $item->get_plugin();
            // end of $paramurlbase definition
            // -----------------------------

            $tablerow = array();

            if (($this->view == SURVEYPRO_CHANGEORDERASK) && ($item->get_itemid() == $this->itemid)) {
                // do not draw the item you are going to move
                continue;
            }

            // plugin
            $plugintitle = get_string('userfriendlypluginname', 'surveypro'.$item->get_type().'_'.$item->get_plugin());
            $content = html_writer::tag('a', '', array('id' => 'sortindex_'.$sortindex, 'class' => 'hide'));
            $content .= $OUTPUT->pix_icon('icon', $plugintitle, 'surveypro'.$item->get_type().'_'.$item->get_plugin(),
                    array('title' => $plugintitle, 'class' => 'icon'));

            $tablerow[] = $content;

            // sortindex
            $tablerow[] = $sortindex;

            // parentid
            if ($item->get_parentid()) {
                // if (!empty($content)) $content .= ' ';
                $message = get_string('parentid_alt', 'surveypro');
                $parentsortindex = $DB->get_field('surveypro_item', 'sortindex', array('id' => $item->get_parentid()));
                $content = $parentsortindex;
                $content .= $OUTPUT->pix_icon('branch', $message, 'surveypro', array('title' => $message, 'class' => 'smallicon'));
                $content .= $item->get_parentcontent('; ');
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // customnumber
            if (($item->get_type() == SURVEYPRO_TYPEFIELD) || ($item->get_plugin() == 'label')) {
                $tablerow[] = $item->get_customnumber();
            } else {
                $tablerow[] = '';
            }

            // content
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = $item->get_content();
            $tablerow[] = $output;

            // variable
            if ($item->get_type() == SURVEYPRO_TYPEFIELD) {
                if ($variable = $item->get_variable()) {
                    $content = $variable;
                } else {
                    $content = $namenotset;
                }
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // page
            if ($item->item_uses_form_page()) {
                $content = $item->get_formpage();
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // availability
            $currenthide = $item->get_hidden();
            if (empty($currenthide)) {
                // first icon: advanced vs standard (generally available)
                if (!$item->get_advanced()) {
                    $message = get_string('available', 'surveypro');
                    if ($item->get_isinitemform('advanced')) {
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEYPRO_MAKEADVANCED;
                        $paramurl['sortindex'] = $sortindex;
                        $paramurl['sesskey'] = sesskey();

                        $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                            new pix_icon('all', $message, 'surveypro', array('title' => $message)),
                            null, array('id' => 'limitaccess_item_'.$item->sortindex, 'title' => $message));
                    } else {
                        $icons = $OUTPUT->pix_icon('all', $message, 'surveypro', array('title' => $message, 'class' => 'smallicon'));
                    }
                } else {
                    $message = get_string('needrole', 'surveypro');
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_MAKESTANDARD;
                    $paramurl['sortindex'] = $sortindex;
                    $paramurl['sesskey'] = sesskey();

                    $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                        new pix_icon('limited', $message, 'surveypro', array('title' => $message)),
                        null, array('id' => 'increaseaccess_item_'.$item->sortindex, 'title' => $message));
                }

                // second icon: insearchform vs not insearchform
                if ($item->get_insearchform()) {
                    $message = get_string('belongtosearchform', 'surveypro');
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_OUTOFSEARCH;
                    $paramurl['sesskey'] = sesskey();

                    $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                        new pix_icon('insearch', $message, 'surveypro', array('title' => $message)),
                        null, array('id' => 'removesearch_item_'.$item->sortindex, 'title' => $message));
                } else {
                    $message = get_string('notinsearchform', 'surveypro');
                    if ($item->get_isinitemform('insearchform')) {
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEYPRO_ADDTOSEARCH;
                        $paramurl['sesskey'] = sesskey();

                        $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                            new pix_icon('absent', $message, 'surveypro', array('title' => $message)),
                            null, array('id' => 'addtosearch_item_'.$item->sortindex, 'title' => $message));
                    } else {
                        $icons .= $OUTPUT->pix_icon('absent', $message, 'surveypro', array('title' => $message, 'class' => 'smallicon'));
                    }
                }
            } else {
                $message = get_string('hidden', 'surveypro');
                $icons = $OUTPUT->pix_icon('absent', $message, 'surveypro', array('title' => $message, 'class' => 'smallicon'));

                // $message = get_string('hidden', 'surveypro');
                $icons .= $OUTPUT->pix_icon('absent', $message, 'surveypro', array('title' => $message, 'class' => 'smallicon'));
            }

            // third icon: hide vs show
            if (!$this->hassubmissions || $riskyediting) {
                $paramurl = $paramurlbase;
                $paramurl['sesskey'] = sesskey();
                if (empty($currenthide)) {
                    $icopath = 't/hide';
                    $paramurl['act'] = SURVEYPRO_HIDEITEM;
                    $paramurl['sortindex'] = $sortindex;
                    $message = $hidetitle;
                    $linkidprefix = 'hide_item_';
                } else {
                    $icopath = 't/show';
                    $paramurl['act'] = SURVEYPRO_SHOWITEM;
                    $paramurl['sortindex'] = $sortindex;
                    $message = $showtitle;
                    $linkidprefix = 'show_item_';
                }

                $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                    new pix_icon($icopath, $message, 'moodle', array('title' => $message)),
                    null, array('id' => $linkidprefix.$item->sortindex, 'title' => $message));
            }
            $tablerow[] = $icons;

            // actions
            if ($this->view != SURVEYPRO_CHANGEORDERASK) {

                $icons = '';
                // SURVEYPRO_EDITITEM
                $paramurl = $paramurlbase;
                $paramurl['view'] = SURVEYPRO_EDITITEM;

                $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_setup.php', $paramurl),
                    new pix_icon('t/edit', $edittitle, 'moodle', array('title' => $edittitle)),
                    null, array('id' => 'edit_item_'.$item->sortindex, 'title' => $edittitle));

                // SURVEYPRO_CHANGEORDERASK
                if (!empty($drawmovearrow)) {
                    $paramurl = $paramurlbase;
                    $paramurl['view'] = SURVEYPRO_CHANGEORDERASK;
                    $paramurl['itm'] = $sortindex;

                    $currentparentid = $item->get_parentid();
                    if (!empty($currentparentid)) {
                        $paramurl['pid'] = $currentparentid;
                    }

                    $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.($sortindex - 1), $paramurl),
                        new pix_icon('t/move', $edittitle, 'moodle', array('title' => $edittitle)),
                        null, array('id' => 'move_item_'.$item->sortindex, 'title' => $edittitle));
                }

                // SURVEYPRO_DELETEITEM
                if (!$this->hassubmissions || $riskyediting) {
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_DELETEITEM;
                    $paramurl['sortindex'] = $sortindex;
                    $paramurl['sesskey'] = sesskey();

                    $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                        new pix_icon('t/delete', $deletetitle, 'moodle', array('title' => $deletetitle)),
                        null, array('id' => 'delete_item_'.$item->sortindex, 'title' => $deletetitle));
                }

                // SURVEYPRO_REQUIRED ON/OFF
                $currentrequired = $item->get_required();
                if ($currentrequired !== false) { // it may not be set as in page_break, autofill or some more
                    $paramurl = $paramurlbase;
                    $paramurl['sesskey'] = sesskey();

                    if ($currentrequired) {
                        $icopath = 'red';
                        $paramurl['act'] = SURVEYPRO_REQUIREDOFF;
                        $message = $optionaltitle;
                        $linkidprefix = 'makeoptional_item_';
                    } else {
                        if ($item->item_mandatory_is_allowed()) {
                            $icopath = 'green';
                            $paramurl['act'] = SURVEYPRO_REQUIREDON;
                            $message = $requiredtitle;
                            $linkidprefix = 'makemandatory_item_';
                        } else {
                            $icopath = 'greenlock';
                            $message = $onlyoptionaltitle;
                        }
                    }

                    if ($icopath == 'greenlock') {
                        $icons .= $OUTPUT->pix_icon($icopath, $message, 'surveypro', array('title' => $message, 'class' => 'icon'));
                    } else {
                        $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                            new pix_icon($icopath, $message, 'surveypro', array('title' => $message)),
                            null, array('id' => $linkidprefix.$item->sortindex, 'title' => $message));
                    }
                }

                // SURVEYPRO_CHANGEINDENT
                $currentindent = $item->get_indent();
                if ($currentindent !== false) { // it may not be set as in page_break, autofill and some more
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_CHANGEINDENT;
                    $paramurl['sesskey'] = sesskey();

                    if ($item->get_indent() > 0) {
                        $indentvalue = $item->get_indent() - 1;
                        $paramurl['ind'] = $indentvalue;

                        $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                            new pix_icon('t/left', $indenttitle, 'moodle', array('title' => $indenttitle)),
                            null, array('id' => 'reduceindent_item_'.$item->sortindex, 'title' => $indenttitle));
                    }
                    $icons .= '&nbsp;['.$item->get_indent().']';
                    if ($item->get_indent() < 9) {
                        $indentvalue = $item->get_indent() + 1;
                        $paramurl['ind'] = $indentvalue;

                        $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                            new pix_icon('t/right', $indenttitle, 'moodle', array('title' => $indenttitle)),
                            null, array('id' => 'increaseindent_item_'.$item->sortindex, 'title' => $indenttitle));
                    }
                }
            } else {
                $icons = '';
            }

            $tablerow[] = $icons;

            $rowclass = empty($currenthide) ? '' : 'dimmed';
            $table->add_data($tablerow, $rowclass);

            // print_object($item);
            if ($this->view == SURVEYPRO_CHANGEORDERASK) {
                // It was asked to move the item with:
                // $this->itemid and $this->parentid
                if ($this->parentid) { // <-- this is the parentid of the item that I am going to move
                    // if a parentid is foreseen
                    // draw the moveherebox only if the current (already displayed) item has: $item->itemid == $this->parentid
                    // once you start to draw the moveherebox, you will never stop
                    $drawmoveherebox = $drawmoveherebox || ($item->get_itemid() == $this->parentid);

                    // if you just passed an item with $item->get_parentid == $itemid, stop forever
                    if ($item->get_parentid() == $this->itemid) {
                        $drawmoveherebox = false;
                    }
                } else {
                    $drawmoveherebox = $drawmoveherebox && ($item->get_parentid() != $this->itemid);
                }

                if (!empty($drawmoveherebox)) {
                    $paramurl = $paramurlmove;
                    $paramurl['lib'] = $sortindex;
                    $paramurl['sesskey'] = sesskey();

                    $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$sortindex, $paramurl),
                        new pix_icon('movehere', $moveheretitle, 'moodle', array('title' => $moveheretitle)),
                        null, array('id' => 'move_item_'.$item->sortindex, 'title' => $moveheretitle));

                    $tablerow = array();
                    $tablerow[] = $icons;
                    $tablerow = array_pad($tablerow, count($table->columns), '');

                    $table->add_data($tablerow);
                }
            }
        }

        $table->set_attribute('align', 'center');
        $table->summary = get_string('itemlist', 'surveypro');
        $table->print_html();
    }

    /**
     * add_child_node
     *
     * @param &$nodelist
     * @param &$sortindexnodelist
     * @param $additionalcondition
     * @return
     */
    public function add_child_node(&$nodelist, &$sortindexnodelist, $additionalcondition) {
        global $DB;

        if (!is_array($additionalcondition)) {
            $a = 'add_child_node';
            print_error('arrayexpected', 'surveypro', null, $a);
        }

        $i = count($nodelist);
        $itemid = $nodelist[$i - 1];
        $where = array('parentid' => $itemid) + $additionalcondition;
        if ($childitems = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, sortindex')) {
            foreach ($childitems as $childitem) {
                $nodelist[] = (int)$childitem->id;
                $sortindexnodelist[] = $childitem->sortindex;
                $this->add_child_node($nodelist, $sortindexnodelist, $additionalcondition);
            }
        }
    }

    /**
     * add_parent_node
     *
     * @param $additionalcondition
     * @return
     */
    public function add_parent_node($additionalcondition) {
        global $DB;

        if (!is_array($additionalcondition)) {
            $a = 'add_parent_node';
            print_error('arrayexpected', 'surveypro', null, $a);
        }

        $nodelist = array($this->itemid);
        $sortindexnodelist = array();

        // get the first parentid
        $parentitem = new stdClass();
        $parentitem->parentid = $DB->get_field('surveypro_item', 'parentid', array('id' => $this->itemid));

        $where = array('id' => $parentitem->parentid) + $additionalcondition;

        while ($parentitem = $DB->get_record('surveypro_item', $where, 'id, parentid, sortindex')) {
            $nodelist[] = (int)$parentitem->id;
            $sortindexnodelist[] = $parentitem->sortindex;
            $where = array('id' => $parentitem->parentid) + $additionalcondition;
        }

        return array($nodelist, $sortindexnodelist);
    }

    /**
     * manage_item_hide
     *
     * @param none
     * @return
     */
    public function manage_item_hide() {
        global $DB, $OUTPUT;

        // build tohidelist
        // here I must select the whole tree down
        $tohidelist = array($this->itemid);
        $sortindextohidelist = array();
        $this->add_child_node($tohidelist, $sortindextohidelist, array('hidden' => 0));

        $itemstoprocess = count($tohidelist);
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // ask for confirmation
                $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->parentid = $item->get_content();
                $a->dependencies = implode(', ', $sortindextohidelist);
                $message = get_string('askitemstohide', 'surveypro', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_HIDEITEM, 'sesskey' => sesskey());

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstohide', 'surveypro'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            } else { // hide without asking
                $DB->set_field('surveypro_item', 'hidden', 1, array('id' => $this->itemid));
                surveypro_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    // hide items
                    foreach ($tohidelist as $tohideitemid) {
                        $DB->set_field('surveypro_item', 'hidden', 1, array('id' => $tohideitemid));
                    }
                    surveypro_reset_items_pages($this->cm->instance);
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'surveypro');
                    echo $OUTPUT->notification($message, 'notifymessage');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }

        return $itemstoprocess; // did you do something?
    }

    /**
     * manage_item_show
     *
     * @param none
     * @return
     */
    public function manage_item_show() {
        global $DB, $OUTPUT;

        // build toshowlist
        list($toshowlist, $sortindextoshowlist) = $this->add_parent_node(array('hidden' => 1));

        $itemstoprocess = count($toshowlist); // this is the list of ancestors
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // ask for confirmation
                $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->lastitem = $item->get_content();
                $a->ancestors = implode(', ', $sortindextoshowlist);
                $message = get_string('askitemstoshow', 'surveypro', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_SHOWITEM, 'itemid' => $this->itemid, 'sesskey' => sesskey());

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstoshow', 'surveypro'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            } else { // show without asking
                $DB->set_field('surveypro_item', 'hidden', 0, array('id' => $this->itemid));
                surveypro_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    // hide items
                    foreach ($toshowlist as $toshowitemid) {
                        $DB->set_field('surveypro_item', 'hidden', 0, array('id' => $toshowitemid));
                    }
                    surveypro_reset_items_pages($this->cm->instance);
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'surveypro');
                    echo $OUTPUT->notification($message, 'notifymessage');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }

        return $itemstoprocess; // did you do something?
    }

    /**
     * manage_item_makeadvanced
     *
     * the idea is: in a chain of parent-child items,
     *     -> items available to each user (standard items) can be parent of item available to each user such as item with limited access (advanced)
     *     -> item with limited access (advanced) can ONLY BE parent of items with limited access (advanced)
     *
     * @param none
     * @return
     */
    public function manage_item_makeadvanced() {
        global $DB, $OUTPUT;

        // build toadvancedlist
        // here I must select the whole tree down
        $toadvancedlist = array($this->itemid);
        $sortindextoadvancedlist = array();
        $this->add_child_node($toadvancedlist, $sortindextoadvancedlist, array('advanced' => 0));

        $itemstoprocess = count($toadvancedlist);
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if (count($toadvancedlist) > 1) { // ask for confirmation
                $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->parentid = $item->get_content();
                $a->dependencies = implode(', ', $sortindextoadvancedlist);
                $message = get_string('askitemstoadvanced', 'surveypro', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_MAKEADVANCED, 'sesskey' => sesskey());

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstoadvanced', 'surveypro'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            } else { // hide without asking
                $DB->set_field('surveypro_item', 'advanced', 1, array('id' => $this->itemid));
                surveypro_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    // hide items
                    foreach ($toadvancedlist as $tohideitemid) {
                        $DB->set_field('surveypro_item', 'advanced', 1, array('id' => $tohideitemid));
                    }
                    surveypro_reset_items_pages($this->cm->instance);
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'surveypro');
                    echo $OUTPUT->notification($message, 'notifymessage');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }

        return $itemstoprocess; // did you do something?
    }

    /**
     * manage_item_makestandard
     *
     * @param none
     * @return
     */
    public function manage_item_makestandard() {
        global $DB, $OUTPUT;

        // build tostandardlist
        list($tostandardlist, $sortindextostandardlist) = $this->add_parent_node(array('advanced' => 1));

        $itemstoprocess = count($tostandardlist); // this is the list of ancestors
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // ask for confirmation
                $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->lastitem = $item->get_content();
                $a->ancestors = implode(', ', $sortindextostandardlist);
                $message = get_string('askitemstostandard', 'surveypro', $a);

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_MAKESTANDARD, 'itemid' => $this->itemid, 'sesskey' => sesskey());

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->itemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmitemstostandard', 'surveypro'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            } else { // show without asking
                $DB->set_field('surveypro_item', 'advanced', 0, array('id' => $this->itemid));
                surveypro_reset_items_pages($this->cm->instance);
            }
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    // hide items
                    foreach ($tostandardlist as $toshowitemid) {
                        $DB->set_field('surveypro_item', 'advanced', 0, array('id' => $toshowitemid));
                    }
                    surveypro_reset_items_pages($this->cm->instance);
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $itemstoprocess = 0;
                    $message = get_string('usercanceled', 'surveypro');
                    echo $OUTPUT->notification($message, 'notifymessage');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }

        return $itemstoprocess; // did you do something?
    }

    /**
     * manage_item_deletion
     *
     * @param none
     * @return
     */
    public function manage_item_deletion() {
        global $DB, $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // ask for confirmation
            // in the frame of the confirmation I need to declare whether some child will break the link
            $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);

            $a = new stdClass();
            $a->content = $item->get_content();
            $a->pluginname = strtolower(get_string('pluginname', 'surveypro'.$this->type.'_'.$this->plugin));
            $message = get_string('askdeleteoneitem', 'surveypro', $a);

            // is there any child item link to break
            if ($childitems = $DB->get_records('surveypro_item', array('parentid' => $this->itemid), 'sortindex', 'sortindex')) { // sortindex is suposed to be a valid key
                $childitems = array_keys($childitems);
                $nodes = implode(', ', $childitems);
                $message .= get_string('deletionbreakslinks', 'surveypro', $nodes);
                $labelyes = get_string('confirmitemsdeletion', 'surveypro');
            } else {
                $labelyes = get_string('yes');
            }

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DELETEITEM, 'sesskey' => sesskey());

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['itemid'] = $this->itemid;
            $optionsyes['plugin'] = $this->plugin;
            $optionsyes['type'] = $this->type;

            $urlyes = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.($this->sortindex - 1), $optionsyes);
            $buttonyes = new single_button($urlyes, $labelyes, 'get');

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;

            $urlno = new moodle_url('/mod/surveypro/items_manage.php#sortindex_'.$this->sortindex, $optionsno);
            $buttonno = new single_button($urlno, get_string('no'), 'get');

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    // $maxsortindex = $DB->get_field('surveypro_item', 'MAX(sortindex)', array('surveyproid' => $this->cm->instance));
                    if ($childrenseeds = $DB->get_records('surveypro_item', array('parentid' => $this->itemid), 'id', 'id, type, plugin')) {
                        foreach ($childrenseeds as $childseed) {
                            $item = surveypro_get_item($childseed->id, $childseed->type, $childseed->plugin);
                            $item->item_delete($childseed->id);
                        }
                    }

                    // get the content of the item for the closing message
                    $item = surveypro_get_item($this->itemid, $this->type, $this->plugin);

                    $a = new stdClass();
                    $a->content = $item->get_content();
                    $a->pluginname = strtolower(get_string('pluginname', 'surveypro'.$this->type.'_'.$this->plugin));

                    $killedsortindex = $item->get_sortindex();
                    $item->item_delete($this->itemid);

                    // renum sortindex
                    $sql = 'SELECT id
                            FROM {surveypro_item}
                            WHERE surveyproid = :surveyproid
                                AND sortindex > :killedsortindex
                            ORDER BY sortindex';
                    $whereparams = array('surveyproid' => $this->surveypro->id, 'killedsortindex' => $killedsortindex);
                    $itemlist = $DB->get_recordset_sql($sql, $whereparams);
                    $currentsortindex = $killedsortindex;
                    foreach ($itemlist as $item) {
                        $DB->set_field('surveypro_item', 'sortindex', $currentsortindex, array('id' => $item->id));
                        $currentsortindex++;
                    }
                    $itemlist->close();

                    if ($childrenseeds) {
                        $message = get_string('chaindeleted', 'surveypro', $a);
                    } else {
                        $message = get_string('itemdeleted', 'surveypro', $a);
                    }
                    echo $OUTPUT->notification($message, 'notifysuccess');
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $message = get_string('usercanceled', 'surveypro');
                    echo $OUTPUT->notification($message, 'notifymessage');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }
    }

    /**
     * manage_item_dropmultilang
     *
     * @param none
     * @return
     */
    public function manage_item_dropmultilang() {
        global $DB, $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // ask for confirmation
            $message = get_string('mastertemplate_noedit', 'surveypro');

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DROPMULTILANG);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $urlyes = new moodle_url('/mod/surveypro/items_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('yes'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('/mod/surveypro/items_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    $template = $this->surveypro->template;
                    $where = array('surveyproid' => $this->surveypro->id);
                    $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');
                    foreach ($itemseeds as $itemseed) {
                        $id = $itemseed->id;
                        $type = $itemseed->type;
                        $plugin = $itemseed->plugin;
                        $item = surveypro_get_item($id, $type, $plugin);
                        if ($multilangfields = $item->item_get_multilang_fields()) {
                            foreach ($multilangfields as $plugin => $fieldnames) {
                                $record = new stdClass();
                                if ($plugin == 'item') {
                                    $record->id = $item->get_itemid();
                                } else {
                                    $record->id = $item->get_pluginid();
                                }

                                $where = array('id' => $record->id);
                                $fieldlist = implode(',', $multilangfields[$plugin]);
                                $reference = $DB->get_record('surveypro'.$type.'_'.$plugin, $where, $fieldlist, MUST_EXIST);

                                foreach ($fieldnames as $fieldname) {
                                    $stringkey = $reference->{$fieldname};
                                    if (strlen($stringkey)) {
                                        $record->{$fieldname} = get_string($stringkey, 'surveyprotemplate_'.$template);
                                    } else {
                                        $record->{$fieldname} = null;
                                    }
                                }
                                $DB->update_record('surveypro'.$type.'_'.$plugin, $record);
                            }
                        }
                    }

                    $record = new stdClass();
                    $record->id = $this->surveypro->id;
                    $record->template = null;
                    $DB->update_record('surveypro', $record);

                    $returnurl = new moodle_url('/mod/surveypro/items_manage.php', array('id' => $this->cm->id));
                    redirect($returnurl);
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $paramurl = array('id' => $this->cm->id, 'view' => SURVEYPRO_PREVIEWSURVEYFORM);
                    $returnurl = new moodle_url('/mod/surveypro/view_userform.php', $paramurl);
                    redirect($returnurl);
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }
    }

    /**
     * reorder_items
     *
     * @param none
     * @return
     */
    public function reorder_items() {
        global $DB;

        // I start loading the id of the item I want to move starting from its known sortindex
        $itemid = $DB->get_field('surveypro_item', 'id', array('surveyproid' => $this->surveypro->id, 'sortindex' => $this->itemtomove));

        // Am I moving it backward or forward?
        if ($this->itemtomove > $this->lastitembefore) {
            // moving the item backward
            $searchitem = $this->itemtomove - 1;
            $replaceitem = $this->itemtomove;

            while ($searchitem > $this->lastitembefore) {
                $DB->set_field('surveypro_item', 'sortindex', $replaceitem, array('surveyproid' => $this->surveypro->id, 'sortindex' => $searchitem));
                $replaceitem = $searchitem;
                $searchitem--;
            }

            $DB->set_field('surveypro_item', 'sortindex', $replaceitem, array('surveyproid' => $this->surveypro->id, 'id' => $itemid));
        } else {
            // moving the item forward
            $searchitem = $this->itemtomove + 1;
            $replaceitem = $this->itemtomove;

            while ($searchitem <= $this->lastitembefore) {
                $DB->set_field('surveypro_item', 'sortindex', $replaceitem, array('surveyproid' => $this->surveypro->id, 'sortindex' => $searchitem));
                $replaceitem = $searchitem;
                $searchitem++;
            }

            $DB->set_field('surveypro_item', 'sortindex', $replaceitem, array('id' => $itemid));
        }

        // you changed item order
        // so, do no forget to reset items per page
        surveypro_reset_items_pages($this->surveypro->id);
    }

    /**
     * validate_relations
     *
     * @param none
     * @return
     */
    public function validate_relations() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $table = new flexible_table('itemslist');

        $paramurl = array('id' => $this->cm->id);
        $baseurl = new moodle_url('/mod/surveypro/items_validate.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'plugin';
        $tablecolumns[] = 'sortindex';
        $tablecolumns[] = 'parentitem';
        $tablecolumns[] = 'customnumber';
        $tablecolumns[] = 'content';
        $tablecolumns[] = 'parentconstraints';
        $tablecolumns[] = 'status';
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('plugin', 'surveypro');
        $tableheaders[] = get_string('sortindex', 'surveypro');
        $tableheaders[] = get_string('branching', 'surveypro');
        $tableheaders[] = get_string('customnumber_header', 'surveypro');
        $tableheaders[] = get_string('content', 'surveypro');
        $tableheaders[] = get_string('parentconstraints', 'surveypro');
        $tableheaders[] = get_string('relation_status', 'surveypro');
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        // $table->collapsible(true);
        $table->sortable(true, 'sortindex', 'ASC'); // sorted by sortindex by default
        $table->no_sorting('content');
        $table->no_sorting('parentitem');
        $table->no_sorting('parentconstraints');
        $table->no_sorting('status');
        $table->no_sorting('actions');

        $table->column_class('plugin', 'plugin');
        $table->column_class('content', 'content');
        $table->column_class('sortindex', 'sortindex');
        $table->column_class('parentitem', 'parentitem');
        $table->column_class('customnumber', 'customnumber');
        $table->column_class('parentconstraints', 'parentconstraints');
        $table->column_class('status', 'status');
        $table->column_class('actions', 'actions');

        // $table->initialbars(true);

        // hide the same info whether in two consecutive rows
        // $table->column_suppress('picture');
        // $table->column_suppress('fullname');

        // general properties for the whole table
        // $table->set_attribute('cellpadding', '5');
        $table->set_attribute('id', 'validaterelations');
        $table->set_attribute('class', 'generaltable');
        // $table->set_attribute('width', '90%');
        $table->setup();

        $edittitle = get_string('edit');
        $okstring = get_string('ok');

        // Changed to a shorter version on September 25, 2014.
        // Older version will be deleted as soon as the wew one will be checked.
        // $sql = 'SELECT si.*, si.id as itemid, si.plugin, si.type
        //         FROM {surveypro_item} si
        //         WHERE si.surveyproid = :surveyproid';
        // if ($table->get_sql_sort()) {
        //     $sql .= ' ORDER BY '.$table->get_sql_sort();
        // } else {
        //     $sql .= ' ORDER BY si.sortindex';
        // }
        // $itemseeds = $DB->get_recordset_sql($sql, array('surveyproid' => $this->surveypro->id));
        $whereparams = array('surveyproid' => $this->surveypro->id);
        $sortfield = ($table->get_sql_sort()) ? $table->get_sql_sort() : 'sortindex';
        $itemseeds = $DB->get_recordset('surveypro_item', $whereparams, $sortfield, 'id as itemid, plugin, type');

        $message = get_string('validationinfo', 'surveypro');
        echo $OUTPUT->notification($message, 'notifymessage');

        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($itemseed->itemid, $itemseed->type, $itemseed->plugin, true);
            $currenthide = $item->get_hidden();

            if ($item->get_parentid()) {
                $parentitem = surveypro_get_item($item->get_parentid()); // here I do not know type and plugin
            }

            $tablerow = array();

            // plugin
            $plugintitle = get_string('pluginname', 'surveypro'.$item->get_type().'_'.$item->get_plugin());
            $content = $OUTPUT->pix_icon('icon', $plugintitle, 'surveypro'.$item->get_type().'_'.$item->get_plugin(),
                    array('title' => $plugintitle, 'class' => 'smallicon'));
            $tablerow[] = $content;

            // sortindex
            $tablerow[] = $item->get_sortindex();

            // parentid
            if ($item->get_parentid()) {
                $message = get_string('parentid_alt', 'surveypro');
                $content = $parentitem->get_sortindex();
                $content .= $OUTPUT->pix_icon('branch', $message, 'surveypro', array('title' => $message, 'class' => 'smallicon'));
                $content .= $item->get_parentcontent('; ');
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // customnumber
            if (($item->get_type() == SURVEYPRO_TYPEFIELD) || ($item->get_plugin() == 'label')) {
                $tablerow[] = $item->get_customnumber();
            } else {
                $tablerow[] = '';
            }

            // content
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = $item->get_content();
            $tablerow[] = $output;

            // parentconstraints
            if ($item->get_parentid()) {
                $tablerow[] = $parentitem->item_list_constraints();
            } else {
                $tablerow[] = '-';
            }

            // status
            if ($item->get_parentid()) {
                $status = $parentitem->parent_validate_child_constraints($item->parentvalue);
                if ($status == SURVEYPRO_CONDITIONOK) {
                    $tablerow[] = $okstring;
                } else {
                    if ($status == SURVEYPRO_CONDITIONNEVERMATCH) {
                        if (empty($currenthide)) {
                            $tablerow[] = '<span class="errormessage">'.get_string('wrongrelation', 'surveypro', $item->get_parentcontent('; ')).'</span>';
                        } else {
                            $tablerow[] = get_string('wrongrelation', 'surveypro', $item->get_parentcontent('; '));
                        }
                    }
                    if ($status == SURVEYPRO_CONDITIONMALFORMED) {
                        if (empty($currenthide)) {
                            $tablerow[] = '<span class="errormessage">'.get_string('malformedchildparentvalue', 'surveypro', $item->get_parentcontent('; ')).'</span>';
                        } else {
                            $tablerow[] = get_string('malformedchildparentvalue', 'surveypro', $item->get_parentcontent('; '));
                        }
                    }
                }
            } else {
                $tablerow[] = '-';
            }

            // actions
            // -----------------------------
            // $paramurlbase definition
            $paramurlbase = array();
            $paramurlbase['id'] = $this->cm->id;
            $paramurlbase['itemid'] = $item->get_itemid();
            $paramurlbase['type'] = $item->get_type();
            $paramurlbase['plugin'] = $item->get_plugin();
            // end of $paramurlbase definition
            // -----------------------------

            // SURVEYPRO_EDITITEM
            $paramurl = $paramurlbase;
            $paramurl['view'] = SURVEYPRO_EDITITEM;

            $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/items_setup.php', $paramurl),
                new pix_icon('t/edit', $edittitle, 'moodle', array('title' => $edittitle)),
                null, array('id' => 'edit_'.$item->itemid, 'title' => $edittitle));

            $tablerow[] = $icons;

            $rowclass = empty($currenthide) ? '' : 'dimmed';
            $table->add_data($tablerow, $rowclass);
        }
        $itemseeds->close();

        $table->set_attribute('align', 'center');
        $table->summary = get_string('itemlist', 'surveypro');
        $table->print_html();
    }

    /**
     * display_user_feedback
     *
     * @param none
     * @return
     */
    public function display_user_feedback() {
        global $OUTPUT;

        if ($this->userfeedbackmask == SURVEYPRO_NOFEEDBACK) {
            return;
        }

        // look at position 1
        $bit = $this->userfeedbackmask & 2; // bitwise logic
        if ($bit) { // edit
            $bit = $this->userfeedbackmask & 1; // bitwise logic
            if ($bit) {
                $message = get_string('itemeditok', 'surveypro');
                $class = 'notifysuccess';
            } else {
                $message = get_string('itemeditfail', 'surveypro');
                $class = 'notifyproblem';
            }
        } else {    // add
            $bit = $this->userfeedbackmask & 1; // bitwise logic
            if ($bit) {
                $message = get_string('itemaddok', 'surveypro');
                $class = 'notifysuccess';
            } else {
                $message = get_string('itemaddfail', 'surveypro');
                $class = 'notifyproblem';
            }
        }

        for ($position = 2; $position <= 5; $position++) {
            $bit = $this->userfeedbackmask & pow(2, $position); // bitwise logic
            switch ($position) {
                case 2: // a chain of items is now shown
                    if ($bit) {
                        $message .= '<br />'.get_string('itemeditshow', 'surveypro');
                    }
                    break;
                case 3: // a chain of items is now hided because one item was hided
                    if ($bit) {
                        $message .= '<br />'.get_string('itemedithidehide', 'surveypro');
                    }
                    break;
                case 4: // a chain of items was moved in the user entry form
                    if ($bit) {
                        $message .= '<br />'.get_string('itemeditshowinbasicform', 'surveypro');
                    }
                    break;
                case 5: // a chain of items was removed from the user entry form
                    if ($bit) {
                        $message .= '<br />'.get_string('itemeditmakeadvanced', 'surveypro');
                    }
                    break;
            }
        }
        echo $OUTPUT->notification($message, $class);
    }

    /**
     * item_welcome
     *
     * @param none
     * @return
     */
    public function item_welcome() {
        global $OUTPUT;

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $plugintitle = get_string('userfriendlypluginname', 'surveypro'.$this->type.'_'.$this->plugin);

        $message = $OUTPUT->pix_icon('icon', $plugintitle, 'surveypro'.$this->type.'_'.$this->plugin,
                array('title' => $plugintitle, 'class' => 'icon'));
        $message .= get_string($this->type, 'surveypro').$labelsep.$plugintitle;

        echo $OUTPUT->box($message);
    }

    /**
     * prevent_direct_user_input
     *
     * @param none
     * @return null
     */
    public function prevent_direct_user_input() {
        if ($this->surveypro->template) {
            print_error('incorrectaccessdetected', 'surveypro');
        }
    }

    // MARK set

    /**
     * set_typeplugin
     *
     * @param $typeplugin
     * @return none
     */
    public function set_typeplugin($typeplugin) {
        if (preg_match('~^('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_(\w+)$~', $typeplugin, $match)) {
            // execution comes from /forms/items/selectitem_form.php
            $this->type = $match[1]; // field or format
            $this->plugin = $match[2]; // boolean or char ... or fieldset ...
        } else {
            debugging('Malformed typeplugin parameter passed to set_typeplugin', DEBUG_DEVELOPER);
        }
    }

    /**
     * set_type
     *
     * @param $type
     * @return none
     */
    public function set_type($type) {
        $this->type = $type;
    }

    /**
     * set_plugin
     *
     * @param $plugin
     * @return none
     */
    public function set_plugin($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * set_itemid
     *
     * @param int $itemid
     * @return none
     */
    public function set_itemid($itemid) {
        $this->itemid = $itemid;
    }

    /**
     * set_sortindex
     *
     * @param int $sortindex
     * @return none
     */
    public function set_sortindex($sortindex) {
        $this->sortindex = $sortindex;
    }

    /**
     * set_action
     *
     * @param int $action
     * @return none
     */
    public function set_action($action) {
        $this->action = $action;
    }

    /**
     * set_view
     *
     * @param int $view
     * @return none
     */
    public function set_view($view) {
        $this->view = $view;
    }

    /**
     * set_lastitembefore
     *
     * @param int $lastitembefore
     * @return none
     */
    public function set_lastitembefore($lastitembefore) {
        $this->lastitembefore = $lastitembefore;
    }

    /**
     * set_confirm
     *
     * @param int $confirm
     * @return none
     */
    public function set_confirm($confirm) {
        $this->confirm = $confirm;
    }

    /**
     * set_nextindent
     *
     * @param int $nextindent
     * @return none
     */
    public function set_nextindent($nextindent) {
        $this->nextindent = $nextindent;
    }

    /**
     * set_parentid
     *
     * @param int $parentid
     * @return none
     */
    public function set_parentid($parentid) {
        $this->parentid = $parentid;
    }

    /**
     * set_userfeedbackmask
     *
     * @param int $userfeedbackmask
     * @return none
     */
    public function set_userfeedbackmask($userfeedbackmask) {
        $this->userfeedbackmask = $userfeedbackmask;
    }

    /**
     * set_itemtomove
     *
     * @param int $itemtomove
     * @return none
     */
    public function set_itemtomove($itemtomove) {
        $this->itemtomove = $itemtomove;
    }

    /**
     * set_saveasnew
     *
     * @param int $saveasnew
     * @return none
     */
    public function set_saveasnew($saveasnew) {
        $this->saveasnew = $saveasnew;
    }
}
