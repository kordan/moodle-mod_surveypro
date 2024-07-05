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
 * Surveypro utemplate_save class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;
use mod_surveypro\utility_layout;

use mod_surveypro\local\ipe\usertemplate_name;

/**
 * The class representing a user template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utemplate_save extends utemplate_base {

    /**
     * Setup.
     *
     * @param int $utemplateid
     * @return void
     */
    public function setup($utemplateid) {
        $this->set_utemplateid($utemplateid);
    }

    // MARK set.

    /**
     * Set utemplateid.
     *
     * @param int $utemplateid
     * @return void
     */
    private function set_utemplateid($utemplateid) {
        $this->utemplateid = $utemplateid;
    }

    // MARK other.

    /**
     * Display the welcome message of the save page.
     *
     * @return void
     */
    public function welcome_save_message() {
        global $OUTPUT;

        $a = get_string('sharinglevel', 'mod_surveypro');
        $message = get_string('welcome_utemplatesave', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    /**
     * Write user template content.
     *
     * TAKE CARE
     * At "usertemplate creation" time, in order to recover the ID of the parent record to be assigned to the child record in a
     * possible parent-child relation, I write, in the parentid field of the child record in the XML, the sortindex of the parent
     * record and not the ID of the parent record. At "usertemplate apply" time, to get the ID of the parent record, I get the
     * sortindex written in the XML and I add to it the sortindexoffset (that is nothing more than the number of preexisting items
     * in the accepting surveypro) that I calculate at the beginning of the "usertemplate apply" process. Finally I get the ID of
     * the item that has sortindex equal to ("sortindex taken from the usertemplate" + sortindexoffset) with a query "SELECT the ID
     * of the item WHERE sortindex = ...".
     *
     * When the user creates a usertemplate with only visible items, this trick does not work because the parent's sortindex could
     * be 100 even if the parent is the first record (and, in my plan, is should have sortindex == 1).
     *
     * For this reason, at "usertemplate creation" time, I AM FORCED to use a "hot" calculated sortindex instead of using the one
     * taken from the db.
     *
     * @param boolean $visiblesonly
     * @return void
     */
    public function write_template_content($visiblesonly) {
        global $DB;

        $pluginversion = self::get_subplugin_versions();

        $where = ['surveyproid' => $this->surveypro->id];
        if ($visiblesonly) {
            $where['hidden'] = '0';
        }
        $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');

        $fs = get_file_storage();
        $context = \context_module::instance($this->cm->id);

        $xmltemplate = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_itemclass($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

            $xmlitem = $xmltemplate->addChild('item');
            $xmlitem->addAttribute('type', $itemseed->type);
            $xmlitem->addAttribute('plugin', $itemseed->plugin);
            $index = $itemseed->type.'_'.$itemseed->plugin;
            $xmlitem->addAttribute('version', $pluginversion[$index]);

            // Surveypro_item.
            $structure = $this->get_table_structure();
            $unrelevantfields = ['id', 'surveyproid', 'type', 'plugin', 'sortindex', 'formpage', 'timecreated', 'timemodified'];
            $unrelevantfields = array_merge($unrelevantfields, $item->item_expected_null_fields());
            $xmltable = $xmlitem->addChild('surveypro_item');
            foreach ($structure as $field) {
                if (in_array($field, $unrelevantfields)) {
                    continue;
                }

                if ($field == 'content') {
                    // If $field == 'content' I can not use the property of the object $item because
                    // in case of pictures, for instance, $item->content has to look like:
                    // '<img src="@@PLUGINFILE@@/img1.png" alt="MMM" width="313" height="70">'
                    // and not like:
                    // '<img src="http://localhost:8888/m401/pluginfile.php/198/mod_surveypro/itemcontent/1960/img1.png" alt="img1"...
                    $val = $DB->get_field('surveypro_item', 'content', ['id' => $itemseed->id], MUST_EXIST);
                    if (core_text::strlen($val)) {
                        $xmlfield = $xmltable->addChild('content', htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE));
                    }
                    if ($files = $fs->get_area_files($context->id, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $itemseed->id)) {
                        foreach ($files as $file) {
                            $filename = $file->get_filename();
                            if ($filename == '.') {
                                continue;
                            }
                            $xmlembedded = $xmltable->addChild('embedded');
                            $xmlembedded->addChild('filename', $filename);
                            $xmlembedded->addChild('filecontent', base64_encode($file->get_content()));
                        }
                    }

                    continue;
                }

                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        // Store the sortindex of the parent instead of its id, because at restore time parentid will change.
                        // Get $parentsortindex.
                        $whereparams = ['id' => $parentid];
                        $parentsortindex = $DB->get_field('surveypro_item', 'sortindex', $whereparams, MUST_EXIST);

                        if ($visiblesonly) {
                            $sql = 'SELECT COUNT(\'x\')
                                    FROM {surveypro_item}
                                    WHERE surveyproid = :surveyproid
                                      AND hidden = :hidden
                                      AND sortindex < :sortindex';
                            $whereparams = ['surveyproid' => $this->surveypro->id, 'hidden' => 1, 'sortindex' => $parentsortindex];
                            $hidedsortindex = $DB->count_records_sql($sql, $whereparams);
                            $parentsortindex -= $hidedsortindex;
                        }

                        // Get $parentsortindex.
                        $parentvalue = $item->get_parentvalue();

                        $xmlparent = $xmltable->addChild('parent');
                        $xmlfield = $xmlparent->addChild('parentid', $parentsortindex);
                        $xmlfield = $xmlparent->addChild('parentvalue', $parentvalue);
                    } // Otherwise: It is empty, do not evaluate: jump.
                    continue;
                }

                if ($field == 'parentvalue') {
                    continue;
                }

                $val = $item->get_generic_property($field);
                $val = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE);
                if (core_text::strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                } // Otherwise: It is empty, do not evaluate: jump.
            }

            // Child table.
            $tablename = 'surveypro'.$itemseed->type.'_'.$itemseed->plugin;
            $structure = $this->get_table_structure($itemseed->type, $itemseed->plugin);

            // Take care: some items plugin may be free of their own specific table.
            if (!count($structure)) {
                continue;
            }

            $unrelevantfields = ['id', 'itemid'];
            $xmltable = $xmlitem->addChild($tablename);
            foreach ($structure as $field) {

                if (in_array($field, $unrelevantfields)) {
                    continue;
                }

                $val = $item->get_generic_property($field);
                $val = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE);
                if (core_text::strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                } // Otherwise: It is empty, do not evaluate: jump.
            }
        }

        // In the coming code, "$option == false;" if 100% waste of time and should be changed to "$option == true;"
        // BUT BUT BUT...
        // the output in $dom->saveXML() is well written.
        // I prefer a more readable xml file instead of few nanoseconds saved.
        $option = false;
        if ($option) {
            return $xmltemplate->asXML();
        } else {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xmltemplate->asXML());

            return $dom->saveXML();
        }
    }

    /**
     * Generate the usertemplate.
     *
     * @return void
     */
    public function generate_utemplate() {
        global $USER;

        $this->templatename = $this->formdata->templatename;
        $this->templatename = str_replace(' ', '_', $this->templatename);
        if (!preg_match('~\.xml$~', $this->templatename)) {
            $this->templatename .= '.xml';
        }
        $xmlcontent = $this->write_template_content($this->formdata->visiblesonly);
        // Debug: echo '<textarea rows="80" cols="100">'.$xmlcontent.'</textarea>';.

        $fs = get_file_storage();
        $filerecord = new \stdClass;

        $contextid = $this->formdata->sharinglevel;
        $filerecord->contextid = $contextid;

        $filerecord->component = 'mod_surveypro';
        $filerecord->filearea = SURVEYPRO_TEMPLATEFILEAREA;
        $filerecord->itemid = 0;
        $filerecord->filepath = '/';
        $filerecord->userid = $USER->id;

        $filerecord->filename = str_replace(' ', '_', $this->templatename);
        if (!preg_match('~\.xml$~', $filerecord->filename)) {
            $filerecord->filename .= '.xml';
        }
        $fs->create_file_from_string($filerecord, $xmlcontent);

        return true;
    }
}
