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

/*
 * Internal library of functions for module surveypro
 *
 * All the surveypro specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SURVEYPRO_GHEIGHT', 500);
define('SURVEYPRO_GWIDTH' , 800);

function fetch_scalesdata($surveyproid) {
    global $DB;

    $iarea = new stdClass();
    // names of areas of investigation
    for ($i = 1; $i < 7; $i++) {
        $iarea->surveyproname[] = get_string('fieldset_content_0'.$i, 'surveyprotemplate_'.$iarea->surveyproname);
    }
    // end of: names of areas of investigation

    // useless now
    // $iarea->name = $DB->get_field('surveypro', 'template', array('id' => $surveyproid));

    // group question id per area of investigation
    $sql = 'SELECT si.id, si.sortindex, si.plugin
            FROM {surveypro_item} si
            WHERE si.surveyproid = :surveyproid
                AND si.plugin = :plugin
            ORDER BY si.sortindex';

    $whereparams = array('surveyproid' => $surveyproid, 'plugin' => 'radiobutton');
    $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

    $countradio = ($iarea->surveyproname == 'collesactualpreferred') ? 8 : 4;
    $idlist = array();
    $i = 0;
    foreach ($itemseeds as $itemseed) {
        $idlist[] = $itemseed->id;
        if (count($idlist) == $countradio) {
            $iarea->itemidlist[] = $idlist;
            $i++;
            $idlist = array();
        }
    }
    $itemseeds->close();
    // end of: group question id per area of investigation

    // options (label for possible answers)
    $itemid = $iarea->itemidlist[0][0]; // one of the itemid of the surveypro (the first)
    $item = surveypro_get_item($itemid, SURVEYPRO_TYPEFIELD, 'radiobutton');
    $iarea->options = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
    // end of: options (label for possible answers)

    // calculate the mean and the standard deviation of answers
    $m = array();
    $i = 0;
    foreach ($iarea->itemidlist as $areaidlist) {
        $sql = 'SELECT count(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                FROM {surveypro_answer} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $aggregate = $DB->get_record_sql($sql);
        $m = $aggregate->sumofanswers/$aggregate->countofanswers;
        $iarea->mean[] = $m;
        $i++;

        $sql = 'SELECT ud.content
                FROM {surveypro_answer} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $answers = $DB->get_recordset_sql($sql);
        $bigsum = 0;
        foreach ($answers as $answer) {
            $xi = (double)$answer->content;
            $bigsum += ($xi - $m) * ($xi - $m);
        }
        $answers->close();

        $bigsum /= $aggregate->countofanswers;
        $iarea->stddeviation[] = sqrt($bigsum);
    }
    // end of: calculate the mean and the standard deviation of answers

    return $iarea;
}

function fetch_summarydata($surveyproid) {
    global $DB;

    $iarea = new stdClass();
    $iarea->surveyproname = $DB->get_field('surveypro', 'template', array('id' => $surveyproid));

    // names of areas of investigation
    for ($i = 1; $i < 7; $i++) {
        $iarea->name[] = get_string('fieldset_content_0'.$i, 'surveyprotemplate_'.$iarea->surveyproname);
    }
    // end of: names of areas of investigation

    // group question id per area of investigation
    $sql = 'SELECT si.id, si.sortindex, si.plugin
            FROM {surveypro_item} si
            WHERE si.surveyproid = :surveyproid
                AND si.plugin = :plugin
            ORDER BY si.sortindex';

    $whereparams = array('surveyproid' => $surveyproid, 'plugin' => 'radiobutton');
    $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

    $countradio = ($iarea->surveyproname == 'collesactualpreferred') ? 8 : 4;
    $idlist = array();
    $i = 0;
    foreach ($itemseeds as $itemseed) {
        $idlist[] = $itemseed->id;
        if (count($idlist) == $countradio) {
            $iarea->itemidlist[] = $idlist;
            $i++;
            $idlist = array();
        }
    }
    $itemseeds->close();
    // end of: group question id per area of investigation

    // options (label for possible answers)
    $itemid = $iarea->itemidlist[0][0]; // one of the itemid of the surveypro (the first)
    $item = surveypro_get_item($itemid, SURVEYPRO_TYPEFIELD, 'radiobutton');
    $iarea->options = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
    // end of: options (label for possible answers)

    // calculate the mean and the standard deviation of answers
    $m = array();
    $i = 0;
    foreach ($iarea->itemidlist as $areaidlist) {
        $sql = 'SELECT count(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                FROM {surveypro_answer} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $aggregate = $DB->get_record_sql($sql);
        $m = $aggregate->sumofanswers/$aggregate->countofanswers;
        $iarea->mean[] = $m;
        $i++;

        $sql = 'SELECT ud.content
                FROM {surveypro_answer} ud
                WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
        $answers = $DB->get_recordset_sql($sql);
        $bigsum = 0;
        foreach ($answers as $answer) {
            $xi = (double)$answer->content;
            $bigsum += ($xi - $m) * ($xi - $m);
        }
        $answers->close();

        $bigsum /= $aggregate->countofanswers;
        $iarea->stddeviation[] = sqrt($bigsum);
    }
    // end of: calculate the mean and the standard deviation of answers

    return $iarea;
}