<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

index.php
The splash page.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/
require_once('common.php');

//If I've tried to add a global announcement, handle that.
if(@$_REQUEST['mode'] == "add_global_announcement") {
	//Check to see if I have permission to add global announcements
	if(!($user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CREATEGLOBALANNOUNCEMENT'])) {
		//No permission - throw an error
		$local_errors[] = array('title'=>'Error','content'=>$strings['PERMISSION_DENIED']);
	}
	else {
		//Permission granted - add an announcement
		$sql_result = add_announcement(0,$_REQUEST['title'],nl2br($_REQUEST['text']));
	}
}

//Get registered courses
$user_courses = array();
$instructor_courses = array();
$temp1 = get_user_courses($user->id);

foreach($temp1 as $row) {
	if($row['type'] == 0) {
		$user_courses[] = $row;
	}
	else {
		$instructor_courses[] = $row;
	}
}
//$user_courses = get_user_courses(3);

//Get the next 20 upcoming assignments
$user_assignments = array();
$instructor_assignments = array();
$temp2 = get_upcoming_student_assignments_short($user->id);

foreach($temp2 as $row) {
	if($row['type'] == 0) {
		$user_assignments[] = $row;
	}
	else {
		$instructor_assignments[] = $row;
	}
}

//Trim weird blank entries
for($i=0;$i<sizeof($user_assignments);$i++) {
	if(sizeof($user_assignments[$i]) == 0) {
		unset($user_assignments[$i]);
	}
}

//Get all overdue assignments
$user_overdue_assignments = get_overdue_student_assignments($user->id);

//Get the 8 most recent announcements
$user_announcements = get_announcements_user($user->id);
//Trim weird blank entries
/*for($i=0;$i<sizeof($user_announcements);$i++) {
	if(sizeof($user_announcements[$i]) == 0) {
		unset($user_announcements[$i]);
	}
}*/
//$local_notices[] = array('title'=>'Debug','content'=>print_r($user_announcements,1));
//Format times
for($i=0;$i<sizeof($user_announcements);$i++) {
	$user_announcements[$i]['announcement_time'] = date('Y-m-d H:i:s',$user_announcements[$i]['announcement_time']);
}

//Assign template vars

$tpl->assign('A_USER_COURSES',$user_courses);
$tpl->assign('A_USER_ASSIGNMENTS',$user_assignments);
$tpl->assign('A_USER_OVERDUE_ASSIGNMENTS',$user_overdue_assignments);
$tpl->assign('A_USER_ANNOUNCEMENTS', $user_announcements);

$tpl->assign('A_INSTRUCTOR_COURSES',$instructor_courses);
$tpl->assign('A_INSTRUCTOR_ASSIGNMENTS',$instructor_assignments);

//$tpl->assign('V_PERMISSIONS_CREATEGLOBALANNOUNCEMENT',$user->user_permissions & GLOBAL_PERMISSIONS_CREATEGLOBALANNOUNCEMENT);

//Assign visibility
if(@sizeof($user_courses[0]) == 0 && @sizeof($instructor_courses) > 0) {
	$tpl->assign('V_USER_COURSES_VISIBLE',0);
	$tpl->assign('V_INSTRUCTOR_COURSES_VISIBLE',1);
}
else if(@sizeof($user_courses[0]) > 0 && @sizeof($instructor_courses) > 0) {
	$tpl->assign('V_USER_COURSES_VISIBLE',1);
	$tpl->assign('V_INSTRUCTOR_COURSES_VISIBLE',1);
}
else {
	$tpl->assign('V_USER_COURSES_VISIBLE',1);
	$tpl->assign('V_INSTRUCTOR_COURSES_VISIBLE',0);
}

if(@sizeof($user_assignments[0]) == 0 && @sizeof($instructor_assignments) > 0) {
	$tpl->assign('V_USER_ASSIGNMENTS_VISIBLE',0);
	$tpl->assign('V_INSTRUCTOR_ASSIGNMENTS_VISIBLE',1);
}
else if(@sizeof($user_assignments[0]) > 0 && @sizeof($instructor_assignments) > 0) {
	$tpl->assign('V_USER_ASSIGNMENTS_VISIBLE',1);
	$tpl->assign('V_INSTRUCTOR_ASSIGNMENTS_VISIBLE',1);
}
else {
	$tpl->assign('V_USER_ASSIGNMENTS_VISIBLE',1);
	$tpl->assign('V_INSTRUCTOR_ASSIGNMENTS_VISIBLE',0);
}

$tpl->assign('A_LOCAL_ERRORS',$local_errors);
$tpl->assign('A_LOCAL_NOTICES',$local_notices);

//Output the page
$tpl->display('index.html');

?>
