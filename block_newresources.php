<?php

/**
 * class block_newresources
 *
 * @package    block_newresources
 * @copyright  2013 Edubit.com.br
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/course/lib.php');

class block_newresources extends block_base {

    protected $timestart = null;

    /**
     * Initialises the block
     */
    function init() {
        $this->title = get_string('pluginname', 'block_newresources');
    }

    /**
     * Returns the content object
     *
     * @return stdObject
     */
    function get_content() {
		global $USER, $DB, $COURSE;
		
        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
        
        $limitcourses = 10;
		if (! empty($this->config->numresourcesinstance)) {
			$limitcourses = $this->config->numresourcesinstance;
		}

        $this->content = new stdClass;
		$this->content->text = '';
		
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
			$mods = $DB->get_records_sql('SELECT cm.id, cm.course, cm.module AS moduleid, cm.instance, cm.section, cm.added, cm.visible, mods.name AS modulename 
					FROM {course_modules} AS cm
					JOIN {modules} AS mods
					WHERE cm.module=mods.id AND cm.course in ('.$courses.') AND cm.module in (3,8,11,12,15,17,20) AND cm.visible=1 
					ORDER BY cm.added DESC',NULL,NULL,$limitcourses);

			//Print last New Resources
			if ($mods) {
				$this->content->text .= '<ul>';
				foreach ($mods as $mod) {
					//CourseModule Object
					$cm = $modinfo[$mod->course]->get_cm($mod->id);
					//Added date Module
					$addeddate = usergetdate($mod->added);
					$addeddate = $addeddate['mday'].'/'.$addeddate['mon'].'/'.$addeddate['year'].' - '.$addeddate['hours'].':'.$addeddate['minutes'];
				
					$this->content->text .= '<li>';
					$this->content->text .= '<img src="'.$cm->get_icon_url().'" /> '.
                    html_writer::link($cm->get_url(), format_string($cm->name, true)).
                    '<small> ('.$addeddate.')</small>';		
					$this->content->text .= '</li>';
				}
				$this->content->text .= '</ul>';			
			}
		
		}
 
		$url = new moodle_url('/blocks/newresources/view.php', array('courseid'=>$COURSE->id,'blockid'=>$this->instance->id));
		$this->content->footer = html_writer::link($url, get_string('advsearch', 'block_newresources'));

        return $this->content;
    }


    /**
     * Which page types this block may appear on.
     *
     * @return array page-type prefix => true/false.
     */
    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }

	// Enable global configuration
	function has_config() {return true;}
  
  
}   // Here's the closing bracket for the class definition