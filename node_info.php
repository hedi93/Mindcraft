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
 * Get node info
 *
 * @package    mod_mindcraft
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

Global $DB;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$userid = required_param('userid', PARAM_INT);
$timecreated = required_param('timecreated', PARAM_INT);

$userupdate = required_param('userupdate', PARAM_INT);
$timemodified = required_param('timemodified', PARAM_INT);

$userid = floor($userid);
$timecreated = floor($timecreated);

$userupdate = floor($userupdate);
$timemodified = floor($timemodified);

$user = $DB->get_record("user", array("id" => $userid));
if(!$user){
    die('user not found');
}
$userpicture = $OUTPUT->user_picture($user);
$userurl = new moodle_url('/user/view.php', array('id' => $user->id));
$userlink = html_writer::link($userurl, $userpicture);
?>
<h4><?= get_string('creator', 'mindcraft') ?></h4>
<?= $userlink ?>
<strong style="display:block"><a href="<?= $userurl ?>"><?= fullname($user) ?></a></strong>
<small><?= userdate($timecreated) ?></small>
<h5><?= get_string('lastupdate', 'mindcraft') ?></h5>
<?php
    $user = $DB->get_record("user", array("id" => $userupdate));
    if(!$user){
        die('--');
    }
?>
<strong style="display:block"><a href="<?= $userurl ?>"><?= fullname($user) ?></a></strong>
<small><?= userdate($timemodified) ?></small>