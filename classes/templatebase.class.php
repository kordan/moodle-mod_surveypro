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

require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');
/**
 * The base class representing a field
 */
class mod_surveypro_templatebase {
    /**
     * Basic necessary essential ingredients
     */
    protected $cm;
    protected $context;
    protected $surveypro;

    /**
     * $templatename
     */
    protected $templatename;

    /**
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /**
     * $langtree
     */
    protected $langtree = array();

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
    }

    /**
     * items_deletion
     *
     * @param records $pluginseeds
     * @param records $parambase
     * @return null
     */
    public function items_deletion($pluginseeds, $parambase) {
        global $DB;

        $dbman = $DB->get_manager();

        $pluginparams = $parambase;
        foreach ($pluginseeds as $pluginseed) {
            $tablename = 'surveypro'.$pluginseed->type.'_'.$pluginseed->plugin;
            if ($dbman->table_exists($tablename)) {
                $pluginparams['plugin'] = $pluginseed->plugin;

                if ($deletelist = $DB->get_records('surveypro_item', $pluginparams, 'id', 'id')) {
                    $deletelist = array_keys($deletelist);

                    $select = 'itemid IN ('.implode(',', $deletelist).')';
                    $DB->delete_records_select($tablename, $select);
                }
            }
        }
        $DB->delete_records('surveypro_item', $parambase);
    }

    /**
     * items_reindex
     *
     * @return null
     */
    public function items_reindex() {
        global $DB;

        // Renum sortindex.
        $sql = 'SELECT id, sortindex
                FROM {surveypro_item}
                WHERE surveyproid = :surveyproid
                ORDER BY sortindex ASC';
        $whereparams = array('surveyproid' => $this->surveypro->id);
        $itemlist = $DB->get_recordset_sql($sql, $whereparams);
        $currentsortindex = 1;
        foreach ($itemlist as $item) {
            if ($item->sortindex != $currentsortindex) {
                $DB->set_field('surveypro_item', 'sortindex', $currentsortindex, array('id' => $item->id));
            }
            $currentsortindex++;
        }
        $itemlist->close();
    }

    /**
     * validate_xml
     *
     * @param $xml
     * @return object|boolean error describing the message to show, false if no error is found
     */
    public function validate_xml($xml) {
        global $CFG;

        $debug = false;
        // $debug = true; //if you want to stop anyway to see where the xml template is buggy

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

            if (($versiondisk["$currentplugin"] != $currentversion)) {
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
                    // I could use a random class here because they all share the same parent item_get_item_schema.
                    // But, I need the right class name for the next table, so I start loading the correct class now.
                    require_once($CFG->dirroot.'/mod/surveypro/'.$currenttype.'/'.$currentplugin.'/classes/plugin.class.php');
                    $itemclassname = 'mod_surveypro_'.$currenttype.'_'.$currentplugin;
                    $xsd = $itemclassname::item_get_item_schema(); // <- itembase schema
                } else {
                    // $classname is already onboard because of the previous loop over surveypro_item fields
                    if (!isset($itemclassname)) {
                        $error = new stdClass();
                        $error->key = 'badtablenamefound';
                        $error->a = $tablename;

                        return $error;
                    }
                    $xsd = $itemclassname::item_get_plugin_schema(); // <- plugin schema
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

                // Clear XML error flag so that we don't incorrectly report failure.
                // When a previous xml parse failed.
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
     * get_table_structure
     *
     * @param $tablename
     * @param $dropid
     * @return
     */
    public function get_table_structure($tablename, $dropid=true) {
        global $DB;

        $dbman = $DB->get_manager();

        if ($dbman->table_exists($tablename)) {
            $dbstructure = array();

            if ($dbfields = $DB->get_columns($tablename)) {
                foreach ($dbfields as $dbfield) {
                    $dbstructure[] = $dbfield->name;
                }
            }

            if ($dropid) {
                array_shift($dbstructure); // ID is always the first item.
            }

            return $dbstructure;
        } else {
            return false;
        }
    }

    /**
     * get_plugin_versiondisk
     *
     * @param none
     * @return versions of each field|format item plugin
     */
    public function get_plugin_versiondisk() {
        // Get plugins versiondisk.
        $pluginman = core_plugin_manager::instance();
        $subplugins = $pluginman->get_subplugins_of_plugin('surveypro');
        $versions = array();
        foreach ($subplugins as $component => $plugin) {
            if (($plugin->type != 'surveypro'.SURVEYPRO_TYPEFIELD) &&
                ($plugin->type != 'surveypro'.SURVEYPRO_TYPEFORMAT)) {
                continue;
            }
            $versions["$plugin->name"] = $plugin->versiondisk;
        }

        return $versions;
    }
}
