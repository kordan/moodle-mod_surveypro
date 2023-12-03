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

namespace surveyproreport_attachments\output;

use templatable;
use renderable;

/**
 * Renderable class for the action bar elements in the view pages in the database activity.
 *
 * @package    surveyproreport_attachments
 * @copyright  2013 onwards kordan <stringapiccola@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_report_paragraph implements templatable, renderable {

    /** @var int $id The surveypro module id. */
    private $id;

    /** @var string $elementid The unique id for html element. */
    private $elementid;

    /** @var array $paragraphlabel The labels of the row of the paragraph. */
    private $paragraphlabel;

    /** @var array $paragraphcontent The content of the row of the paragraph. */
    private $paragraphcontent;

    /**
     * The class constructor.
     *
     * @param int $surveyproid The surveypro module id.
     * @param array $data The content of the paragraph.
     */
    public function __construct(int $surveyproid, array $data) {
        $this->id = $surveyproid;
        $this->elementid = $data['elementid'];
        $this->paragraphlabel = $data['paragraphlabel'];
        $this->paragraphcontent = $data['paragraphcontent'];
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output The renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $data = [
            'elementid' => format_text($this->elementid, FORMAT_HTML),
            'paragraphlabel' => format_text($this->paragraphlabel, FORMAT_HTML),
            'paragraphcontent' => format_text($this->paragraphcontent, FORMAT_HTML),
        ];

        return $data;
    }
}
