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
 * Define all the restore steps that will be used by the restore_mindcraft_activity_task
 *
 * @package   mod_mindcraft
 * @category  backup
 * @copyright 2015 Your Name <your@email.adress>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one mindcraft activity
 *
 * @package    mod_mindcraft
 * @category   backup
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_mindcraft_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines structure of path elements to be processed during the restore
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('mindcraft', '/activity/mindcraft');

        if ($userinfo) {
            $paths[] = new restore_path_element('mindcraft_instance', '/activity/mindcraft/instances/mindcraft_instance');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_mindcraft($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        if (empty($data->timecreated)) {
            $data->timecreated = time();
        }

        if (empty($data->timemodified)) {
            $data->timemodified = time();
        }

        // Create the mindcraft instance.
        $newitemid = $DB->insert_record('mindcraft', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_mindcraft_instance($data) {
        global $DB;
 
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->mindno = $this->get_new_parentid('mindcraft');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('mindcraft_maps', $data);
        $this->set_mapping('mindcraft_maps', $oldid, $newitemid, true); // files by this itemname
    }

    /**
     * Post-execution actions
     */
    protected function after_execute() {
        // Add mindcraft related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_mindcraft', 'intro', null);

        $this->add_related_files('mod_mindcraft', 'intro', 'mindcraft_instance');
    }
}
