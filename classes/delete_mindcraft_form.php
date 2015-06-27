<?php
/**
 * Delete comment form.
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class delete_mindcraft_form extends moodleform {
    //Add elements to form
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('hidden', 'mindcraft_id', '');
        $mform->setType('mindcraft_id', PARAM_INT);

        $this->add_action_buttons(true, get_string("continue"));
    }
}