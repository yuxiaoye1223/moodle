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
 * IMS CP module main user interface
 *
 * @package   mod-imscp
 * @copyright 2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/imscp/locallib.php");

$id = optional_param('id', 0, PARAM_INT);  // Course module ID
$i  = optional_param('i', 0, PARAM_INT);   // IMS CP instance id

if ($i) {  // Two ways to specify the module
    $imscp = $DB->get_record('imscp', array('id'=>$i), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('imscp', $imscp->id, $imscp->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('imscp', $id, 0, false, MUST_EXIST);
    $imscp = $DB->get_record('imscp', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'imscp', 'view', 'view.php?id='.$cm->id, $imscp->id, $cm->id);

$PAGE->set_url('/mod/imscp/view.php', array('id' => $cm->id));
$PAGE->requires->yui2_lib('json');
$PAGE->requires->yui2_lib('event');
$PAGE->requires->yui2_lib('treeview');
$PAGE->requires->yui2_lib('layout');
$PAGE->requires->yui2_lib('button');
$PAGE->requires->yui2_lib('container');
$PAGE->requires->yui2_lib('dragdrop');
$PAGE->requires->yui2_lib('resize');
$PAGE->requires->js('/mod/imscp/functions.js', true);

$PAGE->requires->string_for_js('navigation', 'imscp');
$PAGE->requires->string_for_js('toc', 'imscp');
$PAGE->requires->string_for_js('hide', 'moodle');
$PAGE->requires->string_for_js('show', 'moodle');

//TODO: find some better way to disable blocks and minimise footer - pagetype just for this does not seem like a good solution
//$PAGE->set_pagelayout('maxcontent');

$PAGE->set_title($course->shortname.': '.$imscp->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($imscp);
echo $OUTPUT->header();

// verify imsmanifest was parsed properly
if (!$imscp->structure) {
    echo $OUTPUT->notification(get_string('deploymenterror', 'imscp'), 'notifyproblem');
    echo $OUTPUT->footer();
    die;
}

imscp_print_content($imscp, $cm, $course);

echo $OUTPUT->footer();