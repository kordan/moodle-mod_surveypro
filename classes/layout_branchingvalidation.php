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
class layout_branchingvalidation
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
     * Setup the flexible_table for the relations validation table.
     *
     * @param string $statusstr
     * @return \flexible_table
     */
    protected function setup_relations_table(string $statusstr): \flexible_table {
        $table = new \flexible_table('relations');

        $paramurl = ['s' => $this->cm->instance, 'section' => 'branchingvalidation'];
        $baseurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = [
            'plugin', 'sortindex', 'parentitem', 'customnumber',
            'content', 'parentconstraints', 'status', 'actions',
        ];
        $table->define_columns($tablecolumns);

        $tableheaders = [
            get_string('typeplugin', 'mod_surveypro'),
            get_string('sortindex', 'mod_surveypro'),
            get_string('branching', 'mod_surveypro'),
            get_string('customnumber_header', 'mod_surveypro'),
            get_string('content', 'mod_surveypro'),
            get_string('parentconstraints', 'mod_surveypro'),
            $statusstr,
            get_string('actions'),
        ];
        $table->define_headers($tableheaders);

        foreach ($tablecolumns as $col) {
            $table->column_class($col, $col);
        }

        $table->set_attribute('id', 'validaterelations');
        $table->set_attribute('class', 'generaltable');
        $table->setup();

        return $table;
    }

    /**
     * Display the "validate_relations" table.
     *
     * @return void
     */
    public function display_relations_table() {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->libdir . '/tablelib.php');

        $statusstr = get_string('relation_status', 'mod_surveypro');
        $table = $this->setup_relations_table($statusstr);

        $editstr = get_string('edit');
        $editicn = new \pix_icon('t/edit', $editstr, 'moodle', ['title' => $editstr]);
        $branchicn = new \pix_icon(
            'branch',
            get_string('parentelement_title', 'mod_surveypro'),
            'surveypro',
            ['title' => get_string('parentelement_title', 'mod_surveypro')]
        );

        // Get parents id only.
        $sql = 'SELECT DISTINCT id as paretid, 1
                FROM {surveypro_item} parent
                WHERE EXISTS (
                    SELECT \'x\'
                    FROM {surveypro_item} child
                    WHERE child.parentid = parent.id)
                AND surveyproid = ?';
        $isparent = $DB->get_records_sql_menu($sql, [$this->surveypro->id]);

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
        $itemseeds = $DB->get_recordset_sql($sql, [$this->surveypro->id, $this->surveypro->id]);

        $message = get_string('welcome_relationvalidation', 'mod_surveypro', $statusstr);
        echo $OUTPUT->notification($message, 'notifymessage');

        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_itemclass(
                $this->cm,
                $this->surveypro,
                $itemseed->itemid,
                $itemseed->type,
                $itemseed->plugin,
                true,
            );

            $parentitem = null;
            if ($item->get_parentid()) {
                $parentitem = surveypro_get_itemclass($this->cm, $this->surveypro, $item->get_parentid());
            }

            $tablerow = $this->build_relation_row($item, $parentitem, $isparent, $branchicn, $editicn, $editstr);
            $rowclass = empty($item->get_hidden()) ? '' : 'dimmed';
            $table->add_data($tablerow, $rowclass);
        }
        $itemseeds->close();

        $table->set_attribute('align', 'center');
        $table->print_html();
    }

    /**
     * Build the status cell content for a relation row.
     *
     * @param \mod_surveypro\itembase $item
     * @param \mod_surveypro\itembase|null $parentitem
     * @return string HTML
     */
    protected function get_relation_status_cell(\mod_surveypro\itembase $item, ?\mod_surveypro\itembase $parentitem): string {
        if (!$item->get_parentid() || !$parentitem) {
            return '-';
        }

        $itemishidden = $item->get_hidden();
        $status = $parentitem->parent_validate_child_constraints($item->get_parentvalue());

        if ($status == SURVEYPRO_CONDITIONOK) {
            return get_string('ok');
        }

        if ($status == SURVEYPRO_CONDITIONNEVERMATCH) {
            $text = get_string('wrongrelation', 'mod_surveypro', $item->get_parentcontent('; '));
        } else {
            $text = get_string('badchildparentvalue', 'mod_surveypro', $item->get_parentcontent('; '));
        }

        if (empty($itemishidden)) {
            return \html_writer::tag('span', $text, ['class' => 'errormessage']);
        }

        return $text;
    }

    /**
     * Build the table row array for a single item in the relations table.
     *
     * @param \mod_surveypro\itembase $item
     * @param \mod_surveypro\itembase|null $parentitem
     * @param array $isparent
     * @param \pix_icon $branchicn
     * @param \pix_icon $editicn      (kept for signature compatibility but no longer used)
     * @param string $editstr
     * @return array
     */
    protected function build_relation_row(
        \mod_surveypro\itembase $item,
        ?\mod_surveypro\itembase $parentitem,
        array $isparent,
        \pix_icon $branchicn,
        \pix_icon $editicn,
        string $editstr
    ): array {
        global $OUTPUT;

        $tablerow   = [];
        $itemseedid = $item->get_itemid();

        // Plugin.
        $component = 'surveypro' . $item->get_type() . '_' . $item->get_plugin();
        $alt = get_string('pluginname', $component);
        $tablerow[] = $OUTPUT->pix_icon('icon', $alt, $component, ['title' => $alt, 'class' => 'icon']);

        // Sortindex.
        $tablerow[] = $item->get_sortindex();

        // Parentid.
        if ($item->get_parentid() && $parentitem) {
            $content  = $parentitem->get_sortindex();
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
        $tablerow[] = isset($isparent[$itemseedid]) ? $item->item_list_constraints() : '-';

        // Status.
        $tablerow[] = $this->get_relation_status_cell($item, $parentitem);

        // Actions – kebab menu with Edit only.
        $paramurlbase = [
            's'       => $this->cm->instance,
            'itemid'  => $itemseedid,
            'type'    => $item->get_type(),
            'plugin'  => $item->get_plugin(),
            'section' => 'itemsetup',
            'mode'    => SURVEYPRO_EDITITEM,
        ];
        $link = new \moodle_url('/mod/surveypro/layout.php', $paramurlbase);

        $menu = new \action_menu();
        $menu->set_kebab_trigger(get_string('actions'));
        $menu->set_menu_left(\action_menu::TR, \action_menu::BR);
        $menu->set_boundary('window');
        $menu->add(new \action_menu_link_secondary(
            $link,
            new \pix_icon('t/edit', ''),
            $editstr,
            ['id' => 'edit_' . $itemseedid]
        ));

        $tablerow[] = $OUTPUT->render($menu);

        return $tablerow;
    }
}
