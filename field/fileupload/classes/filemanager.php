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

global $CFG;

require_once($CFG->libdir.'/form/filemanager.php');

class mod_surveypro_mform_filemanager extends MoodleQuickForm_filemanager {


    /**
     * All types must have this constructor implemented.
     */
    public function mod_surveypro_mform_filemanager($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
       parent::MoodleQuickForm_filemanager($elementName, $elementLabel, $attributes, $options);
    }

    /**
     * Returns type of editor element
     *
     * @return string
     */
    public function getElementTemplateType() {
        return 'default';
    }

    /**
     * What to display when element is frozen.
     *
     * @return empty string
     */
    public function getFrozenHtml() {
        global $CFG, $PAGE;

        require_once("$CFG->dirroot/repository/lib.php");

        $id = $this->_attributes['id'];
        $elname = $this->_attributes['name'];
        $subdirs = $this->_options['subdirs'];
        $maxbytes = $this->_options['maxbytes'];
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
        $options->mainfile = $this->_options['mainfile'];
        $options->maxbytes = $this->_options['maxbytes'];
        $options->maxfiles = $this->getMaxfiles();
        $options->client_id = $client_id;
        $options->itemid = $draftitemid;
        $options->subdirs = $this->_options['subdirs'];
        $options->target = $id;
        $options->accepted_types = $accepted_types;
        $options->return_types = $this->_options['return_types'];
        $options->context = $PAGE->context;
        $options->areamaxbytes = $this->_options['areamaxbytes'];

        $fm = new form_filemanager($options);

        $return = '';
        $attachmentcount = count($fm->options->list);
        $attachmentcount -= 1;
        foreach ($fm->options->list as $k => $list) {
            // $return .= '<a href="'.$list->url.'"><img src="'.$list->thumbnail.'" /></a>';
            $return .= html_writer::start_tag('a', array('title' => s($list->filename), 'href' => $list->url));
            $return .= html_writer::empty_tag('img', array('src' => $list->thumbnail));
            $return .= html_writer::end_tag('a');

            // $return .= '<a href="'.$list->url.'">'.s($list->filename).'</a><br />';
            $return .= html_writer::start_tag('a', array('title' => s($list->filename), 'href' => $list->url));
            $return .= s($list->filename);
            $return .= html_writer::end_tag('a');

            if ($k < $attachmentcount) {
                $return .= html_writer::empty_tag('br');
            }
        }

        return $return;
    }
}
