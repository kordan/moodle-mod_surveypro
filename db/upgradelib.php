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
 * Upgrade helper functions
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Delete from surveypro_answer
 * all the records referring to answers
 * to elements not presented to students because of branching.
 */
function surveypro_delete_supposed_blank_answers() {
    global $DB;

    // Step 1 of 2.
    // Start generating the list of each child element in this site (alias: conditioned by a parent child relation).
    // For each one of them verify if the parent item allows it.
    $where = 'type = :type AND parentid <> :parentid';
    $whereparams = array('type' => SURVEYPRO_TYPEFIELD, 'parentid' => 0);
    $orderby = 'surveyproid, sortindex';
    $fields = 'id as childid, parentid, parentvalue';
    $brancheditems = $DB->get_recordset_select('surveypro_item', $where, $whereparams, $orderby, $fields);

    // Delete all the answers to items that were not allowed by arent item.
    foreach ($brancheditems as $brancheditem) {
        // Select all the answers to the item $brancheditem['parentid'] different from $brancheditem['parentvalue'].
        $sql = 'SELECT child.id as answerid
                FROM {surveypro_answer} child
                    INNER JOIN {surveypro_answer} parent ON parent.submissionid = child.submissionid
                WHERE child.itemid = :childid
                    AND parent.itemid = :parentid
                    AND parent.content <> :parentcontent';
        $whereparams = array();
        $whereparams['childid'] = $brancheditem->childid;
        $whereparams['parentid'] = $brancheditem->parentid;
        $whereparams['parentcontent'] = $brancheditem->parentvalue;

        if ($deleturum = $DB->get_records_sql($sql, $whereparams)) {
            foreach ($deleturum as $todelete) {
                $DB->delete_records('surveypro_answer', array('id' => $todelete->answerid));
            }
        }
    }
    $brancheditems->close();

    // Step 2 of 2.
    // Make the list of each parent item.
    // For each one of them verify they actually have a saved answer.
    // Parent items without a saved answer must be considered as parent NOT allowing children.
    // Delete all the answers to children items that have parent without saved answers.
    // Alias: if the parent was not allowed in the userform, its children will not be allowed even more.
    $sql = 'SELECT parentid, MAX(surveyproid) as surveyproid
            FROM {surveypro_item}
            WHERE type = :type
                AND parentid <> :parentid
            GROUP BY parentid
            ORDER BY MAX(surveyproid)';
    $whereparams = array('type' => SURVEYPRO_TYPEFIELD, 'parentid' => 0);
    $parentitems = $DB->get_records_sql($sql, $whereparams);

    $oldsurveyproid = 0;
    foreach ($parentitems as $parentitem) {
        // Get all submissions for this surveyproid.
        if ($parentitem->surveyproid != $oldsurveyproid) {
            $whereparams = array();
            $whereparams['surveyproid'] = $parentitem->surveyproid;
            $submissions = $DB->get_recordset('surveypro_submission', $whereparams, 'id', 'id');

            $oldsurveyproid = $parentitem->surveyproid;
        }
        foreach ($submissions as $submission) {
            // Get all the answers given to the parent item.
            $whereparams = array();
            $whereparams['submissionid'] = $submission->id;
            $whereparams['itemid'] = $parentitem->parentid;
            if (!$DB->count_records('surveypro_answer', $whereparams)) {
                // OK, parent item was not answered so its children were not allowed!
                // Get the list of children of $parentitem->parentid.
                $childrenitems = $DB->get_records('surveypro_item', array('parentid' => $parentitem->parentid), 'id', 'id');
                // Delete its childldren.
                foreach ($childrenitems as $childitem) {
                    $whereparams['itemid'] = $childitem->id;
                    $DB->delete_records('surveypro_answer', $whereparams);
                }
            }
        }
        $submissions->close();
    }
}

/**
 * Images of thankshtml area were saved to db with wrong itemid before version 2018020200.
 * The purpose of this routine is to correct them.
 * Even backups of courses created with surveypro older than the same version were buggy
 * and they restored (and still restore) wrong itemid to databases.
 * Because of this, this routine is also called by function after_restore (in restore_surveypro_stepslib.php)
 * to correct itemid just after they are wrongly restored.
 *
 * @param object $surveypro
 * @return void
 */
function surveypro_old_restore_fix($surveypro) {
    global $DB;

    $course = $DB->get_record('course', array('id' => $surveypro->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id);

    $areas = array(SURVEYPRO_THANKSHTMLFILEAREA, SURVEYPRO_STYLEFILEAREA, SURVEYPRO_TEMPLATEFILEAREA);

    $fs = get_file_storage();

    foreach ($areas as $area) {
        $files = $fs->get_area_files($context->id, 'mod_surveypro', $area);
        foreach ($files as $file) {
            $filerecord = array();
            $filerecord['contextid'] = $file->get_contextid();
            $filerecord['component'] = 'mod_surveypro';
            $filerecord['filearea'] = $area;
            $filerecord['itemid'] = 0;

            $fs->create_file_from_storedfile($filerecord, $file);

            $file->delete();
        }
    }
}
