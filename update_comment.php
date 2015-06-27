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
 * Response for comments
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

Global $USER, $PAGE, $DB;

require_once('classes/comment_form.php');

$post_id = optional_param('post_id', 0, PARAM_INT);
$response_id = optional_param('response_id', 0, PARAM_INT);
$id = optional_param('mindcraft_id', 0, PARAM_INT);

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
    $_SESSION['mindcraft_map_id'] = $mindcraft_map->id;
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

$url = [];
if($post_id){
    $url['post_id'] = $post_id;
}
if($response_id){
    $url['response_id'] = $response_id;
}
if($id){
    $url['mindcraft_id'] = $id;
}
$PAGE->set_url('/mod/mindcraft/update_comment.php', $url);
$PAGE->set_title(get_string('responsecomment', 'mindcraft'));
$PAGE->set_pagelayout('incourse');
if($post_id){
    $post = $DB->get_record("mindcraft_posts", array('id' => $post_id));
    $commentid = $post->userid;
}
if($response_id){
    $response = $DB->get_record("mindcraft_responses", array('id' => $response_id));
    $commentid = $response->userid;
}
$mform = new comment_form();
if ($mform->is_cancelled()) {
    $id = $_SESSION['mindcraft_map_id'];
    unset($_SESSION['mindcraft_map_id']);
    redirect("view.php?id=" . $cm->id . "&amp;viewmap=" . $id);
}
elseif ($fromform = $mform->get_data()) {
    unset($_SESSION['mindcraft_map_id']);
    if(has_capability('mod/mindcraft:addcomments', $context)){
        if( !mindcraft_update_comment($fromform->postid, $fromform->responseid, $fromform->comment)){
            die("update failed");
        }
    }
    redirect("view.php?id=" . $fromform->cm . "&amp;viewmap=" . $fromform->mindcraftid);
}
else {
    $toform = new stdClass();
    $toform->cm = $cm->id;
    $toform->mindcraftid = $id;
    $toform->postid = $post_id;
    $toform->responseid = $response_id;
    if($post_id){
        $toform->comment = $post->content;
    }
    if($response_id){
        $toform->comment = $response->content;
    }
}
$PAGE->set_heading((isset($course->fullname)) ? $course->fullname : get_string('responsecomment', 'mindcraft'));
echo $OUTPUT->header();
echo $OUTPUT->box_start();
$user = $DB->get_record("user", array("id"=>$commentid));
?>
<h2><?= $mindcraft_map->name ?></h2>
<div class="comment-entity">
    <?php
    $userpicture = $OUTPUT->user_picture($user);
    $userurl = new moodle_url('/user/view.php', array('id' => $user->id));
    $userlink = html_writer::link($userurl, $userpicture);
    echo $userlink;
    ?>
    <strong style="display:block"><a href="<?= $userurl ?>"><?= fullname($user) ?></a></strong>
    <small><?= get_string('posted', 'mindcraft') ?> <?= userdate($post->timecreated) ?></small>
    <p class="comment-content"><?= $post->content ?></p>
    <p style="text-align: right">
        <?php if($USER->id === $user->id) : ?>
            <a href="update_comment.php?post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $id ?>" class="update-comment-link"><?= get_string('update', 'mindcraft') ?></a> |
            <a href="delete_comment.php?post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $id ?>" class="delete-comment-link"><?= get_string('delete', 'mindcraft') ?></a> |
        <?php endif; ?>
        <a href="response_comment.php?post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $id ?>" class="response-comment-link"><?= get_string('reply', 'mindcraft') ?></a>
    </p>
</div>
<?php
if($post_id && !$response_id){
    if(isset($toform)){
        $mform->set_data($toform);
    }
    $mform->display();
}
$responses = $DB->get_records("mindcraft_responses", array('postid' => $post->id));
if($responses) :
    foreach ($responses as $response) : $user = $DB->get_record("user", array("id"=>$response->userid));
        $userpicture = $OUTPUT->user_picture($user);
        $userurl = new moodle_url('/user/view.php', array('id' => $user->id));
        $userlink = html_writer::link($userurl, $userpicture);
        ?>
        <div class="comment-entity" style="margin-left: 10%;">
            <?= $userlink ?>
            <strong style="display:block"><a href="<?= $userurl ?>"><?= fullname($user) ?></a></strong>
            <small><?= get_string('posted', 'mindcraft') ?> <?= userdate($response->timecreated) ?></small>
            <p class="comment-content"><?= $response->content ?></p>
            <p style="text-align: right">
                <?php if($USER->id === $user->id) : ?>
                    <a href="update_comment.php?response_id=<?= $response->id ?>&amp;post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $id ?>" class="update-comment-link"><?= get_string('update', 'mindcraft') ?></a> |
                    <a href="delete_comment.php?response_id=<?= $response->id ?>&amp;mindcraft_id=<?= $id ?>" class="delete-comment-link"><?= get_string('delete', 'mindcraft') ?></a> |
                <?php endif; ?>
                <a href="response_comment.php?id=<?= $post->id ?>&amp;mindcraft_id=<?= $id ?>" class="response-comment-link"><?= get_string('reply', 'mindcraft') ?></a>
            </p>
        </div>
    <?php
    if($response_id){
        if($response_id == $response->id){
            if(isset($toform)){
                $mform->set_data($toform);
            }
            $mform->display();
        }
    }
    endforeach;
endif;
echo $OUTPUT->box_end();
echo $OUTPUT->footer();