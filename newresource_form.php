<?php

/**
 * Display all New Resources
 *
 * @copyright 2013 Edubit.com.br
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package block_newresources
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class newresource_form extends moodleform {
    function definition() {
        global $CFG, $COURSE, $USER;

        $mform =& $this->_form;
        
		// add optional grouping
		$mform->addElement('header', 'search', get_string('search', 'block_newresources'), null, false);
		
		// add page title element
		$mform->addElement('date_time_selector', 'dateend', get_string('dateend', 'block_newresources'), array('optional' => false));
		$mform->addRule('dateend', null, 'required', null, 'client');
		
		//Course
		$mform->addElement('text', 'titlecourse', get_string('titlecourse', 'block_newresources'));
		$mform->setType('titlecourse', PARAM_MULTILANG); 
		$mform->setAdvanced('titlecourse');
		
		//Title or Description
// 		$mform->addElement('text', 'titlemod', get_string('titleordescription', 'block_newresources'));
// 		$mform->setType('titlemod', PARAM_MULTILANG);
// 		$mform->setAdvanced('titlemod');

		// Start Date
		$mform->addElement('date_time_selector', 'datestart', get_string('datestart', 'block_newresources'), array('optional' => true));
		$mform->setAdvanced('datestart');
        
		$this->add_action_buttons(false, get_string('search', 'block_newresources'));

		// hidden elements
		$mform->addElement('hidden', 'blockid');
		$mform->setType('blockid', PARAM_MULTILANG); 
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_MULTILANG); 
    }
}
