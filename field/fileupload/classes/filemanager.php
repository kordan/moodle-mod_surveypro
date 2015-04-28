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

require_once($CFG->libdir.'/form/filemanager.php');

class mod_surveypro_mform_filemanager extends MoodleQuickForm_filemanager {

    /**
     * Returns type of editor element
     *
     * @return string
     */
    function getElementTemplateType() {
        if ($this->_flagFrozen){
            return 'nodisplay';
        } else {
            return 'default';
        }
    }

    /**
     * What to display when element is frozen.
     *
     * @return empty string
     */
    function getFrozenHtml() {
        global $CFG, $PAGE;

        require_once("$CFG->dirroot/repository/lib.php");

        $id          = $this->_attributes['id'];
        $elname      = $this->_attributes['name'];
        $subdirs     = $this->_options['subdirs'];
        $maxbytes    = $this->_options['maxbytes'];
        $draftitemid = $this->getValue();
        $accepted_types = $this->_options['accepted_types'];

        if (empty($draftitemid)) {
            // no existing area info provided - let's use fresh new draft area
            require_once("$CFG->libdir/filelib.php");
            $this->setValue(file_get_unused_draft_itemid());
            $draftitemid = $this->getValue();
        }

        $client_id = uniqid();

        // filemanager options
        $options = new stdClass();
        $options->mainfile  = $this->_options['mainfile'];
        $options->maxbytes  = $this->_options['maxbytes'];
        $options->maxfiles  = $this->getMaxfiles();
        $options->client_id = $client_id;
        $options->itemid    = $draftitemid;
        $options->subdirs   = $this->_options['subdirs'];
        $options->target    = $id;
        $options->accepted_types = $accepted_types;
        $options->return_types = $this->_options['return_types'];
        $options->context = $PAGE->context;
        $options->areamaxbytes = $this->_options['areamaxbytes'];

        $fm = new form_filemanager($options);

        $output = '';
        foreach ($fm->options->list as $list) {
            $output .= '<a href="'.$list->url.'"><img src="'.$list->thumbnail.'" /></a>';
            $output .= '<a href="'.$list->url.'">'.s($list->filename).'</a><br />';
        }
        $output = substr($output, 0, -6); // cut down last <br />

        return 'Scrivo dal metodo getFrozenHtml() locale da ' . __FILE__ . '<br />' . $output;
    }
}
