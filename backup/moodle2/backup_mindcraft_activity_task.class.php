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
 * Defines backup_mindcraft_activity_task class
 *
 * @package   mod_mindcraft
 * @category  backup
 * @copyright 2015 Your Name <your@email.adress>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/mindcraft/backup/moodle2/backup_mindcraft_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the mindcraft instance
 *
 * @package    mod_mindcraft
 * @category   backup
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_mindcraft_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the mindcraft.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_mindcraft_activity_structure_step('mindcraft_structure', 'mindcraft.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of mindcrafts.
        $search = '/('.$base.'\/mod\/mindcraft\/index.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@mindcraftINDEX*$2@$', $content);

        // Link to mindcraft view by moduleid.
        $search = '/('.$base.'\/mod\/mindcraft\/view.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@mindcraftVIEWBYID*$2@$', $content);

        return $content;
    }
}
