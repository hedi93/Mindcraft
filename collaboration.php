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
 * Collaboration
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

Global $DB;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$mindcraftmapid = optional_param('mindcraftid', 0, PARAM_INT);
$action = required_param('action', PARAM_TEXT);

if ($mindcraftmapid) {
    if (! $mindcraft_map = $DB->get_record("mindcraft_maps", array("id"=>$mindcraftmapid))) {
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

if($action == 'setInUse') {
    $mindcraftmapinuse = $DB->get_record("mindcraft_used", array('mindcraftmapid' => $mindcraftmapid));
    if ($mindcraftmapinuse) {
        $mindcraftmapinuse->date = time();
        if(has_capability('mod/mindcraft:editmaps', $context)){
            if (!$DB->update_record("mindcraft_used", $mindcraftmapinuse)) {
                die("update failed");
            }
        }
    }
}
elseif($action == 'checkInUse'){
    $mindcraftmaps = $DB->get_records("mindcraft_used");
    if($mindcraftmaps){
        foreach($mindcraftmaps as $mindcraftmap){
            $now = time();
            if(($now - $mindcraftmap->date) > 60){
                if (!$DB->delete_records("mindcraft_used", array("id" => $mindcraftmap->id))) {
                    echo "delete failed";
                }
            }
        }
    }
}