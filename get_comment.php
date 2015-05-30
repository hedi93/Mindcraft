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
 * Get comments
 *
 * @package    mod_mindcraft
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

Global $DB;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$node_id = required_param('node_id', PARAM_INT);
$mindcraft_id = required_param('mindcraft_id', PARAM_INT);

$params = array(
    'nodeid'      => $node_id,
    'mindcraftmapid' => $mindcraft_id
);

$topic = $DB->get_record("mindcraft_topics", $params);

if(!$topic){
    die("<div class='comment-entity' style='text-align: center;'>".get_string('notopics', 'mindcraft')."</div>");
}

$posts = $DB->get_records("mindcraft_posts", array('topicid' => $topic->id), 'id DESC');

foreach ($posts as $post) : $user = $DB->get_record("user", array("id"=>$post->userid));?>
    <div class="comment-wrapper">
        <div class="comment-entity">
            <?php
                $userpicture = $OUTPUT->user_picture($user);
                $userurl = new moodle_url('/user/view.php', array('id' => $user->id));
                $userlink = html_writer::link($userurl, $userpicture);
            ?>
            <?= $userlink ?>
            <strong style="display:block"><a href="<?= $userurl ?>"><?= fullname($user) ?></a></strong>
            <small><?= get_string('posted', 'mindcraft') ?> <?= userdate($post->timecreated) ?></small>
            <p class="comment-content"><?= $post->content ?></p>
            <p style="text-align: right">
                <?php if($USER->id === $user->id) : ?>
                    <a href="update_comment.php?post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $mindcraft_id ?>" class="update-comment-link"><?= get_string('update', 'mindcraft') ?></a> |
                    <a href="delete_comment.php?post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $mindcraft_id ?>" class="delete-comment-link"><?= get_string('delete', 'mindcraft') ?></a> |
                <?php endif; ?>
                <a href="response_comment.php?post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $mindcraft_id ?>" class="response-comment-link"><?= get_string('reply', 'mindcraft') ?></a>
            </p>
        </div>
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
                        <a href="update_comment.php?response_id=<?= $response->id ?>&amp;post_id=<?= $post->id ?>&amp;mindcraft_id=<?= $mindcraft_id ?>" class="update-comment-link"><?= get_string('update', 'mindcraft') ?></a> |
                        <a href="delete_comment.php?response_id=<?= $response->id ?>&amp;mindcraft_id=<?= $mindcraft_id ?>" class="delete-comment-link"><?= get_string('delete', 'mindcraft') ?></a> |
                    <?php endif; ?>
                    <a href="response_comment.php?id=<?= $post->id ?>&amp;mindcraft_id=<?= $mindcraft_id ?>" class="response-comment-link"><?= get_string('reply', 'mindcraft') ?></a>
                </p>
            </div>
        <?php
        endforeach;
    endif;
    ?>
<?php endforeach; ?>