<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

courses.php
Primary page for managing courses.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/
require_once('common.php');

if(@!$_REQUEST['mode']) {
	$_REQUEST['mode'] = "";
}

/*
 * This page requires login.
 */

if($user->id == 0) {
	$tpl->display('error_mustlogin.html');
	die();
}

/*
 * Assign permission values to template
 */

if($user->user_permissions & GLOBAL_PERMISSIONS_MANAGECOURSES)
{
	$tpl->assign('V_PERMISSIONS_MANAGECOURSES',1);
}
if($user->user_permissions & GLOBAL_PERMISSIONS_SEEALLCOURSES)
{
	$tpl->assign('V_PERMISSIONS_SEEALLCOURSES',1);
}

/*
 * If called with the mode "viewcourse", lists vital data about a particular course,
 * 	and a form to edit it.
 * If called with the mode "editcourse", edits course data according to the info provided,
 * 	then lists vital data about that course along with a form to edit it.
 * If called with the mode "addcourse", adds a course to the database,
 * 	then displays the course list with a form to add a new course.
 * If called with the mode "deletecourse", deletes a course from the database,
 * 	then displays the course list with a form to add a new course.
 * Otherwise, lists all courses (broken down by category).
 * 	with a form to add a new course.
 */

/*
 * Fetches the necessary information to render a View Course page,
 * then renders it.
 */

function viewcourse($id) {
	global $tpl, $user, $local_errors, $local_notices, $strings;
	//Load data about the course queried.
	if($id) {
		$course_data = get_course($id);
		$course_membership_data = get_course_membership($id, $user->id);
		$course_announcements = get_announcements_course($id);
		for($i=0;$i<sizeof($course_announcements);$i++) {
			$course_announcements[$i]['announcement_time'] = date('Y-m-d H:i:s',$course_announcements[$i]['announcement_time']);
		}
		$tpl->assign('A_COURSE_DATA',$course_data);
		//$tpl->assign('A_COURSE_MEMBERSHIP_DATA',$course_membership_data);
		$tpl->assign('A_COURSE_ANNOUNCEMENTS',$course_announcements);
		
		//If I have course membership data, start setting flags
		if(!$course_membership_data) {
			//I don't have course membership data.
			$tpl->assign('V_IS_MEMBER',0);
			//Check to see if I have permissions here!
			if(!($user->user_permissions & GLOBAL_PERMISSIONS_MANAGECOURSES) && !($user->user_permissions & GLOBAL_PERMISSIONS_SEEALLCOURSES)) {
				$local_errors[] = array('title'=>'Error','content'=>$strings['PERMISSION_DENIED']);
				$tpl->assign('LOCKOUT_PAGE',1);
			}
			if($user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_VIEWCLASSLIST',1);
				$course_class_list = get_class_list($id);
				$tpl->assign('A_COURSE_CLASS_LIST',$course_class_list);
				$course_upcoming_assignments = get_course_assignments($id,TRUE);
				$course_recent_assignments = get_course_assignments($id,FALSE);
				$tpl->assign('A_COURSE_UPCOMING_ASSIGNMENTS',$course_upcoming_assignments);
				$tpl->assign('A_COURSE_RECENT_ASSIGNMENTS',$course_recent_assignments);
			}
		}
		else {
			//I do.
			$tpl->assign('V_IS_MEMBER',1);
			//Set the user type flag
			$tpl->assign('V_USER_TYPE',$course_membership_data['type']);
			//Set user permission flags
			//TODO: Change permission systems to use a bitfield array. Then we can use a foreach loop instead of this byzantine mess
			if($course_membership_data['permissions'] & CLASS_PERMISSIONS_VIEWOWNGRADES || $user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_VIEWOWNGRADES',1);
				//Get percent and letter grade
				$percent_grade = get_student_grade($user->id, $id);
				$tpl->assign('V_DATA_GRADES_OVERALLPERCENT',$percent_grade['grade_percent']);
				$tpl->assign('V_DATA_GRADES_LETTERGRADE',percent_to_letter_grade($percent_grade['grade_percent']));
			}
			if($course_membership_data['permissions'] & CLASS_PERMISSIONS_VIEWALLGRADES || $user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_VIEWALLGRADES',1);
				//Load Gradebook-at-a-Glance required information
			}
			if($course_membership_data['permissions'] & CLASS_PERMISSIONS_SETGRADES || $user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_SETGRADES',1);
			}
			if($course_membership_data['permissions'] & CLASS_PERMISSIONS_VIEWASSIGNMENTSDETAILED || $user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_VIEWASSIGNMENTSDETAILED',1);
			}
			if($course_membership_data['permissions'] & CLASS_PERMISSIONS_MANAGEASSIGNMENTS || $user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_MANAGEASSIGNMENTS',1);
			}
			if($course_membership_data['permissions'] & CLASS_PERMISSIONS_CREATEANNOUNCEMENT || $user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_CREATEANNOUNCEMENT',1);
			}
			if($course_membership_data['permissions'] & CLASS_PERMISSIONS_VIEWCLASSLIST || $user->user_permissions & GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE)
			{
				$tpl->assign('V_PERMISSIONS_C_VIEWCLASSLIST',1);
				$course_class_list = get_class_list($id);
				$tpl->assign('A_COURSE_CLASS_LIST',$course_class_list);
			}
			//Load assignment list
			$course_upcoming_assignments = get_course_assignments($id,TRUE);
			$course_recent_assignments = get_course_assignments($id,FALSE);
			foreach($course_upcoming_assignments as &$assignment) {
				if(is_array($assignment)) {
					$assignment['num_graded'] = get_number_graded($assignment['id']);
					$assignment['num_total'] = get_class_size($id);
				}
			}
			foreach($course_recent_assignments as &$assignment) {
				if(is_array($assignment)) {
					$assignment['num_graded'] = get_number_graded($assignment['id']);
					$assignment['num_total'] = get_class_size($id);
				}
			}
			$tpl->assign('A_COURSE_UPCOMING_ASSIGNMENTS',$course_upcoming_assignments);
			$tpl->assign('A_COURSE_RECENT_ASSIGNMENTS',$course_recent_assignments);
		}
		
	}
	//For this page I need a course ID. Display an error if I don't have one
	else {
		$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ARGUMENT_NOTFOUND']);
	}
	
	$tpl->assign('A_LOCAL_ERRORS',$local_errors);
	$tpl->assign('A_LOCAL_NOTICES',$local_notices);
	
	$tpl->display('viewcourse.html');
}

/*
 * Fetches the necessary information to render a List Courses page,
 * then renders it.
 */

function listcourses() {
	global $tpl, $user, $local_errors, $local_notices;
	
	$course_list = get_all_courses($user->id);
	
	//Trim weird blank entries
	for($i=0;$i<sizeof($course_list);$i++) {
		if(sizeof($course_list[$i]) == 0) {
			unset($course_list[$i]);
		}
	}
	
	//Split up the course list into sections - 
	//one section for instructor, one section for student, one section for unenrolled.
	
	$course_list_instructor = array();
	$course_list_student = array();
	$course_list_other = array();
	
	foreach($course_list as $item) {
		if($item['type'] === NULL) {
			$course_list_other[] = $item;
		}
		else if($item['type'] == 0) {
			$course_list_student[] = $item;
		}
		else {
			$course_list_instructor[] = $item;
		}
	}
	
	$tpl->assign('A_COURSE_LIST',$course_list);
	$tpl->assign('A_COURSE_LIST_INSTRUCTOR',$course_list_instructor);
	$tpl->assign('A_COURSE_LIST_STUDENT',$course_list_student);
	$tpl->assign('A_COURSE_LIST_OTHER',$course_list_other);
	
	$tpl->assign('A_LOCAL_ERRORS',$local_errors);
	$tpl->assign('A_LOCAL_NOTICES',$local_notices);
	
	$tpl->display('listcourses.html');
}

if($_REQUEST['mode'] == "viewcourse") {
	
	viewcourse($_REQUEST['id']);
	
}

else if($_REQUEST['mode'] == "editcourse") {
	
	//Validate input
	if(@$_REQUEST['id']) {
	
		if($user->user_permissions & GLOBAL_PERMISSIONS_MANAGECOURSES)
		{
			//Attempt to edit the course based on received data
			if(!update_course($_REQUEST['id'],$_REQUEST['course_name'],$_REQUEST['course_name_short'],$_REQUEST['instructor'],$_REQUEST['term'],$_REQUEST['description'],$_REQUEST['info']))
			{
				throw new DatabaseException("Failed to update course data. MySQL said: ".$db->error);
			}
			else {
				$local_notices[] = array('title'=>'Success','content'=>$strings['SUCCESS_COURSE_UPDATED']);
			}
		}
		else {
			$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
		}
	}
	else {
		$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ARGUMENT_NOTFOUND']);
	}
	
	viewcourse(@$_REQUEST['id']);
	
}

else if($_REQUEST['mode'] == "addcourse") {
	//Validate input
	if(@$_REQUEST['course_name'] && $_REQUEST['course_name_short']) {
		if($user->user_permissions & GLOBAL_PERMISSIONS_MANAGECOURSES)
		{
		//Attempt to add a course to the database based on received data
			if(!add_course($_REQUEST['course_name'],$_REQUEST['course_name_short'],$_REQUEST['instructor'],$_REQUEST['term'],$_REQUEST['description'],$_REQUEST['info']))
			{
				throw new DatabaseException("Failed to add course. MySQL said: ".$db->error);
			}
			else {
				$local_notices[] = array('title'=>'Success','content'=>$strings['SUCCESS_COURSE_ADDED']);
			}
		}
		else {
			$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
		}
	}
	else {
		$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ARGUMENT_NOTFOUND']);
	}
	
	//Display the course list
	listcourses();
	
}

else if($_REQUEST['mode'] == "deletecourse") {
	if(@$_REQUEST['id']) {
		if($user->user_permissions & GLOBAL_PERMISSIONS_MANAGECOURSES)
		{
		//Attempt to delete a course from the database based on received data
			if(!delete_course($_REQUEST['id'])) {
				throw new DatabaseException("Failed to delete course. MySQL said: ".$db->error);
			}
			else {
				$local_notices[] = array('title'=>'Success','content'=>$strings['SUCCESS_COURSE_DELETED']);
			}
		}
		else {
			$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
		}
	}
	else {
		$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ARGUMENT_NOTFOUND']);
	}
		
	//display the course list
	listcourses();
}

else if($_REQUEST['mode'] == "add_announcement") {
	//Validate input
	$course_membership_data = get_course_membership($_REQUEST['id'], $user->id);
	if($_REQUEST['title'] && $_REQUEST['text'] && $_REQUEST['id']) {
		if($user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE'] || $course_membership_data['permissions'] & $bitfields['CLASS_PERMISSIONS']['CREATEANNOUNCEMENT']) {
			//Attempt to add a course announcement.
			if(!add_announcement($_REQUEST['id'],$_REQUEST['title'],nl2br($_REQUEST['text'])))
			{
				throw new DatabaseException("Failed to add an announcement. MySQL said: ".$db->error);
			}
			else {
				//TODO: if we have to, add a localised string denoting success.
				//$local_notices[] = array('title'=>'Success','content'=>$strings['SUCCESS_COURSE_UPDATED']);
			}
		}
		else {
			$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
		}
	}
	else {
		$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ARGUMENT_NOTFOUND']);
	}
	
	viewcourse(@$_REQUEST['id']);
}

else {
	listcourses();
}

?>
