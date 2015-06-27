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
 * Delete mindcraft
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once('classes/delete_mindcraft_form.php');

Global $PAGE, $CFG;

$id = required_param('mindcraft_id', PARAM_INT);

if ($id) {
    if (! $mindcraft_map = $DB->get_record("mindcraft_maps", array("id"=>$id))) {
        print_error('errorinvalidmindcraft', 'mindcraft');
    }
    if (! $mindcraft = $DB->get_record("mindcraft", array("id"=>$mindcraft_map->mindcraftid))) {
        print_error('invalidid', 'mindcraft');
    }
    if (! $course = $DB->get_record("course", array("id"=>$mindcraft->course))) {
        print_error('coursemisconf', 'mindcraft');
    }
    if (! $cm = get_coursemodule_from_instance("mindcraft", $mindcraft->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    $_SESSION['course_module'] = $cm->id;
    $_SESSION['course'] = $course;
    $_SESSION['mindcraft_map_id'] = $mindcraft_map->id;
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

$PAGE->set_url('/mod/mindcraft/delete_mindcraft.php', array('mindcraft_id' => $id));
$PAGE->set_title(get_string('deletemindmap', 'mindcraft'));
$PAGE->set_pagelayout('incourse');

$mform = new delete_mindcraft_form();
if ($mform->is_cancelled()) {
    $id = $_SESSION['mindcraft_map_id'];
    unset($_SESSION['mindcraft_map_id']);
    redirect("view.php?id=" . $cm->id . "&amp;viewmap=" . $id);
} elseif ($fromform = $mform->get_data()) {
    unset($_SESSION['mindcraft_map_id']);
    if(confirm_sesskey() && data_submitted()) {
        if(has_capability('mod/mindcraft:editmaps', $context)){
            mindcraft_delete_map($fromform->mindcraft_id);
        }
        redirect($CFG->wwwroot . "/mod/mindcraft/view.php?id=" . $cm->id);
    }
} else {
    $toform = new stdClass();
    $toform->mindcraft_id = $id;
}
if(isset($toform)){
    $mform->set_data($toform);
}

$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->box_start();
echo '<h2>' . get_string('deletemindmap', 'mindcraft') . '"' . $mindcraft_map->name . '"</h2>';

$a = new stdClass();
$a->mapname = $mindcraft_map->name;
$a->instanceof = $mindcraft->name;

echo "<div style='width: 60%;min-width: 220px;margin: auto;'>";
echo "<p>".get_string('surefordeletingmindcraft', 'mindcraft', $a)."</p>";
echo "<p>".get_string('instancesofmindcraft', 'mindcraft', $mindcraft->nummap)."</p>";
$mform->display();
echo "</div>";
echo $OUTPUT->box_end();
echo $OUTPUT->footer();