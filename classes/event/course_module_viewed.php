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
 * Defines the view event.
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_mindcraft\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_mindcraft instance list viewed event class
 *
 * If the view mode needs to be stored as well, you may need to
 * override methods get_url() and get_legacy_log_data(), too.
 *
 * @package    mod_mindcraft
 * @copyright  2015 Your Name <your@email.adress>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_viewed extends \core\event\course_module_viewed {

    /**
     * Initialize the event
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'mindcraft';
        //parent::init();
    }

    /**
     * Returns non-localised description of what happened.
     * @return string
     */
    public function get_description() {
        $param = '';
        if ($this->other['viewmap']){
            $param .= 'viewmap='.$this->other['viewmap'];
        }
        if (substr($param, strlen($param)-2) == ', '){
            $param = substr($param, 0, strlen($param)-2);
        }
        if (!empty($param)){
            $param = ' ('.$param.')';
        }
        return 'User with id ' . $this->userid . ' viewed mindcraft activity with instance id ' . $this->objectid. $param;
    }


    /**
     * replace add_to_log() statement.
     * @return array of parameters to be passed to legacy add_to_log() function.
     */
    protected function get_legacy_logdata() {
        $param = array();
        $param['id'] = $this->contextinstanceid;
        if ($this->other['viewmap']){
            $param['viewmap'] = $this->other['viewmap'];
        }
        $url = new \moodle_url('view.php', $param);
        return array($this->courseid, 'mindcraft', 'view', $url->out(), $this->objectid, $this->contextinstanceid);
    }
}