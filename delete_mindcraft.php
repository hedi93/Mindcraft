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
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

Global $PAGE;

$id = required_param('mindcraft_id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);

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

require_login($course, true, $cm);

if($delete){
    $link = mindcraft_delete_map($id);
    header("Location: " . $link);
}

$PAGE->set_url('/mod/mindcraft/delete_mindcraft.php', array('mindcraft_id' => $id));
$PAGE->set_title('Delete mindcraft');
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();

echo $OUTPUT->box_start();

echo '<h2>Suppression de la carte  "' . $mindcraft_map->name . '"</h2>';

$a = new stdClass();
$a->mapname = $mindcraft_map->name;
$a->instanceof = $mindcraft->name;

echo "<div style='width: 60%;min-width: 220px;margin: auto;'>";
echo "<p>".get_string('surefordeletingmindcraft', 'mindcraft', $a)."</p>";
echo "<p>".get_string('instancesofmindcraft', 'mindcraft', $mindcraft->nummap)."</p>";
echo "<div class='mindcraft-delete-buttons' style='padding-left:265px'><a href='delete_mindcraft.php?mindcraft_id=" . $id . "&amp;delete=1' class='btn btn-primary'>".get_string('continue', 'mindcraft')."</a><a class='btn' href='view.php?id=" . $cm->id . "&amp;viewmap=" . $id . "'>".get_string('cancel', 'mindcraft')."</a></div>";
echo "</div>";

echo $OUTPUT->box_end();

echo $OUTPUT->footer();