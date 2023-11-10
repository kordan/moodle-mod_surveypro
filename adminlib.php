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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/adminlib.php');

/**
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_admin_page_manage_surveypro_plugins extends admin_externalpage {

    /**
     * @var string Name of plugin subtype.
     */
    private $subtype = '';

    /**
     * The constructor - calls parent constructor.
     *
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->subtype = $subtype;
        $url = new \moodle_url('/mod/surveypro/adminmanageplugins.php', ['subtype' => $subtype]);
        parent::__construct('manage'.$subtype.'plugins',
                            get_string('manage'.$subtype.'plugins', 'mod_surveypro'),
                            $url);
    }

    /**
     * Search plugins for the specified string.
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
            if (strpos(strtolower(get_string('pluginname', $this->subtype.'_'.$name)), $query) !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new \stdClass();
            $result->page = $this;
            $result->settings = [];
            return [$this->name => $result];
        } else {
            return [];
        }
    }
}

/**
 * Class that handles the display and configuration of the list of submission plugins.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
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
     * Constructor for this surveypro plugin manager.
     *
     * @param string $subtype Either surveyprofield, surveyproformat, surveyprotemplate or surveyproreport
     */
    public function __construct($subtype) {
        $this->pageurl = new \moodle_url('/mod/surveypro/adminmanageplugins.php', ['subtype' => $subtype]);
        $this->subtype = $subtype;
    }

    /**
     * Return a list of plugins sorted by the order defined in the admin interface.
     *
     * @return array The list of plugins
     */
    public function get_sorted_plugins_list() {
        $names = core_component::get_plugin_list($this->subtype);

        $result = [];

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
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $plugin URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     */
    private function format_icon_link($action, $plugin, $icon, $alt) {
        global $OUTPUT;

        $url = $this->pageurl;

        if ($action === 'delete') {
            $url = core_plugin_manager::instance()->get_uninstall_url($this->subtype.'_'.$plugin, 'manage');
            if (!$url) {
                return '&nbsp;';
            }
            return html_writer::link($url, get_string('uninstallplugin', 'core_admin'));
        }

        return $OUTPUT->action_icon(new moodle_url($url,
                ['action' => $action, 'plugin' => $plugin, 'sesskey' => sesskey()]),
                new pix_icon($icon, $alt, 'moodle', ['title' => $alt]),
                null, ['title' => $alt]) . ' ';
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
        $table = new \flexible_table($this->subtype.'pluginsadminttable');
        $table->define_baseurl($this->pageurl);

        $tablecolumns = [];
        $tablecolumns[] = 'pluginname';
        $tablecolumns[] = 'version';
        $tablecolumns[] = 'numinstances';
        $tablecolumns[] = 'hideshow';
        $tablecolumns[] = 'delete';
        $tablecolumns[] = 'settings';
        $table->define_columns($tablecolumns);

        $tableheaders = [];
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
        $shortsubtype = core_text::substr($this->subtype, core_text::strlen('surveypro'));

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
            $whereparams = ['type' => $type];
            $counts = $DB->get_records_sql($countsql, $whereparams);
        }
        if ($this->subtype == 'surveyprotemplate') {
            $countsql = 'SELECT template, COUNT(1) as numinstances
                FROM {surveypro}
                WHERE template IS NOT NULL
                GROUP BY template';
            $counts = $DB->get_records_sql($countsql);
        }

        foreach ($plugins as $plugin) {
            $row = [];
            $class = '';

            // Pluginname.
            $icon = $OUTPUT->pix_icon('icon', $plugin, $this->subtype.'_'.$plugin, ['title' => $plugin, 'class' => 'icon']);

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
                $row[] = $this->format_icon_link('hide', $plugin, 't/hide', get_string('disable'));
            } else {
                $row[] = $this->format_icon_link('show', $plugin, 't/show', get_string('enable'));
                $class = 'dimmed_text';
            }

            // Delete.
            if (isset($counts[$plugin])) {
                $row[] = '&nbsp;';
            } else {
                $row[] = $this->format_icon_link('delete', $plugin, 't/delete', get_string('delete'));
            }

            $exists = file_exists($CFG->dirroot.'/mod/surveypro/'.$shortsubtype.'/'.$plugin.'/settings.php');
            if ($row[1] != '' && $exists) {
                $row[] = \html_writer::link(new \moodle_url('/admin/settings.php',
                        ['section' => $this->subtype.'_'.$plugin]), get_string('settings'));
            } else {
                $row[] = '&nbsp;';
            }

            $table->add_data($row, $class);
        }

        $table->finish_output();
        $this->view_footer();
    }

    /**
     * Write the page header.
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
     * Write the page footer.
     *
     * @return void
     */
    private function view_footer() {
        global $OUTPUT;

        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the list of installed plugins.
     *
     * @return void
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = \context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
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
        $shortsubtype = core_text::substr($this->subtype, core_text::strlen('surveypro'));
        $messageparams = ['name' => $pluginname, 'directory' => ('/mod/surveypro/'.$shortsubtype.'/'.$plugin)];
        echo $OUTPUT->notification(get_string('plugindeletefiles', 'moodle', $messageparams), 'notifymessage');
        echo $OUTPUT->continue_button($this->pageurl);
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
        if ($action == 'hide' && $plugin != null) {
            $action = $this->hide_plugin($plugin);
        } else if ($action == 'show' && $plugin != null) {
            $action = $this->show_plugin($plugin);
        }

        // View.
        if ($action == 'view') {
            $this->view_plugins_table();
        }
    }
}
