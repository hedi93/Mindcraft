<?php
/**
 * add mind map form.
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class mindcraft_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'addcard', get_string('addmap', 'mindcraft'));
        $mform->addElement('text', 'name', get_string('mapname', 'mindcraft'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('checkbox', 'interactive', get_string('interactive', 'mindcraft'));
        $mform->addHelpButton('interactive', 'mindmapinteractive', 'mindcraft');

        $mform->addElement('hidden', 'mindcraftid', '');
        $mform->addElement('hidden', 'cm', '');

        $this->add_action_buttons();
    }
}