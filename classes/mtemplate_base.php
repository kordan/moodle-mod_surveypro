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
 * Surveypro utemplate_base class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

/**
 * The base class for templates
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mtemplate_base extends templatebase {

    // MARK other.

    /**
     * Trigger the provided event.
     *
     * @param string $eventname Event to trigger
     * @return void
     */
    public function trigger_event($eventname) {
        $eventdata = ['context' => $this->context, 'objectid' => $this->surveypro->id];
        $eventdata['other'] = ['templatename' => $this->formdata->mastertemplate];
        switch ($eventname) {
            case 'mastertemplate_applied':
                $event = \mod_surveypro\event\mastertemplate_applied::create($eventdata);
                break;
            case 'mastertemplate_saved': // Sometimes called 'downloaded' too.
                $event = \mod_surveypro\event\mastertemplate_saved::create($eventdata);
                break;
            default:
                $message = 'Unexpected $eventname = '.$eventname;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        $event->trigger();
    }
}
