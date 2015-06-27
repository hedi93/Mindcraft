<?php
// This file is part of Mindmap module for Moodle - http://moodle.org/
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
 * Add mindcraft
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

require_once('classes/mindcraft_form.php');

Global $PAGE;

$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    if (! $mindcraft = $DB->get_record("mindcraft", array("id"=>$id))) {
        print_error('invalidid', 'mindcraft');
    }
    if (! $course = $DB->get_record("course", array("id"=>$mindcraft->course))) {
        print_error('coursemisconf', 'mindcraft');
    }
    if (! $cm = get_coursemodule_from_instance("mindcraft", $mindcraft->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    $_SESSION['course_module'] = $cm;
    $_SESSION['course'] = $course;
}

if(!isset($course) || !isset($cm)){
    if(isset($_SESSION['course']) && isset($_SESSION['course_module'])){
        $course = $_SESSION['course'];
        $cm = $_SESSION['course_module'];
        unset($_SESSION['course']);
        unset($_SESSION['course_module']);
    }
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

//$PAGE->set_url('/mod/mindcraft/add_mindcraft.php', array('id' => $id));
$PAGE->set_title(get_string('addmap', 'mindcraft'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');
echo $OUTPUT->header();
echo $OUTPUT->box_start();

$mform = new mindcraft_form();
if ($mform->is_cancelled()) {
    redirect("view.php?id=" . $cm->id);
} elseif ($fromform = $mform->get_data()) {
    if(has_capability('mod/mindcraft:addinstance', $context)){
        $mindcraft = $DB->get_record("mindcraft", array("id"=>$fromform->mindcraftid));
        $mindcraft_map = mindcraft_set_new_instance($mindcraft);
        $mindcraft_map->name = $fromform->name;
        $mindcraft_map->id = $DB->insert_record("mindcraft_maps", $mindcraft_map);
        if(!$mindcraft_map->id){
            die("insertion failed");
        }
        $version = new stdClass();
        $version->mindcraftmapid = $mindcraft_map->id;
        $version->actualjsondata = $mindcraft_map->jsondata;
        $version->previousjsondata = $mindcraft_map->jsondata;
        $version->lastupdate = time();
        $version->oldlastnodeid = 0;
        $version->userid = $USER->id;
        $version->id = $DB->insert_record("mindcraft_versions", $version);
        $mindcraft->nummap++;
        if(!$DB->update_record("mindcraft", $mindcraft)){
            echo 'update failed';
        }
    }
    redirect("view.php?id=" . $fromform->cm);
    die;
} else {
    $toform->mindcraftid = $id;
    $toform->cm = $cm->id;
}
if(isset($toform)){
    $mform->set_data($toform);
}
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();