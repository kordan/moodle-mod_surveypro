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
 * Surveypro itemlist class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The base class representing the list of elements of this surveypro
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_itemlist {

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
     * @var int Required view
     */
    protected $view;

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
    }

    /**
     * Display all the items in a table.
     *
     * @return void
     */
    public function display_items_table() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $table = new flexible_table('itemslist');

        $paramurl = array('id' => $this->cm->id);
        $baseurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
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
        $tableheaders[] = get_string('plugin', 'mod_surveypro');
        $tableheaders[] = get_string('sortindex', 'mod_surveypro');
        $tableheaders[] = get_string('branching', 'mod_surveypro');
        $tableheaders[] = get_string('customnumber_header', 'mod_surveypro');
        $tableheaders[] = get_string('content', 'mod_surveypro');
        $tableheaders[] = get_string('variable', 'mod_surveypro');
        $tableheaders[] = get_string('page');
        $tableheaders[] = get_string('availability', 'mod_surveypro');
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        $table->sortable(true, 'sortindex'); // Sorted by sortindex by default.
        $table->no_sorting('customnumber');
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

        // General properties for the whole table.
        if ($this->view == SURVEYPRO_CHANGEORDERASK) {
            $table->set_attribute('id', 'sortitems');
        } else {
            $table->set_attribute('id', 'manageitems');
        }
        $table->set_attribute('class', 'generaltable');
        $table->setup();

        // Strings.
        $iconparams = array();
        // Icons for further use.
        $editstr = get_string('edit');
        $iconparams['title'] = $editstr;
        $editicn = new pix_icon('t/edit', $editstr, 'moodle', $iconparams);

        $parentelementstr = get_string('parentelement_title', 'mod_surveypro');
        $iconparams['title'] = $parentelementstr;
        $branchicn = new pix_icon('branch', $parentelementstr, 'surveypro', $iconparams);

        $reorderstr = get_string('changeorder_title', 'mod_surveypro');
        $iconparams['title'] = $reorderstr;
        $moveicn = new pix_icon('t/move', $editstr, 'moodle', $iconparams);

        $hidestr = get_string('hidefield_title', 'mod_surveypro');
        $iconparams['title'] = $hidestr;
        $hideicn = new pix_icon('i/hide', $hidestr, 'moodle', $iconparams);

        $showstr = get_string('showfield_title', 'mod_surveypro');
        $iconparams['title'] = $showstr;
        $showicn = new pix_icon('i/show', $showstr, 'moodle', $iconparams);

        $deletestr = get_string('delete');
        $iconparams['title'] = $deletestr;
        $deleteicn = new pix_icon('t/delete', $deletestr, 'moodle', $iconparams);

        $indentstr = get_string('indent', 'mod_surveypro');
        $iconparams['title'] = $indentstr;
        $lefticn = new pix_icon('t/left', $indentstr, 'moodle', $iconparams);
        $righticn = new pix_icon('t/right', $indentstr, 'moodle', $iconparams);

        $moveherestr = get_string('movehere');
        $movehereicn = new pix_icon('movehere', $moveherestr, 'moodle', array('title' => $moveherestr, 'class' => 'placeholder'));

        $availablestr = get_string('available_title', 'mod_surveypro');
        $iconparams['title'] = $availablestr;
        $freeicn = new pix_icon('free', $availablestr, 'surveypro', $iconparams);

        $reservedstr = get_string('reserved_title', 'mod_surveypro');
        $iconparams['title'] = $reservedstr;
        $reservedicn = new pix_icon('reserved', $reservedstr, 'surveypro', $iconparams);

        $unreservablestr = get_string('unreservable_title', 'mod_surveypro');
        $iconparams['title'] = $unreservablestr;
        $unreservableicn = new pix_icon('unreservable', $unreservablestr, 'surveypro', $iconparams);

        $unsearchablestr = get_string('unsearchable_title', 'mod_surveypro');
        $iconparams['title'] = $unsearchablestr;
        $unsearchableicn = new pix_icon('unsearchable', $unsearchablestr, 'surveypro', $iconparams);

        $unavailablestr = get_string('unavailableelement_title', 'mod_surveypro');
        $iconparams['title'] = $unavailablestr;
        $unavailableicn = new pix_icon('unavailable', $unavailablestr, 'surveypro', $iconparams);

        $forcedoptionalitemstr = get_string('forcedoptionalitem_title', 'mod_surveypro');
        $iconparams['title'] = $forcedoptionalitemstr;
        $lockedgreenicn = new pix_icon('lockedgreen', $forcedoptionalitemstr, 'surveypro', $iconparams);

        // Begin of: $paramurlmove definition.
        $paramurlmove = array();
        $paramurlmove['id'] = $this->cm->id;
        $paramurlmove['act'] = SURVEYPRO_CHANGEORDER;
        $paramurlmove['itm'] = $this->itemtomove;
        // End of: $paramurlmove definition.

        list($where, $params) = surveypro_fetch_items_seeds($this->surveypro->id, false, true, null, null, null, true);
        // If you are reordering, force ordering to...
        $orderby = ($this->view == SURVEYPRO_CHANGEORDERASK) ? 'sortindex ASC' : $table->get_sql_sort();
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, $orderby, 'id as itemid, type, plugin');
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $itemcount = $utilityman->has_input_items(0, true, true, true);
        $drawmovearrow = ($itemcount > 1);

        // This is the very first position, so if the item has a parent, no "moveherebox" must appear.
        if (($this->view == SURVEYPRO_CHANGEORDERASK) && (!$this->parentid)) {
            $drawmoveherebox = true;
            $paramurl = $paramurlmove;
            $paramurl['lib'] = 0; // Move just after this sortindex (lib == last item before).
            $paramurl['sesskey'] = sesskey();

            $link = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
            $paramlink = array('id' => 'moveafter_0', 'title' => $moveherestr);
            $icons = $OUTPUT->action_icon($link, $movehereicn, null, $paramlink);

            $tablerow = array();
            $tablerow[] = $icons;
            $tablerow = array_pad($tablerow, count($table->columns), '');

            $table->add_data($tablerow);
        } else {
            $drawmoveherebox = false;
        }

        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->itemid, $itemseed->type, $itemseed->plugin, true);
            $itemid = $itemseed->itemid;
            $itemishidden = $item->get_hidden();
            $sortindex = $item->get_sortindex();

            // Begin of: $paramurlbase definition.
            $paramurlbase = array();
            $paramurlbase['id'] = $this->cm->id;
            $paramurlbase['itemid'] = $item->get_itemid();
            $paramurlbase['type'] = $item->get_type();
            $paramurlbase['plugin'] = $item->get_plugin();
            // End of: $paramurlbase definition.

            $tablerow = array();

            if (($this->view == SURVEYPRO_CHANGEORDERASK) && ($item->get_itemid() == $this->rootitemid)) {
                // Do not draw the item you are going to move.
                continue;
            }

            // Plugin.
            $component = 'surveypro'.$item->get_type().'_'.$item->get_plugin();
            $alt = get_string('userfriendlypluginname', $component);
            $content = html_writer::tag('a', '', array('name' => 'sortindex_'.$sortindex));
            $iconparams = array('title' => $alt);
            $icon = $OUTPUT->pix_icon('icon', $alt, $component, $iconparams);
            $content .= html_writer::tag('span', $icon, array('class' => 'pluginicon'));

            $tablerow[] = $content;

            // Sortindex.
            $tablerow[] = $sortindex;

            // Parentid.
            if ($item->get_parentid()) {
                $parentsortindex = $DB->get_field('surveypro_item', 'sortindex', array('id' => $item->get_parentid()));
                $content = $parentsortindex;
                $content .= html_writer::tag('span', $OUTPUT->render($branchicn), array('class' => 'branch'));
                $content .= $item->get_parentcontent('; ');
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // Customnumber.
            if (($item->get_type() == SURVEYPRO_TYPEFIELD) || ($item->get_plugin() == 'label')) {
                $itemid = $item->get_itemid();
                $customnumber = $item->get_customnumber();
                $tmpl = new mod_surveypro_itemlist_customnumber($itemid, $customnumber);

                $tablerow[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
            } else {
                $tablerow[] = '';
            }

            // Content.
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = $item->get_content();
            $tablerow[] = $output;

            // Variable.
            if ($item->get_type() == SURVEYPRO_TYPEFIELD) {
                $itemid = $item->get_itemid();
                $variablename = $item->get_variable();
                $tmpl = new mod_surveypro_itemlist_variable($itemid, $variablename);

                $tablerow[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
            } else {
                $tablerow[] = '';
            }

            // Page.
            if ($item->item_uses_form_page()) {
                $content = $item->get_formpage();
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // Availability.
            $icons = '';
            if (!$itemishidden) {
                // First icon: reserved vs generally available.
                if ($item->get_insetupform('reserved')) {
                    $reserved = $item->get_reserved();
                    if ($item->item_has_children() || $item->item_is_child()) {
                        $paramurl = $paramurlbase;
                        if ($reserved) {
                            $paramurl['act'] = SURVEYPRO_MAKEAVAILABLE;
                            $paramurl['sortindex'] = $sortindex;
                            $paramurl['sesskey'] = sesskey();

                            $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$sortindex, $paramurl);
                            $paramlink = array('id' => 'makeavailable_item_'.$sortindex, 'title' => $reservedstr);
                            $icons .= $OUTPUT->action_icon($link, $reservedicn, null, $paramlink);
                        } else {
                            $paramurl['act'] = SURVEYPRO_MAKERESERVED;
                            $paramurl['sortindex'] = $sortindex;
                            $paramurl['sesskey'] = sesskey();

                            $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$sortindex, $paramurl);
                            $paramlink = array('id' => 'makereserved_item_'.$sortindex, 'title' => $availablestr);
                            $icons .= $OUTPUT->action_icon($link, $freeicn, null, $paramlink);
                        }
                    } else {
                        $tmpl = new mod_surveypro_itemlist_reserved($itemid, $reserved, $sortindex);
                        $tmpl->set_type_toggle();
                        $icons .= $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
                    }
                } else {
                    // Icon only, not a link!
                    $icons .= html_writer::tag('span', $OUTPUT->render($unreservableicn), array('class' => 'noactionicon'));
                }

                // Second icon: insearchform vs notinsearchform.
                if ($item->get_insetupform('insearchform')) {
                    // Second icon: insearchform vs not insearchform.
                    $insearchform = $item->get_insearchform();
                    $tmpl = new mod_surveypro_itemlist_insearchform($itemid, $insearchform, $sortindex);
                    $tmpl->set_type_toggle();
                    $icons .= $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
                } else {
                    // Icon only, not a link!
                    $icons .= html_writer::tag('span', $OUTPUT->render($unsearchableicn), array('class' => 'noactionicon'));
                }
            } else {
                // Icons only, not links!
                // First icon: reserved vs free availability.
                $icons .= html_writer::tag('span', $OUTPUT->render($unavailableicn), array('class' => 'noactionicon'));

                // Second icon: insearchform vs notinsearchform.
                $icons .= html_writer::tag('span', $OUTPUT->render($unavailableicn), array('class' => 'noactionicon'));
            }

            // Third icon: hide vs show.
            // Here I can not use the cool \core\output\inplace_editable because
            // this action make changes not restricted to this icons state only.
            if (!$this->hassubmissions || $riskyediting) {
                $paramurl = $paramurlbase;
                $paramurl['sesskey'] = sesskey();
                if (empty($itemishidden)) {
                    $paramurl['act'] = SURVEYPRO_HIDEITEM;
                    $paramurl['sortindex'] = $sortindex;
                    $message = $hidestr;
                    $linkidprefix = 'hide_item_';
                } else {
                    $paramurl['act'] = SURVEYPRO_SHOWITEM;
                    $paramurl['sortindex'] = $sortindex;
                    $message = $showstr;
                    $linkidprefix = 'show_item_';
                }
                $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$sortindex, $paramurl);
                $paramlink = array('id' => $linkidprefix.$sortindex, 'class' => 'icon');
                if (empty($itemishidden)) {
                    $paramlink['title'] = $hidestr;
                    $icons .= html_writer::tag('span', $OUTPUT->action_icon($link, $hideicn, null, $paramlink));
                } else {
                    $paramlink['title'] = $showstr;
                    $icons .= html_writer::tag('span', $OUTPUT->action_icon($link, $showicn, null, $paramlink));
                }
            }
            $tablerow[] = $icons;

            // Icon ations.
            $icons = '';
            if ($this->view != SURVEYPRO_CHANGEORDERASK) {
                // SURVEYPRO_EDITITEM.
                $paramurl = $paramurlbase;
                $paramurl['view'] = SURVEYPRO_EDITITEM;

                $link = new moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);
                $paramlink = array('id' => 'edit_item_'.$sortindex, 'class' => 'icon', 'title' => $editstr);
                $icons .= html_writer::tag('span', $OUTPUT->action_icon($link, $editicn, null, $paramlink));

                // SURVEYPRO_CHANGEORDERASK.
                if (!empty($drawmovearrow)) {
                    $paramurl = $paramurlbase;
                    $paramurl['view'] = SURVEYPRO_CHANGEORDERASK;
                    $paramurl['itm'] = $sortindex;

                    $currentparentid = $item->get_parentid();
                    if (!empty($currentparentid)) {
                        $paramurl['pid'] = $currentparentid;
                    }

                    $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.($sortindex - 1), $paramurl);
                    $paramlink = array('id' => 'move_item_'.$sortindex, 'class' => 'icon', 'title' => $reorderstr);
                    $actionicon = $OUTPUT->action_icon($link, $moveicn, null, $paramlink);
                    $icons .= html_writer::tag('span', $actionicon, array('class' => 'reorder'));
                }

                // SURVEYPRO_DELETEITEM.
                if (!$this->hassubmissions || $riskyediting) {
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_DELETEITEM;
                    $paramurl['sortindex'] = $sortindex;
                    $paramurl['sesskey'] = sesskey();

                    $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$sortindex, $paramurl);
                    $paramlink = array('id' => 'delete_item_'.$sortindex, 'class' => 'icon', 'title' => $deletestr);
                    $icons .= html_writer::tag('span', $OUTPUT->action_icon($link, $deleteicn, null, $paramlink));
                }

                // SURVEYPRO_REQUIRED ON/OFF.
                $currentrequired = $item->get_required();
                if ($currentrequired !== false) { // It may not be set as in page_break, autofill or some more.
                    $paramurl = $paramurlbase;
                    $paramurl['sesskey'] = sesskey();

                    if ($item->item_canbemandatory()) {
                        $required = $item->get_required();
                        $tmpl = new mod_surveypro_itemlist_required($itemid, $required, $sortindex);
                        $tmpl->set_type_toggle();
                        $icons .= $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
                    } else {
                        $icons .= html_writer::tag('span', $OUTPUT->render($lockedgreenicn), array('class' => 'noactionicon'));
                    }
                }

                // SURVEYPRO_CHANGEINDENT.
                if ($item->get_insetupform('indent')) { // It may not be set as in page_break, fieldset and some more.
                    $currentindent = $item->get_indent();
                    if ($currentindent !== false) { // It may be false like for labels with fullwidth == 1.
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEYPRO_CHANGEINDENT;
                        $paramurl['sesskey'] = sesskey();

                        if ($currentindent > 0) {
                            $indentvalue = $currentindent - 1;
                            $paramurl['ind'] = $indentvalue;

                            $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$sortindex, $paramurl);
                            $paramlink = array('id' => 'reduceindent_item_'.$sortindex, 'title' => $indentstr);
                            $icons .= html_writer::tag('span', $OUTPUT->action_icon($link, $lefticn, null, $paramlink));
                        }
                        $icons .= '['.$currentindent.']';
                        if ($currentindent < 9) {
                            $indentvalue = $currentindent + 1;
                            $paramurl['ind'] = $indentvalue;

                            $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$sortindex, $paramurl);
                            $paramlink = array('id' => 'increaseindent_item_'.$sortindex, 'title' => $indentstr);
                            $icons .= html_writer::tag('span', $OUTPUT->action_icon($link, $righticn, null, $paramlink));
                        }
                    }
                }
            }
            $tablerow[] = $icons;

            $rowclass = empty($itemishidden) ? '' : 'dimmed';
            $table->add_data($tablerow, $rowclass);

            if ($this->view == SURVEYPRO_CHANGEORDERASK) {
                // It was asked to move the item with: $this->rootitemid and $this->parentid.
                if ($this->parentid) { // This is the parentid of the item that I am going to move.
                    // If a parentid is foreseen.
                    // Draw the moveherebox only if the current (already displayed) item has: $item->itemid == $this->parentid.
                    // Once you start to draw the moveherebox, you will never stop.
                    $drawmoveherebox = $drawmoveherebox || ($item->get_itemid() == $this->parentid);

                    // If you just passed an item with $item->get_parentid == $itemid, stop forever.
                    if ($item->get_parentid() == $this->rootitemid) {
                        $drawmoveherebox = false;
                    }
                } else {
                    $drawmoveherebox = $drawmoveherebox && ($item->get_parentid() != $this->rootitemid);
                }

                if (!empty($drawmoveherebox)) {
                    $paramurl = $paramurlmove;
                    $paramurl['lib'] = $sortindex;
                    $paramurl['sesskey'] = sesskey();

                    $link = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$sortindex, $paramurl);
                    $paramlink = array('id' => 'move_item_'.$sortindex, 'title' => $moveherestr);
                    $icons = $OUTPUT->action_icon($link, $movehereicn, null, $paramlink);

                    $tablerow = array();
                    $tablerow[] = $icons;
                    $tablerow = array_pad($tablerow, count($table->columns), '');

                    $table->add_data($tablerow);
                }
            }
        }

        $table->set_attribute('align', 'center');
        $table->summary = get_string('itemlist', 'mod_surveypro');
        $table->print_html();
    }

    /**
     * Display the "validate_relations" table.
     *
     * @return void
     */
    public function display_relations_table() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $statusstr = get_string('relation_status', 'mod_surveypro');
        $table = new flexible_table('relations');

        $paramurl = array('id' => $this->cm->id);
        $baseurl = new moodle_url('/mod/surveypro/layout_validation.php', $paramurl);
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
        $tableheaders[] = get_string('plugin', 'mod_surveypro');
        $tableheaders[] = get_string('sortindex', 'mod_surveypro');
        $tableheaders[] = get_string('branching', 'mod_surveypro');
        $tableheaders[] = get_string('customnumber_header', 'mod_surveypro');
        $tableheaders[] = get_string('content', 'mod_surveypro');
        $tableheaders[] = get_string('parentconstraints', 'mod_surveypro');
        $tableheaders[] = $statusstr;
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        $table->sortable(true, 'sortindex'); // Sorted by sortindex by default.
        $table->no_sorting('plugin');
        $table->no_sorting('parentitem');
        $table->no_sorting('customnumber');
        $table->no_sorting('content');
        $table->no_sorting('parentconstraints');
        $table->no_sorting('status');
        $table->no_sorting('actions');

        $table->column_class('plugin', 'plugin');
        $table->column_class('sortindex', 'sortindex');
        $table->column_class('parentitem', 'parentitem');
        $table->column_class('customnumber', 'customnumber');
        $table->column_class('content', 'content');
        $table->column_class('parentconstraints', 'parentconstraints');
        $table->column_class('status', 'status');
        $table->column_class('actions', 'actions');

        // General properties for the whole table.
        $table->set_attribute('id', 'validaterelations');
        $table->set_attribute('class', 'generaltable');
        $table->setup();

        $okstr = get_string('ok');

        $iconparams = array();

        $editstr = get_string('edit');
        $iconparams['title'] = $editstr;
        $editicn = new pix_icon('t/edit', $editstr, 'moodle', $iconparams);

        $parentelementstr = get_string('parentelement_title', 'mod_surveypro');
        $iconparams['title'] = $parentelementstr;
        $branchicn = new pix_icon('branch', $parentelementstr, 'surveypro', $iconparams);

        $whereparams = array('surveyproid' => $this->surveypro->id);
        $sortfield = ($table->get_sql_sort()) ? $table->get_sql_sort() : 'sortindex';
        $itemseeds = $DB->get_recordset('surveypro_item', $whereparams, $sortfield, 'id as itemid, plugin, type');

        $message = get_string('welcome_relationvalidation', 'mod_surveypro', $statusstr);
        echo $OUTPUT->notification($message, 'notifymessage');

        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->itemid, $itemseed->type, $itemseed->plugin, true);
            $itemishidden = $item->get_hidden();

            if ($item->get_parentid()) {
                // Here I do not know type and plugin.
                $parentitem = surveypro_get_item($this->cm, $this->surveypro, $item->get_parentid());
            }

            $tablerow = array();

            // Plugin.
            $component = 'surveypro'.$item->get_type().'_'.$item->get_plugin();
            $alt = get_string('pluginname', $component);
            $iconparams = array('title' => $alt, 'class' => 'icon');
            $content = $OUTPUT->pix_icon('icon', $alt, $component, $iconparams);
            $tablerow[] = $content;

            // Sortindex.
            $tablerow[] = $item->get_sortindex();

            // Parentid.
            if ($item->get_parentid()) {
                $content = $parentitem->get_sortindex();
                $content .= $OUTPUT->render($branchicn);
                $content .= $item->get_parentcontent('; ');
            } else {
                $content = '';
            }
            $tablerow[] = $content;

            // Customnumber.
            if (($item->get_type() == SURVEYPRO_TYPEFIELD) || ($item->get_plugin() == 'label')) {
                $tablerow[] = $item->get_customnumber();
            } else {
                $tablerow[] = '';
            }

            // Content.
            $item->set_contentformat(FORMAT_HTML);
            $item->set_contenttrust(1);

            $output = $item->get_content();
            $tablerow[] = $output;

            // Parentconstraints.
            if ($item->get_parentid()) {
                $tablerow[] = $parentitem->item_list_constraints();
            } else {
                $tablerow[] = '-';
            }

            // Status.
            if ($item->get_parentid()) {
                $status = $parentitem->parent_validate_child_constraints($item->get_parentvalue());
                if ($status == SURVEYPRO_CONDITIONOK) {
                    $tablerow[] = $okstr;
                } else {
                    if ($status == SURVEYPRO_CONDITIONNEVERMATCH) {
                        if (empty($itemishidden)) {
                            $errormessage = html_writer::start_tag('span', array('class' => 'errormessage'));
                            $errormessage .= get_string('wrongrelation', 'mod_surveypro', $item->get_parentcontent('; '));
                            $errormessage .= html_writer::end_tag('span');
                            $tablerow[] = $errormessage;
                        } else {
                            $tablerow[] = get_string('wrongrelation', 'mod_surveypro', $item->get_parentcontent('; '));
                        }
                    }
                    if ($status == SURVEYPRO_CONDITIONMALFORMED) {
                        if (empty($itemishidden)) {
                            $errormessage = html_writer::start_tag('span', array('class' => 'errormessage'));
                            $errormessage .= get_string('badchildparentvalue', 'mod_surveypro', $item->get_parentcontent('; '));
                            $errormessage .= html_writer::end_tag('span');
                            $tablerow[] = $errormessage;
                        } else {
                            $tablerow[] = get_string('badchildparentvalue', 'mod_surveypro', $item->get_parentcontent('; '));
                        }
                    }
                }
            } else {
                $tablerow[] = '-';
            }

            // Actions.
            // Begin of: $paramurlbase definition.
            $paramurlbase = array();
            $paramurlbase['id'] = $this->cm->id;
            $paramurlbase['itemid'] = $item->get_itemid();
            $paramurlbase['type'] = $item->get_type();
            $paramurlbase['plugin'] = $item->get_plugin();
            // End of $paramurlbase definition.

            // SURVEYPRO_EDITITEM.
            $paramurl = $paramurlbase;
            $paramurl['view'] = SURVEYPRO_EDITITEM;

            $link = new moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);
            $paramlink = array('id' => 'edit_'.$item->get_itemid(), 'title' => $editstr);
            $icons = $OUTPUT->action_icon($link, $editicn, null, $paramlink);

            $tablerow[] = $icons;

            $rowclass = empty($itemishidden) ? '' : 'dimmed';
            $table->add_data($tablerow, $rowclass);
        }
        $itemseeds->close();

        $table->set_attribute('align', 'center');
        $table->summary = get_string('itemlist', 'mod_surveypro');
        $table->print_html();
    }

    /**
     * Get the recursive list of children of a specific item.
     * This list counts children and children of children for as much generation as it is.
     *
     * @param int $baseitemid: the id of the root item for the tree of children to get
     * @param array $where: permanent condition needed to filter target items
     * @return object $childrenitems
     */
    public function item_get_children($baseitemid=null, $where=null) {
        global $DB;

        if (empty($baseitemid)) {
            $baseitemid = $this->rootitemid;
        }

        if (empty($where)) {
            $where = array();
        }

        if (!is_array($where)) {
            $a = 'item_get_children';
            print_error('arrayexpected', 'mod_surveypro', null, $a);
        }

        $idscontainer = array($baseitemid);

        // Lets start populating the list of items to return.
        $childrenitems = $DB->get_records('surveypro_item', array('id' => $baseitemid), 'sortindex', 'id, parentid, sortindex');

        $childid = $baseitemid;
        $i = 1;
        do {
            $where['parentid'] = $childid;
            if ($morechildren = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, parentid, sortindex')) {
                foreach ($morechildren as $k => $unused) {
                    $idscontainer[] = $k;
                }
                $childrenitems += $morechildren;
            }
            $childid = next($idscontainer);
            $i++;
        } while ($i <= count($idscontainer));

        return $childrenitems;
    }

    /**
     * Adds elements to an array starting from initial conditions.
     *
     * $additionalcondition is array('hidden' => 1) OR array('reserved' => 1)
     *
     * @param array $additionalcondition
     * @return array $nodelist
     */
    public function add_parent_node($additionalcondition) {
        global $DB;

        if (!is_array($additionalcondition)) {
            $a = 'add_parent_node';
            print_error('arrayexpected', 'mod_surveypro', null, $a);
        }

        $nodelist = array($this->sortindex => $this->rootitemid);

        // Get the first parentid.
        $parentitem = new stdClass();
        $parentitem->parentid = $DB->get_field('surveypro_item', 'parentid', array('id' => $this->rootitemid));

        $where = array('id' => $parentitem->parentid) + $additionalcondition;

        while ($parentitem = $DB->get_record('surveypro_item', $where, 'id, parentid, sortindex')) {
            $nodelist[$parentitem->sortindex] = (int)$parentitem->id;
            $where = array('id' => $parentitem->parentid) + $additionalcondition;
        }

        return $nodelist;
    }

    /**
     * Store to the database sortindex field, the relative position at the items according to last changes.
     *
     * @return void
     */
    public function reorder_items() {
        global $DB;

        // I start loading the id of the item I want to move starting from its known sortindex.
        $where = array('surveyproid' => $this->surveypro->id, 'sortindex' => $this->itemtomove);
        $itemid = $DB->get_field('surveypro_item', 'id', $where);

        // Am I moving it backward or forward?
        if ($this->itemtomove > $this->lastitembefore) {
            // Moving the item backward.
            $searchitem = $this->itemtomove - 1;
            $replaceitem = $this->itemtomove;

            $where = array('surveyproid' => $this->surveypro->id);
            while ($searchitem > $this->lastitembefore) {
                $where['sortindex'] = $searchitem;
                $DB->set_field('surveypro_item', 'sortindex', $replaceitem, $where);
                $replaceitem = $searchitem;
                $searchitem--;
            }

            $DB->set_field('surveypro_item', 'sortindex', $replaceitem, array('id' => $itemid));
        } else {
            // Moving the item forward.
            $searchitem = $this->itemtomove + 1;
            $replaceitem = $this->itemtomove;

            $where = array('surveyproid' => $this->surveypro->id);
            while ($searchitem <= $this->lastitembefore) {
                $where['sortindex'] = $searchitem;
                $DB->set_field('surveypro_item', 'sortindex', $replaceitem, $where);
                $replaceitem = $searchitem;
                $searchitem++;
            }

            $DB->set_field('surveypro_item', 'sortindex', $replaceitem, array('id' => $itemid));
        }

        // You changed item order. Don't forget to reset items per page.
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $utilityman->reset_items_pages();
    }

    /**
     * Display a feedback for the editing teacher once an item is edited.
     *
     * @return void
     */
    public function display_item_editing_feedback() {
        global $OUTPUT;

        if ($this->itemeditingfeedback == SURVEYPRO_NOFEEDBACK) {
            return;
        }

        // Look at position 1.
        $bit = $this->itemeditingfeedback & 2; // Bitwise logic.
        if ($bit) { // Edit.
            $bit = $this->itemeditingfeedback & 1; // Bitwise logic.
            if ($bit) {
                $message = get_string('feedback_itemediting_ok', 'mod_surveypro');
                $class = 'notifysuccess';
            } else {
                $message = get_string('feedback_itemediting_ko', 'mod_surveypro');
                $class = 'notifyproblem';
            }
        } else {    // Add.
            $bit = $this->itemeditingfeedback & 1; // Bitwise logic.
            if ($bit) {
                $message = get_string('feedback_itemadd_ok', 'mod_surveypro');
                $class = 'notifysuccess';
            } else {
                $message = get_string('feedback_itemadd_ko', 'mod_surveypro');
                $class = 'notifyproblem';
            }
        }

        for ($position = 2; $position <= 5; $position++) {
            $bit = $this->itemeditingfeedback & pow(2, $position); // Bitwise logic.
            switch ($position) {
                case 2: // A chain of items is now shown.
                    if ($bit) {
                        $message .= '<br />'.get_string('feedback_itemediting_showchainitems', 'mod_surveypro');
                    }
                    break;
                case 3: // A chain of items is now hided because one item was hided.
                    if ($bit) {
                        $message .= '<br />'.get_string('feedback_itemediting_hidechainitems', 'mod_surveypro');
                    }
                    break;
                case 4: // A chain of items was moved in the user entry form.
                    if ($bit) {
                        $message .= '<br />'.get_string('feedback_itemediting_freechainitems', 'mod_surveypro');
                    }
                    break;
                case 5: // A chain of items was removed from the user entry form.
                    if ($bit) {
                        $message .= '<br />'.get_string('feedback_itemediting_reservechainitems', 'mod_surveypro');
                    }
                    break;
            }
        }
        echo $OUTPUT->notification($message, $class);
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

        $iconparams = array('title' => $friendlyname, 'class' => 'icon');
        $message = $OUTPUT->pix_icon('icon', $friendlyname, 'surveypro'.$this->type.'_'.$this->plugin, $iconparams);
        $message .= get_string($this->type, 'mod_surveypro').$labelsep.$friendlyname;

        echo $OUTPUT->box($message);
    }

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    public function prevent_direct_user_input() {
        if ($this->surveypro->template) {
            print_error('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    // MARK item action execution.

    /**
     * Perform the actions required through icon click into items table.
     *
     * @return void
     */
    public function actions_execution() {
        global $DB;

        switch ($this->action) {
            case SURVEYPRO_NOACTION:
                break;
            case SURVEYPRO_HIDEITEM:
                $this->item_hide_execute();
                break;
            case SURVEYPRO_SHOWITEM:
                $this->item_show_execute();
                break;
            case SURVEYPRO_DELETEITEM:
                $this->item_delete_execute();
                break;
            case SURVEYPRO_DROPMULTILANG:
                $this->drop_multilang_execute();
                break;
            case SURVEYPRO_CHANGEORDER:
                $this->reorder_items();
                // After item reorder, if you reload the page whithout cleaning the url, the reorder action is performed again.
                $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', array('id' => $this->cm->id));
                redirect($returnurl);
                break;
            case SURVEYPRO_CHANGEINDENT:
                $where = array('itemid' => $this->rootitemid);
                $DB->set_field('surveypro'.$this->type.'_'.$this->plugin, 'indent', $this->nextindent, $where);
                break;
            case SURVEYPRO_MAKERESERVED:
                $this->item_makereserved_execute();
                break;
            case SURVEYPRO_MAKEAVAILABLE:
                $this->item_makeavailable_execute();
                break;
            case SURVEYPRO_HIDEALLITEMS:
                $this->hide_all_execute();
                break;
            case SURVEYPRO_SHOWALLITEMS:
                $this->show_all_execute();
                break;
            case SURVEYPRO_DELETEALLITEMS:
                $this->delete_all_execute();
                break;
            case SURVEYPRO_DELETEVISIBLEITEMS:
                $this->delete_visible_execute();
                break;
            case SURVEYPRO_DELETEHIDDENITEMS:
                $this->delete_hidden_execute();
                break;
            default:
                $message = 'Unexpected $this->action = '.$this->action;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * Provide a feedback for the actions performed in actions_execution.
     *
     * @return void
     */
    public function actions_feedback() {
        switch ($this->action) {
            case SURVEYPRO_NOACTION:
                if (!empty($this->surveypro->template)) {
                    $this->drop_multilang_feedback();
                }
                break;
            case SURVEYPRO_HIDEITEM:
                $this->item_hide_feedback();
                break;
            case SURVEYPRO_SHOWITEM:
                $this->item_show_feedback();
                break;
            case SURVEYPRO_DELETEITEM:
                $this->item_delete_feedback();
                break;
            case SURVEYPRO_MAKERESERVED:
                $this->item_makereserved_feedback();
                break;
            case SURVEYPRO_MAKEAVAILABLE:
                $this->item_makeavailable_feedback();
                break;
            case SURVEYPRO_HIDEALLITEMS:
                $this->hide_all_feedback();
                break;
            case SURVEYPRO_SHOWALLITEMS:
                $this->show_all_feedback();
                break;
            case SURVEYPRO_DELETEALLITEMS:
                $this->delete_all_feedback();
                break;
            case SURVEYPRO_DELETEVISIBLEITEMS:
                $this->delete_visible_feedback();
                break;
            case SURVEYPRO_DELETEHIDDENITEMS:
                $this->delete_hidden_feedback();
                break;
            default:
                // Black hole for all the actions not needing feedback.
        }
    }

    /**
     * Ask for confirmation before a bulk action.
     *
     * @param string $message
     * @return void
     */
    public function bulk_action_ask($message) {
        global $OUTPUT;

        $optionbase = array('id' => $this->cm->id, 'act' => $this->action, 'sesskey' => sesskey());

        $optionsyes = $optionbase;
        $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
        $urlyes = new moodle_url('/mod/surveypro/layout_itemlist.php', $optionsyes);
        $buttonyes = new single_button($urlyes, get_string('continue'), 'get');

        $optionsno = $optionbase;
        $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
        $urlno = new moodle_url('/mod/surveypro/layout_itemlist.php', $optionsno);
        $buttonno = new single_button($urlno, get_string('no'), 'get');

        echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
        echo $OUTPUT->footer();
        die();
    }

    // MARK item hide.

    /**
     * Hide an item and (maybe) all its descendants.
     *
     * @return void
     */
    public function item_hide_execute() {
        global $DB;

        // Build tohidelist.
        // Here I must select the whole tree down.
        $itemstohide = $this->item_get_children(null, array('hidden' => 0));

        $itemstoprocess = count($itemstohide);
        if ( ($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1) ) {
            // Hide items.
            foreach ($itemstohide as $itemtohide) {
                $DB->set_field('surveypro_item', 'hidden', 1, array('id' => $itemtohide->id));
            }
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $utilityman->reset_items_pages();
        }
    }

    /**
     * Provide a feedback after item_hide_execute.
     *
     * @return void
     */
    public function item_hide_feedback() {
        global $OUTPUT;

        // Build tohidelist.
        // Here I must select the whole tree down.
        $itemstohide = $this->item_get_children(null, array('hidden' => 0));

        $itemstoprocess = count($itemstohide);
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // Ask for confirmation.
                $dependencies = array();
                $item = surveypro_get_item($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->itemcontent = $item->get_content();
                foreach ($itemstohide as $itemtohide) {
                    $dependencies[] = $itemtohide->sortindex;
                }
                // Drop the original item because it doesn't go in the message.
                $key = array_search($this->sortindex, $dependencies);
                if ($key !== false) { // Should always happen.
                    unset($dependencies[$key]);
                }
                $a->dependencies = implode(', ', $dependencies);
                if (count($dependencies) == 1) {
                    $message = get_string('confirm_hide1item', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_hidechainitems', 'mod_surveypro', $a);
                }

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_HIDEITEM, 'sesskey' => sesskey());

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('continue'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            }
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }
    }

    // MARK item show.

    /**
     * Show an item and (maybe) all its ascendants.
     *
     * @return void
     */
    public function item_show_execute() {
        global $DB;

        // Build toshowlist.
        $toshowlist = $this->add_parent_node(array('hidden' => 1));

        $itemstoprocess = count($toshowlist); // This is the list of ancestors.
        if ( ($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1) ) {
            // Show items.
            foreach ($toshowlist as $toshowitemid) {
                $DB->set_field('surveypro_item', 'hidden', 0, array('id' => $toshowitemid));
            }
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $utilityman->reset_items_pages();
        }
    }

    /**
     * Provide a feedback after item_show_execute.
     *
     * @return void
     */
    public function item_show_feedback() {
        global $OUTPUT;

        // Build toshowlist.
        $toshowlist = $this->add_parent_node(array('hidden' => 1));

        $itemstoprocess = count($toshowlist); // This is the list of ancestors.
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // Ask for confirmation.
                $item = surveypro_get_item($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

                $a = new stdClass();
                $a->lastitem = $item->get_content();
                $ancestors = array_keys($toshowlist);
                // Drop the original item because it doesn't go in the message.
                $key = array_search($this->sortindex, $ancestors);
                if ($key !== false) { // Should always happen.
                    unset($ancestors[$key]);
                }
                $a->ancestors = implode(', ', $ancestors);
                if (count($ancestors) == 1) {
                    $message = get_string('confirm_show1item', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_showchainitems', 'mod_surveypro', $a);
                }

                $optionbase = array();
                $optionbase['id'] = $this->cm->id;
                $optionbase['act'] = SURVEYPRO_SHOWITEM;
                $optionbase['itemid'] = $this->rootitemid;
                $optionbase['sesskey'] = sesskey();

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('continue'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            }
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }
    }

    // MARK item delete.

    /**
     * Delete an item and (maybe) all its descendants.
     *
     * @return void
     */
    public function item_delete_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $utilityman->reset_items_pages();
            $whereparams = array('surveyproid' => $this->surveypro->id);

            $childrenids = array();

            $itemstodelete = $this->item_get_children();
            array_shift($itemstodelete);
            if ($itemstodelete) {
                foreach ($itemstodelete as $itemtodelete) {
                    $whereparams['id'] = $itemtodelete->id;
                    $utilityman->delete_items($whereparams);
                }
            }

            // Get the content of the item for the feedback message.
            $item = surveypro_get_item($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

            $killedsortindex = $item->get_sortindex();
            $whereparams = array('id' => $this->rootitemid);
            $utilityman->delete_items($whereparams);

            $this->itemcount -= 1;

            $utilityman->items_reindex($killedsortindex);
            $this->confirm = SURVEYPRO_ACTION_EXECUTED;

            $this->actionfeedback = new stdClass();
            $this->actionfeedback->chain = !empty($itemstodelete);
            $this->actionfeedback->content = $item->get_content();
            $this->actionfeedback->pluginname = strtolower(get_string('pluginname', 'surveypro'.$this->type.'_'.$this->plugin));
        }
    }

    /**
     * Provide a feedback after item_delete_execute.
     *
     * @return void
     */
    public function item_delete_feedback() {
        global $DB, $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            // In the frame of the confirmation I need to declare whether some child will break the link.
            $item = surveypro_get_item($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

            $a = new stdClass();
            $a->content = $item->get_content();
            $a->pluginname = strtolower(get_string('pluginname', 'surveypro'.$this->type.'_'.$this->plugin));
            $message = get_string('confirm_delete1item', 'mod_surveypro', $a);

            // Is there any child item chain to break? (Sortindex is supposed to be a valid key in the next query).
            $itemstodelete = $this->item_get_children();
            array_shift($itemstodelete);
            if ($itemstodelete) {
                foreach ($itemstodelete as $itemtodelete) {
                    $childrenids[] = $itemtodelete->sortindex;
                }
                $nodes = implode(', ', $childrenids);
                $message .= ' '.get_string('confirm_deletechainitems', 'mod_surveypro', $nodes);
                $labelyes = get_string('continue');
            } else {
                $labelyes = get_string('yes');
            }

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DELETEITEM, 'sesskey' => sesskey());

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['itemid'] = $this->rootitemid;
            $optionsyes['plugin'] = $this->plugin;
            $optionsyes['type'] = $this->type;

            $urlyes = new moodle_url('/mod/surveypro/layout_itemlist.php', $optionsyes);
            $buttonyes = new single_button($urlyes, $labelyes, 'get');

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;

            $urlno = new moodle_url('/mod/surveypro/layout_itemlist.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'), 'get');

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $a = new stdClass();
            $a->content = $this->actionfeedback->content;
            $a->pluginname = $this->actionfeedback->pluginname;
            if ($this->actionfeedback->chain) {
                $message = get_string('feedback_deletechainitems', 'mod_surveypro', $a);
            } else {
                $message = get_string('feedback_delete1item', 'mod_surveypro', $a);
            }
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK drop multilang.

    /**
     * Drop multilang from all the item.
     *
     * @return void
     */
    public function drop_multilang_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $template = $this->surveypro->template;
            $where = array('surveyproid' => $this->surveypro->id);
            $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');
            foreach ($itemseeds as $itemseed) {
                $id = $itemseed->id;
                $type = $itemseed->type;
                $plugin = $itemseed->plugin;
                $item = surveypro_get_item($this->cm, $this->surveypro, $id, $type, $plugin);
                if ($multilangfields = $item->item_get_multilang_fields()) { // Pagebreak and fieldsetend have no multilang_fields.
                    foreach ($multilangfields as $mlplugin) { // Take in mind that $mlplugin is an array of fields.
                        $record = new stdClass();
                        if ($plugin == 'item') {
                            $record->id = $item->get_itemid();
                        } else {
                            $record->id = $item->get_pluginid();
                        }

                        $where = array('id' => $record->id);
                        $fieldlist = implode(',', $mlplugin);
                        $reference = $DB->get_record('surveypro'.$type.'_'.$plugin, $where, $fieldlist, MUST_EXIST);

                        foreach ($mlplugin as $fieldname) {
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

            $surveypro = new stdClass();
            $surveypro->id = $this->surveypro->id;
            $surveypro->template = null;
            $DB->update_record('surveypro', $surveypro);

            $paramurl = array();
            $paramurl['id'] = $this->cm->id;
            $paramurl['act'] = SURVEYPRO_DROPMULTILANG;
            $paramurl['sesskey'] = sesskey();
            $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
            $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
            redirect($returnurl);
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $paramurl = array('id' => $this->cm->id);
            $returnurl = new moodle_url('/mod/surveypro/layout_preview.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after drop_multilang_execute.
     *
     * @return void
     */
    public function drop_multilang_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $message = get_string('confirm_dropmultilang', 'mod_surveypro');

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DROPMULTILANG);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $urlyes = new moodle_url('/mod/surveypro/layout_itemlist.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('yes'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('/mod/surveypro/layout_itemlist.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_dropmultilang', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK item make reserved.

    /**
     * Set the item as reserved.
     *
     * the idea is this: in a chain of parent-child items,
     *     -> reserved items can be parent of reserved items only
     *     -> reserved items can be child of reserved items only
     *
     * @return void
     */
    public function item_makereserved_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            return;
        }

        // Here I must select the whole tree down.
        $itemstoreserve = $this->add_parent_node(array('reserved' => 0));

        // I am interested to oldest parent only.
        $baseitemid = end($itemstoreserve);

        // Build itemstoreserve starting from the oldest parent.
        $itemstoreserve = $this->item_get_children($baseitemid, array('reserved' => 0));

        $itemstoprocess = count($itemstoreserve);
        if ( ($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1) ) {
            // Make items reserved.
            foreach ($itemstoreserve as $itemtoreserve) {
                $DB->set_field('surveypro_item', 'reserved', 1, array('id' => $itemtoreserve->id));
            }
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $utilityman->reset_items_pages();
        }
    }

    /**
     * Provide a feedback after item_makereserved_execute.
     *
     * the idea is this: in a chain of parent-child items,
     *     -> reserved items can be parent of reserved items only
     *     -> reserved items can be child of reserved items only
     *
     * @return void
     */
    public function item_makereserved_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
            return;
        }

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Here I must select the whole tree down.
            $itemstoreserve = $this->add_parent_node(array('reserved' => 0));

            // I am interested to oldest parent only.
            $baseitemid = end($itemstoreserve);

            // Build itemstoreserve starting from the oldest parent.
            $itemstoreserve = $this->item_get_children($baseitemid, array('reserved' => 0));

            $itemstoprocess = count($itemstoreserve); // This is the list of ancestors.
            if ($itemstoprocess > 1) { // Ask for confirmation.
                // If the clicked element has not parents.
                $a = new stdClass();
                $item = surveypro_get_item($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);
                $a->itemcontent = $item->get_content();
                foreach ($itemstoreserve as $itemtoreserve) {
                    $dependencies[] = $itemtoreserve->sortindex;
                }
                // Drop the original item because it doesn't go in the message.
                $key = array_search($this->sortindex, $dependencies);
                if ($key !== false) { // Should always happen.
                    unset($dependencies[$key]);
                }
                $a->dependencies = implode(', ', $dependencies);

                if ($baseitemid != $this->rootitemid) {
                    $firstparentitem = reset($itemstoreserve);
                    $parentitem = surveypro_get_item($this->cm, $this->surveypro, $firstparentitem->id);
                    $a->parentcontent = $parentitem->get_content();
                    $message = get_string('confirm_reservechainitems_newparent', 'mod_surveypro', $a);
                } else {
                    if (count($dependencies) == 1) {
                        $message = get_string('confirm_reserve1item', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('confirm_reservechainitems', 'mod_surveypro', $a);
                    }
                }

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_MAKERESERVED, 'sesskey' => sesskey());

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('continue'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            }
        }
    }

    // MARK item make free.

    /**
     * Set the item as standard (free).
     *
     * the idea is this: in a chain of parent-child items,
     *     -> available items (not reserved) can be parent of available items only
     *     -> available items (not reserved) can be child of available items only
     *
     * @return void
     */
    public function item_makeavailable_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            return;
        }

        // Build itemstoavailable.
        $itemstoavailable = $this->add_parent_node(array('reserved' => 1));

        // I am interested to oldest parent only.
        $baseitemid = end($itemstoavailable);

        // Build itemstoavailable starting from the oldest parent.
        $itemstoavailable = $this->item_get_children($baseitemid, array('reserved' => 1));

        $itemstoprocess = count($itemstoavailable); // This is the list of ancestors.
        if ( ($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1) ) {
            // Make items available.
            foreach ($itemstoavailable as $itemtoavailable) {
                $DB->set_field('surveypro_item', 'reserved', 0, array('id' => $itemtoavailable->id));
            }
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $utilityman->reset_items_pages();
        }
    }

    /**
     * Provide a feedback after item_makeavailable_execute.
     *
     * the idea is this: in a chain of parent-child items,
     *     -> available items (not reserved) can be parent of available items only
     *     -> available items (not reserved) can be child of available items only
     *
     * @return void
     */
    public function item_makeavailable_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
            return;
        }

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Build itemstoavailable.
            $itemstoavailable = $this->add_parent_node(array('reserved' => 1));

            // I am interested to oldest parent only.
            $baseitemid = end($itemstoavailable);

            // Build itemstoavailable starting from the oldest parent.
            $itemstoavailable = $this->item_get_children($baseitemid, array('reserved' => 1));

            $itemstoprocess = count($itemstoavailable); // This is the list of ancestors.
            if ($itemstoprocess > 1) { // Ask for confirmation.
                // If the clicked element has not parents.
                $a = new stdClass();
                $item = surveypro_get_item($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);
                $a->itemcontent = $item->get_content();
                foreach ($itemstoavailable as $itemtoavailable) {
                    $dependencies[] = $itemtoavailable->sortindex;
                }
                // Drop the original item because it doesn't go in the message.
                $key = array_search($this->sortindex, $dependencies);
                if ($key !== false) { // Should always happen.
                    unset($dependencies[$key]);
                }
                $a->dependencies = implode(', ', $dependencies);

                if ($baseitemid != $this->rootitemid) {
                    $firstparentitem = reset($itemstoavailable);
                    $parentitem = surveypro_get_item($this->cm, $this->surveypro, $firstparentitem->id);
                    $a->parentcontent = $parentitem->get_content();
                    $message = get_string('confirm_freechainitems_newparent', 'mod_surveypro', $a);
                } else {
                    if (count($dependencies) == 1) {
                        $message = get_string('confirm_free1item', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('confirm_freechainitems', 'mod_surveypro', $a);
                    }
                }

                $optionbase = array();
                $optionbase['id'] = $this->cm->id;
                $optionbase['act'] = SURVEYPRO_MAKEAVAILABLE;
                $optionbase['itemid'] = $this->rootitemid;
                $optionbase['sesskey'] = sesskey();

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('continue'), 'get');

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/layout_itemlist.php#sortindex_'.$this->sortindex, $optionsno);
                $buttonno = new single_button($urlno, get_string('no'), 'get');

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            }
        }
    }

    // MARK all hide.

    /**
     * Hide all items.
     *
     * @return void
     */
    public function hide_all_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $whereparams = array('surveyproid' => $this->surveypro->id);
            $utilityman->items_set_visibility($whereparams, 0);

            $utilityman->reset_items_pages();

            $this->set_confirm(SURVEYPRO_ACTION_EXECUTED);
        }
    }

    /**
     * Provide a feedback after hide_all_execute.
     *
     * @return void
     */
    public function hide_all_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_hideallitems', 'mod_surveypro');
            $this->bulk_action_ask($message);
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_hideallitems', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK all show.

    /**
     * Show all items.
     *
     * @return void
     */
    public function show_all_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);

            $whereparams = array('surveyproid' => $this->surveypro->id);
            $utilityman->items_set_visibility($whereparams, 1);

            $utilityman->items_reindex();

            $this->set_confirm(SURVEYPRO_ACTION_EXECUTED);
        }
    }

    /**
     * Provide a feedback after show_all_execute.
     *
     * @return void
     */
    public function show_all_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_showallitems', 'mod_surveypro');
            $this->bulk_action_ask($message);
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_showallitems', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK all delete.

    /**
     * Delete all items.
     *
     * @return void
     */
    public function delete_all_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);

            $whereparams = array('surveyproid' => $this->surveypro->id);
            $utilityman->delete_items($whereparams);

            $paramurl = array();
            $paramurl['id'] = $this->cm->id;
            $paramurl['act'] = SURVEYPRO_DELETEALLITEMS;
            $paramurl['sesskey'] = sesskey();
            $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
            $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after delete_all_execute.
     *
     * @return void
     */
    public function delete_all_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_deleteallitems', 'mod_surveypro');
            $this->bulk_action_ask($message);
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_deleteallitems', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK visible delete.

    /**
     * Delete visible items.
     *
     * @return void
     */
    public function delete_visible_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);

            $whereparams = array('surveyproid' => $this->surveypro->id);
            $whereparams['hidden'] = 0;
            $utilityman->delete_items($whereparams);

            $utilityman->items_reindex();

            $paramurl = array();
            $paramurl['id'] = $this->cm->id;
            $paramurl['act'] = SURVEYPRO_DELETEVISIBLEITEMS;
            $paramurl['sesskey'] = sesskey();
            $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
            $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after delete_visible_execute.
     *
     * @return void
     */
    public function delete_visible_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_deletevisibleitems', 'mod_surveypro');
            $this->bulk_action_ask($message);
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_deletevisibleitems', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK hidden delete.

    /**
     * Delete hidden items.
     *
     * @return void
     */
    public function delete_hidden_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);

            $whereparams = array('surveyproid' => $this->surveypro->id);
            $whereparams['hidden'] = 1;
            $utilityman->delete_items($whereparams);

            $utilityman->items_reindex();

            $paramurl = array();
            $paramurl['id'] = $this->cm->id;
            $paramurl['act'] = SURVEYPRO_DELETEHIDDENITEMS;
            $paramurl['sesskey'] = sesskey();
            $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
            $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after delete_hidden_feedback.
     *
     * @return void
     */
    public function delete_hidden_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_deletehiddenitems', 'mod_surveypro');
            $this->bulk_action_ask($message);
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_deletehiddenitems', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK get.

    /**
     * Get item id.
     *
     * @return void
     */
    public function get_itemid() {
        return $this->rootitemid;
    }

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

    /**
     * Get has submissions.
     *
     * @return void
     */
    public function get_hassubmissions() {
        return $this->hassubmissions;
    }

    /**
     * Get item count.
     *
     * @return void
     */
    public function get_itemcount() {
        return $this->itemcount;
    }

    // MARK set.

    /**
     * Set typeplugin.
     *
     * @param string $typeplugin
     * @return void
     */
    public function set_typeplugin($typeplugin) {
        if (preg_match('~^('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_(\w+)$~', $typeplugin, $match)) {
            // Execution comes from /form/items/selectitem_form.php.
            $this->type = $match[1]; // Field or format.
            $this->plugin = $match[2]; // Boolean or char ... or fieldset ...
        } else {
            $message = 'Malformed typeplugin parameter passed to set_typeplugin';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

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
     * Set sortindex.
     *
     * @param int $sortindex
     * @return void
     */
    public function set_sortindex($sortindex) {
        $this->sortindex = $sortindex;
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
     * Set view.
     *
     * @param int $view
     * @return void
     */
    public function set_view($view) {
        $this->view = $view;
    }

    /**
     * Set last item before.
     *
     * @param int $lastitembefore
     * @return void
     */
    public function set_lastitembefore($lastitembefore) {
        $this->lastitembefore = $lastitembefore;
    }

    /**
     * Set confirm.
     *
     * @param int $confirm
     * @return void
     */
    public function set_confirm($confirm) {
        $this->confirm = $confirm;
    }

    /**
     * Set nextindent.
     *
     * @param int $nextindent
     * @return void
     */
    public function set_nextindent($nextindent) {
        $this->nextindent = $nextindent;
    }

    /**
     * Set parentid.
     *
     * @param int $parentid
     * @return void
     */
    public function set_parentid($parentid) {
        $this->parentid = $parentid;
    }

    /**
     * Set item editing feedback.
     *
     * @param int $itemeditingfeedback
     * @return void
     */
    public function set_itemeditingfeedback($itemeditingfeedback) {
        $this->itemeditingfeedback = $itemeditingfeedback;
    }

    /**
     * Set itemtomove.
     *
     * @param int $itemtomove
     * @return void
     */
    public function set_itemtomove($itemtomove) {
        $this->itemtomove = $itemtomove;
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

    /**
     * Set itemcount.
     *
     * @param int $itemcount
     * @return void
     */
    public function set_itemcount($itemcount) {
        $this->itemcount = $itemcount;
    }
}
