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
        
		$mform->addElement('header', 'search', get_string('search', 'block_newresources'), null, false);
		
		// Start Date
		$mform->addElement('date_time_selector', 'datestart', get_string('datestart', 'block_newresources'), array('optional' => false));
		$mform->addRule('datestart', null, 'required', null, 'client');

		//End Date
		$mform->addElement('date_time_selector', 'dateend', get_string('dateend', 'block_newresources'), array('optional' => false));
		$mform->setAdvanced('dateend');
		
		//Course
		$mycourses = enrol_get_my_courses($fields = NULL, $sort = 'fullname ASC', $limit = 0);
		$list = Array();
		foreach ($mycourses as $course) {
			$list[$course->id] = $course->fullname;
		}
		$mform->addElement('select', 'titlecourse', get_string('titlecourse', 'block_newresources'), $list);
		$mform->getElement('titlecourse')->setMultiple(true);
		$mform->setAdvanced('titlecourse');
		
		//Title or Description
 		$mform->addElement('text', 'titlemod', get_string('titleordescription', 'block_newresources'));
 		$mform->setType('titlemod', PARAM_MULTILANG);
 		$mform->setAdvanced('titlemod');


        
		$this->add_action_buttons(false, get_string('search', 'block_newresources'));

		// hidden elements
		$mform->addElement('hidden', 'blockid');
		$mform->setType('blockid', PARAM_MULTILANG); 
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_MULTILANG); 
    }
}
