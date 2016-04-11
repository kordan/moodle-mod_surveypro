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
 * This file contains the classes for the admin settings of the surveypro module.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/adminlib.php');

/**
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_admin_page_manage_surveypro_plugins extends admin_externalpage {

    /**
    * @var string Name of plugin subtype
    */
    private $subtype = '';

    /**
     * The constructor - calls parent constructor
     *
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->subtype = $subtype;
        $url = new moodle_url('/mod/surveypro/adminmanageplugins.php', array('subtype' => $subtype));
        parent::__construct('manage'.$subtype.'plugins',
                            get_string('manage'.$subtype.'plugins', 'mod_surveypro'),
                            $url);
    }

    /**
     * Search plugins for the specified string
     *
     * @param string $query String to search for
     * @return array
     */
    public function search($query) {
        if ($result = parent::search($query)) {
            return $result;
        }

        $found = false;

        foreach (core_component::get_plugin_list($this->subtype) as $name => $unused) {
            if (strpos(core_text::strtolower(get_string('pluginname', $this->subtype.'_'.$name)),
                    $query) !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new stdClass();
            $result->page = $this;
            $result->settings = array();
            return array($this->name => $result);
        } else {
            return array();
        }
    }
}

/**
 * Class that handles the display and configuration of the list of submission plugins.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_plugin_manager {

    /**
     * @var object Url of the manage submission plugin page
     */
    private $pageurl;

    /**
     * @var string Any error from the current action
     */
    private $error = '';

    /**
     * @var string Either submission or feedback
     */
    private $subtype = '';

    /**
     * Constructor for this surveypro plugin manager
     *
     * @param string $subtype Either surveyprofield, surveyproformat, surveyprotemplate or surveyproreport
     */
    public function __construct($subtype) {
        $this->pageurl = new moodle_url('/mod/surveypro/adminmanageplugins.php', array('subtype' => $subtype));
        $this->subtype = $subtype;
    }

    /**
     * Return a list of plugins sorted by the order defined in the admin interface
     *
     * @return array The list of plugins
     */
    public function get_sorted_plugins_list() {
        $names = core_component::get_plugin_list($this->subtype);

        $result = array();

        foreach ($names as $name => $unused) {
            $idx = get_config($this->subtype.'_'.$name, 'sortorder');
            if (!$idx) {
                $idx = 0;
            }
            while (array_key_exists($idx, $result)) {
                $idx += 1;
            }
            $result[$idx] = $name;
        }
        ksort($result);

        return $result;
    }

    /**
     * Write the HTML for the submission plugins table.
     *
     * @return void
     */
    private function view_plugins_table() {
        global $OUTPUT, $CFG, $DB;

        require_once($CFG->libdir.'/tablelib.php');
        require_once($CFG->dirroot.'/mod/surveypro/lib.php');

        // Set up the table.
        $this->view_header();
        $table = new flexible_table($this->subtype.'pluginsadminttable');
        $table->define_baseurl($this->pageurl);

        $tablecolumns = array();
        $tablecolumns[] = 'pluginname';
        $tablecolumns[] = 'version';
        $tablecolumns[] = 'numinstances';
        $tablecolumns[] = 'hideshow';
        $tablecolumns[] = 'delete';
        $tablecolumns[] = 'settings';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string($this->subtype.'pluginname', 'mod_surveypro');
        $tableheaders[] = get_string('version');
        $tableheaders[] = get_string('numinstances', 'mod_surveypro');
        $tableheaders[] = get_string('hideshow', 'mod_surveypro');
        $tableheaders[] = get_string('delete');
        $tableheaders[] = get_string('settings');
        $table->define_headers($tableheaders);

        $table->set_attribute('id', $this->subtype.'plugins');
        $table->set_attribute('class', 'generaltable generalbox boxaligncenter boxwidthwide');
        $table->setup();

        $plugins = $this->get_sorted_plugins_list();
        $shortsubtype = substr($this->subtype, strlen('surveypro'));

        if (($this->subtype == 'surveyprofield') || ($this->subtype == 'surveyproformat')) {
            if ($this->subtype == 'surveyprofield') {
                $type = SURVEYPRO_TYPEFIELD;
            }
            if ($this->subtype == 'surveyproformat') {
                $type = SURVEYPRO_TYPEFORMAT;
            }
            $countsql = 'SELECT plugin, COUNT(1) as numinstances
                FROM {surveypro_item}
                WHERE type = :type
                GROUP BY plugin';
            $whereparams = array('type' => $type);
            $counts = $DB->get_records_sql($countsql, $whereparams);
        }
        if (($this->subtype == 'surveyprotemplate')) {
            $countsql = 'SELECT template, COUNT(1) as numinstances
                FROM {surveypro}
                WHERE template IS NOT NULL
                GROUP BY template';
            $counts = $DB->get_records_sql($countsql);
        }

        foreach ($plugins as $plugin) {
            $row = array();

            // Pluginname.
            $icon = $OUTPUT->pix_icon('icon', $plugin, $this->subtype.'_'.$plugin,
                array('title' => $plugin, 'class' => 'icon'));

            $row[] = $icon.get_string('pluginname', $this->subtype.'_'.$plugin);

            // Version.
            $row[] = get_config($this->subtype.'_'.$plugin, 'version');

            // Number of instances.
            if (isset($counts[$plugin])) {
                $row[] = $counts[$plugin]->numinstances;
            } else {
                $row[] = 0;
            }

            // Enable/disable.
            $visible = !get_config($this->subtype.'_'.$plugin, 'disabled');
            if ($visible) {
                $title = get_string('disable');
                $row[] = $OUTPUT->action_icon(new moodle_url($this->pageurl,
                    array('action' => 'hidden', 'plugin' => $plugin, 'sesskey' => sesskey())),
                    new pix_icon('t/hide', $title, 'moodle', array('title' => $title)),
                    null, array('title' => $title));
            } else {
                $title = get_string('enable');
                $row[] = $OUTPUT->action_icon(new moodle_url($this->pageurl,
                    array('action' => 'show', 'plugin' => $plugin, 'sesskey' => sesskey())),
                    new pix_icon('t/show', $title, 'moodle', array('title' => $title)),
                    null, array('title' => $title));
            }

            // Delete.
            if (isset($counts[$plugin])) {
                $row[] = '&nbsp;';
            } else {
                $title = get_string('delete');
                $row[] = $OUTPUT->action_icon(new moodle_url($this->pageurl,
                    array('action' => 'delete', 'plugin' => $plugin, 'sesskey' => sesskey())),
                    new pix_icon('t/delete', $title, 'moodle', array('title' => $title)),
                    null, array('title' => $title));

            }
            $exists = file_exists($CFG->dirroot.'/mod/surveypro/'.$shortsubtype.'/'.$plugin.'/settings.php');
            if ($row[1] != '' && $exists) {
                $row[] = html_writer::link(new moodle_url('/admin/settings.php',
                        array('section' => $this->subtype.'_'.$plugin)), get_string('settings'));
            } else {
                $row[] = '&nbsp;';
            }

            $table->add_data($row);
        }

        $table->finish_output();
        $this->view_footer();
    }

    /**
     * Write the page header
     *
     * @return void
     */
    private function view_header() {
        global $OUTPUT;

        admin_externalpage_setup('manage'.$this->subtype.'plugins');

        // Print the page heading.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('manage'.$this->subtype.'plugins', 'mod_surveypro'));
    }

    /**
     * Write the page footer
     *
     * @return void
     */
    private function view_footer() {
        global $OUTPUT;

        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the list of installed plugins
     *
     * @return void
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * Delete the database and files associated with this plugin.
     *
     * @param string $plugin Type of the plugin to delete
     * @return string the name of the next page to display
     */
    public function delete_plugin($plugin) {
        global $CFG, $OUTPUT;

        $confirm = optional_param('confirm', null, PARAM_BOOL);

        if ($confirm) {
            // Delete any configuration records.
            if (!unset_all_config_for_plugin($this->subtype.'_'.$plugin)) {
                $this->error = $OUTPUT->notification(get_string('errordeletingconfig', 'admin', $this->subtype.'_'.$plugin), 'notifyproblem');
            }

            // Should be covered by the previous function - but just in case.
            unset_config('disabled', $this->subtype.'_'.$plugin);
            unset_config('sortorder', $this->subtype.'_'.$plugin);

            // Delete the plugin specific config settings.
            // $DB->delete_records('surveypro_item', array('plugin' => $plugin, 'subtype' => $this->subtype));

            // Then the tables themselves.
            $shortsubtype = substr($this->subtype, strlen('surveypro'));
            $installxml = $CFG->dirroot.'/mod/surveypro/'.$shortsubtype.'/'.$plugin.'/db/install.xml';
            drop_plugin_tables($this->subtype.'_'.$plugin,
                               $installxml,
                               false);

            // Remove event handlers and dequeue pending events.
            events_uninstall($this->subtype.'_'.$plugin);

            // The page to display.
            return 'plugindeleted';
        } else {
            // The page to display.
            return 'confirmdelete';
        }

    }

    /**
     * Show the page that gives the details of the plugin that was just deleted.
     *
     * @param string $plugin Plugin that was just deleted
     * @return void
     */
    private function view_plugin_deleted($plugin) {
        global $OUTPUT;

        $this->view_header();
        $pluginname = get_string('pluginname', $this->subtype.'_'.$plugin);
        echo $OUTPUT->heading(get_string('deletingplugin', 'mod_surveypro', $pluginname));
        echo $this->error;
        $shortsubtype = substr($this->subtype, strlen('surveypro'));
        $messageparams = array('name' => $pluginname,
                               'directory' => ('/mod/surveypro/'.$shortsubtype.'/'.$plugin));
        echo $OUTPUT->notification(get_string('plugindeletefiles', 'moodle', $messageparams), 'notifymessage');
        echo $OUTPUT->continue_button($this->pageurl);
        $this->view_footer();
    }

    /**
     * Show the page that asks the user to confirm they want to delete a plugin.
     *
     * @param string $plugin Plugin that will be deleted
     * @return void
     */
    private function view_confirm_delete($plugin) {
        global $OUTPUT;

        $this->view_header();
        $pluginname = get_string('pluginname', $this->subtype.'_'.$plugin);
        echo $OUTPUT->heading(get_string('deletingplugin', 'mod_surveypro', $pluginname));
        $urlparams = array('action' => 'delete', 'plugin' => $plugin, 'confirm' => 1);
        $confirmurl = new moodle_url($this->pageurl, $urlparams);
        echo $OUTPUT->confirm(get_string('deletepluginmessage', 'mod_surveypro', $pluginname),
                $confirmurl,
                $this->pageurl);
        $this->view_footer();
    }

    /**
     * Hide this plugin.
     *
     * @param string $plugin Plugin to hide
     * @return string The next page to display
     */
    public function hide_plugin($plugin) {
        set_config('disabled', 1, $this->subtype.'_'.$plugin);
        return 'view';
    }

    /**
     * Show this plugin.
     *
     * @param string $plugin Plugin to show
     * @return string The next page to display
     */
    public function show_plugin($plugin) {
        set_config('disabled', 0, $this->subtype.'_'.$plugin);
        return 'view';
    }

    /**
     * This is the entry point for this controller class.
     *
     * @param string $action Action to perform
     * @param string $plugin Optional name of a plugin type to perform the action on
     * @return void
     */
    public function execute($action, $plugin) {
        if ($action == null) {
            $action = 'view';
        }

        $this->check_permissions();

        // Process.
        if ($action == 'delete' && $plugin != null) {
            $action = $this->delete_plugin($plugin);
        } else if ($action == 'hidden' && $plugin != null) {
            $action = $this->hide_plugin($plugin);
        } else if ($action == 'show' && $plugin != null) {
            $action = $this->show_plugin($plugin);
        }

        // View.
        if ($action == 'confirmdelete' && $plugin != null) {
            $this->view_confirm_delete($plugin);
        } else if ($action == 'plugindeleted' && $plugin != null) {
            $this->view_plugin_deleted($plugin);
        } else if ($action == 'view') {
            $this->view_plugins_table();
        }
    }

    /**
     * This function adds plugin pages to the navigation menu.
     *
     * @param string $subtype Type of plugin (submission or feedback)
     * @param part_of_admin_tree $admin Handle to the admin menu
     * @param admin_settingpage $settings Handle to current node in the navigation tree
     * @param stdClass|plugininfo_mod $module Handle to the current module
     * @return void
     */
    public static function add_admin_surveypro_plugin_settings($subtype,
                                                            part_of_admin_tree $admin,
                                                            admin_settingpage $settings,
                                                            $module) {
        global $CFG;

        $plugins = core_component::get_plugin_list_with_file($subtype, 'settings.php', false);
        $pluginsbyname = array();
        foreach ($plugins as $plugin => $unused) {
            $pluginname = get_string('pluginname', $subtype.'_'.$plugin);
            $pluginsbyname[$pluginname] = $plugin;
        }
        ksort($pluginsbyname);

        foreach ($pluginsbyname as $pluginname => $plugin) {
            $settings = new admin_settingpage($subtype.'_'.$plugin,
                                              $pluginname,
                                              'moodle/site:config',
                                              $module->is_enabled() === false);
            if ($admin->fulltree) {
                $shortsubtype = substr($subtype, strlen('surveypro'));
                include($CFG->dirroot."/mod/surveypro/$shortsubtype/$plugin/settings.php");
            }

            $admin->add($subtype.'plugins', $settings);
        }
    }
}
