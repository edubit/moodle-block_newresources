<?php

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'block_newresources'),
            get_string('descconfig', 'block_newresources')
        ));
 
$settings->add(new admin_setting_configcheckbox(
            'newresources/notify',
            get_string('labelnotify', 'block_newresources'),
            get_string('descnotify', 'block_newresources'),
            '1'
        ));

$settings->add(new admin_setting_configselect(
            'newresources/freqnotifyday',
            get_string('labelfreqnotifyday', 'block_newresources'),
            get_string('descfreqnotifyday', 'block_newresources'),
            0, Array(0=>get_string('alldays', 'block_newresources'), 1=>get_string('monday', 'calendar'), 2=>get_string('tuesday', 'calendar'), 3=>get_string('wednesday', 'calendar'), 4=>get_string('thursday', 'calendar'), 5=>get_string('friday', 'calendar'), 6=>get_string('saturday', 'calendar'), 7=>get_string('sunday', 'calendar'))
        ));

$settings->add(new admin_setting_configtime(
            'newresources/hourstimenotify',
            'minutestimenotify',
            get_string('labeltimenotify', 'block_newresources'),
            get_string('desctimenotify', 'block_newresources'),
            array('h' => 17, 'm' => 30)
        ));

$settings->add(new admin_setting_heading(
            'advsearch',
            get_string('advsearch', 'block_newresources'),
            get_string('descadvsearch', 'block_newresources')
        ));

$settings->add(new admin_setting_configtext(
            'newresources/interval',
            get_string('labelinterval', 'block_newresources'),
            get_string('desinterval', 'block_newresources'),
            '7'
        ));

$settings->add(new admin_setting_configtext(
            'newresources/itemsperpage',
            get_string('labelitemsperpage', 'block_newresources'),
            get_string('desitemsperpage', 'block_newresources'),
            '10'
        ));