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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The utility class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_utility_submission {

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
        $this->context = context_module::instance($cm->id);
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
        }
        $this->surveypro = $surveypro;
    }

    /**
     * Set the status to submissions.
     *
     * @param array $whereparams
     * @param bool $status
     * @return void
     */
    public function submissions_set_status($whereparams=null, $status) {
        global $DB;

        if ( ($status != SURVEYPRO_STATUSCLOSED) && ($status != SURVEYPRO_STATUSINPROGRESS) ) {
            debugging('Bad parameters passed to submissions_set_status', DEBUG_DEVELOPER);
        }

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $whereparams['status'] = 1 - $status;
        $DB->set_field('surveypro_submission', 'status', $status, $whereparams);
    }

    /**
     * Display an alarming message whether there are submissions.
     *
     * @return void
     */
    public function get_submissions_warning() {
        global $COURSE;

        $message = get_string('hassubmissions_alert', 'mod_surveypro');

        $keepinprogress = $this->surveypro->keepinprogress;
        if (empty($keepinprogress)) {
            $message .= get_string('hassubmissions_danger', 'mod_surveypro');
        }

        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $message .= get_string('hassubmissions_alert_activitycompletion', 'mod_surveypro');
        }

        return $message;
    }

    /**
     * Get used plugin list.
     *
     * This method provide the list af the plugin used in the current surveypro
     * getting them from the items already added
     *
     * @param string $type Optional plugin type
     * @return array $pluginlist;
     */
    public function get_used_plugin_list($type='') {
        global $DB;

        $whereparams = array();
        $sql = 'SELECT plugin
                FROM {surveypro_item}
                WHERE surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;
        if (!empty($type)) {
            $sql .= ' AND type = :type';
            $whereparams['type'] = $type;
        }
        $sql .= ' GROUP BY plugin';

        $pluginlist = $DB->get_fieldset_sql($sql, $whereparams);

        return $pluginlist;
    }

    /**
     * Assign to the user outform the custom css provided for the instance.
     *
     * @return void
     */
    public function add_custom_css() {
        global $PAGE;

        $fs = get_file_storage();
        if ($fs->get_area_files($this->context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, 'sortorder', false)) {
            $PAGE->requires->css('/mod/surveypro/userstyle.php?id='.$this->surveypro->id.'&amp;cmid='.$this->cm->id);
        }
    }

    /**
     * Get the list of available URLs for admin menu and for module pages tree both
     *
     * @param int $caller of this routine. It can be: SURVEYPRO_TAB, SURVEYPRO_BLOCK.
     * @return array of boolean permissions to show link in the admin blook or pages in the module tree
     */
    public function get_common_links_url($caller) {
        global $DB;

        $callers = array(SURVEYPRO_TAB, SURVEYPRO_BLOCK);
        if (!in_array($caller, $callers)) {
            $message = 'Wrong caller passed to get_common_links_url';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $canview = has_capability('mod/surveypro:view', $this->context);
        $canpreview = has_capability('mod/surveypro:preview', $this->context);
        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);
        $cansearch = has_capability('mod/surveypro:searchsubmissions', $this->context);
        $canimportdata = has_capability('mod/surveypro:importdata', $this->context);
        $canexportdata = has_capability('mod/surveypro:exportdata', $this->context);
        $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context);
        $cansaveusertemplates = has_capability('mod/surveypro:saveusertemplates', $this->context);
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context);
        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context);
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context);
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);

        $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);
        $hassubmissions = $utilitylayoutman->has_submissions();

        $whereparams = array('surveyproid' => $this->surveypro->id);
        $countparents = $DB->count_records_select('surveypro_item', 'surveyproid = :surveyproid AND parentid <> 0', $whereparams);

        $availableurllist = array();

        $paramurlbase = array('id' => $this->cm->id);

        // Tab/Container layout.
        $elements = array();

        // Layout -> preview.
        $elements['preview'] = false;
        if ($canpreview) {
            $elementurl = new moodle_url('/mod/surveypro/layout_preview.php', $paramurlbase);
            $elements['preview'] = $elementurl;
        }

        // Layout -> elements.
        $elements['manage'] = false;
        if ($canmanageitems) {
            $elementurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurlbase);
            $elements['manage'] = $elementurl;
        }

        // Layout -> validate.
        $elements['validate'] = false;
        if ($canmanageitems && empty($this->surveypro->template) && $countparents) {
            $elementurl = new moodle_url('/mod/surveypro/layout_validation.php', $paramurlbase);
            $elements['validate'] = $elementurl;
        }

        // Layout -> itemsetup.
        $elements['itemsetup'] = false;
        if ($canmanageitems) {
            $elements['itemsetup'] = empty($this->surveypro->template);
        }

        // Layout -> container.
        $elements['container'] = false;
        if ($elements['preview'] || $elements['manage'] || $elements['validate'] || $elements['itemsetup']) {
            $elementurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurlbase);
            $elements['container'] = $elementurl;
        }

        $availableurllist['tab_layout'] = $elements;
        // End of: Tab/Container layout.

        // Tab/Container submissions.
        $elements = array();

        // Submissions -> cover.
        $elements['cover'] = false;
        if ($canview) {
            $elementurl = new moodle_url('/mod/surveypro/view_cover.php', $paramurlbase);
            $elements['cover'] = $elementurl;
        }

        // Submissions -> responses.
        $elements['responses'] = false;
        if (!is_guest($this->context)) {
            $elementurl = new moodle_url('/mod/surveypro/view.php', array('id' => $this->cm->id, 'force' => 1));
            $elements['responses'] = $elementurl;
        }

        // Submissions -> search.
        $elements['search'] = false;
        $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);
        if ($cansearch && $utilitylayoutman->has_search_items()) {
            $elementurl = new moodle_url('/mod/surveypro/view_search.php', $paramurlbase);
            $elements['search'] = $elementurl;
        }

        // Submissions -> import.
        $elements['import'] = false;
        if ($canimportdata) {
            $elementurl = new moodle_url('/mod/surveypro/view_import.php', $paramurlbase);
            $elements['import'] = $elementurl;
        }

        // Submissions -> export.
        $elements['export'] = false;
        if ($canexportdata) {
            $elementurl = new moodle_url('/mod/surveypro/view_export.php', $paramurlbase);
            $elements['export'] = $elementurl;
        }

        // Submissions -> report.
        $elements['report'] = $canaccessreports;

        // Submissions -> container.
        $elements['container'] = false;
        if ($caller == SURVEYPRO_TAB) {
            if ($elements['cover'] || $elements['responses'] || $elements['search'] || $elements['report']) {
                $elementurl = new moodle_url('/mod/surveypro/view.php', $paramurlbase);
                $elements['container'] = $elementurl;
            }
        }
        if ($caller == SURVEYPRO_BLOCK) {
            if ($elements['import'] || $elements['export']) {
                $elementurl = new moodle_url('/mod/surveypro/view.php', $paramurlbase);
                $elements['container'] = $elementurl;
            }
        }

        $availableurllist['tab_submissions'] = $elements;
        // End of: Tab/Container submissions.

        // Tab/Container user template.
        $elements = array();

        // User template -> container.
        $elements['container'] = $canmanageusertemplates && empty($this->surveypro->template);

        // User template -> manage.
        $elements['manage'] = false;
        if ($elements['container']) {
            $elementurl = new moodle_url('/mod/surveypro/utemplate_manage.php', $paramurlbase);
            $elements['manage'] = $elementurl;
        }

        // User template -> save.
        $elements['save'] = false;
        if ($elements['container'] && $cansaveusertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/utemplate_save.php', $paramurlbase);
            $elements['save'] = $elementurl;
        }

        // User template -> import.
        $elements['import'] = false;
        if ($elements['container'] && $canimportusertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/utemplate_import.php', $paramurlbase);
            $elements['import'] = $elementurl;
        }

        // User template -> apply.
        $elements['apply'] = false;
        if ($elements['container'] && (!$hassubmissions || $riskyediting) && $canapplyusertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/utemplate_apply.php', $paramurlbase);
            $elements['apply'] = $elementurl;
        }

        $availableurllist['tab_utemplate'] = $elements;
        // End of: Tab/Container user template.

        // Tab/Container master template.
        $elements = array();

        // Master template -> save.
        $elements['save'] = false;
        if ($cansavemastertemplates && empty($this->surveypro->template)) {
            $elementurl = new moodle_url('/mod/surveypro/mtemplate_save.php', $paramurlbase);
            $elements['save'] = $elementurl;
        }

        // Master template -> apply.
        $elements['apply'] = false;
        if ((!$hassubmissions || $riskyediting) && $canapplymastertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/mtemplate_apply.php', $paramurlbase);
            $elements['apply'] = $elementurl;
        }

        // Master template -> container.
        $elements['container'] = $elements['save'] || $elements['apply'];

        $availableurllist['tab_mtemplate'] = $elements;
        // End of: Tab/Container master template.

        return $availableurllist;
    }

    /**
     * surveypro_groupmates
     *
     * @param object $cm
     * @param int $userid Optional $userid: the user you want to know his/her groupmates
     * @return Array with the list of groupmates of the user
     */
    public function get_groupmates($cm, $userid=0) {
        global $COURSE, $USER;

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $groupusers = array();
        if ($currentgroups = groups_get_all_groups($COURSE->id, $USER->id, $cm->groupingid)) {
            foreach ($currentgroups as $currentgroup) {
                $groupusers += groups_get_members($currentgroup->id, 'u.id');
            }
        }

        return array_keys($groupusers);
    }
}
