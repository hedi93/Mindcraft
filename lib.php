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
 * Library of interface functions and constants for module mindcraft
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the mindcraft specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
/*function mindcraft_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}*/

/**
 * Saves a new instance of the mindcraft into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $mindcraft Submitted data from the form in mod_form.php
 * @return int The id of the newly inserted mindcraft record
 */
function mindcraft_add_instance(stdClass $mindcraft) {
    global $DB, $USER;
    
    // mindcraft
    $mindcraft->userid = $USER->id;
    $mindcraft->timecreated = time();

    $mindcraft->id = $DB->insert_record("mindcraft", $mindcraft);
    
    for ($i = 0; $i < $mindcraft->nummap; $i++) {
        $mindcraft_instance = mindcraft_set_new_instance($mindcraft);
        $mindcraft_instance->name = get_string('map', 'mindcraft').' '.($i+1);
        $mindcraft_instance->id = $DB->insert_record("mindcraft_maps", $mindcraft_instance);

        // versioning
        $version = new stdClass();
        $version->mindcraftmapid = $mindcraft_instance->id;
        $version->actualjsondata = $mindcraft_instance->jsondata;
        $version->previousjsondata = $mindcraft_instance->jsondata;
        $version->lastupdate = time();
        $version->userid = $USER->id;
        $version->oldlastnodeid = 0;
        $version->id = $DB->insert_record("mindcraft_versions", $version);
    }

    return $mindcraft->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form) this function
 * will update an existing instance with new data.
 *
 * @param object $mindcraft An object from the form in mod_form
 * @return boolean
 **/
function mindcraft_update_instance($mindcraft) {
    global $DB;
    $mindcraft->id = $mindcraft->instance;
    $mindcraft_old = $DB->get_record("mindcraft", array("id" => $mindcraft->id));
    if($mindcraft->nummap < $mindcraft_old->nummap){
        $mindcraft_maps = $DB->get_records("mindcraft_maps", ["mindcraftid" => $mindcraft_old->id], "id DESC");
        foreach($mindcraft_maps as $mindcraft_map){
            $DB->delete_records("mindcraft_maps", ["id" => $mindcraft_map->id]);
            $mindcraft_old->nummap--;
            if($mindcraft_old->nummap == $mindcraft->nummap) break;
        }
    } elseif($mindcraft->nummap > $mindcraft_old->nummap){
        $nummap = $mindcraft->nummap - $mindcraft_old->nummap;
        for($i = 0; $i < $nummap; $i++){
            $mindcraft_new_map = mindcraft_set_new_instance($mindcraft_old);
            $mindcraft_new_map->name = get_string('map', 'mindcraft').' '.($mindcraft_old->nummap+$i+1);
            $DB->insert_record("mindcraft_maps", $mindcraft_new_map);
        }
    }
    return $DB->update_record("mindcraft", $mindcraft);
}

/**
 * Removes all an instance of the mindcraft from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function mindcraft_delete_instance($id){
    global $DB;
    if (! $mindcraft = $DB->get_record("mindcraft", array("id"=>$id))) {
        return false;
    }
    $result = true;
    if(! $mindcraft_maps = $DB->get_records("mindcraft_maps", array("mindcraftid" => $mindcraft->id))){
        return false;
    }
    foreach($mindcraft_maps as $mindcraft_map){
        $topics = $DB->get_records("mindcraft_topics", array("mindcraftmapid" => $mindcraft_map->id));
        if ($topics) {
            foreach ($topics as $topic) {
                $posts = $DB->get_records("mindcraft_posts", array("topicid" => $topic->id));
                if ($posts) {
                    foreach ($posts as $post) {
                        if (!$DB->delete_records("mindcraft_responses", array("postid" => $post->id))) {
                            return false;
                        }
                    }
                    if (!$DB->delete_records("mindcraft_posts", array("topicid" => $topic->id))) {
                        return false;
                    }
                }
            }
            if (!$DB->delete_records("mindcraft_topics", array("mindcraftmapid" => $mindcraft_map->id))) {
                return false;
            }
        }
    }
    if (! $DB->delete_records("mindcraft_maps", array("mindcraftid"=>$mindcraft->id))) {
        $result = false;
    }
    if (! $DB->delete_records("mindcraft", array("id"=>$mindcraft->id))) {
        $result = false;
    }
    return $result;
}

/**
 * Update a mindcraft map in the database
 *
 * Given an Object of a map of this module and the new json data,
 *
 * @param StdClass $id Id of the mind map
 * @param string $jsondata the new json data
 * @return boolean Success/Failure
 */
function mindcraft_update_map($id, $jsondata) {
    global $DB, $USER;
    $mindcraft_map = $DB->get_record("mindcraft_maps", array("id"=>$id));
    if($mindcraft_map->jsondata === $jsondata){
        return false;
    }
    $mindcraft_map->jsondata = $jsondata;
    $mindcraft_map->timemodified = time();
    if (!$DB->update_record("mindcraft_maps", $mindcraft_map)) {
        return false;
    }
    $mindcraft_version = $DB->get_record("mindcraft_versions", array('mindcraftmapid' => $mindcraft_map->id));
    if(!$mindcraft_version) return false;
    $mindcraft_version->previousjsondata = $mindcraft_version->actualjsondata;
    $mindcraft_version->actualjsondata = $mindcraft_map->jsondata;
    $mindcraft_version->lastupdate = $mindcraft_map->timemodified;
    $mindcraft_version->userid = $USER->id;
    if (!$DB->update_record("mindcraft_versions", $mindcraft_version)) {
        return false;
    }
    return true;
}

/**
 * Removes an instance of the mindcraft from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function mindcraft_delete_map($id) {
    global $DB, $USER;
    if (! $mindcraft_map = $DB->get_record("mindcraft_maps", array("id"=>$id))) {
        return false;
    }
    if (! $mindcraft = $DB->get_record("mindcraft", array("id"=>$mindcraft_map->mindcraftid))) {
        return false;
    }
    if($USER->id == $mindcraft_map->userid) {
        if (!$DB->delete_records("mindcraft_maps", array("id" => $id))) {
            return false;
        }
        $topics = $DB->get_records("mindcraft_topics", array("mindcraftmapid" => $id));
        if ($topics) {
            foreach ($topics as $topic) {
                $posts = $DB->get_records("mindcraft_posts", array("topicid" => $topic->id));
                if ($posts) {
                    foreach ($posts as $post) {
                        if (!$DB->delete_records("mindcraft_responses", array("postid" => $post->id))) {
                            return false;
                        }
                    }
                    if (!$DB->delete_records("mindcraft_posts", array("topicid" => $topic->id))) {
                        return false;
                    }
                }
            }
            if (!$DB->delete_records("mindcraft_topics", array("mindcraftmapid" => $id))) {
                return false;
            }
        }
        $mindcraft->nummap--;
        if (!$DB->update_record("mindcraft", $mindcraft)) {
            return false;
        }
        return true;
    }
    return false;
}

/**
 * Get a previous version of a mindcraft map from the database
 * @param int $id Id of the mindcraft map
 * @return string | boolean Success/Failure
 */
function mindcraft_get_previous_version($id){
    global $DB;
    $version = $DB->get_record("mindcraft_versions", array("mindcraftmapid" => $id));
    if($version){
        return $version->previousjsondata;
    }
    else{
        return false;
    }
}

/**
 * Valide or invalidate a mindcraft map
 * That will allow or not the student to consult the mindmap
 * @param int $id Id of the mindcraft map
 * @return boolean Success/Failure
 */
function mindcraft_validate($id){
    global $DB, $USER;
    if (! $mindcraft_map = $DB->get_record("mindcraft_maps", array("id"=>$id))) {
        return false;
    }
    if($USER->id == $mindcraft_map->userid) {
        if($mindcraft_map->state == 0){
            $mindcraft_map->state = 1;
            if (!$DB->delete_records("mindcraft_used", array("mindcraftmapid" => $id))) {
                return false;
            }
        } else {
            $mindcraft_map->state = 0;
        }
        if (!$DB->update_record("mindcraft_maps", $mindcraft_map)) {
            return false;
        }
        return true;
    }
    return false;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in newmodule activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function mindcraft_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG;
    return false;
}

/**
 * Create a new mindcraft instance with default values copy from mindcraft object
 * @param $mindcraft stdclass that contain the default values
 * @return object
 */
function mindcraft_set_new_instance($mindcraft) {
    global $USER;

    $instance = new stdClass();
    $instance->mindcraftid = $mindcraft->id;
    if(isset($mindcraft->interactive)){
        $instance->interactive = '1';
    } else {
        $instance->interactive = '0';
    }
    $instance->state = 0;
    $instance->userid = $mindcraft->userid;
    $instance->timecreated = time();
    $instance->timemodified = time();
    $instance->lastnodeid = 0;

    $instance->jsondata = '{ "class": "go.GraphLinksModel",
  "nodeKeyProperty": "id",
  "nodeDataArray": [ { "id": 0, "loc": "120 120", "text": "'.get_string('mainsubject','mindcraft').'", "figure": "RoundedRectangle",
  "background": "#fff", "color":"rgb(76, 76, 76)", "border":  "#60ac60", "borderWidth": 2, "img": "images/null.png",
  "userid": "'.$USER->id.'", "timecreated": "'.time().'", "userupdate": "0", "timemodified": "0",
  "link": "#", "linkIcon": "images/null.png", "file": "null", "fileIcon" : "images/null.png", "fileName" : "null"} ],
  "linkDataArray": [  ]}';
    return $instance;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the mindcraft.
 *
 * @param object $mform form passed by reference
 */
function mindcraft_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'mindcraftheader', get_string('modulenameplural', 'mindcraft'));
    $mform->addElement('advcheckbox', 'reset_mindcraft', get_string('removeresponses','mindcraft'));
}

/**
 * Course reset form defaults.
 *
 * @return array
 */
function mindcraft_reset_course_form_defaults($course) {
    return array('reset_mindcraft'=>1);
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * mindcraft instances for course $data->courseid.
 *
 * @global object
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function mindcraft_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'mindcraft');
    $status = array();

    if (!empty($data->reset_mindcraft)) {
        $mindcraftssql = "SELECT ad.id
                           FROM {mindcraft} ad
                           WHERE ad.course=?";
        $params = array($data->courseid);
        
        $DB->delete_records_select('mindcraft_maps', "mindcraftid IN ($mindcraftssql)", $params);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeinstances', 'mindcraft'), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    /*
    if ($data->timeshift) {
        shift_course_mod_dates('mindcraft', array(''), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }
    */

    return $status;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link mindcraft_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function mindcraft_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link mindcraft_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function mindcraft_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function mindcraft_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function mindcraft_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

function mindcraft_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_ADVANCED_GRADING:        return false;

        default: return null;
    }
}

/* Comments API */

/**
 * Saves a comment int the database
 * @param int $node_id id of the commented node
 * @param int $mindcraft_id id of mindmap
 * @param string $content the comment content
 * @return boolean
 */
function mindcraft_save_comment($node_id, $mindcraft_id, $content){
    global $DB, $USER;
    $content = trim($content);
    if(!$content){
        return false;
    }
    $params = array(
        'nodeid'      => $node_id,
        'mindcraftmapid' => $mindcraft_id
    );
    $topic = $DB->get_record("mindcraft_topics", $params);
    if(!$topic){
        $topic = new stdClass();
        $topic->nodeid = $node_id;
        $topic->mindcraftmapid = $mindcraft_id;
        $topic->id = $DB->insert_record("mindcraft_topics", $topic);
        if(!$topic->id){
            return false;
        }
    }

    $post = new stdClass();
    $post->topicid = $topic->id;
    $post->content = $content;
    $post->userid = $USER->id;
    $post->timecreated = time();
    $topic->nodeid = $node_id;
    $topic->mindcraftmapid = $mindcraft_id;

    if (!$DB->insert_record("mindcraft_posts", $post)) {
        return false;
    }

    return true;
}

/**
 * Deletes the comment which the id is passed a parameter
 * @param $post_id id of the post to delete
 * @param $response_id id of the response to delete
 * @return boolean
 */
function mindcraft_delete_comment($post_id = null, $response_id = null){
    global $DB, $USER;
    if($post_id){
        $post = $DB->get_record("mindcraft_posts", array('id' => $post_id));
        if($USER->id == $post->userid) {
            $nb_comments = $DB->count_records("mindcraft_posts", array("topicid" => $post->topicid));
            if ($nb_comments == 1) {
                if (!$DB->delete_records("mindcraft_topics", array("id" => $post->topicid))) {
                    return false;
                }
            }
            if (!$DB->delete_records("mindcraft_posts", array("id" => $post_id))) {
                return false;
            }
            if (!$DB->delete_records("mindcraft_responses", array("postid" => $post_id))) {
                return false;
            }
        }
    }
    elseif($response_id){
        $response = $DB->get_record("mindcraft_responses", array('id' => $response_id));
        if($USER->id == $response->userid) {
            if (!$DB->delete_records("mindcraft_responses", array("id" => $response_id))) {
                return false;
            }
        }
    }
    return true;
}

/**
 * Insert a comment as a response in the database
 * @param $postid id of the post to delete
 * @param $content content of the response
 * @return boolean
 */
function mindcraft_response_comment($postid, $content){
    global $USER, $DB;
    $content = trim($content);
    if(!$content){
        return false;
    }
    $response = new stdClass();
    $response->userid = $USER->id;
    $response->postid = $postid;
    $response->content = $content;
    $response->timecreated = time();
    $response->id = $DB->insert_record("mindcraft_responses", $response);
    if(!$response->id){
        return false;
    }
    return true;
}

/**
 * Insert a comment as a response in the database
 * @param $post_id id of the post to be updated
 * @param $response_id id of the response to be updated
 * @param $content string the new content oh the comment
 * @return boolean
 */
function mindcraft_update_comment($post_id = null, $response_id = null, $content){
    global $DB, $USER;
    $content = trim($content);
    if(!$content){
        return false;
    }
    if($post_id && !$response_id){
        $comment = $DB->get_record("mindcraft_posts", array("id"=>$post_id));
        $table = "posts";
    }
    else{
        $comment = $DB->get_record("mindcraft_responses", array("id"=>$response_id));
        $table = "responses";
    }
    $actual_comment = $DB->get_record("mindcraft_" . $table, array("id"=>$comment->id));
    if($actual_comment->userid != $USER->id){
        return false;
    }
    $comment->content = $content;
    $comment->timecreated = time();
    if( !$DB->update_record("mindcraft_" . $table, $comment)){
        return false;
    }
    return true;
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of mindcraft?
 *
 * This function returns if a scale is being used by one mindcraft
 * if it has support for grading and scales.
 *
 * @param int $mindcraftid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given mindcraft instance
 */
function mindcraft_scale_used($mindcraftid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('mindcraft', array('id' => $mindcraftid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function mindcraft_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mindcraft file areas
 *
 * @package mod_mindcraft
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function mindcraft_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mindcraft file areas
 *
 * @package mod_mindcraft
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the mindcraft's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function mindcraft_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}