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
        global $CFG;

		//Global configuration Block
		$notify = get_config('newresources', 'notify');
		$freqnotify = get_config('newresources', 'freqnotify');
		$hourstimenotify = get_config('newresources', 'hourstimenotify');
		$minutestimenotify = get_config('newresources', 'minutestimenotify');
		$blocktime = $hourstimenotify.':'.$minutestimenotify;
		
		$now = date('H:i');
		
mtrace($now);
mtrace($blocktime);

$day = date('N');
mtrace($day);

		if ($freqnotify == 'diario' || $day == 1) {
			$freq = true;
		} else {
			$freq = false;
		}

		mtrace($freqnotify);

		if ($notify && $now == $blocktime) {
		    mtrace('newresources-ON');

		
		} else {
		    mtrace(get_string('notificationsdisabled', 'block_newresources'));
		}
		
		return true;
		
		
		// Prepare to actually send the post now, and build up the content
// 
// 		$cleanforumname = str_replace('"', "'", strip_tags(format_string($forum->name)));
// 
// 		$userfrom->customheaders = array (  // Headers to make emails easier to track
// 				   'Precedence: Bulk',
// 				   'List-Id: "'.$cleanforumname.'" <moodleforum'.$forum->id.'@'.$hostname.'>',
// 				   'List-Help: '.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id,
// 				   'Message-ID: '.forum_get_email_message_id($post->id, $userto->id, $hostname),
// 				   'X-Course-Id: '.$course->id,
// 				   'X-Course-Name: '.format_string($course->fullname, true)
// 		);
// 
// 		if ($post->parent) {  // This post is a reply, so add headers for threading (see MDL-22551)
// 			$userfrom->customheaders[] = 'In-Reply-To: '.forum_get_email_message_id($post->parent, $userto->id, $hostname);
// 			$userfrom->customheaders[] = 'References: '.forum_get_email_message_id($post->parent, $userto->id, $hostname);
// 		}
// 
// 		$shortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
// 
// 		$postsubject = html_to_text("$shortname: ".format_string($post->subject, true));
// 		$posttext = forum_make_mail_text($course, $cm, $forum, $discussion, $post, $userfrom, $userto);
// 		$posthtml = forum_make_mail_html($course, $cm, $forum, $discussion, $post, $userfrom, $userto);
// 
// 		// Send the post now!
// 
// 		mtrace('Sending ', '');
// 
// 		$eventdata = new stdClass();
// 		$eventdata->component        = 'mod_forum';
// 		$eventdata->name             = 'posts';
// 		$eventdata->userfrom         = $userfrom;
// 		$eventdata->userto           = $userto;
// 		$eventdata->subject          = $postsubject;
// 		$eventdata->fullmessage      = $posttext;
// 		$eventdata->fullmessageformat = FORMAT_PLAIN;
// 		$eventdata->fullmessagehtml  = $posthtml;
// 		$eventdata->notification = 1;
// 
// 		// If forum_replytouser is not set then send mail using the noreplyaddress.
// 		if (empty($CFG->forum_replytouser)) {
// 			// Clone userfrom as it is referenced by $users.
// 			$cloneduserfrom = clone($userfrom);
// 			$cloneduserfrom->email = $CFG->noreplyaddress;
// 			$eventdata->userfrom = $cloneduserfrom;
// 		}
// 
// 		$smallmessagestrings = new stdClass();
// 		$smallmessagestrings->user = fullname($userfrom);
// 		$smallmessagestrings->forumname = "$shortname: ".format_string($forum->name,true).": ".$discussion->name;
// 		$smallmessagestrings->message = $post->message;
// 		//make sure strings are in message recipients language
// 		$eventdata->smallmessage = get_string_manager()->get_string('smallmessage', 'forum', $smallmessagestrings, $userto->lang);
// 
// 		$eventdata->contexturl = "{$CFG->wwwroot}/mod/forum/discuss.php?d={$discussion->id}#p{$post->id}";
// 		$eventdata->contexturlname = $discussion->name;
// 
// 		$mailresult = message_send($eventdata);
// 		if (!$mailresult){
// 			mtrace("Error: mod/forum/lib.php forum_cron(): Could not send out mail for id $post->id to user $userto->id".
// 				 " ($userto->email) .. not trying again.");
// 			add_to_log($course->id, 'forum', 'mail error', "discuss.php?d=$discussion->id#p$post->id",
// 					   substr(format_string($post->subject,true),0,30), $cm->id, $userto->id);
// 			$errorcount[$post->id]++;
// 		} else {
// 			$mailcount[$post->id]++;
// 
// 		// Mark post as read if forum_usermarksread is set off
// 			if (!$CFG->forum_usermarksread) {
// 				$userto->markposts[$post->id] = $post->id;
// 			}
// 		}
// 
// 		mtrace('post '.$post->id. ': '.$post->subject);

    }  
  
}   // Here's the closing bracket for the class definition