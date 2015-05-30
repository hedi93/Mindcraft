<?php
/**
 * Comment form.
 *
 * @package    mod_mindcraft
 * @copyright  2015 Your Name
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
        $mform->addElement('hidden', 'mindcraftid', '');
        $mform->addElement('hidden', 'postid', '');
        $mform->addElement('hidden', 'responseid', '');

        $this->add_action_buttons();
    }
}