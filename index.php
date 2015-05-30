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
 * This is a one-line short description of the file
 *
 * @package    mod_mindcraft
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT); // Course.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_mindcraft\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

/// Get all required strings
$strsectionname = get_string('sectionname', 'format_'.$course->format);
$strmindcrafts = get_string("modulenameplural", "mindcraft");
$strmindcraft  = get_string("modulename", "mindcraft");

//$strname = get_string('modulenameplural', 'mod_mindcraft');
$PAGE->set_url('/mod/mindcraft/index.php', array('id' => $id));
$PAGE->navbar->add($strname);
$PAGE->set_title("$course->shortname: $strname");
//$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
//echo $OUTPUT->heading($strname);

if (! $mindcrafts = get_all_instances_in_course('mindcraft', $course)) {
    notice(get_string('thereareno', 'mindcraft'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

/// Print the list of instances
$timenow  = time();
$strname  = get_string('name');

$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname);
    $table->align = array ('center', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left');
}

$currentsection = '';
foreach ($mindcrafts as $mindcraft) {

    if (!$mindcraft->visible) {
        //Show dimmed if the mod is hidden
        $link = html_writer::link('view.php?id='.$mindcraft->coursemodule, format_string($mindcraft->name,true), array('class'=>'dimmed'));
    } else {
        //Show normal if the mod is visible
        $link = html_writer::link('view.php?id='.$mindcraft->coursemodule, format_string($mindcraft->name,true));
    }
    $printsection = '';
    if ($mindcraft->section !== $currentsection) {
        if ($mindcraft->section) {
            $printsection = get_section_name($course, $sections[$mindcraft->section]);
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $mindcraft->section;
    }
    if ($usesections) {
        $table->data[] = array ($printsection, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo html_writer::table($table);

echo $OUTPUT->footer();
