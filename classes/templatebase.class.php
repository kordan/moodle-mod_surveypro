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
 * Surveypro templatebase class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');
/**
 * The base class for templates
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_templatebase {

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
     * @var string Name of the template
     */
    protected $templatename;

    /**
     * @var object Form content as submitted by the user
     */
    public $formdata = null;

    /**
     * @var array, $langtree
     */
    protected $langtree = array();

    /**
     * Class constructor
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
     * Validate the uploaded xml file
     *
     * @param object $xml File to validate
     * @return object|boolean error describing the message to show, false if no error is found
     */
    public function validate_xml($xml) {
        global $CFG;

        $debug = false;
        // $debug = true; // if you want to stop anyway to see where the xml template is buggy.

        $versiondisk = $this->get_plugin_versiondisk();
        if ($CFG->debug == DEBUG_DEVELOPER) {
            $simplexml = new SimpleXMLElement($xml);
        } else {
            $simplexml = @new SimpleXMLElement($xml);
        }
        foreach ($simplexml->children() as $xmlitem) {
            foreach ($xmlitem->attributes() as $attribute => $value) {
                // <item type="format" plugin="label" version="2014030201">
                // echo 'Found: '.$attribute.' = '.$value.'<br />';
                if ($attribute == 'type') {
                    $currenttype = (string)$value;
                }
                if ($attribute == 'plugin') {
                    $currentplugin = (string)$value;
                }
                if ($attribute == 'version') {
                    $currentversion = (string)$value;
                }
            }
            if (!isset($currenttype)) {
                $error = new stdClass();
                $error->key = 'missingitemtype';

                return $error;
            }
            if (!isset($currentplugin)) {
                $error = new stdClass();
                $error->key = 'missingitemplugin';

                return $error;
            }
            if (!isset($currentversion)) {
                $error = new stdClass();
                $error->key = 'missingitemversion';

                return $error;
            }

            // Ok, $currenttype and $currentplugin are onboard.
            // Do they define correctly a class?
            if (!file_exists($CFG->dirroot.'/mod/surveypro/'.$currenttype.'/'.$currentplugin.'/version.php')) {
                $error = new stdClass();
                $error->key = 'invalidtypeorplugin';

                return $error;
            }

            if (($versiondisk["$currentplugin"] < $currentversion)) {
                $a = new stdClass();
                $a->type = $currenttype;
                $a->plugin = $currentplugin;
                $a->currentversion = $currentversion;
                $a->versiondisk = $versiondisk["$currentplugin"];

                $error = new stdClass();
                $error->a = $a;
                $error->key = 'versionmismatch';

                return $error;
            }

            foreach ($xmlitem->children() as $xmltable) {
                $tablename = $xmltable->getName();

                // I am assuming that surveypro_item table is ALWAYS before the surveypro_<<plugin>> table.
                if ($tablename == 'surveypro_item') {
                    // I could use a random class here because they all share the same parent item_get_item_schema
                    // but, I need the right class name for the next table, so I choose to load the correct class from the beginning.
                    require_once($CFG->dirroot.'/mod/surveypro/'.$currenttype.'/'.$currentplugin.'/classes/plugin.class.php');
                    $classname = 'mod_surveypro_'.$currenttype.'_'.$currentplugin;
                    $xsd = $classname::item_get_item_schema(); // <- itembase schema
                } else {
                    // $classname is already onboard because of the previous loop over surveypro_item fields
                    if (!isset($classname)) {
                        $error = new stdClass();
                        $error->key = 'badtablenamefound';
                        $error->a = $tablename;

                        return $error;
                    }
                    $xsd = $classname::item_get_plugin_schema(); // <- plugin schema
                }

                if (empty($xsd)) {
                    $error = new stdClass();
                    $error->key = 'xsdnotfound';

                    return $error;
                }

                $mdom = new DOMDocument();
                $status = $mdom->loadXML($xmltable->asXML());

                // Let's capture errors.
                $olderrormode = libxml_use_internal_errors(true);

                // Clear XML error flag so that we don't incorrectly report failure
                // when a previous xml parse failed.
                libxml_clear_errors();

                if ($debug) {
                    $status = $status && $mdom->schemaValidateSource($xsd);
                } else {
                    $status = $status && @$mdom->schemaValidateSource($xsd);
                }

                // Check for errors.
                $errors = libxml_get_errors();

                // Stop capturing errors.
                libxml_use_internal_errors($olderrormode);

                if (!empty($errors)) {
                    $firsterror = array_shift($errors);
                    $atemplate = get_string('reportederrortemplate', 'mod_surveypro');
                    // $atemplate = '%s as required by the xsd of the "%s" plugin'
                    $a = sprintf($atemplate, trim($firsterror->message, "\n\r\t ."), $currentplugin);

                    $error = new stdClass();
                    $error->a = $a;
                    $error->key = 'reportederror';

                    return $error;
                }

                if (!$status) {
                    // Stop here. It is useless to continue.
                    if ($debug) {
                        echo '<hr /><textarea rows="10" cols="100">'.$xmltable->asXML().'</textarea>';
                        echo '<textarea rows="10" cols="100">'.$xsd.'</textarea>';
                    }

                    $error = new stdClass();
                    $error->key = 'schemavalidationfailed';

                    return $error;
                }
            }
        }

        return false;
    }

    // MARK get

    /**
     * Get table structure
     *
     * @param string $tablename
     * @return void
     */
    public function get_table_structure($tablename) {
        global $DB;

        $dbman = $DB->get_manager();

        $table = new xmldb_table($tablename);
        if ($dbman->table_exists($table)) {
            $dbstructure = array_keys($DB->get_columns($tablename));
            return $dbstructure;
        } else {
            $message = 'Database table "'.$tablename.'" doesn\'t exist';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * Get plugin version on disk
     *
     * @return versions of each field|format item plugin
     */
    public function get_plugin_versiondisk() {
        // Get plugins versiondisk.
        $pluginman = core_plugin_manager::instance();
        $subplugins = $pluginman->get_subplugins_of_plugin('surveypro');
        $versions = array();
        foreach ($subplugins as $plugin) {
            if (($plugin->type != 'surveypro'.SURVEYPRO_TYPEFIELD) &&
                ($plugin->type != 'surveypro'.SURVEYPRO_TYPEFORMAT)) {
                continue;
            }
            $versions["$plugin->name"] = $plugin->versiondisk;
        }

        return $versions;
    }
}
