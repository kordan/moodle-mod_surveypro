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

namespace mod_surveypro\output;

use moodle_url;
use url_select;
use mod_surveypro\utility_layout;

/**
 * Class responsible for generating the action bar elements in the surveypro module pages.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_bar {

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

    /** @var moodle_url $currenturl The URL of the current page. */
    private $currenturl;

    /** @var moodle_url $hostingpage The page going to host the menu. */
    private $hostingpage;

    /**
     * The class constructor.
     *
     * @param int $id The surveypro module id.
     * @param moodle_url $pageurl The URL of the current page.
     */
    public function __construct($cm, $context, $surveypro) {
        global $PAGE;

        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;

        $this->currenturl = $PAGE->url;
    }

    /**
     * Generate the output for the action bar in the field page.
     *
     * @param bool $hasfieldselect Whether the field selector element should be rendered.
     * @param null $unused1 This parameter has been deprecated since 4.1 and should not be used anymore.
     * @param null $unused2 This parameter has been deprecated since 4.1 and should not be used anymore.
     * @return string The HTML code for the action bar.
     */
    /*public function get_fields_action_bar(
        bool $hasfieldselect = false,
        ?bool $unused1 = null,
        ?bool $unused2 = null
    ): string {
        global $PAGE;

        if ($unused1 !== null || $unused2 !== null) {
            debugging('Deprecated argument passed to get_fields_action_bar method', DEBUG_DEVELOPER);
        }

        $fieldselect = null;
        if ($hasfieldselect) {
            $fieldselect = $this->get_create_fields();
        }

        $renderer = $PAGE->get_renderer('mod_surveypro');
        $fieldsactionbar = new fields_action_bar($this->id, null, null, null, null, $fieldselect);

        return $renderer->render_fields_action_bar($fieldsactionbar);
    }*/

    /**
     * Generate the output for the action bar in the field mappings page.
     *
     * @return string The HTML code for the action bar.
     */
    /*public function get_fields_mapping_action_bar(): string {
        global $PAGE;

        $renderer = $PAGE->get_renderer('mod_surveypro');
        $fieldsactionbar = new fields_mappings_action_bar($this->id);

        $data = $fieldsactionbar->export_for_preset($renderer);
        return $renderer->render_from_template('mod_surveypro/fields_action_bar', $data);
    }*/

    /**
     * Generate the output for the create a new field action menu.
     *
     * @return \action_menu Action menu to create a new field
     */
    /*public function get_create_fields(): \action_menu {
        // Get the list of possible fields (plugins).
        $plugins = \core_component::get_plugin_list('datafield');
        $menufield = [];
        foreach ($plugins as $plugin => $fulldir) {
            $menufield[$plugin] = get_string('pluginname', "datafield_{$plugin}");
        }
        asort($menufield);

        $fieldselect = new \action_menu();
        $fieldselect->set_menu_trigger(get_string('newfield', 'mod_surveypro'), 'btn btn-secondary');
        $fieldselectparams = ['d' => $this->id, 'mode' => 'new'];
        foreach ($menufield as $fieldtype => $fieldname) {
            $fieldselectparams['newtype'] = $fieldtype;
            $fieldselect->add(new \action_menu_link(
                new moodle_url('/mod/surveypro/field.php', $fieldselectparams),
                new \pix_icon('field/' . $fieldtype, $fieldname, 'data'),
                $fieldname,
                false
            ));
        }
        $fieldselect->set_additional_classes('singlebutton');

        return $fieldselect;
    }*/

    /**
     * Generate the output for the action selector in the view page.
     *
     * @param bool $hasentries Whether entries exist.
     * @param string $mode The current view mode (list, view...).
     * @return string The HTML code for the action selector.
     */
    public function draw_view_action_bar(): string {
        global $PAGE;

        $paramurl = ['s' => $this->surveypro->id];

        $cansearch = has_capability('mod/surveypro:searchsubmissions', $this->context);
        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $addsearchitem = ($cansearch && $utilitylayoutman->has_search_items());

        // First item.
        $paramurl['sheet'] = 'cover';
        $linktocover = new moodle_url('/mod/surveypro/view.php', $paramurl);
        $menu[$linktocover->out(false)] = get_string('tabsurveypro_dashboard', 'mod_surveypro');

        // Second item.
        $paramurl['sheet'] = 'collectedsubmissions';
        $linktosubmissions = new moodle_url('/mod/surveypro/view.php', $paramurl);
        $menu[$linktosubmissions->out(false)] = get_string('tabsurveypro_responses', 'mod_surveypro');

        if ($addsearchitem) {
            // Third item.
            $paramurl['sheet'] = 'searchsubmissions';
            $linktosearch = new moodle_url('/mod/surveypro/view.php', $paramurl);
            $menu[$linktosearch->out(false)] = get_string('tabsurveypro_search', 'mod_surveypro');
        }

        // If sheet = 'newsubmission', set $activeurl to to the URL of the second menu item.
        if (strpos($this->currenturl->out(false), 'newsubmission')) { // If strpos is not null, for sure it will never be zero.
            $activeurl = $linktosubmissions;
        } else {
            $activeurl = $this->currenturl;
        }

        $urlselect = new url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        $viewactionbar = new view_action_bar($this->surveypro->id, $urlselect);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        return $renderer->render_view_action_bar($viewactionbar);
    }

    /**
     * Generate the output for the action selector in the presets page.
     *
     * @return string The HTML code for the action selector.
     */
    /*public function get_presets_action_bar(): string {
        global $PAGE;

        $listpresetlink = new moodle_url('/mod/surveypro/presets.php', ['d' => $this->id,
            'mode' => 'listpreset']);
        $singlepresetlink = new moodle_url('/mod/surveypro/presets.php', ['d' => $this->id,
            'mode' => 'singlepreset']);
        $advancedsearchpresetlink = new moodle_url('/mod/surveypro/presets.php', ['d' => $this->id,
            'mode' => 'asearchpreset']);
        $addpresetlink = new moodle_url('/mod/surveypro/presets.php', ['d' => $this->id, 'mode' => 'addpreset']);
        $rsspresetlink = new moodle_url('/mod/surveypro/presets.php', ['d' => $this->id, 'mode' => 'rsspreset']);
        $csspresetlink = new moodle_url('/mod/surveypro/presets.php', ['d' => $this->id, 'mode' => 'csspreset']);
        $jspresetlink = new moodle_url('/mod/surveypro/presets.php', ['d' => $this->id, 'mode' => 'jspreset']);

        $menu = [
            $addpresetlink->out(false) => get_string('addpreset', 'mod_surveypro'),
            $singlepresetlink->out(false) => get_string('singlepreset', 'mod_surveypro'),
            $listpresetlink->out(false) => get_string('listpreset', 'mod_surveypro'),
            $advancedsearchpresetlink->out(false) => get_string('asearchpreset', 'mod_surveypro'),
            $csspresetlink->out(false) => get_string('csspreset', 'mod_surveypro'),
            $jspresetlink->out(false) => get_string('jspreset', 'mod_surveypro'),
            $rsspresetlink->out(false) => get_string('rsspreset', 'mod_surveypro'),
        ];

        $selectmenu = new \core\output\select_menu('presetsactions', $menu, $this->currenturl->out(false));
        $selectmenu->set_label(get_string('presetsnavigation', 'mod_surveypro'), ['class' => 'sr-only']);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        $presetsactions = $this->get_presets_actions_select(false);

        // Reset all presets action.
        $resetallurl = new moodle_url($this->currenturl);
        $resetallurl->params([
            'action' => 'resetallpresets',
            'sesskey' => sesskey(),
        ]);
        $presetsactions->add(new \action_menu_link(
            $resetallurl,
            null,
            get_string('resetallpresets', 'mod_surveypro'),
            false,
            ['data-action' => 'resetallpresets', 'data-dataid' => $this->id]
        ));

        $presetsactionbar = new presets_action_bar($this->id, $selectmenu, null, null, $presetsactions);

        return $renderer->render_presets_action_bar($presetsactionbar);
    }*/

    /**
     * Generate the output for the action selector in the presets page.
     *
     * @return string The HTML code for the action selector.
     */
    /*public function get_presets_action_bar(): string {
        global $PAGE;

        $renderer = $PAGE->get_renderer('mod_surveypro');
        $presetsactionbar = new presets_action_bar($this->cmid, $this->get_presets_actions_select(true));

        return $renderer->render_presets_action_bar($presetsactionbar);
    }*/

    /**
     * Generate the output for the action selector in the presets preview page.
     *
     * @param manager $manager the manager instance
     * @param string $fullname the preset fullname
     * @param string $current the current preset name
     * @return string The HTML code for the action selector
     */
    /*public function get_presets_preview_action_bar(manager $manager, string $fullname, string $current): string {
        global $PAGE;

        $renderer = $PAGE->get_renderer(manager::PLUGINNAME);

        $cm = $manager->get_coursemodule();

        $menu = [];
        $selected = null;
        foreach (['listpreset', 'singlepreset'] as $presetname) {
            $link = new moodle_url('/mod/surveypro/preset.php', [
                'd' => $this->id,
                'preset' => $presetname,
                'fullname' => $fullname,
                'action' => 'preview',
            ]);
            $menu[$link->out(false)] = get_string($presetname, manager::PLUGINNAME);
            if (!$selected || $presetname == $current) {
                $selected = $link->out(false);
            }
        }
        $urlselect = new url_select($menu, $selected, null);
        $urlselect->set_label(get_string('presetsnavigation', manager::PLUGINNAME), ['class' => 'sr-only']);

        $data = [
            'title' => get_string('preview', manager::PLUGINNAME, preset::get_name_from_plugin($fullname)),
            'hasback' => true,
            'backtitle' => get_string('back'),
            'backurl' => new moodle_url('/mod/surveypro/preset.php', ['id' => $cm->id]),
            'extraurlselect' => $urlselect->export_for_preset($renderer),
        ];
        return $renderer->render_from_template('mod_surveypro/action_bar', $data);
    }*/

    /**
     * Helper method to get the selector for the presets action.
     *
     * @param bool $hasimport Whether the Import buttons must be included or not.
     * @return \action_menu|null The selector object used to display the presets actions. Null when the import button is not
     * displayed and the database hasn't any fields.
     */
    /*protected function get_presets_actions_select(bool $hasimport = false): ?\action_menu {
        global $DB;

        $hasfields = $DB->record_exists('data_fields', ['dataid' => $this->id]);

        // Early return if the database has no fields and the import action won't be displayed.
        if (!$hasfields && !$hasimport) {
            return null;
        }

        $actionsselect = new \action_menu();
        $actionsselect->set_menu_trigger(get_string('actions'), 'btn btn-secondary');

        if ($hasimport) {
            // Import.
            $actionsselectparams = ['id' => $this->cmid];
            $actionsselect->add(new \action_menu_link(
                new moodle_url('/mod/surveypro/preset.php', $actionsselectparams),
                null,
                get_string('importpreset', 'mod_surveypro'),
                false,
                ['data-action' => 'importpresets', 'data-dataid' => $this->cmid]
            ));
        }

        // If the database has no fields, export and save as preset options shouldn't be displayed.
        if ($hasfields) {
            // Export.
            $actionsselectparams = ['id' => $this->cmid, 'action' => 'export'];
            $actionsselect->add(new \action_menu_link(
                new moodle_url('/mod/surveypro/preset.php', $actionsselectparams),
                null,
                get_string('exportpreset', 'mod_surveypro'),
                false
            ));
            // Save as preset.
            $actionsselect->add(new \action_menu_link(
                new moodle_url('/mod/surveypro/preset.php', $actionsselectparams),
                null,
                get_string('saveaspreset', 'mod_surveypro'),
                false,
                ['data-action' => 'saveaspreset', 'data-dataid' => $this->id]
            ));
        }

        return $actionsselect;
    }*/
}
