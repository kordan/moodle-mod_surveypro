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
class utemplate_base extends templatebase {

    /**
     * @var int ID of the current user template
     */
    protected $utemplateid;

    // MARK get.

    /**
     * Provide a label explaining the meaning of the contexid
     *
     * @param int $contextid The contextid I am interested in
     * @return string
     */
    public function get_label_forcontextid($contextid) {
        switch ($contextid) {
            case CONTEXT_SYSTEM:
                $sharinglabel = get_string('system', 'mod_surveypro');
                break;
            case CONTEXT_COURSECAT:
                $sharinglabel = get_string('currentcategory', 'mod_surveypro');
                break;
            case CONTEXT_COURSE:
                $sharinglabel = get_string('currentcourse', 'mod_surveypro');
                break;
            case CONTEXT_MODULE:
                $a = get_string('modulename', 'mod_surveypro');
                $sharinglabel = get_string('module', 'mod_surveypro', $a);
                break;
            case CONTEXT_USER:
                $sharinglabel = get_string('user');
                break;
            default:
                $message = 'Unexpected $contextid = '.$contextid;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        return $sharinglabel;
    }

    /**
     * Get sharing level contexts.
     *
     * @return $options
     */
    public function get_sharingcontexts() {
        global $USER;

        $parentcontexts = $this->context->get_parent_contexts();

        $usercontext = \context_module::instance($this->cm->id);
        $usercontextid = $usercontext->id;
        $parentcontexts[$usercontextid] = $usercontext;

        $usercontext = \context_user::instance($USER->id);
        $usercontextid = $usercontext->id;
        $parentcontexts[$usercontextid] = $usercontext;

        return $parentcontexts;
    }

    /**
     * Get user template name.
     *
     * @return void
     */
    public function get_utemplate_name() {
        $fs = get_file_storage();
        $xmlfile = $fs->get_file_by_id($this->utemplateid);

        return $xmlfile->get_filename();
    }

    /**
     * Gets an array of all of the templates that users have saved to the site.
     *
     * Few explanation to better understand.
     * Asking for sharingcontexts I get the list of parentcontexts AND the usercontext.
     * Each single context has a "context level" (50 for courses, 40 for categories, 10 for system, 30 for user).
     * There are A LOT of contexts having "context level" == 50. One context per each course.
     * The context of the course where I am in has:
     * contextlevel = 50 (of course) AND id = another number, for instance, 79.
     * 79 is the ID of the context of the course I am in, but 79 is NOT the ID of the course I am in.

     * When I ask for usertemplates saved at course level, I want to get all the usertemplates of MY COURSE
     * and not all the usetemplates of EACH COURSE in this instance of moodle.
     * This is why I ask for $this->get_utemplates_per_contextlevel($context->id);
     * and NOT for $this->get_utemplates_per_contextlevel($context->contextlevel).

     * @param int $contextid Context that we are looking for
     * @return array $templates
     */
    public function get_utemplates_per_contextlevel($contextid) {
        global $USER;

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, 'sortorder', false);
        if (empty($files)) {
            return [];
        }

        $templates = [];
        foreach ($files as $file) {
            if ($file->get_component() == 'user') {
                $fileallowed = has_capability('mod/surveypro:applyusertemplates', $this->context);
                $fileallowed = $fileallowed || ($file->userid == $USER->id);
                if ($fileallowed) {
                    break;
                }
            } else {
                $fileallowed = true;
            }
            if ($fileallowed) {
                $templates[] = $file;
            }
        }

        return $templates;
    }

    /**
     * Get the list of each user template file.
     *
     * @param int $acontextid Context that we are looking for
     * @return array
     */
    public function get_xmlfiles_list($acontextid=null) {
        $utemplates = [];

        $contexts = $this->get_sharingcontexts();
        // I am allowed to "see" usertemplates if they belong to one of my parent contextid
        // or if their uid is $USER->id.
        foreach ($contexts as $context) {
            $contextid = $context->id;
            if (is_null($acontextid) || ($contextid == $acontextid)) {
                $xmlfiles = $this->get_utemplates_per_contextlevel($contextid);
                if (count($xmlfiles)) {
                    foreach ($xmlfiles as $xmlfile) {
                        $utemplates[$context->contextlevel][] = $xmlfile;
                    }
                }
            }
        }
        asort($utemplates);

        return $utemplates;
    }

    /**
     * Trigger the provided event.
     *
     * @param string $eventname Event to trigger
     * @param int $action
     * @return void
     */
    public function trigger_event($eventname, $action=null) {
        $eventdata = ['context' => $this->context, 'objectid' => $this->surveypro->id];
        switch ($eventname) {
            case 'all_usertemplates_viewed':
                $event = \mod_surveypro\event\all_usertemplates_viewed::create($eventdata);
                break;
            case 'usertemplate_applied':
                if ($action == SURVEYPRO_IGNOREITEMS) {
                    $straction = get_string('ignoreitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_HIDEALLITEMS) {
                    $straction = get_string('hideitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_DELETEALLITEMS) {
                    $straction = get_string('deleteallitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_DELETEVISIBLEITEMS) {
                    $straction = get_string('deletevisibleitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_DELETEHIDDENITEMS) {
                    $straction = get_string('deletehiddenitems', 'mod_surveypro');
                }
                $other = ['templatename' => $this->get_utemplate_name()];
                $other['action'] = $straction;
                $eventdata['other'] = $other;
                $event = \mod_surveypro\event\usertemplate_applied::create($eventdata);
                break;
            case 'usertemplate_exported':
                $eventdata['other'] = ['templatename' => $this->get_utemplate_name()];
                $event = \mod_surveypro\event\usertemplate_exported::create($eventdata);
                break;
            case 'usertemplate_imported':
                $eventdata['other'] = ['templatename' => $this->get_utemplate_name()];
                $event = \mod_surveypro\event\usertemplate_imported::create($eventdata);
                break;
            case 'usertemplate_saved':
                $eventdata['other'] = ['templatename' => $this->templatename];
                $event = \mod_surveypro\event\usertemplate_saved::create($eventdata);
                break;
            case 'usertemplate_deleted':
                $eventdata['other'] = ['templatename' => $this->templatename];
                $event = \mod_surveypro\event\usertemplate_deleted::create($eventdata);
                break;
            default:
                $message = 'Unexpected $event = '.$eventname;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        $event->trigger();
    }
}
