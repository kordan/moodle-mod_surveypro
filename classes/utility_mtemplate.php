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
 * Surveypro utility_submission class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

/**
 * The utility class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility_mtemplate
{
    /**
     * @var \stdClass Course module object
     */
    protected $cm;

    /**
     * @var \stdClass Context object
     */
    protected $context;

    /**
     * @var \stdClass Surveypro object
     */
    protected $surveypro;

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $surveypro
     */
    public function __construct($cm, $surveypro) {
        global $DB;

        $this->cm = $cm;
        $this->context = \context_module::instance($cm->id);
        $this->surveypro = $surveypro;
    }

    /**
     * Define the default section for master templates
     *
     * @return string section
     */
    public function surveypro_get_defaults_section(): ?string {
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context);
        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context);

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $hassubmissions = $utilitylayoutman->has_submissions();

        $section = null;

        // Those conditions must reflect the ones in draw_mtemplates_action_bar in surveypro/classes/output/action_bar.php
        // If they are different, this is an error.
        $condition = ($canapplymastertemplates && (!$hassubmissions)); // Apply.
        if ($condition) {
            $section = 'apply';
        }

        $condition = ($cansavemastertemplates && empty($this->surveypro->template)); // Save.
        if ($condition) {
            $section = 'save';
        }

        return $section;
    }
}
