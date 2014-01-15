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

    function init() {
        $this->title = get_string('pluginname', 'block_newresources');
    }
    
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

    /**
     * cron - goes through all feeds and retrieves them with the cache
     * duration set to 0 in order to force the retrieval of the item and
     * refresh the cache
     *
     * @return boolean true if all feeds were retrieved succesfully
     */
    function cron() {
        global $CFG, $DB;

		//Global configuration Block
		$notify = get_config('newresources', 'notify');
		$freqnotifyday = get_config('newresources', 'freqnotifyday');
		$hourstimenotify = get_config('newresources', 'hourstimenotify');
		$minutestimenotify = get_config('newresources', 'minutestimenotify');
		$blocktime = $hourstimenotify.':'.$minutestimenotify;

		$day = date('N');		
		$now = date('H:i');

		//Frequency day in configuration, is today?
		if ($freqnotifyday == 0 || $freqnotifyday == $day) {
			$freq = true;
		} else {
			$freq = false;
		}

		// Configuration Block start cron task
		if ($notify && $freq && $now == $blocktime) {
		    mtrace('newresources-ON');
		
		} else {
		    mtrace(get_string('notificationsdisabled', 'block_newresources'));
		}
		

		//Depois colocar dentro do IF acima, testar funcionalidades


		// PROCESSO ================
		// 1) Obter lista dos recursos incluidos no ultimo dia ou ultima semana
		// 2) Ordenar modulos novos por COURSEID
		// 3) Loop nos modulos novos
		// 4) Criar tabela com os modulos e links
		// 5) Ao trocar de COURSEID enviar email para os participantes (um email por disciplina em que o aluno está inscrito)
		// =========================

		//Global configuration Block
		$itemsperpage = get_config('newresources', 'itemsperpage');
		$freqnotifyday = get_config('newresources', 'freqnotifyday');
		if ($freqnotifyday == 0) {
			$interval = 1; //one day
		} else {
			$interval = 7; //one week
		}

		//Calculating the time interval
		$datestart = time() - $interval*24*60*60;
		$dateend = time();

		//Conditional Query
		$query = '';
		if ($datestart)
		   $query .= ' AND cm.added > '.$datestart;
		if ($dateend)
		   $query .= ' AND cm.added < '.$dateend;

		//Search New resources
		$modinfo = Array(); //
		$mods = $DB->get_records_sql('SELECT cm.id, course.id AS courseid, course.shortname AS shortname, course.fullname AS coursename, cm.module AS moduleid, cm.instance, cm.section, cm.added, cm.visible, mods.name AS modulename 
					FROM {course_modules} AS cm
					JOIN {modules} AS mods
					JOIN {course} AS course
					WHERE cm.course=course.id AND cm.module=mods.id AND cm.module in (3,8,11,12,15,17,20) AND cm.visible=1 
					'.$query.'
					ORDER BY courseid ASC');

		if ($mods) {
			//Message mail table new resources
			$table = new html_table();
			$table->head = array(get_string('titlecourse','block_newresources'), get_string('dateadded','block_newresources'), get_string('titlecourse','block_newresources'));
			$table->data = array();

			// COLOCAR um item a mais no fim do objeto, pois o loop abaixo obtem o próximo sempre
			$mods['Z']= new stdClass;
			$mods['Z']->courseid = 'Z';

			//Bulk mail parameters
			$urlinfo = parse_url($CFG->wwwroot);
			$hostname = $urlinfo['host'];

			//GET Students
			$lastmod = new stdClass;
			$lastmod->courseid = 'A';
			$i = 0;
			foreach ($mods as $mod) {
				//TABLE ITEMS ====
				if ($mod->courseid != 'Z') { //last item array, compile one email per course
					//CourseModule Object
					$modinfo[$mod->courseid] = get_fast_modinfo($mod->courseid);
					$cm = $modinfo[$mod->courseid]->get_cm($mod->id);
					//Added date Module
					$addeddate = usergetdate($mod->added);
					$addeddate = $addeddate['mday'].'/'.$addeddate['mon'].'/'.$addeddate['year'].' - '.$addeddate['hours'].':'.$addeddate['minutes'];
				
					$table->data[] = array ('<img src="'.$cm->get_icon_url().'" /> '.
					html_writer::link($cm->get_url(), format_string($cm->name, true)), $addeddate, 
					html_writer::link(new moodle_url('/course/view.php', array('id'=>$mod->courseid)), $mod->coursename));
				}

				//Sempre no proximo item, pois podem existir diversos novos recursos em um mesmo curso, enviar email 1 vez por curso
				if (($lastmod->courseid != $mod->courseid) && $i != 0) {
					//end table and mail message
					$posthtml = html_writer::table($table);
					$posttext = $posthtml;
					$postsubject = html_to_text("$lastmod->shortname: ".get_string('newresources', 'block_newresources'));					

					$coursecontext = context_course::instance($lastmod->courseid);
					$users = get_enrolled_users($coursecontext);
					foreach ($users as $user) {
						//send mail
						$userfrom = $user;
						// $userfrom->email = $CFG->noreplyaddress;
						$userfrom->customheaders = array (  // Headers to make emails easier to track
								   'Precedence: Bulk',
								   'List-Id: "New Resources" <newresources'.$lastmod->courseid.'@'.$hostname.'>',
								   'X-Course-Id: '.$lastmod->courseid
						);

						// Send the post now!

						mtrace('Sending ', '');

						$eventdata = new stdClass();
						$eventdata->component        = 'mod_forum';
						$eventdata->name             = 'posts';
						$eventdata->userfrom         = $userfrom;
						$eventdata->userto           = $user;
						$eventdata->subject          = $postsubject;
						$eventdata->fullmessage      = $posttext;
						$eventdata->fullmessageformat = FORMAT_PLAIN;
						$eventdata->fullmessagehtml  = $posthtml;
						$eventdata->notification = 1;

						$smallmessagestrings = new stdClass();
						$smallmessagestrings->user = fullname($userfrom);
						$smallmessagestrings->name = "$lastmod->shortname: ".get_string('newresources', 'block_newresources');
						$smallmessagestrings->message = 'smallmessage';
						//make sure strings are in message recipients language
						$eventdata->smallmessage = get_string_manager()->get_string('smallmessage', 'block_newresources', $smallmessagestrings, $user->lang);

						$eventdata->contexturl = "{$CFG->wwwroot}/blocks/newresources/view.php?courseid={$lastmod->courseid}";
						$eventdata->contexturlname = $lastmod->shortname;

						$mailresult = message_send($eventdata);
						if (!$mailresult){
							mtrace("Error: blocks/newresources/block_newresources.php cron(): Could not send out mail for course $lastmod->shortname to user $user->id".
								 " ($user->email) .. not trying again.");
							add_to_log($lastmod->courseid, 'block_newresources', 'mail error', $CFG->wwwroot.'/blocks/newresources/view.php?courseid='.$lastmod->courseid,
									   'Debug Mail Error', $lastmod->courseid, $user->id);
							$errorcount[$user->id]++;
						}

						mtrace('Course ('.$lastmod->courseid.' - '.$lastmod->shortname. '): '.$lastmod->coursename.', User ('.$user->id.'): '.$user->firstname.' '.$user->lastname);
					} //end foreach $users

					//New table
					$table = new html_table();
					$table->head = array(get_string('titlecourse','block_newresources'), get_string('dateadded','block_newresources'), get_string('titlecourse','block_newresources'));
					$table->data = array();
				} //end if $lastmod

				$lastmod = $mod;
				$i++;
			} //end foreach $mods
		} //end if $mods

		return true;

    }  //end cron()
  
}   // Here's the closing bracket for the class definition