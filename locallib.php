<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the definition for the library class for file feedback plugin
 *
 *
 * @package   assignfeedback_file
 * @copyright 2023 copymonitor.jp
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class assign_feedback_copybridge extends assign_feedback_plugin
{
	private $PLUGIN_NAME = 'assignfeedback_copybridge';
	private $enabledcache = null;

	/**
	 * Get the name of the copybridge feedback plugin.
	 *
	 * @return string
	 */
	public function get_name() {
		global $PAGE;

		if ($this->plagiarism_isused()) {
			$courseid = $this->assignment->get_course()->id;
			$cmid = $this->assignment->get_context()->instanceid;

            // 2023.01.06 - mdl_assign_grades 테이블에 데이터 추가
            $instance = $this->assignment->get_instance();
            $course_context = context_course::instance($instance->course);
            $this->assign_grade_user($course_context->id, $instance->id);

			$jsmodule = array('name' => 'mod_assign_feedback_copybridge',
				'fullpath' => '/mod/assign/feedback/copybridge/module.js');

			$PAGE->requires->js_init_call(
				'M.mod_assign_feedback_copybridge.init',
				array(
					'scripturl' => $this->plagiarism_configval('scripturl'),
					'bridgeurl' => $this->plagiarism_configval('bridgeurl'),
					'group_id' => $this->plagiarism_configval('prefixid') . '_' . $courseid . '_' . $cmid,
					'lang' => $this->plagiarism_configval('lang')),
				true, $jsmodule);

		}
		return get_string('copybridge', $this->PLUGIN_NAME);
	}

	/**
	 * Override to indicate a plugin supports quickgrading.
	 *
	 * @return boolean - True if the plugin supports quickgrading
	 */
	public function supports_quickgrading() {
		return true;
	}

	/**
	 * Get quickgrading form elements as html.
	 *
	 * @param int $userid The user id in the table this quickgrading element relates to
	 * @param mixed $grade - The grade data - may be null if there are no grades for this user (yet)
	 * @return mixed - A html string containing the html form elements required for quickgrading
	 */
	public function get_quickgrading_html($userid, $grade) {
		return '';
	}

	/**
	 * Get form elements for grading form.
	 *
	 * @param stdClass $grade
	 * @param MoodleQuickForm $mform
	 * @param stdClass $data
	 * @param int $userid The userid we are currently grading
	 * @return bool true if elements were added to the form
	 */
// 	public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
// 		return true;
// 	}

	/**
	 * Return true if there are no feedback files
	 * @param stdClass $grade
	 */
	public function is_empty(stdClass $grade) {
		$context = $this->assignment->get_context();
		if (has_capability('mod/assign:grade', $context)) return false;

		//if ($this->plagiarism_isused())) return false;
 		if ($this->plagiarism_isopend()) return false;

		return true;
	}

	/**
	 * Display the list of files in the feedback status table.
	 *
	 * @param stdClass $grade
	 * @param bool $showviewlink - Set to true to show a link to see the full list of files
	 * @return string
	 */
	public function view_summary(stdClass $grade, &$showviewlink) {
		global $DB, $CFG;

		$obj = new stdClass();

		$html = '-';

		$courseid = $this->assignment->get_course()->id;
		$cmid = $this->assignment->get_context()->instanceid;
		$userid = $grade->userid;

		$oAssign = $this->assignment->get_instance();

		$param = array('assignment' => $oAssign->id, 'userid' => 0, 'groupid' => 0);
		if (empty($oAssign->teamsubmission)) {
			$obj->writer = $DB->get_field_sql(
				"SELECT username FROM {user} WHERE id = :id", array('id' => $userid));
			$param['userid'] = $userid;
		} else if ($group = $this->assignment->get_submission_group($userid, false)) {
			$obj->writer = 'CBTEAM-' . $group->id;
			$param['groupid'] = $group->id;
		}

		$oSubmission = $DB->get_record_sql(
			"SELECT * FROM {assign_submission} WHERE assignment = :assignment
				AND userid = :userid AND groupid = :groupid
				AND status = 'submitted' AND latest = 1", $param);
// 		debugging('<PRE>'.var_export($oSubmission, true).'</PRE>');

		if (!empty($obj->writer) && !empty($oSubmission)) {
			$obj->updated = date('YmdHis', $oSubmission->timecreated);
			if ($oSubmission->timemodified > $oSubmission->timecreated) {
				$obj->updated = date('YmdHis', $oSubmission->timemodified);
			}

			$obj->uri = $this->plagiarism_configval('prefixid')
				. '_' . $courseid . '_' . $cmid . '_' . $obj->writer;

			$html = '<span class="total-copy-ratio" update_date="'.$obj->updated
				. '" uri="'.$obj->uri.'" writer_id="'.$obj->writer.'">'. $html . '</span>';
		}

		return $html;
	}

	/**
	 * Display the list of files in the feedback status table.
	 *
	 * @param stdClass $grade
	 * @return string
	 */
	public function view(stdClass $grade) {
		return '';
	}

	/**
	 * Get the feedback comment from the database
	 *
	 * @param int $gradeid
	 * @return stdClass|false The feedback comments for the given grade if it exists. False if it doesn't.
	 */
	public function get_feedback_copybridge($gradeid) {
		//global $DB;
		//return $DB->get_record($this->PLUGIN_NAME, array('grade' => $gradeid));

		$result = new stdClass();
		$result->id = 1;
		$result->assignment = $this->assignment->get_instance()->id;
		$result->grade = $gradeid;

		return $result;
	}


	/**
	 * Return the plugin configs for external functions.
	 *
	 * @return array the list of settings
	 * @since Moodle 3.2
	 */
	public function get_config_for_external() {
		return (array) $this->get_config();
	}

	// =====================================================================================

	public static function plagiarism_settings() {
		static $plagiarismsettings;
		if (!empty($plagiarismsettings) || $plagiarismsettings === false) {
			return $plagiarismsettings;
		}

		$plagiarismsettings = (array) get_config('plagiarism_copybridge');
		if (isset($plagiarismsettings['enabled']) && $plagiarismsettings['enabled']) {
			if (empty($plagiarismsettings['copybridge_id'])) return false;
			return $plagiarismsettings;
		}
		return false;
	}

	public function plagiarism_configval($confname, $nullval = null) {
		global $COURSE;
		$result = $nullval;

		//$plugin = new assign_feedback_copybridge();
		$plagiarismsettings = $this->plagiarism_settings();
		if (!$plagiarismsettings) return $result;

		//$lang = current_language();
		//$lang = ($lang == 'ja') ? "jp" : $lang;
        $lang = "jp" ; // CM Bridge - Fixed language setting

		switch (trim($confname)) {
			case  'scripturl' :
                $result = 'https://www.cmbridge.jp/cm/common/js/copymonitor.bridge.js';
				break;
			case 'bridgeurl' :
				$result = '/plagiarism/copybridge/bridge.php'; break;
			case 'courseid' :
				$result = $COURSE->id; break;
			case 'lang' :
				$result = $lang; break;
			case 'prefixid' :
				$result = strtoupper($plagiarismsettings['copybridge_id'] ?? 'CBGRP');
				break;
			default :
				if ($plagiarismsettings[trim($confname)]) {
					$result = $plagiarismsettings[trim($confname)];
				}
		}

		return $result;
	}

	public function plagiarism_isused() {
		global $DB;

		//$plugin = new assign_feedback_copybridge();
		//if (!$plugin->plagiarism_settings()) return false;
		if (!$this->plagiarism_settings()) return false;

		$context = $this->assignment->get_context();
//		debugging('<PRE>'.var_export($context, true).'</PRE>');
		if (empty($context)) return false;

		$cmid = $context->instanceid;
		$config = $DB->get_record('plagiarism_copybridge', array('cm' => $cmid));
//		debugging('<PRE>'.var_export($config, true).'</PRE>');
		if (empty($config) || !$config->isused) return false;

		return true;
	}

    public function plagiarism_isopend() {
        global $DB;

        if (!$this->plagiarism_settings()) return false;

        $context = $this->assignment->get_context();
        if (empty($context)) return false;

        $cmid = $context->instanceid;
        $config = $DB->get_record('plagiarism_copybridge', array('cm' => $cmid));

        if (empty($config) || !$config->isopen) return false;

        return true;
    }

	/**
	 * Allows hiding this plugin from the submission/feedback screen if it is not enabled.
	 *
	 * @return bool - if false - this plugin will not accept submissions / feedback
	 */
	public function is_enabled() {
		if ($this->enabledcache === null) {
			$this->enabledcache = ($this->plagiarism_isused() && $this->get_config('enabled'));
		}
		return $this->enabledcache;
	}

    public function assign_grade_user($contextId, $assignmentId) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        $assignInfo = $this->assignment->get_instance();
        $tmp_time = $DB->get_field('assign', 'timemodified', array('id' => $assignInfo->id));
        $gradebookroles = get_config('core', 'gradebookroles');

        $sql = "INSERT INTO {assign_grades} (assignment, userid, grade, timecreated)
                (
                    SELECT ? , u.id , NULL, {$tmp_time}
                    FROM {role_assignments} ra JOIN {user} u ON u.id = ra.userid
                    WHERE (ra.contextid = ?)
                        AND ra.roleid IN (".$gradebookroles.")
                        AND NOT EXISTS (
                                            SELECT userid
                                            FROM {assign_grades} AS ag
                                            WHERE ag.assignment = ?
                                                AND ag.userid = u.id
                                        )
                    GROUP BY u.id
                )";
        $params = array($assignmentId, $contextId, $assignmentId);
        $DB->execute($sql, $params);

    }

}
