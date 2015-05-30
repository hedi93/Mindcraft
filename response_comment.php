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
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("lib.php");

Global $USER, $PAGE, $DB;

require_once('classes/comment_form.php');

$post_id = optional_param('post_id', 0, PARAM_INT);
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
    $_SESSION['course_module'] = $cm->id;
}

$PAGE->set_url('/mod/mindcraft/response_mindcraft.php', array('id' => $id, 'mindcraft_id' => $mindcraft_id));
$PAGE->set_title(get_string('responsecomment', 'mindcraft'));
$PAGE->set_heading((isset($course->fullname)) ? $course->fullname : get_string('responsecomment', 'mindcraft'));
$PAGE->set_pagelayout('incourse');
echo $OUTPUT->header();
echo $OUTPUT->box_start();
$mform = new comment_form();
if ($mform->is_cancelled()) {
    $cm = $_SESSION['course_module'];
    $id = $_SESSION['mindcraft_map_id'];
    unset($_SESSION['course_module']);
    unset($_SESSION['mindcraft_map_id']);
    redirect("view.php?id=" . $cm . "&amp;viewmap=" . $id);
} elseif ($fromform = $mform->get_data()) {
    mindcraft_response_comment($fromform->postid, $fromform->comment);
    unset($_SESSION['course_module']);
    unset($_SESSION['mindcraft_map_id']);
    redirect("view.php?id=" . $fromform->cm . "&amp;viewmap=" . $fromform->mindcraftid);
    die;
} else {
    $toform->cm = $cm->id;
    $toform->mindcraftid = $id;
    if($post_id){
        $toform->postid = $post_id;
    }
}
$post = $DB->get_record("mindcraft_posts", array('id' => $post_id));
$user = $DB->get_record("user", array("id"=>$post->userid));
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
        <a href="response_comment.php?post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $id ?>" class="response-comment-link"><?= get_string('reply', 'mindcraft') ?></a>
    </p>
</div>
<?php
endforeach;
endif;
if(isset($toform)){
    $mform->set_data($toform);
}
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();