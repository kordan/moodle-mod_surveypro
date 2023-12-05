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

use mod_surveypro\local\importer\preset_existing_importer;
use mod_surveypro\manager;
use mod_surveypro\output\view_action_bar;

defined('MOODLE_INTERNAL') || die();

/**
 * Surveypro report attachments renderer.
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_attachments_renderer extends plugin_renderer_base {

    /**
     * Renders the action bar for the view page.
     *
     * @param \surveyproreport_attachments\output\view_report_paragraph $reportparagraph
     * @return null
     */
    public function render_report_paragraph(\surveyproreport_attachments\output\view_report_paragraph $reportparagraph): string {
        $data = $reportparagraph->export_for_template($this);

        return $this->render_from_template('surveyproreport_attachments/report_details', $data);
    }
}
