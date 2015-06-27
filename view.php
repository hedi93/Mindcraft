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
 * Prints a particular instance of mindcraft
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace mindcraft with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$m  = optional_param('m', 0, PARAM_INT);  // ... mindcraft instance ID - it should be named as the first character of the module.

$viewmap = optional_param('viewmap', 0, PARAM_INT);

if ($id) {
    if (! $cm = $DB->get_record("course_modules", array("id"=>$id))) {
    	print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    	print_error('coursemisconf', 'mindcraft');
    }
    if (! $mindcraft = $DB->get_record("mindcraft", array("id"=>$cm->instance))) {
    	print_error('invalidid', 'mindcraft');
    }
} else if ($m) {
    if (! $cm = get_coursemodule_from_instance("mindcraft", $mindcraft->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$mindcraft->course))) {
        print_error('coursemisconf', 'mindcraft');
    }
    if (! $mindcraft = $DB->get_record("mindcraft", array("id"=>$m))) {
    	print_error('invalidid', 'mindcraft');
    }
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$event = \mod_mindcraft\event\course_module_viewed::create(array(
    'objectid' => $mindcraft->id,
    'context'  => $context,
    'courseid' => $course->id,
    'other'    => array("content" => "mindcraftactivityview", "viewmap"=>$viewmap)
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('mindcraft', $mindcraft);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();

$url = new moodle_url('/mod/mindcraft/view.php');
$url->param('id', $id);
$url->param('m', $m);

// Print the page header.
$PAGE->set_url('/mod/mindcraft/view.php', array('id' => $cm->id));
$PAGE->set_pagelayout('incourse');
$PAGE->set_context($context);

// getting the map
$params = array(
    "id" => $viewmap
);
if($viewmap){
    $mindcraft_map = $DB->get_record("mindcraft_maps", $params);
}

// checking if maps are no more in use
$mindcraftusedmaps = $DB->get_records("mindcraft_used");
foreach($mindcraftusedmaps as $mindcraftusedmap){
    $now = time();
    if(($now - $mindcraftusedmap->date) > 180){
        if (!$DB->delete_records("mindcraft_used", array("id" => $mindcraftusedmap->id))) {
            echo "delete failed";
        }
    }
}

/// Print the page header
$strmindcrafts = get_string("modulenameplural", "mindcraft");
$strmindcraft  = get_string("modulename", "mindcraft");

$courseshortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
$title = $courseshortname . ': ' . format_string($mindcraft->name);

$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

// Output starts here.
echo $OUTPUT->header();

echo $OUTPUT->box_start();

// getting the description of the activity
echo '<h2>'.$mindcraft->name.'</h2>';
if($mindcraft->introformat){
    echo $mindcraft->intro;
}

// Construct a unique link for the map
$uniquelink = $CFG->wwwroot."/mod/mindcraft/view.php?id=$id";
if ($viewmap) {
    $uniquelink .= "&amp;viewmap=$viewmap";
}
else if (isset($mindcraft_map)) {
    $uniquelink .= "&amp;viewuser=".$mindcraft_map->id;
}

?>

<div class="mindcraft-info clearfix">
    <div class="span4">
        <?php if (isset($mindcraft_map)) {
            echo "<img src='ressources/img/info.png' style='width:32px' alt=''/><strong style='font-size: 18px'>".get_string('mapname', 'mindcraft')."</strong><div class='mindcraft-description' id='mindcraft_title'>" . $mindcraft_map->name . "</div>";
        }?>
    </div>
    <div class="span4">
        <?php if (isset($mindcraft_map)) {
            $userupdate = $DB->get_record("mindcraft_versions", array("mindcraftmapid"=>$mindcraft_map->id));
            $userupdate = $DB->get_record("user", array("id"=>$userupdate->userid));
            $userupdateurl = new moodle_url('/user/view.php', array('id' => $userupdate->id));
            $userupdatelink = html_writer::link($userupdateurl, fullname($userupdate));
            echo "<img src='ressources/img/time.png' style='width:32px' alt=''/><strong style='font-size: 18px'>".get_string('lastupdated', 'mindcraft')."</strong><div class='mindcraft-description'>" . userdate($mindcraft_map->timemodified) . "<br/>" . get_string("by", "mindcraft") . " " . $userupdatelink . "</div>";
        }?>
    </div>
    <div class="span4">
        <?php if (isset($mindcraft_map)) {
            echo "<img src='ressources/img/link.png' style='width:32px' alt=''/><strong style='font-size: 18px'>".get_string('uniquelink', 'mindcraft')."</strong><div class='mindcraft-description'>" . $uniquelink . "</div>";
        }?>
    </div>
</div>
<script src="ressources/js/jquery.js"></script>
<script>
    var lang = "<?php echo current_language() ?>";
    <?php if(has_capability('mod/mindcraft:editmaps', $context)) : ?>
        var userid = <?php echo $USER->id ?>;
        var lastnodeid = <?php echo $mindcraft_map->lastnodeid ?>;
    <?php endif; ?>
    function checkInUse(){
        var data = { action : "checkInUse" }
        $.ajax({
            url: "collaboration.php",
            type: "POST",
            data: data
        })
        .done(function(){
            console.log('checking in use...')
        })
    }
    var timerCheckInUse = setInterval(checkInUse, 5000);
</script>
<?php
    
echo $OUTPUT->box_end();

if (isset($mindcraft_map)){
    if(has_capability('mod/mindcraft:viewmaps', $context)){
        include 'mindcraft_form.php';
    }
}

// Only show a table listing other mindmaps if user have the permission
if (has_capability('mod/mindcraft:viewother', $context)) {
    $table = new html_table();
    $params = array($mindcraft->id);
    $mindmaps = $DB->get_records_select('mindcraft_maps', 'mindcraftid = ?', $params, 'id ASC');
    if ($mindmaps) {
        $strmap = get_string("maps", "mindcraft");
        $struser = get_string("owner", "mindcraft");
        $strstate = get_string("state", "mindcraft");
        $strtimecreated = get_string("timecreated", "mindcraft");
        $strlastupdated  = get_string("timemodified", "mindcraft");
        $table->head  = array($strmap, $struser, $strstate, $strtimecreated, $strlastupdated);
        $table->align = array("left", "left", "left", "left", "right");

        foreach ($mindmaps as $mindmap) {
            if( !has_capability('mod/mindcraft:editmaps', $context) && $mindmap->state == 0 ){
                continue;
            }
            $mapinuse = $DB->get_record("mindcraft_used", array('mindcraftmapid' => $mindmap->id));
            if($mapinuse){
                if($_SERVER['REMOTE_ADDR'] == $mapinuse->ip && $USER->id == $mapinuse->userid && $viewmap) {
                    $state = "<span style='color:#AA6708'>" . get_string("inusebyyou", "mindcraft") . "</span>";
                    $link = html_writer::link("view.php?id=" . $cm->id . "&viewmap=" . $mindmap->id, $mindmap->name);
                } elseif($_SERVER['REMOTE_ADDR'] == $mapinuse->ip && $USER->id == $mapinuse->userid && !$viewmap) {
                    $state = get_string("available", "mindcraft");
                    $link = html_writer::link("view.php?id=".$cm->id."&viewmap=".$mindmap->id, $mindmap->name);
                } else {
                    $state = "<span style='color:#A94442'>".get_string("inuse", "mindcraft")."</span>";
                    $link = "<span style='color:#A94442'>".$mindmap->name."</span>";
                }
            }
            else{
                $state = get_string("available", "mindcraft");
                $link = html_writer::link("view.php?id=".$cm->id."&viewmap=".$mindmap->id, $mindmap->name);
            }

            $user = $DB->get_record("user", array("id"=>$mindmap->userid));
            $userpicture = $OUTPUT->user_picture($user);
            $userurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
            $userlink = html_writer::link($userurl, fullname($user));

            $timecreated = userdate($mindmap->timecreated);

            $userupdate = $DB->get_record("mindcraft_versions", array("mindcraftmapid"=>$mindmap->id));
            $userupdate = $DB->get_record("user", array("id"=>$userupdate->userid));
            $userupdateurl = new moodle_url('/user/view.php', array('id' => $userupdate->id));
            $userupdatelink = html_writer::link($userupdateurl, fullname($userupdate));
            $lastupdated = $userupdatelink . '<br>';
            $lastupdated .= userdate($mindmap->timemodified?$mindmap->timemodified:$mindmap->timecreated);

            $table->data[] = array($link, $userpicture . ' ' . $userlink, $state, $timecreated, $lastupdated);
        }
        echo "<br />";
        echo html_writer::table($table);
        if(empty($table->data) && $mindcraft->nummap > 0){
            echo "<p>" . get_string('mapsnotvisible', 'mindcraft') . "</p>";
        }
    } else {
        echo '<p>' . get_string('nomindcraftfound', 'mindcraft') . '</p>';
    }
    if(has_capability('mod/mindcraft:addinstance', $context)){
        echo "<a class='btn btn-primary' href='add_mindcraft.php?id=" . $mindcraft->id . "'>".get_string("addmap", "mindcraft")."</a>";
    }
}

// Finish the page.
echo $OUTPUT->footer();