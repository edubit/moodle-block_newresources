<?php

/**
 * Display all New Resources
 *
 * @copyright 2013 Edubit.com.br
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package block_newresources
 */

require_once('../../config.php');
require_once('newresource_form.php');
 
global $DB, $OUTPUT, $PAGE;

//Global configuration Block
$interval = get_config('newresources', 'interval');
$itemsperpage = get_config('newresources', 'itemsperpage');

if (empty($interval)) {
   $interval = 7;
}

if (empty($itemsperpage)) {
   $itemsperpage = 10;
}

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', $itemsperpage, PARAM_INT);

$datestart = optional_param('datestart', time() - $interval*24*60*60, PARAM_INT);
$dateend = optional_param('dateend', time(), PARAM_INT);

$titlecourse = optional_param('titlecourse', '', PARAM_TEXT);
$titlemod = optional_param('titlemod', '', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_newresources', $courseid);
}

require_login($courseid);

$PAGE->set_url('/blocks/newresources/view.php', array('courseid'=>$courseid,'blockid'=>$blockid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('advsearch', 'block_newresources'));

//Form Defaults
$param = new stdClass();
$param->courseid = $courseid;
$param->blockid = $blockid;
$param->page = $page;
$param->perpage = $perpage;
$param->datestart = $datestart;
$param->dateend = $dateend;
$param->titlecourse = $titlecourse;
$param->titlemod = $titlemod;

//Transform ArrayDate in TimeStamp string
if (is_array($datestart))
	$datestart = make_timestamp($datestart['year'], $datestart['month'], $datestart['day'], $datestart['hour'], $datestart['minute']);
if (is_array($dateend))
	$dateend = make_timestamp($dateend['year'], $dateend['month'], $dateend['day'], $dateend['hour'], $dateend['minute']);

$selectedcourses = '';
//Transform Array in String
if (is_array($titlecourse)) {
	foreach ($titlecourse as $course) {
		if ($selectedcourses) {
			$selectedcourses .= ','.$course;
		} else {
			$selectedcourses = $course;
			
		}
	}
} else {
	$selectedcourses = $titlecourse;
}

//Caso desfaca a selecao (fix)
if ($selectedcourses == '_qf__force_multiselect_submission') {
	$selectedcourses = '';
}
//Conditional Query
$query = '';
if ($datestart)
   $query .= ' AND cm.added > '.$datestart;
if ($dateend)
   $query .= ' AND cm.added < '.$dateend;
if ($selectedcourses)
   $query .= ' AND course.id in ('.$selectedcourses.')';

//Search in table of Resources
//Modules (3,8,11,12,15,17,20) = (book,folder,imscp,label,page,resource,url)
if ($titlemod) { 
	$query .= ' AND 
(
-- BOOK
(cm.instance in (SELECT book.id FROM mdl_book AS book WHERE cm.instance = book.id AND 
(book.name like "%'.$titlemod.'%" OR book.intro like "%'.$titlemod.'%")) AND mods.name="book") OR 
-- PAGE
(cm.instance in (SELECT page.id FROM mdl_page AS page WHERE cm.instance = page.id AND 
(page.name like "%'.$titlemod.'%" OR page.intro like "%'.$titlemod.'%" OR page.content like "%'.$titlemod.'%")) AND mods.name="page") OR
-- FOLDER
(cm.instance in (SELECT folder.id FROM mdl_folder AS folder WHERE cm.instance = folder.id AND 
(folder.name like "%'.$titlemod.'%" OR folder.intro like "%'.$titlemod.'%")) AND mods.name="folder") OR
-- IMSCP
(cm.instance in (SELECT imscp.id FROM mdl_imscp AS imscp WHERE cm.instance = imscp.id AND 
(imscp.name like "%'.$titlemod.'%" OR imscp.intro like "%'.$titlemod.'%")) AND mods.name="imscp") OR
-- LABEL
(cm.instance in (SELECT label.id FROM mdl_label AS label WHERE cm.instance = label.id AND 
(label.intro like "%'.$titlemod.'%")) AND mods.name="label") OR
-- RESOURCE
(cm.instance in (SELECT resource.id FROM mdl_resource AS resource WHERE cm.instance = resource.id AND 
(resource.name like "%'.$titlemod.'%" OR resource.intro like "%'.$titlemod.'%")) AND mods.name="resource") OR
-- URL
(cm.instance in (SELECT url.id FROM mdl_url AS url WHERE cm.instance = url.id AND 
(url.name like "%'.$titlemod.'%" OR url.intro like "%'.$titlemod.'%" OR url.externalurl like "%'.$titlemod.'%")) AND mods.name="url")
) '; //criteria
}

//Form
$newresource = new newresource_form();
$newresource->set_data($param);

//Header / Breadcrumb
$name = get_string('pluginname','block_newresources');
$PAGE->navbar->add($name, new moodle_url('/blocks/newresources/view.php', array('courseid'=>$courseid,'blockid'=>$blockid)));

echo $OUTPUT->header();
$newresource->display();

//Get My Courses
$mycourses = enrol_get_my_courses($fields = NULL, $sort = 'sortorder ASC', $limit = 0); //ordenar conforme visualizado no MDL
$modinfo = Array();

if (isloggedin() && $mycourses) {
	//Get My Courses
	foreach ($mycourses as $course) {
		//Get Course Modules Objects
		$modinfo[$course->id] = get_fast_modinfo($course);
		//Query Courses
		if (isset($courses)) {
			$courses .= ','.$course->id;
		} else {
			$courses = $course->id;
			
		}
	}

	//Get new Course Resources
	$countmods =  count($DB->get_records_sql('SELECT cm.id, course.id AS courseid, course.fullname AS coursename, cm.module AS moduleid, cm.instance, cm.section, cm.added, cm.visible, mods.name AS modulename 
			FROM {course_modules} AS cm
			JOIN {modules} AS mods
			JOIN {course} AS course 
			WHERE cm.course=course.id AND cm.module=mods.id AND cm.course in ('.$courses.') AND cm.module in (3,8,11,12,15,17,20) AND cm.visible=1 
			'.$query));
	$mods = $DB->get_records_sql('SELECT cm.id, course.id AS courseid, course.fullname AS coursename, cm.module AS moduleid, cm.instance, cm.section, cm.added, cm.visible, mods.name AS modulename 
			FROM {course_modules} AS cm
			JOIN {modules} AS mods
			JOIN {course} AS course
			WHERE cm.course=course.id AND cm.module=mods.id AND cm.course in ('.$courses.') AND cm.module in (3,8,11,12,15,17,20) AND cm.visible=1 
			'.$query.'
			ORDER BY cm.added DESC',NULL, $perpage*$page, $perpage);

	$baseurl = new moodle_url('/blocks/newresources/view.php', array('courseid'=>$courseid,'blockid'=>$blockid,
	'page'=>$page, 'perpage'=>$perpage, 'datestart'=>$datestart, 'dateend'=>$dateend, 'titlecourse'=>$selectedcourses, 'titlemod'=>$titlemod,
	'sort' => 0, 'dir' => 0));
	
	echo $OUTPUT->paging_bar($countmods, $page, $perpage, $baseurl);

	//Print last New Resources
	if ($mods) {
		$table = new html_table();
		$table->head = array(get_string('titleresource','block_newresources'), get_string('dateadded','block_newresources'), get_string('titlecourse','block_newresources'));
		$table->data = array();
		
		foreach ($mods as $mod) {
			//CourseModule Object
			$cm = $modinfo[$mod->courseid]->get_cm($mod->id);
			//Added date Module
			$addeddate = usergetdate($mod->added);
			$addeddate = $addeddate['mday'].'/'.$addeddate['mon'].'/'.$addeddate['year'];
		
			$table->data[] = array ('<img src="'.$cm->get_icon_url().'" /> '.
			html_writer::link($cm->get_url(), format_string($cm->name, true)), $addeddate, 
			html_writer::link(new moodle_url('/course/view.php', array('id'=>$mod->courseid)), $mod->coursename));

		}
		echo html_writer::table($table);
	
	}

}

echo $OUTPUT->footer();

