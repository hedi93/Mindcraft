<?php
/**
 * Comment form.
 *
 * @package    mod_mindcraft
 * @author     Hedi Akrout <http://www.hedi-akrout.com>
 * @copyright  2015 Hedi Akrout <contact@hedi-akrout.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class comment_form extends moodleform {
    //Add elements to form
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'comments', get_string('response', 'mindcraft'));
        $mform->addElement('textarea', 'comment', "Message", 'style="width:100%;box-sizing:border-box;height:308px;"');
        $mform->addRule('comment', null, 'required', null, 'client');

        $mform->addElement('hidden', 'cm', '');
        $mform->setType('cm', PARAM_INT);

        $mform->addElement('hidden', 'mindcraftid', '');
        $mform->setType('mindcraftid', PARAM_INT);

        $mform->addElement('hidden', 'postid', '');
        $mform->setType('postid', PARAM_INT);

        $mform->addElement('hidden', 'responseid', '');
        $mform->setType('responseid', PARAM_INT);

        $this->add_action_buttons();
    }
}