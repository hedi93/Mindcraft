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
 * Delete comment
 *
 * @package    mod_mindcraft
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

Global $USER, $PAGE, $DB;

$response_id = optional_param('response_id', 0, PARAM_INT);
$post_id = optional_param('post_id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
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
}

if($delete) {
    mindcraft_delete_comment($post_id, $response_id);
    header("Location: " . $CFG->wwwroot . "/mod/mindcraft/view.php?id=" . $cm->id . "&viewmap=" . $id);
}

//$PAGE->set_url('/mod/mindcraft/delete_comment.php', array('id' => $post_id));
$PAGE->set_title(get_string('deletecomment', 'mindcraft'));
$PAGE->set_heading((isset($course->fullname)) ? $course->fullname : 'Delete mindcraft');
$PAGE->set_pagelayout('incourse');
echo $OUTPUT->header();
echo $OUTPUT->box_start();
echo "<h2>".get_string('deletecomment', 'mindcraft')."</h2>";
echo "<div style='width: 60%;min-width: 220px;margin: auto;'>";
echo "<p>".get_string('surefordeletingcomment', 'mindcraft')."</p>";
if($post_id){
    $nb_responses = $DB->count_records("mindcraft_responses", array("postid" => $post_id));
    if($nb_responses > 0){
        echo "<p>" . $nb_responses . " " .get_string('commentsposted', 'mindcraft')."</p>";
    }
}
$href = "delete=1&amp;mindcraft_id=" . $id;
$href .= ($post_id) ? "&amp;post_id=" . $post_id : "";
$href .= ($response_id) ? "&amp;response_id=" . $response_id : "";
echo "<div class='mindcraft-delete-buttons' style='padding-left:265px'><a href='delete_comment.php?" . $href . "' class='btn btn-primary'>".get_string('continue', 'mindcraft')."</a><a class='btn' href='view.php?id=" . $cm->id . "&amp;viewmap=" . $id . "'>".get_string('cancel', 'mindcraft')."</a></div>";
echo "</div>";
echo $OUTPUT->box_end();
echo $OUTPUT->footer();