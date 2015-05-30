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
 * Define all the backup steps that will be used by the backup_mindcraft_activity_task
 *
 * @package   mod_mindcraft
 * @category  backup
 * @copyright 2015 Your Name <your@email.adress>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete mindcraft structure for backup, with file and id annotations
 *
 * @package    mod_mindcraft
 * @category   backup
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_mindcraft_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the root element describing the mindcraft instance.
        $mindcraft = new backup_nested_element('mindcraft', array('id'), 
                array(
                    'userid',
                    'intro',
                    'introformat',
                    'nummap',
                    'timecreated'
                )
        );

        $instances = new backup_nested_element('maps');
        
        $instance = new backup_nested_element('map', array('id'), array(
                                     'mindcraftid', 'userid', 'name', 'intro', 'introformat',
                                     'interactive', 'jsondata', 'state', 'timecreated', 'timemodified'));

        // If we had more elements, we would build the tree here.
        $mindcraft->add_child($instances);
        $instances->add_child($instance);

        // Define data sources.
        $mindcraft->set_source_table('mindcraft', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            $instance->set_source_table('mindcraft_maps', array('mindcraftid' => backup::VAR_PARENTID));
        }

        $mindcraft->annotate_ids('user', 'userid');
        
        $instance->annotate_ids('user', 'userid');

        // If we were referring to other tables, we would annotate the relation
        // with the element's annotate_ids() method.

        // Define file annotations (we do not use itemid in this example).
        $mindcraft->annotate_files('mod_mindcraft', 'intro', null);

        $instance->annotate_files('mod_mindcraft', 'intro', 'id');

        // Return the root element (mindcraft), wrapped into standard activity structure.
        return $this->prepare_activity_structure($mindcraft);
    }
}
