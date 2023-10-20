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
 * filemanager mform element
 *
 * Extends the core mform class for filemanager element
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/form/filemanager.php');

// @codingStandardsIgnoreFile

/**
 * filemanager mform element
 *
 * Extends the core mform class for filemanager element
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveypromform_fileupload extends \MoodleQuickForm_filemanager {

    /**
     * Constructor.
     *
     * @param string $elementName Optional name of the filemanager
     * @param string $elementLabel Optional filemanager label
     * @param array $attributes Optional either a typical HTML attribute string
     *              or an associative array
     * @param array $options set of options to initalize filemanager
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        parent::__construct($elementName, $elementLabel, $attributes, $options);
        $this->_options['class'] = !isset($options['class']) ? 'indent-0' : $options['class'];
    }

    /**
     * Returns type of editor element.
     *
     * @return string
     */
    public function getElementTemplateType() {
        return 'default';
    }

    /**
     * Returns HTML for editor form element.
     *
     * I want to change at the beginning:
     *     <div id="filemanager-556eb09a98375" class="filemanager fm-loading">
     * to
     *     <div id="filemanager-556eb09a98375" class="indent-x filemanager fm-loading">
     *
     * @return string $output
     */
    public function toHtml() {
        $output = parent::toHtml(); // Core code.

        // I need to trim the code because mform library add a newline at the beginning.
        $output = trim($output);

        $tabs = $this->_getTabs();
        $pattern = '~^'.$tabs.'<div id="filemanager-([a-z0-9]*)" class="(.*)"~';
        $class = $this->_options['class'];
        $replacement = $tabs.'<div id="filemanager-${1}" class="'.$class.' ${2}"';
        $output = preg_replace($pattern, $replacement, $output);

        return $output;
    }

    /**
     * What to display when element is frozen.
     *
     * @return html of the frozen element
     */
    public function getFrozenHtml() {
        global $CFG, $PAGE;

        require_once("$CFG->dirroot/repository/lib.php");

        $id = $this->_attributes['id'];
        // $elname = $this->_attributes['name'];
        // $subdirs = $this->_options['subdirs'];
        // $maxbytes = $this->_options['maxbytes'];
        $draftitemid = $this->getValue();
        $accepted_types = $this->_options['accepted_types'];

        if (empty($draftitemid)) {
            // No existing area info provided - let's use fresh new draft area.
            require_once("$CFG->libdir/filelib.php");
            $this->setValue(file_get_unused_draft_itemid());
            $draftitemid = $this->getValue();
        }

        $client_id = uniqid();

        // Filemanager options.
        $options = new \stdClass();
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

        $class = $this->_options['class'];

        $fm = new form_filemanager($options);

        $return = '';
        $attachmentcount = count($fm->options->list);
        $attachmentcount -= 1;
        foreach ($fm->options->list as $list) {
            $return .= \html_writer::start_tag('div', ['class' => $class]);

            // $return .= '<a href="'.$list->url.'"><img src="'.$list->thumbnail.'" /></a>';
            $return .= \html_writer::start_tag('a', ['title' => s($list->filename), 'href' => $list->url]);
            $return .= \html_writer::empty_tag('img', ['src' => $list->thumbnail]);
            $return .= \html_writer::end_tag('a');

            // $return .= '<a href="'.$list->url.'">'.s($list->filename).'</a><br>';
            $return .= \html_writer::start_tag('a', ['title' => s($list->filename), 'href' => $list->url]);
            $return .= s($list->filename);
            $return .= \html_writer::end_tag('a');

            $return .= \html_writer::end_tag('div');
        }

        return $return;
    }
}
