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
 * Surveypro layout class.
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
class layout_itemlist
{
    /**
     * @var \stdClass Course module object
     */
    protected $cm;

    /**
     * @var \stdClass Context object
     */
    protected $context;

    /**
     * @var \stdClass Surveypro object
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
     * @var stdClass object with the feedback for the user
     */
    protected $actionfeedback;

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
     * Display all the items in a table.
     *
     * @return void
     */
    public function display_items_table() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir . '/tablelib.php');

        $table = $this->setup_items_table();
        $iconset = $this->get_icon_set();

        // Build $paramurlmove.
        $paramurlmove = [
            's'   => $this->cm->instance,
            'act' => SURVEYPRO_CHANGEORDER,
            'itm' => $this->itemtomove,
        ];

        [$where, $params] = surveypro_fetch_items_seeds($this->surveypro->id, false, true, null, null, null, true);
        $orderby = ($this->mode == SURVEYPRO_CHANGEORDERASK) ? 'sortindex ASC' : $table->get_sql_sort();
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, $orderby, 'id as itemid, type, plugin');

        // Draw the very first moveherebox if needed.
        if (($this->mode == SURVEYPRO_CHANGEORDERASK) && (!$this->parentid)) {
            $drawmoveherebox = true;
            $paramurl = $paramurlmove;
            $paramurl['lib'] = 0;
            $paramurl['section'] = 'itemslist';
            $paramurl['sesskey'] = sesskey();

            $link = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            $paramlink = [
                'id' => 'moveafter_0',
                'title' => $iconset['moveherestr'],
                'aria-label' => $iconset['moveherestr'],
            ];
            $moveicn = new \pix_icon('movehere', $editstr['reservedstr'], 'moodle', $iconparams);
            $icons = $OUTPUT->action_icon($link, $moveicn, null, $paramlink);

            $tablerow = array_pad([$icons], count($table->columns), '');
            $table->add_data($tablerow);
        } else {
            $drawmoveherebox = false;
        }

        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_itemclass(
                $this->cm,
                $this->surveypro,
                $itemseed->itemid,
                $itemseed->type,
                $itemseed->plugin,
                true
            );

            if (($this->mode == SURVEYPRO_CHANGEORDERASK) && ($item->get_itemid() == $this->rootitemid)) {
                continue;
            }

            $tablerow = $this->build_item_row($item, $iconset);
            $rowclass = empty($item->get_hidden()) ? '' : 'dimmed';
            $table->add_data($tablerow, $rowclass);

            // Draw moveherebox after each row if needed.
            $sortindex = $item->get_sortindex();
            if ($this->mode == SURVEYPRO_CHANGEORDERASK) {
                if ($this->parentid) {
                    $drawmoveherebox = $drawmoveherebox || ($item->get_itemid() == $this->parentid);
                    if ($item->get_parentid() == $this->rootitemid) {
                        $drawmoveherebox = false;
                    }
                } else {
                    $drawmoveherebox = $drawmoveherebox && ($item->get_parentid() != $this->rootitemid);
                }

                if (!empty($drawmoveherebox)) {
                    $paramurl = $paramurlmove;
                    $paramurl['lib'] = $sortindex;
                    $paramurl['section'] = 'itemslist';
                    $paramurl['sesskey'] = sesskey();

                    $link = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);
                    $paramlink = [
                        'id' => 'move_item_' . $sortindex,
                        'title' => $iconset['moveherestr'],
                        'aria-label' => $iconset['moveherestr'],
                    ];
                    $icons = $OUTPUT->action_icon($link, $moveicn, null, $paramlink);

                    $tablerow = array_pad([$icons], count($table->columns), '');
                    $table->add_data($tablerow);
                }
            }
        }
        $itemseeds->close();

        $table->set_attribute('align', 'center');
        $table->print_html();
    }

    /**
     * Setup the flexible_table for the items list.
     *
     * @return \flexible_table
     */
    protected function setup_items_table(): \flexible_table {
        $table = new \flexible_table('itemslist');

        $paramurl = ['s' => $this->cm->instance, 'section' => 'itemslist'];
        $baseurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = [
            'plugin', 'sortindex', 'parentid', 'customnumber',
            'content', 'variable', 'formpage', 'availability', 'actions',
        ];
        $table->define_columns($tablecolumns);

        $tableheaders = [
            get_string('typeplugin', 'mod_surveypro'),
            get_string('sortindex', 'mod_surveypro'),
            get_string('branching', 'mod_surveypro'),
            get_string('customnumber_header', 'mod_surveypro'),
            get_string('content', 'mod_surveypro'),
            get_string('variable', 'mod_surveypro'),
            get_string('page'),
            get_string('availability', 'mod_surveypro'),
            get_string('actions'),
        ];
        $table->define_headers($tableheaders);

        $table->sortable(true, 'sortindex');
        $table->no_sorting('customnumber');
        $table->no_sorting('content');
        $table->no_sorting('variable');
        $table->no_sorting('availability');
        $table->no_sorting('actions');

        foreach ($tablecolumns as $col) {
            $table->column_class($col, $col);
        }

        $id = ($this->mode == SURVEYPRO_CHANGEORDERASK) ? 'sortitems' : 'manageitems';
        $table->set_attribute('id', $id);
        $table->set_attribute('class', 'generaltable');
        $table->setup();

        return $table;
    }

    /**
     * Build the set of label strings used in the items table.
     * Icons are no longer stored here: each caller instantiates its own pix_icon
     * so that Moodle cannot mutate a shared object across loop iterations.
     *
     * @return array keyed by string name
     */
    protected function get_icon_set(): array {
        return [
            'editstr'           => get_string('edit'),
            'parentelementstr'  => get_string('parentelement_title', 'mod_surveypro'),
            'reorderstr'        => get_string('changeorder_title', 'mod_surveypro'),
            'hidestr'           => get_string('hide_title', 'mod_surveypro'),
            'showstr'           => get_string('show_title', 'mod_surveypro'),
            'deletestr'         => get_string('delete'),
            'outdentstr'        => get_string('outdent', 'mod_surveypro'),
            'indentstr'         => get_string('indent', 'mod_surveypro'),
            'moveherestr'       => get_string('movehere'),
            'publicstr'         => get_string('public_title', 'mod_surveypro'),
            'reservedstr'       => get_string('reserved_title', 'mod_surveypro'),
            'insearchstr'       => get_string('insearchform_title', 'mod_surveypro'),
            'notinsearchstr'    => get_string('notinsearchform_title', 'mod_surveypro'),
            'unreservablestr'   => get_string('unreservable_title', 'mod_surveypro'),
            'unsearchablestr'   => get_string('unsearchable_title', 'mod_surveypro'),
            'unavailablestr'    => get_string('unavailableelement_title', 'mod_surveypro'),
            'forcedoptionalstr' => get_string('forcedoptionalitem_title', 'mod_surveypro'),
        ];
    }

    /**
     * Build the table row array for a single item.
     *
     * @param \mod_surveypro\itembase $item
     * @param array $iconset  String-only icon set from get_icon_set().
     * @return array
     */
    protected function build_item_row(\mod_surveypro\itembase $item, array $iconset): array {
        global $DB, $OUTPUT, $PAGE;

        $renderer = $PAGE->get_renderer('core');

        $sortindex = $item->get_sortindex();
        $itemid    = $item->get_itemid();

        $tablerow = [];

        // Plugin icon.
        $component = 'surveypro' . $item->get_type() . '_' . $item->get_plugin();
        $alt = get_string('userfriendlypluginname', $component);
        $content = \html_writer::tag('a', '', ['name' => 'sortindex_' . $sortindex]);
        $content .= \html_writer::tag(
            'span',
            $OUTPUT->pix_icon('icon', $alt, $component, ['title' => $alt]),
            ['class' => 'pluginicon']
        );
        $tablerow[] = $content;

        // Sortindex.
        $tablerow[] = $sortindex;

        // Parentid.
        if ($item->get_parentid()) {
            $parentsortindex = $DB->get_field('surveypro_item', 'sortindex', ['id' => $item->get_parentid()]);
            $paramicon = [
                'title'       => $iconset['parentelementstr'],
                'alt'         => $iconset['parentelementstr'],
                'aria-hidden' => 'true',
            ];
            $parentelementicn = new \pix_icon('branch', $iconset['parentelementstr'], 'surveypro', $paramicon);
            $content  = $parentsortindex;
            $content .= \html_writer::tag('span', $OUTPUT->render($parentelementicn), ['class' => 'branch']);
            $content .= $item->get_parentcontent('; ');
        } else {
            $content = '';
        }
        $tablerow[] = $content;

        // Customnumber.
        if (($item->get_type() == SURVEYPRO_TYPEFIELD) || ($item->get_plugin() == 'label')) {
            $tmpl = new \mod_surveypro\local\ipe\layout_customnumber($itemid, $item->get_customnumber());
            $tablerow[] = $renderer->render_from_template('core/inplace_editable', $tmpl->export_for_template($renderer));
        } else {
            $tablerow[] = '';
        }

        // Content.
        $tablerow[] = $item->get_content();

        // Variable.
        if ($item->get_type() == SURVEYPRO_TYPEFIELD) {
            $tmpl = new \mod_surveypro\local\ipe\layout_variable($itemid, $item->get_variable());
            $tablerow[] = $renderer->render_from_template('core/inplace_editable', $tmpl->export_for_template($renderer));
        } else {
            $tablerow[] = '';
        }

        // Page.
        $tablerow[] = $item->item_uses_form_page() ? $item->get_formpage() : '';

        // Availability icons.
        $tablerow[] = $this->get_availability_icons($item, $iconset);

        // Action icons.
        $tablerow[] = $this->get_action_icons($item, $iconset);

        return $tablerow;
    }

    /**
     * Build the availability icons for a single item row.
     *
     * @param \mod_surveypro\itembase $item
     * @param array $iconset  String-only icon set from get_icon_set().
     * @return string HTML
     */
    protected function get_availability_icons(\mod_surveypro\itembase $item, array $iconset): string {
        $itemishidden = $item->get_hidden();
        $sortindex    = $item->get_sortindex();
        $itemid       = $item->get_itemid();
        $icons        = '';
        $paramurlbase = [
            's'      => $this->cm->instance,
            'itemid' => $itemid,
            'type'   => $item->get_type(),
            'plugin' => $item->get_plugin(),
        ];

        // First icon: reserved vs available.
        if (!$itemishidden) {
            if ($item->get_insetupform('reserved')) {
                if ($item->get_reserved()) {
                    $icons .= $this->draw_reserved_icon($paramurlbase, $iconset, $sortindex);
                } else {
                    $icons .= $this->draw_public_icon($paramurlbase, $iconset, $sortindex);
                }
            } else {
                $icons .= $this->draw_unreservable_icon($iconset);
            }
        } else {
            $icons .= $this->draw_unavailable_icon($iconset);
        }

        // Second icon: insearchform.
        if (!$itemishidden) {
            if ($item->get_insetupform('insearchform')) {
                if ($item->get_insearchform()) {
                    $icons .= $this->draw_removefromsearch_icon($paramurlbase, $iconset, $sortindex);
                } else {
                    $icons .= $this->draw_addtosearch_icon($paramurlbase, $iconset, $sortindex);
                }
            } else {
                $icons .= $this->draw_unsearchable_icon($iconset, $sortindex);
            }
        } else {
            $icons .= $this->draw_unavailable_icon($iconset);
        }

        return $icons;
    }

    /**
     * Draw the "reserved" icon (item is reserved, click to make it available).
     *
     * @param array $paramurlbase
     * @param array $iconset String-only icon set from get_icon_set()
     * @param int $sortindex
     * @return string HTML fragment
     */
    protected function draw_reserved_icon(array $paramurlbase, array $iconset, int $sortindex): string {
        global $OUTPUT;

        $paramurl = $paramurlbase;
        $paramurl['act']       = SURVEYPRO_MAKEAVAILABLE;
        $paramurl['sortindex'] = $sortindex;
        $paramurl['section']   = 'itemslist';
        $paramurl['sesskey']   = sesskey();
        $link = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);

        $iconparams = [
            'id'          => 'reservedup_' . $sortindex,
            'title'       => $iconset['reservedstr'],
            'alt'         => $iconset['reservedstr'],
            'aria-hidden' => 'true',
        ];
        $icon = new \pix_icon('reserved', $iconset['reservedstr'], 'surveypro', $iconparams);
        $paramlink = [
            'id'         => 'makeavailable_item_' . $sortindex,
            'title'      => $iconset['reservedstr'],
            'aria-label' => $iconset['reservedstr'],
        ];

        return $OUTPUT->action_icon($link, $icon, null, $paramlink);
    }

    /**
     * Draw the "public/available" icon (item is public, click to make it reserved).
     *
     * @param array $paramurlbase
     * @param array $iconset  String-only icon set from get_icon_set()
     * @param int $sortindex
     * @return string HTML fragment
     */
    protected function draw_public_icon(array $paramurlbase, array $iconset, int $sortindex): string {
        global $OUTPUT;

        $paramurl = $paramurlbase;
        $paramurl['act']       = SURVEYPRO_MAKERESERVED;
        $paramurl['sortindex'] = $sortindex;
        $paramurl['section']   = 'itemslist';
        $paramurl['sesskey']   = sesskey();
        $link = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);

        $iconparams = [
            'id'          => 'reserveddown_' . $sortindex,
            'title'       => $iconset['publicstr'],
            'alt'         => $iconset['publicstr'],
            'aria-hidden' => 'true',
        ];
        $icon = new \pix_icon('free', $iconset['publicstr'], 'surveypro', $iconparams);
        $paramlink = [
            'id'         => 'makereserved_item_' . $sortindex,
            'title'      => $iconset['publicstr'],
            'aria-label' => $iconset['publicstr'],
        ];

        return $OUTPUT->action_icon($link, $icon, null, $paramlink);
    }

    /**
     * Draw the "unreservable" icon (item cannot be reserved).
     *
     * @param array $iconset  String-only icon set from get_icon_set().
     * @return string \html_writer
     */
    protected function draw_unreservable_icon(array $iconset): string {
        global $OUTPUT;

        $icon = new \pix_icon(
            'unreservable',
            $iconset['unreservablestr'],
            'surveypro',
            ['title' => $iconset['unreservablestr'], 'alt' => $iconset['unreservablestr'], 'aria-hidden' => 'true']
        );

        return \html_writer::tag('span', $OUTPUT->render($icon), ['class' => 'noactionicon']);
    }

    /**
     * Draw the "unavailable" icon (item is hidden).
     *
     * @param array $iconset  String-only icon set from get_icon_set().
     * @return string \html_writer
     */
    protected function draw_unavailable_icon(array $iconset): string {
        global $OUTPUT;

        $icon = new \pix_icon(
            'unavailable',
            $iconset['unavailablestr'],
            'surveypro',
            ['title' => $iconset['unavailablestr'], 'alt' => $iconset['unavailablestr'], 'aria-hidden' => 'true']
        );

        return \html_writer::tag('span', $OUTPUT->render($icon), ['class' => 'noactionicon']);
    }

    /**
     * Draw the "remove from search" icon (item is in search form, click to remove it).
     *
     * @param array $paramurlbase
     * @param array $iconset  String-only icon set from get_icon_set()
     * @param int $sortindex
     * @return string HTML fragment
     */
    protected function draw_removefromsearch_icon(array $paramurlbase, array $iconset, int $sortindex): string {
        global $OUTPUT;

        $paramurl = $paramurlbase;
        $paramurl['act']       = SURVEYPRO_REMOVEFROMSEARCH;
        $paramurl['sortindex'] = $sortindex;
        $paramurl['section']   = 'itemslist';
        $paramurl['sesskey']   = sesskey();
        $link = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);

        $iconparams = [
            'id'          => 'searchup_' . $sortindex,
            'title'       => $iconset['insearchstr'],
            'alt'         => $iconset['insearchstr'],
            'aria-hidden' => 'true',
        ];
        $icon = new \pix_icon('insearch', $iconset['insearchstr'], 'mod_surveypro', $iconparams);
        $paramlink = [
            'id'         => 'removefromsearch_item_' . $sortindex,
            'title'      => $iconset['insearchstr'],
            'aria-label' => $iconset['insearchstr'],
        ];

        return $OUTPUT->action_icon($link, $icon, null, $paramlink);
    }

    /**
     * Draw the "add to search" icon (item is not in search form, click to add it).
     *
     * @param array $paramurlbase
     * @param array $iconset  String-only icon set from get_icon_set()
     * @param int $sortindex
     * @return string HTML fragment
     */
    protected function draw_addtosearch_icon(array $paramurlbase, array $iconset, int $sortindex): string {
        global $OUTPUT;

        $paramurl = $paramurlbase;
        $paramurl['act']       = SURVEYPRO_ADDTOSEARCH;
        $paramurl['sortindex'] = $sortindex;
        $paramurl['section']   = 'itemslist';
        $paramurl['sesskey']   = sesskey();
        $link = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);

        $iconparams = [
            'id'          => 'searchdown_' . $sortindex,
            'title'       => $iconset['notinsearchstr'],
            'alt'         => $iconset['notinsearchstr'],
            'aria-hidden' => 'true',
        ];
        $icon = new \pix_icon('notinsearch', $iconset['notinsearchstr'], 'mod_surveypro', $iconparams);
        $paramlink = [
            'id'         => 'addtosearch_item_' . $sortindex,
            'title'      => $iconset['notinsearchstr'],
            'aria-label' => $iconset['notinsearchstr'],
        ];

        return $OUTPUT->action_icon($link, $icon, null, $paramlink);
    }

    /**
     * Draw the "unsearchable" icon (item cannot be added to search form).
     *
     * @param array $iconset  String-only icon set from get_icon_set()
     * @param int $sortindex
     * @return \html_writer
     */
    protected function draw_unsearchable_icon(array $iconset, int $sortindex): string {
        global $OUTPUT;

        $icon = new \pix_icon(
            'unsearchable',
            $iconset['unsearchablestr'],
            'surveypro',
            ['id' => 'searchoff_' . $sortindex, 'title' => $iconset['unsearchablestr'], 'alt' => $iconset['unsearchablestr'], 'aria-hidden' => 'true']
        );

        return \html_writer::tag('span', $OUTPUT->render($icon), ['class' => 'noactionicon']);
    }

    /**
     * Build the action menu (kebab ⋮) for a single item row.
     *
     * @param \mod_surveypro\itembase $item
     * @param array $iconset  String-only icon set from get_icon_set().
     * @return string HTML
     */
    protected function get_action_icons(\mod_surveypro\itembase $item, array $iconset): string {
        global $OUTPUT, $PAGE;

        if ($this->mode == SURVEYPRO_CHANGEORDERASK) {
            return '';
        }

        $sortindex    = $item->get_sortindex();
        $itemid       = $item->get_itemid();
        $itemishidden = $item->get_hidden();

        $paramurlbase = [
            's'      => $this->cm->instance,
            'itemid' => $itemid,
            'type'   => $item->get_type(),
            'plugin' => $item->get_plugin(),
        ];

        $parts = [];

        // ── SLOT 1: Required toggle / forced-optional
        // Fixed width: the .surveypro-action-slot class ensures
        // that all items occupy the same amount of space in this slot.
        $classname = 'surveypro' . $item->get_type() . '_' . $item->get_plugin() . '\item';
        if ($classname::has_mandatoryattribute()) {
            if ($item->item_canbesettomandatory()) {
                $renderer = $PAGE->get_renderer('core');
                $tmpl = new \mod_surveypro\local\ipe\layout_required($itemid, $item->get_required(), $sortindex);
                $tmpl->set_type_toggle();
                $inner = $renderer->render_from_template('core/inplace_editable', $tmpl->export_for_template($renderer));
            } else {
                $forcedoptionalicn = new \pix_icon(
                    'lockedgreen',
                    $iconset['forcedoptionalstr'],
                    'surveypro',
                    ['title' => $iconset['forcedoptionalstr'], 'alt' => $iconset['forcedoptionalstr'], 'aria-hidden' => 'true']
                );
                $inner = $OUTPUT->render($forcedoptionalicn);
            }
        } else {
            // Placeholder della stessa larghezza di un'icona (24px = dimensione standard Moodle icon).
            $inner = \html_writer::tag('span', '', [
                'style'       => 'display:inline-block;width:24px;',
                'aria-hidden' => 'true',
            ]);
        }
        $parts[] = \html_writer::tag('span', $inner, ['class' => 'surveypro-action-slot']);

        // ── SLOT 2: Indentation value
        // Here, too, each sub-slot has a fixed width.
        if ($item->get_insetupform('indent')) {
            $currentindent = $item->get_indent();
            $parts[] = \html_writer::tag('span', '[' . $currentindent . ']', [
                'class' => 'surveypro-action-slot text-center',
                'style' => 'display:inline-block;min-width:2ch;',
            ]);
        }

        // ── SLOT 3: Action menu ⋮ ────────────────────────────
        $menu = new \action_menu();
        $menu->set_kebab_trigger(get_string('actions'));
        $menu->set_boundary('window');

        // Edit.
        $paramurl            = $paramurlbase;
        $paramurl['mode']    = SURVEYPRO_EDITITEM;
        $paramurl['section'] = 'itemsetup';
        $url = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
        $menu->add(new \action_menu_link_secondary(
            $url,
            new \pix_icon('t/edit', ''),
            $iconset['editstr'],
            ['id' => 'edit_item_' . $sortindex]
        ));

        // Hide/Show.
        $riskyediting = ($this->surveypro->riskyeditdeadline > time());
        if (!$this->hassubmissions || $riskyediting) {
            $paramurl            = $paramurlbase;
            $paramurl['section'] = 'itemslist';
            $paramurl['sesskey'] = sesskey();

            if (empty($itemishidden)) {
                $paramurl['act']       = SURVEYPRO_HIDEITEM;
                $paramurl['sortindex'] = $sortindex;
                $url = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);
                $menu->add(new \action_menu_link_secondary(
                    $url,
                    new \pix_icon('i/show', ''),
                    $iconset['hidestr'],
                    ['id' => 'hide_item_' . $sortindex]
                ));
            } else {
                $paramurl['act']       = SURVEYPRO_SHOWITEM;
                $paramurl['sortindex'] = $sortindex;
                $url = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);
                $menu->add(new \action_menu_link_secondary(
                    $url,
                    new \pix_icon('i/hide', ''),
                    $iconset['showstr'],
                    ['id' => 'show_item_' . $sortindex]
                ));
            }
        }

        // Move up/down.
        if ($this->itemcount > 1) {
            $paramurl            = $paramurlbase;
            $paramurl['mode']    = SURVEYPRO_CHANGEORDERASK;
            $paramurl['itm']     = $sortindex;
            $paramurl['section'] = 'itemslist';
            $currentparentid = $item->get_parentid();
            if (!empty($currentparentid)) {
                $paramurl['pid'] = $currentparentid;
            }
            $url = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . ($sortindex - 1), $paramurl);
            $menu->add(new \action_menu_link_secondary(
                $url,
                new \pix_icon('t/move', ''),
                $iconset['reorderstr'],
                ['id' => 'move_item_' . $sortindex]
            ));
        }

        // Outdent/Indent.
        if ($item->get_insetupform('indent')) {
            // $currentindent has already been defined when the [n] was written.
            if ($currentindent !== false) {
                // Outdent.
                $paramurl            = $paramurlbase;
                $paramurl['act']     = SURVEYPRO_CHANGEINDENT;
                $paramurl['section'] = 'itemslist';
                $paramurl['sesskey'] = sesskey();

                if ($currentindent > 0) {
                    $paramurl['ind'] = $currentindent - 1;
                    $url = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);
                    $menu->add(new \action_menu_link_secondary(
                        $url,
                        new \pix_icon('t/left', ''),
                        $iconset['outdentstr'],
                        ['id' => 'reduceindent_item_' . $sortindex]
                    ));
                }

                // Indent.
                // $paramurl has already been defined for outdent.
                if ($currentindent < 9) {
                    $paramurl['ind'] = $currentindent + 1;
                    $url = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);
                    $menu->add(new \action_menu_link_secondary(
                        $url,
                        new \pix_icon('t/right', ''),
                        $iconset['indentstr'],
                        ['id' => 'increaseindent_item_' . $sortindex]
                    ));
                }
            }
        }

        // Delete.
        $riskyediting = ($this->surveypro->riskyeditdeadline > time());
        if (!$this->hassubmissions || $riskyediting) {
            $paramurl              = $paramurlbase;
            $paramurl['act']       = SURVEYPRO_DELETEITEM;
            $paramurl['sortindex'] = $sortindex;
            $paramurl['section']   = 'itemslist';
            $paramurl['sesskey']   = sesskey();
            $url = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $sortindex, $paramurl);
            $menu->add(new \action_menu_link_secondary(
                $url,
                new \pix_icon('t/delete', ''),
                $iconset['deletestr'],
                ['id' => 'delete_item_' . $sortindex, 'class' => 'text-danger']
            ));
        }

        $parts[] = $OUTPUT->render($menu);

        // ── Wrapper flex ─────────────────────────────────────
        // gap-0 removes the extra space between slots; alignment
        // is ensured by the fixed widths of the inner spans.
        return \html_writer::tag(
            'div',
            implode('', $parts),
            ['class' => 'd-flex align-items-center gap-0']
        );
    }

    /**
     * Adds elements to an array starting from initial conditions.
     *
     * Called by:
     *     item_show_execute()
     *     item_show_feedback()
     *     item_makereserved_execute()
     *     item_makereserved_feedback()
     *     item_makeavailable_execute()
     *     item_makeavailable_feedback()
     *
     * $additionalcondition is ['hidden' => 1] OR ['reserved' => 1]
     *
     * @param array $additionalcondition
     * @return array $nodelist
     */
    public function add_parent_node($additionalcondition) {
        global $DB;

        if (!is_array($additionalcondition)) {
            $a = 'add_parent_node';
            throw new \moodle_exception('arrayexpected', 'mod_surveypro', null, $a);
        }

        $nodelist = [$this->sortindex => $this->rootitemid];

        // Get the first parentid.
        $parentitem = new \stdClass();
        $parentitem->parentid = $DB->get_field('surveypro_item', 'parentid', ['id' => $this->rootitemid]);

        $where = ['id' => $parentitem->parentid] + $additionalcondition;

        while ($parentitem = $DB->get_record('surveypro_item', $where, 'id, parentid, sortindex')) {
            $nodelist[$parentitem->sortindex] = (int)$parentitem->id;
            $where = ['id' => $parentitem->parentid] + $additionalcondition;
        }

        return $nodelist;
    }

    /**
     * Get the recursive list of children of a specific item.
     * This method counts children and children of children for as much generation as it founds.
     *
     * Called by:
     *     item_hide_execute()
     *     item_hide_feedback()
     *     item_makereserved_execute()
     *     item_makereserved_feedback()
     *     item_makeavailable_execute()
     *     item_makeavailable_feedback()
     *     item_delete_execute()
     *     item_delete_feedback()
     *
     * @param int $baseitemid the id of the root item for the tree of children to get
     * @param array $where permanent condition needed to filter target items
     * @return object $childrenitems
     */
    public function get_children($baseitemid = null, $where = null) {
        global $DB;

        [$baseitemid, $where] = $this->normalize_children_request($baseitemid, $where);
        $idscontainer = [$baseitemid];
        $childrenitems = $DB->get_records('surveypro_item', ['id' => $baseitemid], 'sortindex', 'id, parentid, sortindex');

        return $this->collect_children_items($childrenitems, $idscontainer, $where);
    }

    /**
     * Normalize request parameters for get_children().
     *
     * @param int|null $baseitemid
     * @param array|null $where
     * @return array
     */
    protected function normalize_children_request($baseitemid, $where): array {
        if (empty($baseitemid)) {
            $baseitemid = $this->rootitemid;
        }
        if (empty($where)) {
            $where = [];
        }
        if (!is_array($where)) {
            $a = 'get_children';
            throw new \moodle_exception('arrayexpected', 'mod_surveypro', null, $a);
        }

        return [(int)$baseitemid, $where];
    }

    /**
     * Collect recursive children records starting from current container.
     *
     * @param array $childrenitems
     * @param array $idscontainer
     * @param array $where
     * @return array
     */
    protected function collect_children_items(array $childrenitems, array $idscontainer, array $where): array {
        global $DB;

        $childid = reset($idscontainer);
        $i = 1;
        do {
            $where['parentid'] = $childid;
            $morechildren = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, parentid, sortindex');
            if ($morechildren) {
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

    // MARK item action execution.

    /**
     * Ask for confirmation before a bulk action.
     *
     * Called by:
     *     show_all_feedback()
     *
     * @param string $message
     * @param string $yeskey
     * @return void
     */
    public function bulk_action_ask($message, $yeskey = null) {
        global $OUTPUT;

        $optionbase = ['s' => $this->cm->instance, 'act' => $this->action, 'section' => 'itemslist', 'sesskey' => sesskey()];

        $optionsyes = $optionbase;
        $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
        $urlyes = new \moodle_url('/mod/surveypro/layout.php', $optionsyes);

        $yeslabel = ($yeskey) ? get_string($yeskey, 'mod_surveypro') : get_string('continue');
        $buttonyes = new \single_button($urlyes, $yeslabel);

        $optionsno = $optionbase;
        $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
        $urlno = new \moodle_url('/mod/surveypro/layout.php', $optionsno);
        $buttonno = new \single_button($urlno, get_string('no'));

        echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
        echo $OUTPUT->footer();
        die();
    }

    /**
     * Perform the actions required through icon click into items table.
     *
     * Called by:
     *     layout.php
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
                $returnurl = new \moodle_url('/mod/surveypro/layout.php', ['s' => $this->cm->instance, 'section' => 'itemslist']);
                redirect($returnurl);
                break;
            case SURVEYPRO_CHANGEINDENT:
                $where = ['id' => $this->rootitemid];
                $DB->set_field('surveypro_item', 'indent', $this->nextindent, $where);
                break;
            case SURVEYPRO_MAKERESERVED:
                $this->item_makereserved_execute();
                break;
            case SURVEYPRO_MAKEAVAILABLE:
                $this->item_makeavailable_execute();
                break;
            case SURVEYPRO_ADDTOSEARCH:
                $this->item_addtosearch_execute();
                break;
            case SURVEYPRO_REMOVEFROMSEARCH:
                $this->item_removefromsearch_execute();
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
                $message = 'Unexpected $this->action = ' . $this->action;
                debugging('Error at line ' . __LINE__ . ' of ' . __FILE__ . '. ' . $message, DEBUG_DEVELOPER);
        }
    }

    /**
     * Provide a feedback for the actions performed in actions_execution.
     *
     * Called by:
     *     layout.php
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
            case SURVEYPRO_ADDTOSEARCH:
                $this->item_addtosearch_feedback();
                break;
            case SURVEYPRO_REMOVEFROMSEARCH:
                $this->item_removefromsearch_feedback();
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
     * Store to the database sortindex field, the relative position at the items according to last changes.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function reorder_items() {
        global $DB;

        // I start loading the id of the item I want to move starting from its known sortindex.
        $where = ['surveyproid' => $this->surveypro->id, 'sortindex' => $this->itemtomove];
        $itemid = $DB->get_field('surveypro_item', 'id', $where);

        // Am I moving it backward or forward?
        if ($this->itemtomove > $this->lastitembefore) {
            // Moving the item backward.
            $searchitem = $this->itemtomove - 1;
            $replaceitem = $this->itemtomove;

            $where = ['surveyproid' => $this->surveypro->id];
            while ($searchitem > $this->lastitembefore) {
                $where['sortindex'] = $searchitem;
                $DB->set_field('surveypro_item', 'sortindex', $replaceitem, $where);
                $replaceitem = $searchitem;
                $searchitem--;
            }

            $DB->set_field('surveypro_item', 'sortindex', $replaceitem, ['id' => $itemid]);
        } else {
            // Moving the item forward.
            $searchitem = $this->itemtomove + 1;
            $replaceitem = $this->itemtomove;

            $where = ['surveyproid' => $this->surveypro->id];
            while ($searchitem <= $this->lastitembefore) {
                $where['sortindex'] = $searchitem;
                $DB->set_field('surveypro_item', 'sortindex', $replaceitem, $where);
                $replaceitem = $searchitem;
                $searchitem++;
            }

            $DB->set_field('surveypro_item', 'sortindex', $replaceitem, ['id' => $itemid]);
        }

        // You changed item order. Don't forget to reset the page of each items.
        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $utilitylayoutman->reset_pages();
    }

    // MARK ITEM - hide.

    /**
     * Hide an item and (maybe) all its descendants.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function item_hide_execute() {
        global $DB;

        // Build tohidelist.
        // Here I must select the whole tree down.
        $itemstohide = $this->get_children(null, ['hidden' => 0]);

        $itemstoprocess = count($itemstohide);
        if (($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1)) {
            // Hide items.
            foreach ($itemstohide as $itemtohide) {
                $DB->set_field('surveypro_item', 'hidden', 1, ['id' => $itemtohide->id]);
            }
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $utilitylayoutman->reset_pages();
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
        $itemstohide = $this->get_children(null, ['hidden' => 0]);

        $itemstoprocess = count($itemstohide);
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // Ask for confirmation.
                $dependencies = [];
                $item = surveypro_get_itemclass($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

                $a = new \stdClass();
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

                $optionbase = ['s' => $this->cm->instance];
                $optionbase['act'] = SURVEYPRO_HIDEITEM;
                $optionbase['section'] = 'itemslist';
                $optionbase['sesskey'] = sesskey();

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsyes);
                $buttonyes = new \single_button($urlyes, get_string('continue'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsno);
                $buttonno = new \single_button($urlno, get_string('no'));

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

    // MARK ITEM - show.

    /**
     * Show an item and (maybe) all its ascendants.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function item_show_execute() {
        global $DB;

        // Build toshowlist.
        $toshowlist = $this->add_parent_node(['hidden' => 1]);

        $itemstoprocess = count($toshowlist); // This is the list of ancestors.
        if (($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1)) {
            // Show items.
            foreach ($toshowlist as $toshowitemid) {
                $DB->set_field('surveypro_item', 'hidden', 0, ['id' => $toshowitemid]);
            }
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $utilitylayoutman->reset_pages();
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
        $toshowlist = $this->add_parent_node(['hidden' => 1]);

        $itemstoprocess = count($toshowlist); // This is the list of ancestors.
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            if ($itemstoprocess > 1) { // Ask for confirmation.
                $item = surveypro_get_itemclass($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

                $a = new \stdClass();
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

                $optionbase = [];
                $optionbase['s'] = $this->cm->instance;
                $optionbase['act'] = SURVEYPRO_SHOWITEM;
                $optionbase['itemid'] = $this->rootitemid;
                $optionbase['section'] = 'itemslist';
                $optionbase['sesskey'] = sesskey();

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsyes);
                $buttonyes = new \single_button($urlyes, get_string('continue'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsno);
                $buttonno = new \single_button($urlno, get_string('no'));

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

    // MARK ITEM - make reserved.

    /**
     * Set the item as reserved.
     *
     * Called by:
     *     actions_execution()
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
        $itemstoreserve = $this->add_parent_node(['reserved' => 0]);

        // I am interested to oldest parent only.
        $baseitemid = end($itemstoreserve);

        // Build itemstoreserve starting from the oldest parent.
        $itemstoreserve = $this->get_children($baseitemid, ['reserved' => 0]);

        $itemstoprocess = count($itemstoreserve);
        if (($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1)) {
            // Make items reserved.
            foreach ($itemstoreserve as $itemtoreserve) {
                $DB->set_field('surveypro_item', 'reserved', 1, ['id' => $itemtoreserve->id]);
            }
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $utilitylayoutman->reset_pages();
        }
    }

    /**
     * Provide a feedback after item_makereserved_execute.
     *
     * Called by:
     *     actions_feedback()
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
            $itemstoreserve = $this->add_parent_node(['reserved' => 0]);

            // I am interested to oldest parent only.
            $baseitemid = end($itemstoreserve);

            // Build itemstoreserve starting from the oldest parent.
            $itemstoreserve = $this->get_children($baseitemid, ['reserved' => 0]);

            $itemstoprocess = count($itemstoreserve); // This is the list of ancestors.
            if ($itemstoprocess > 1) { // Ask for confirmation.
                // If the clicked element has not parents.
                $a = new \stdClass();
                $item = surveypro_get_itemclass($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);
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
                    $parentitem = surveypro_get_itemclass($this->cm, $this->surveypro, $firstparentitem->id);
                    $a->parentcontent = $parentitem->get_content();
                    $message = get_string('confirm_reservechainitems_newparent', 'mod_surveypro', $a);
                } else {
                    if (count($dependencies) == 1) {
                        $message = get_string('confirm_reserve1item', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('confirm_reservechainitems', 'mod_surveypro', $a);
                    }
                }

                $optionbase = [];
                $optionbase['s'] = $this->cm->instance;
                $optionbase['act'] = SURVEYPRO_MAKERESERVED;
                $optionbase['section'] = 'itemslist';
                $optionbase['sesskey'] = sesskey();

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsyes);
                $buttonyes = new \single_button($urlyes, get_string('continue'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsno);
                $buttonno = new \single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            }
        }
    }

    // MARK ITEM - make available.

    /**
     * Set the item as standard (free).
     *
     * Called by:
     *     actions_execution()
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
        $itemstoavailable = $this->add_parent_node(['reserved' => 1]);

        // I am interested to oldest parent only.
        $baseitemid = end($itemstoavailable);

        // Build itemstoavailable starting from the oldest parent.
        $itemstoavailable = $this->get_children($baseitemid, ['reserved' => 1]);

        $itemstoprocess = count($itemstoavailable); // This is the list of ancestors.
        if (($this->confirm == SURVEYPRO_CONFIRMED_YES) || ($itemstoprocess == 1)) {
            // Make items available.
            foreach ($itemstoavailable as $itemtoavailable) {
                $DB->set_field('surveypro_item', 'reserved', 0, ['id' => $itemtoavailable->id]);
            }
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $utilitylayoutman->reset_pages();
        }
    }

    /**
     * Provide a feedback after item_makeavailable_execute.
     *
     * Called by:
     *     actions_feedback()
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
            $itemstoavailable = $this->add_parent_node(['reserved' => 1]);

            // I am interested to oldest parent only.
            $baseitemid = end($itemstoavailable);

            // Build itemstoavailable starting from the oldest parent.
            $itemstoavailable = $this->get_children($baseitemid, ['reserved' => 1]);

            $itemstoprocess = count($itemstoavailable); // This is the list of ancestors.
            if ($itemstoprocess > 1) { // Ask for confirmation.
                // If the clicked element has not parents.
                $a = new \stdClass();
                $item = surveypro_get_itemclass($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);
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
                    $parentitem = surveypro_get_itemclass($this->cm, $this->surveypro, $firstparentitem->id);
                    $a->parentcontent = $parentitem->get_content();
                    $message = get_string('confirm_freechainitems_newparent', 'mod_surveypro', $a);
                } else {
                    if (count($dependencies) == 1) {
                        $message = get_string('confirm_free1item', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('confirm_freechainitems', 'mod_surveypro', $a);
                    }
                }

                $optionbase = [];
                $optionbase['s'] = $this->cm->instance;
                $optionbase['act'] = SURVEYPRO_MAKEAVAILABLE;
                $optionbase['itemid'] = $this->rootitemid;
                $optionbase['section'] = 'itemslist';
                $optionbase['sesskey'] = sesskey();

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['itemid'] = $this->rootitemid;
                $optionsyes['plugin'] = $this->plugin;
                $optionsyes['type'] = $this->type;
                $urlyes = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsyes);
                $buttonyes = new \single_button($urlyes, get_string('continue'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new \moodle_url('/mod/surveypro/layout.php#sortindex_' . $this->sortindex, $optionsno);
                $buttonno = new \single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            }
        }
    }

    // MARK ITEM - add to search.

    /**
     * Set the item as searchable.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function item_addtosearch_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            return;
        }

        $DB->set_field('surveypro_item', 'insearchform', 1, ['id' => $this->rootitemid]);
    }

    /**
     * Provide a feedback after item_addtosearch_execute.
     *
     * Called by:
     *     actions_feedback()
     *
     * @return void
     */
    public function item_addtosearch_feedback() {
        // No confirmation needed: action is immediate and reversible.
    }

    // MARK ITEM - remove from search.

    /**
     * Set the item as not searchable.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function item_removefromsearch_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            return;
        }

        $DB->set_field('surveypro_item', 'insearchform', 0, ['id' => $this->rootitemid]);
    }

    /**
     * Provide a feedback after item_removefromsearch_execute.
     *
     * Called by:
     *     actions_feedback()
     *
     * @return void
     */
    public function item_removefromsearch_feedback() {
        // No confirmation needed: action is immediate and reversible.
    }

    // MARK ITEM - delete.

    /**
     * Delete an item and (maybe) all its descendants.
     *
     * @return void
     */
    public function item_delete_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            // After the item deletion action, if the user reload the page, the deletion is performed again rising up an error.
            // If the item to drop is not in the db, this means that the user already deleted it and is reloading the page.
            // In this case, stop the deletion execution.
            if (!$DB->record_exists('surveypro_item', ['id' => $this->rootitemid])) {
                return;
            }

            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $utilitylayoutman->reset_pages();

            $whereparams = ['surveyproid' => $this->surveypro->id];

            $itemstodelete = $this->get_children();
            array_shift($itemstodelete);
            if ($itemstodelete) {
                foreach ($itemstodelete as $itemtodelete) {
                    $whereparams['id'] = $itemtodelete->id;
                    $utilitylayoutman->delete_items($whereparams);
                }
            }

            // Get the content of the item for the feedback message.
            $item = surveypro_get_itemclass($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

            $killedsortindex = $item->get_sortindex();
            $whereparams = ['id' => $this->rootitemid];
            $utilitylayoutman->delete_items($whereparams);

            $utilitylayoutman->items_reindex($killedsortindex);
            $this->confirm = SURVEYPRO_ACTION_EXECUTED;

            $itemcount = $utilitylayoutman->has_items(0, SURVEYPRO_TYPEFIELD, true, true, true);
            $this->set_itemcount($itemcount);

            $this->actionfeedback = new \stdClass();
            $this->actionfeedback->chain = !empty($itemstodelete);
            $this->actionfeedback->content = $item->get_content();
            $this->actionfeedback->pluginname = strtolower(get_string('pluginname', 'surveypro' . $this->type . '_' . $this->plugin));
        }
    }

    /**
     * Provide a feedback after item_delete_execute.
     *
     * @return void
     */
    public function item_delete_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            // In the frame of the confirmation I need to declare whether some child will break the link.
            $item = surveypro_get_itemclass($this->cm, $this->surveypro, $this->rootitemid, $this->type, $this->plugin);

            $a = new \stdClass();
            $a->content = $item->get_content();
            $a->pluginname = strtolower(get_string('pluginname', 'surveypro' . $this->type . '_' . $this->plugin));
            $message = get_string('confirm_delete1item', 'mod_surveypro', $a);

            // Is there any child item chain to break? (Sortindex is supposed to be a valid key in the next query).
            $itemstodelete = $this->get_children();
            array_shift($itemstodelete);
            if ($itemstodelete) {
                foreach ($itemstodelete as $itemtodelete) {
                    $childrenids[] = $itemtodelete->sortindex;
                }
                $nodes = implode(', ', $childrenids);
                $message .= ' ' . get_string('confirm_deletechainitems', 'mod_surveypro', $nodes);
                $labelyes = get_string('continue');
            } else {
                $labelyes = get_string('yes');
            }

            $optionbase['s'] = $this->cm->instance;
            $optionbase['act'] = SURVEYPRO_DELETEITEM;
            $optionbase['section'] = 'itemslist';
            $optionbase['sesskey'] = sesskey();

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['itemid'] = $this->rootitemid;
            $optionsyes['plugin'] = $this->plugin;
            $optionsyes['type'] = $this->type;

            $urlyes = new \moodle_url('/mod/surveypro/layout.php', $optionsyes);
            $buttonyes = new \single_button($urlyes, $labelyes);

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;

            $urlno = new \moodle_url('/mod/surveypro/layout.php', $optionsno);
            $buttonno = new \single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $a = new \stdClass();
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

    // MARK BULK - all show.

    /**
     * Show all items.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function show_all_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);

            $whereparams = ['surveyproid' => $this->surveypro->id];
            $utilitylayoutman->items_set_visibility($whereparams, 1);

            $utilitylayoutman->items_reindex();

            $this->set_confirm(SURVEYPRO_ACTION_EXECUTED);
        }
    }

    /**
     * Provide a feedback after show_all_execute.
     *
     * Called by:
     *     actions_feedback()
     *
     * @return void
     */
    public function show_all_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_showallitems', 'mod_surveypro');
            $yeskey = 'yes_showallitems';
            $this->bulk_action_ask($message, $yeskey);
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

    // MARK BULK - all hide.

    /**
     * Hide all items.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function hide_all_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $whereparams = ['surveyproid' => $this->surveypro->id];
            $utilitylayoutman->items_set_visibility($whereparams, 0);

            $utilitylayoutman->reset_pages();

            $this->set_confirm(SURVEYPRO_ACTION_EXECUTED);
        }
    }

    /**
     * Provide a feedback after hide_all_execute.
     *
     * Called by:
     *     actions_feedback()
     *
     * @return void
     */
    public function hide_all_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_hideallitems', 'mod_surveypro');
            $yeskey = 'yes_hideallitems';
            $this->bulk_action_ask($message, $yeskey);
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

    // MARK BULK - all delete.

    /**
     * Delete all items.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function delete_all_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);

            $whereparams = ['surveyproid' => $this->surveypro->id];
            $utilitylayoutman->delete_items($whereparams);

            $paramurl = [];
            $paramurl['s'] = $this->cm->instance;
            $paramurl['section'] = 'itemslist';
            $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after delete_all_execute.
     *
     * Called by:
     *     actions_feedback()
     *
     * @return void
     */
    public function delete_all_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_deleteallitems', 'mod_surveypro');
            $yeskey = 'yes_deleteallitems';
            $this->bulk_action_ask($message, $yeskey);
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

    // MARK BULK - visible delete.

    /**
     * Delete visible items.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function delete_visible_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);

            $whereparams = ['surveyproid' => $this->surveypro->id];
            $whereparams['hidden'] = 0;
            $utilitylayoutman->delete_items($whereparams);

            $utilitylayoutman->items_reindex();

            $paramurl = [];
            $paramurl['s'] = $this->cm->instance;
            $paramurl['act'] = SURVEYPRO_DELETEVISIBLEITEMS;
            $paramurl['section'] = 'itemslist';
            $paramurl['sesskey'] = sesskey();
            $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
            $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after delete_visible_execute.
     *
     * Called by:
     *     actions_feedback()
     *
     * @return void
     */
    public function delete_visible_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_deletevisibleitems', 'mod_surveypro');
            $yeskey = 'yes_deletevisibleitems';
            $this->bulk_action_ask($message, $yeskey);
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

    // MARK BULK - hidden delete.

    /**
     * Delete hidden items.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function delete_hidden_execute() {
        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);

            $whereparams = ['surveyproid' => $this->surveypro->id];
            $whereparams['hidden'] = 1;
            $utilitylayoutman->delete_items($whereparams);

            $utilitylayoutman->items_reindex();

            $paramurl = [];
            $paramurl['s'] = $this->cm->instance;
            $paramurl['act'] = SURVEYPRO_DELETEHIDDENITEMS;
            $paramurl['section'] = 'itemslist';
            $paramurl['sesskey'] = sesskey();
            $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
            $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after delete_hidden_feedback.
     *
     * Called by:
     *     actions_feedback
     *
     * @return void
     */
    public function delete_hidden_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            $message = get_string('confirm_deletehiddenitems', 'mod_surveypro');
            $yeskey = 'yes_deletehiddenitems';
            $this->bulk_action_ask($message, $yeskey);
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

    // MARK feedback section.

    /**
     * Display a feedback for the editing teacher once an item is edited.
     *
     * Called by:
     *     layout.php
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
                        $message .= '<br>' . get_string('feedback_itemediting_showchainitems', 'mod_surveypro');
                    }
                    break;
                case 3: // A chain of items is now hided because one item was hided.
                    if ($bit) {
                        $message .= '<br>' . get_string('feedback_itemediting_hidechainitems', 'mod_surveypro');
                    }
                    break;
                case 4: // A chain of items was moved in the user entry form.
                    if ($bit) {
                        $message .= '<br>' . get_string('feedback_itemediting_freechainitems', 'mod_surveypro');
                    }
                    break;
                case 5: // A chain of items was removed from the user entry form.
                    if ($bit) {
                        $message .= '<br>' . get_string('feedback_itemediting_reservechainitems', 'mod_surveypro');
                    }
                    break;
            }
        }
        echo $OUTPUT->notification($message, $class);
    }

    // MARK drop multilang.

    /**
     * Drop multilang from all the item.
     *
     * Called by:
     *     actions_execution()
     *
     * @return void
     */
    public function drop_multilang_execute() {
        global $DB;

        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            // Overwrite keys from the database and replace it with the actual strings.
            $template = $this->surveypro->template;

            $where = ['surveyproid' => $this->surveypro->id];
            $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');
            foreach ($itemseeds as $itemseed) {
                $id = $itemseed->id;
                $type = $itemseed->type;
                $plugin = $itemseed->plugin;
                $item = surveypro_get_itemclass($this->cm, $this->surveypro, $id, $type, $plugin);

                $itemsmlfields = $item->get_multilang_fields(false);
                if ($itemsmlfields) { // Pagebreak and fieldsetend have no multilang_fields.
                    // SELECT content,extranote,options,labelother,defaultvalue FROM {surveyprofield_radiobutton} WHERE id = 8.
                    foreach ($itemsmlfields as $table => $fields) { // Note: $itemmlfield is an array of arrays of fields.
                        if (!count($fields)) {
                            continue;
                        }
                        $record = new \stdClass();

                        $fieldlist = implode(',', $fields);
                        if ($table == 'surveypro_item') {
                            $where = ['id' => $id];
                            $savedrecord = $DB->get_record($table, $where, $fieldlist, MUST_EXIST);
                            $record->id = $id;
                        } else {
                            $where = ['itemid' => $id];
                            $savedrecord = $DB->get_record($table, $where, 'id,' . $fieldlist, MUST_EXIST);
                            $record->id = $savedrecord->id;
                        }

                        foreach ($fields as $mlfieldname) {
                            $stringkey = $savedrecord->{$mlfieldname};

                            if (core_text::strlen($stringkey)) {
                                $record->{$mlfieldname} = get_string($stringkey, 'surveyprotemplate_' . $template);
                            } else {
                                $record->{$mlfieldname} = null;
                            }
                        }

                        $DB->update_record($table, $record);
                    }
                }
            }
            $surveypro = new \stdClass();
            $surveypro->id = $this->surveypro->id;
            $surveypro->template = null;
            $DB->update_record('surveypro', $surveypro);

            $paramurl = [];
            $paramurl['s'] = $this->cm->instance;
            $paramurl['act'] = SURVEYPRO_DROPMULTILANG;
            $paramurl['section'] = 'itemslist';
            $paramurl['sesskey'] = sesskey();
            $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
            $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($returnurl);
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $paramurl = ['s' => $this->cm->instance, 'section' => 'itemslist'];
            $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($returnurl);
        }
    }

    /**
     * Provide a feedback after drop_multilang_execute.
     *
     * Called by:
     *     actions_feedback()
     *
     * @return void
     */
    public function drop_multilang_feedback() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $message = get_string('confirm_dropmultilang', 'mod_surveypro');

            $optionbase = ['s' => $this->cm->instance];

            $optionsyes = $optionbase + ['section' => 'itemslist', 'act' => SURVEYPRO_DROPMULTILANG];
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $urlyes = new \moodle_url('/mod/surveypro/layout.php', $optionsyes);
            $buttonyes = new \single_button($urlyes, get_string('yes'));

            $optionsno = $optionbase + ['section' => 'preview'];
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new \moodle_url('/mod/surveypro/layout.php', $optionsno);
            $buttonno = new \single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_dropmultilang', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }
    }

    // MARK set.

    /**
     * Set type.
     *
     * Called by:
     *     layout.php
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
     * Called by:
     *     layout.php
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
     * Called by:
     *     layout.php
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
     * Called by:
     *     layout.php
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
     * Called by:
     *     layout.php
     *
     * @param int $action
     * @return void
     */
    public function set_action($action) {
        $this->action = $action;
    }

    /**
     * Set mode.
     *
     * Called by:
     *     layout.php
     *
     * @param int $mode
     * @return void
     */
    public function set_mode($mode) {
        $this->mode = $mode;
    }

    /**
     * Set itemtomove.
     *
     * Called by:
     *     layout.php
     *
     * @param int $itemtomove
     * @return void
     */
    public function set_itemtomove($itemtomove) {
        $this->itemtomove = $itemtomove;
    }

    /**
     * Set last item before.
     *
     * Called by:
     *     layout.php
     *
     * @param int $lastitembefore
     * @return void
     */
    public function set_lastitembefore($lastitembefore) {
        $this->lastitembefore = $lastitembefore;
    }

    /**
     * Set nextindent.
     *
     * Called by:
     *     layout.php
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
     * Called by:
     *     layout.php
     *
     * @param int $parentid
     * @return void
     */
    public function set_parentid($parentid) {
        $this->parentid = $parentid;
    }

    /**
     * Set confirm.
     *
     * Called by:
     *     layout.php
     *
     * @param int $confirm
     * @return void
     */
    public function set_confirm($confirm) {
        $this->confirm = $confirm;
    }

    /**
     * Set item editing feedback.
     *
     * Called by:
     *     layout.php
     *
     * @param int $itemeditingfeedback
     * @return void
     */
    public function set_itemeditingfeedback($itemeditingfeedback) {
        $this->itemeditingfeedback = $itemeditingfeedback;
    }

    /**
     * Set hassubmissions.
     *
     * Called by:
     *     layout.php
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
     * Called by:
     *     setup()
     *
     * @param int $itemcount
     * @return void
     */
    public function set_itemcount($itemcount) {
        $this->itemcount = $itemcount;
    }
}
