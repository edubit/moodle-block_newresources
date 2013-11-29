<?php
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
            'newresources/freqnotify',
            get_string('labelfreqnotify', 'block_newresources'),
            get_string('descfreqnotify', 'block_newresources'),
            'semanal', Array('diario'=>'Diário', 'semanal'=>'Semanal')
        ));

$settings->add(new admin_setting_configtime(
            'newresources/hourstimenotify',
            'newresources/minutestimenotify',
            get_string('labeltimenotify', 'block_newresources'),
            get_string('desctimenotify', 'block_newresources'),
            ''
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