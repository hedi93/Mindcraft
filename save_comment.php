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
 * Saving comments
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

Global $USER;

$mindcraft_id = required_param('mindcraft_id', PARAM_INT);
$node_id = required_param('node_id', PARAM_INT);
$post = required_param('comment_field', PARAM_TEXT);

if ($mindcraft_id) {
    if (! $mindcraft_map = $DB->get_record("mindcraft_maps", array("id"=>$mindcraft_id))) {
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
$context = context_module::instance($cm->id);

if(has_capability('mod/mindcraft:addcomments', $context)){
    if(!mindcraft_save_comment($node_id, $mindcraft_id, $post)){
        die("Action failed");
    }
}