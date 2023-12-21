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
 * File browsing support.
 *
 * @package    mod_surveypro
 * @copyright  2013 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// namespace mod_surveypro;

/**
 * Class representing the virtual node with all itemids in the file browser
 *
 * @category  files
 * @copyright 2012 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_file_info extends file_info {
    /**
     * @var stdClass course
     */
    protected $course;

    /**
     * @var stdClass cm
     */
    protected $cm;

    /**
     * @var string component
     */
    protected $component;

    /**
     * @var array areas
     */
    protected $areas;

    /**
     * @var string filearea
     */
    protected $filearea;

    /**
     * Constructor (in case you did not realize it ;-)
     *
     * @param file_browser $browser
     * @param stdClass $course
     * @param stdClass $cm
     * @param stdClass $context
     * @param array $areas
     * @param string $filearea
     */
    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->course = $course;
        $this->cm = $cm;
        $this->areas = $areas;
        $this->filearea = $filearea;
    }

    /**
     * Method to get each class param
     *
     * @return array with keys contextid, filearea, itemid, filepath and filename
     */
    public function get_params() {
        return [
            'contextid' => $this->context->id,
            'component' => 'mod_surveypro',
            'filearea' => $this->filearea,
            'itemid' => null,
            'filepath' => null,
            'filename' => null,
        ];
    }

    /**
     * Can new files or directories be added via the file browser
     *
     * @return bool
     */
    public function is_writable() {
        return false;
    }

    /**
     * Should this node be considered as a folder in the file browser
     *
     * @return bool
     */
    public function is_directory() {
        return true;
    }

    /**
     * Returns localised visible name of this node
     *
     * @return string
     */
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    /**
     * Returns list of children nodes
     *
     * @return array of file_info instances
     */
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    /**
     * Help function to return files matching extensions or their count
     *
     * @param string|array $extensions either '*' or array of lowercase extensions, i.e. array('.gif','.jpg')
     * @param bool|int $countonly if false returns the children, if an int returns just the
     *    count of children but stops counting when $countonly number of children is reached
     * @param bool $returnemptyfolders if true returns items that don't have matching files inside
     * @return array|int array of file_info instances or the count
     */
    private function get_filtered_children($extensions='*', $countonly=false, $returnemptyfolders=false) {
        global $DB;

        $params = [
            'contextid' => $this->context->id,
            'component' => 'mod_surveypro',
            'filearea' => $this->filearea,
        ];
        $sql = 'SELECT DISTINCT itemid
                    FROM {files}
                    WHERE contextid = :contextid
                    AND component = :component
                    AND filearea = :filearea';

        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }

        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);

        if ($countonly === false) {
            $sql .= ' ORDER BY itemid DESC';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = [];
        foreach ($rs as $record) {
            if ($child = $this->browser->get_file_info($this->context, 'mod_surveypro', $this->filearea, $record->itemid)) {
                $children[] = $child;
            }
            if ($countonly !== false && count($children) >= $countonly) {
                break;
            }
        }
        $rs->close();
        if ($countonly !== false) {
            return count($children);
        }

        return $children;
    }

    /**
     * Returns list of children which are either files matching the specified extensions
     * or folders that contain at least one such file.
     *
     * @param string|array $extensions either '*' or array of lowercase extensions, i.e. array('.gif','.jpg')
     * @return array of file_info instances
     */
    public function get_non_empty_children($extensions='*') {
        return $this->get_filtered_children($extensions, false);
    }

    /**
     * Returns the number of children which are either files matching the specified extensions
     * or folders containing at least one such file.
     *
     * @param string|array $extensions for example '*' or array('.gif','.jpg')
     * @param int $limit stop counting after at least $limit non-empty children are found
     * @return int
     */
    public function count_non_empty_children($extensions='*', $limit=1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    /**
     * Returns parent file_info instance
     *
     * @return file_info or null for root
     */
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }
}
