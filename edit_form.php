<?php
 
class block_newresources_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        
        // A sample string variable with a default value.
		$mform->addElement('text', 'config_title', get_string('blocktitle', 'block_newresources'));
		$mform->setDefault('config_title', 'default value');
		$mform->setType('config_title', PARAM_MULTILANG);
 
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_numresourcesinstance', get_string('numresourcesinstance', 'block_newresources'));
        $mform->setDefault('config_numresourcesinstance', '10');
        $mform->setType('config_numresourcesinstance', PARAM_MULTILANG);        
 
    }
    
    
}