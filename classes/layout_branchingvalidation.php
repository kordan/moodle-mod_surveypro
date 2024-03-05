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

/**
 * The base class representing the list of elements of this surveypro
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class layout_branchingvalidation {

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
     * @param object $context
     * @param object $surveypro
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
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
        $table = new \flexible_table('relations');

        $paramurl = ['s' => $this->cm->instance, 'section' => 'branchingvalidation'];
        $baseurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = [];
        $tablecolumns[] = 'plugin';
        $tablecolumns[] = 'sortindex';
        $tablecolumns[] = 'parentitem';
        $tablecolumns[] = 'customnumber';
        $tablecolumns[] = 'content';
        $tablecolumns[] = 'parentconstraints';
        $tablecolumns[] = 'status';
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = [];
        $tableheaders[] = get_string('typeplugin', 'mod_surveypro');
        $tableheaders[] = get_string('sortindex', 'mod_surveypro');
        $tableheaders[] = get_string('branching', 'mod_surveypro');
        $tableheaders[] = get_string('customnumber_header', 'mod_surveypro');
        $tableheaders[] = get_string('content', 'mod_surveypro');
        $tableheaders[] = get_string('parentconstraints', 'mod_surveypro');
        $tableheaders[] = $statusstr;
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

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

        $iconparams = [];

        $editstr = get_string('edit');
        $iconparams = ['title' => $editstr];
        $editicn = new \pix_icon('t/edit', $editstr, 'moodle', $iconparams);

        $parentelementstr = get_string('parentelement_title', 'mod_surveypro');
        $iconparams = ['title' => $parentelementstr];
        $branchicn = new \pix_icon('branch', $parentelementstr, 'surveypro', $iconparams);

        // Get parents id only.
        $sql = 'SELECT DISTINCT id as paretid, 1
                FROM {surveypro_item} parent
                WHERE EXISTS (
                    SELECT \'x\'
                    FROM {surveypro_item} child
                    WHERE child.parentid = parent.id)
                AND surveyproid = ?';
        $whereparams = [$this->surveypro->id];
        $isparent = $DB->get_records_sql_menu($sql, $whereparams);

        // Get itemseeds.
        $sql = 'SELECT DISTINCT id as itemid, plugin, type, sortindex
                FROM {surveypro_item} parent
                WHERE EXISTS (
                    SELECT \'x\'
                    FROM {surveypro_item} child
                    WHERE child.parentid = parent.id)
                AND surveyproid = ?

                UNION

                SELECT DISTINCT id as itemid, plugin, type, sortindex
                FROM {surveypro_item}
                WHERE surveyproid = ?
                    AND parentid > 0

                ORDER BY sortindex;';
        $whereparams = [$this->surveypro->id, $this->surveypro->id];
        $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

        $message = get_string('welcome_relationvalidation', 'mod_surveypro', $statusstr);
        echo $OUTPUT->notification($message, 'notifymessage');

        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->itemid, $itemseed->type, $itemseed->plugin, true);
            $itemishidden = $item->get_hidden();

            if ($item->get_parentid()) {
                // Here I do not know type and plugin.
                $parentitem = surveypro_get_item($this->cm, $this->surveypro, $item->get_parentid());
            }

            $tablerow = [];

            // Plugin.
            $component = 'surveypro'.$item->get_type().'_'.$item->get_plugin();
            $alt = get_string('pluginname', $component);
            $iconparams = ['title' => $alt, 'class' => 'icon'];
            $content = $OUTPUT->pix_icon('icon', $alt, $component, $iconparams);
            $tablerow[] = $content;

            // Sortindex.
            $tablerow[] = $item->get_sortindex();

            // Parentid.
            if ($item->get_parentid()) {
                $content = $parentitem->get_sortindex();
                $content .= \html_writer::tag('span', $OUTPUT->render($branchicn), ['class' => 'branch']);
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
            $tablerow[] = $item->get_content();

            // Parentconstraints.
            if (isset($isparent[$itemseed->itemid])) {
                $tablerow[] = $item->item_list_constraints();
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
                            $errormessage = \html_writer::start_tag('span', ['class' => 'errormessage']);
                            $errormessage .= get_string('wrongrelation', 'mod_surveypro', $item->get_parentcontent('; '));
                            $errormessage .= \html_writer::end_tag('span');
                            $tablerow[] = $errormessage;
                        } else {
                            $tablerow[] = get_string('wrongrelation', 'mod_surveypro', $item->get_parentcontent('; '));
                        }
                    }
                    if ($status == SURVEYPRO_CONDITIONMALFORMED) {
                        if (empty($itemishidden)) {
                            $errormessage = \html_writer::start_tag('span', ['class' => 'errormessage']);
                            $errormessage .= get_string('badchildparentvalue', 'mod_surveypro', $item->get_parentcontent('; '));
                            $errormessage .= \html_writer::end_tag('span');
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
            $paramurlbase = [];
            $paramurlbase['s'] = $this->cm->instance;
            $paramurlbase['itemid'] = $item->get_itemid();
            $paramurlbase['type'] = $item->get_type();
            $paramurlbase['plugin'] = $item->get_plugin();
            $paramurlbase['section'] = 'itemsetup';
            // End of $paramurlbase definition.

            // SURVEYPRO_NEWITEM.
            $paramurl = $paramurlbase;
            $paramurl['mode'] = SURVEYPRO_NEWITEM;

            $link = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            $paramlink = ['id' => 'edit_'.$item->get_itemid(), 'title' => $editstr];
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
}
