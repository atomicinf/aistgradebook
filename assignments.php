<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

assignments.php
Displays assignments.

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

//Your code goes here

function list_assignments() {
	global $tpl, $user, $bitfields, $local_errors, $local_notices;
	if($user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
		$user_courses = get_all_courses($user->id);
	}
	else {
		$user_courses = get_user_courses($user->id);
	}
	
	$student_courses = array();
	$instructor_courses = array();

	//First off, I want to get the courses I'm enrolled in as a STUDENT, then get assignment lists for those courses.
	
	//Seed the student_courses and instructor_courses arrays.
	foreach($user_courses as $value) {
		if($value['type'] == 0 && !($user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE'])) {
			$student_courses[$value['id']] = $value;
		}
		else {
			$instructor_courses[$value['id']] = $value;
			$tmp_course_membership = get_course_membership($value['id'], $user->id);
			$instructor_courses[$value['id']]['MANAGEASSIGNMENTS'] = ($tmp_course_membership['permissions'] & $bitfields['CLASS_PERMISSIONS']['MANAGEASSIGNMENTS'] || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']);
		}
	}
	
	//The student assignment list is going to be a two-dimensional array.
	//The first key corresponds to the course ID.
	//The second key is the assignment's index (not necessarily id).
	$student_assignment_list = array();
	foreach($student_courses as $course) {
		$student_assignment_list[$course['id']] = array_merge(array_reverse(get_course_assignments($course['id'], 0)), array("separator" => 1), get_course_assignments($course['id'],1));
		//For each assignment, retrieve the appropriate grade array.
		foreach($student_assignment_list[$course['id']] as &$assignment) {
			if(is_array($assignment)) {
				$assignment['grade'] = get_assignment_grade($assignment['id'],$user->id);
			}
		}
	}
	
	//$local_notices[] = array("title" => "Debug", "content" => print_r($student_assignment_list, true));

	//Then I want to get the courses I'm enrolled in as an INSTRUCTOR, then get assignment lists for those courses.
	
	$instructor_assignment_list = array();
	foreach($instructor_courses as $course) {
		$instructor_assignment_list[$course['id']] = array_merge(array_reverse(get_course_assignments($course['id'], 0)), array("separator" => 1), get_course_assignments($course['id'],1));
		//For each assignment, retrieve the number of grades entered.
		foreach($instructor_assignment_list[$course['id']] as &$assignment) {
			if(is_array($assignment)) {
				$assignment['num_graded'] = get_number_graded($assignment['id']);
				$assignment['num_total'] = get_class_size($course['id']);
			}
		}
	}
	
	//$local_notices[] = array("title" => "Debug", "content" => "<pre>".print_r($student_assignment_list, true)."</pre>");
	//$local_notices[] = array("title" => "Debug", "content" => "<pre>".print_r($instructor_assignment_list, true)."</pre>");
	//$local_notices[] = array("title" => "Debug", "content" => "<pre>".print_r($student_courses, true)."</pre>");
	
	$tpl->assign('A_LOCAL_ERRORS', $local_errors);
	$tpl->assign('A_LOCAL_NOTICES', $local_notices);
	
	$tpl->assign('A_STUDENT_COURSES', $student_courses);
	$tpl->assign('A_INSTRUCTOR_COURSES', $instructor_courses);
	
	$tpl->assign('A_STUDENT_ASSIGNMENT_LIST', $student_assignment_list);
	$tpl->assign('A_INSTRUCTOR_ASSIGNMENT_LIST', $instructor_assignment_list);

	//Output the page
	$tpl->display('listassignments.html');
		
}

function assignment_details() {
	global $bitfields, $user, $local_errors, $local_notices, $db, $tpl;
	if(!$assignment_data) {
		$assignment_data = get_assignment(@$_REQUEST['id']);
	}
	
	//Load up course membership data
	
	if(!$course_membership_data) {
		$course_membership_data = get_course_membership($assignment_data['course_id'], $user->id);
	}
	
	//Export permissions variables
	
	foreach($bitfields['CLASS_PERMISSIONS'] as $key => $value) {
		if($course_membership_data['permissions'] & $value || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
			$tpl->assign('V_PERMISSIONS_C_'.$key,1);
		}
	}
	
	//Get the student's own grade
	if($course_membership_data['permissions'] & $bitfields['CLASS_PERMISSIONS']['VIEWOWNGRADES'] || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
		$user_grade = get_assignment_grade($_REQUEST['id'], $user->id);
	}
	//If permissible, get all grades for the assignment

	if($course_membership_data['permissions'] & $bitfields['CLASS_PERMISSIONS']['VIEWALLGRADES'] || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
		//Get the class list
		$course_class_list = get_class_list($assignment_data['course_id']);
		
		//Build the grade list.
		//For each member of the class, get his/her grade for the assignment (as a row).
		
		$grade_list = array();
		foreach($course_class_list as $row) {
			$tmp_grade_data = db_query("SELECT * FROM `assignment_grades` WHERE `user_id`='".$row['id']."' AND `assignment_id`='".$_REQUEST['id']."'");
			$grade_list[] = array(
				'user_id' => $row['id'],
				'student_name' => $row['name'],
				'is_null' => ($tmp_grade_data == NULL ? 1 : 0),
				'is_overdue' => ($tmp_grade_data == NULL ? 0 : $tmp_grade_data['is_overdue']),
				'number_grade' => ($tmp_grade_data == NULL ? "" : $tmp_grade_data['points']),
				'letter_grade' => ($tmp_grade_data == NULL ? "-" : ($tmp_grade_data['points'] === NULL ? "-" : percent_to_letter_grade($tmp_grade_data['points']/$assignment_data['max_points']*100))),
				'comments' => ($tmp_grade_data == NULL ? "" : $tmp_grade_data['comments']),
			);
		}
		
		//Build the raw numbers array
		$grade_numbers = array();
		foreach($grade_list as $row) {
			if($row['number_grade'] !== NULL) {
				$grade_numbers[] = $row['number_grade'];
			}
		}
		
		//Build the statistics array.
		$assignment_statistics = array();
		$assignment_statistics['class_size'] = get_class_size($assignment_data['course_id']);
		$assignment_statistics['num_graded'] = get_number_graded($_REQUEST['id']);
		$assignment_statistics['average_grade'] = (count($grade_numbers) ? array_sum($grade_numbers) / count($grade_numbers) : 0);
		$assignment_statistics['median_grade'] = (count($grade_numbers) ? array_median($grade_numbers) : 0);
		$assignment_statistics['standard_deviation'] = (count($grade_numbers) ? array_stddev($grade_numbers) : 0);
		
	}
	
	$tpl->assign('A_ASSIGNMENT_DATA',$assignment_data);
	$tpl->assign('A_ASSIGNMENT_STATISTICS',$assignment_statistics);
	$tpl->assign('A_GRADE_LIST',$grade_list);
	$tpl->assign('A_USER_GRADE',$user_grade);
	$tpl->assign('A_LOCAL_ERRORS',$local_errors);
	$tpl->assign('A_LOCAL_NOTICES',$local_notices);
	$tpl->display('viewassignment.html');
}

switch($_REQUEST['mode']) {

	//If the mode is update grades, make it happen. Include the assignment_details page.
	case 'update_grades':
		//$local_notices[] = array('title'=>'Form Debug (34)','content'=>'<pre>'.print_r($_REQUEST['grades'],true).'</pre>');
		if(!$assignment_data) {
			$assignment_data = get_assignment(@$_REQUEST['id']);
		}
		//Does the assignment even exist?
		if($assignment_data) {
			//Proceed ONLY if I can set grades for the requested course!
			
			if(!$course_membership_data) {
				$course_membership_data = get_course_membership($assignment_data['course_id'], $user->id);
			}
			
			if($course_membership_data) {
			
				if($course_membership_data['permissions'] & $bitfields['CLASS_PERMISSIONS']['SETGRADES'] || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
				
					//Run through the grades array, updating the user grade entries as appropriate.
					
					foreach($_REQUEST['grades'] as $k => $v) {
						//$local_notices[] = array('title'=>'Debug (47)','content'=>"Key $k");
						add_assignment_grade($_REQUEST['id'],$k,($v['grade'] === NULL || $v['grade'] === "" || $v['grade'] == "ERASE" ? NULL : $v['grade']),($v['is_overdue'] ? 1 : 0),$v['comments']);
						/*if($v['grade'] === NULL) {
							db_query_simple("UPDATE `assignment_grades` SET `points`=NULL WHERE `assignment_id`='".$_REQUEST['id']."' AND `user_id`='".$k."'");
							$local_notices[] = array('title'=>'SQL Debug (50)','content'=>"UPDATE `assignment_grades` SET `points`=NULL WHERE `assignment_id`='".$_REQUEST['id']."' AND `user_id`='".$k."'");
						}*/
					}
				}
				
				else {
					$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
				}
			}
			else {
				$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ITEM_NOTFOUND']);
			}
		}
		else {
			$local_errors[] = array('title'=>'Error','content'=>$strings['REQUESTED_ITEM_NOTFOUND']);
		}
		
		assignment_details();
		break;
		
	case 'edit_assignment':
		//Check to see if I have permissions. Only then do I proceed
		if(!$assignment_data) {
			$assignment_data = get_assignment(@$_REQUEST['id']);
		}
		if($assignment_data) {
			if(!$course_membership_data) {
				$course_membership_data = get_course_membership($assignment_data['course_id'], $user->id);
			}
			if($course_membership_data) {
				if($course_membership_data['permissions'] & $bitfields['CLASS_PERMISSIONS']['MANAGEASSIGNMENTS'] || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
					//Okay, I have permissions. Go ahead and edit the assignment.
					//Make sure the assignment exists. :)
					
					if(@$_REQUEST['id']) {
						edit_assignment($_REQUEST['id'], $assignment_data['course_id'], $_REQUEST['assignment_name'], $_REQUEST['assignment_code'], $_REQUEST['description'], $_REQUEST['max_points'], strtotime($_REQUEST['due_date']), $_REQUEST['weight']);
					}
					else {
						$local_errors[] = array('title'=>'Permission Error','content'=>$strings['REQUESTED_ITEM_NOTFOUND']);
					}
				}
				else {
					$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
				}
			}
			else {
				$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ITEM_NOTFOUND']);
			}
		}
		else {
			$local_errors[] = array('title'=>'Error','content'=>$strings['REQUESTED_ITEM_NOTFOUND']);
		}
		assignment_details();
		break;
		
	//If the mode is view assignment details, call up all the details for an assignment, including grade entries if I'm an instructor.
	case 'assignment_details':
		
		assignment_details();
	
		break;
		
		
	case 'add_assignment':
		
		if(!$course_membership_data) {
			$course_membership_data = get_course_membership($_REQUEST['course_id'], $user->id);
		}
		if($course_membership_data) {
			if($course_membership_data['permissions'] & $bitfields['CLASS_PERMISSIONS']['MANAGEASSIGNMENTS'] || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
				//Okay, I have permissions. Go ahead and add the assignment.
				if(add_assignment($_REQUEST['course_id'], $_REQUEST['name'], $_REQUEST['code'], $_REQUEST['description'], $_REQUEST['max_points'], strtotime($_REQUEST['due_date']), $_REQUEST['weight'])) {
					$local_notices[] = array('title'=>'Success','content'=>$strings['SUCCESS_ASSIGNMENT_ADDED']);
				}
				else {
					throw new DatabaseException("Failed to add assignment. MySQL said: ".$db->error);
				}
			}
			else {
				$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
			}
		}
		else {
				$local_errors[] = array('title'=>'Error','content'=>$strings['REQUIRED_ITEM_NOTFOUND']);
		}
		list_assignments();
		break;
	case 'delete_assignment':
	//Check to see if I have permissions. Only then do I proceed
		if(!$assignment_data) {
			$assignment_data = get_assignment(@$_REQUEST['id']);
		}
		//Does the assignment even exist?
		if($assignment_data) {
		
			if(!$course_membership_data) {
				$course_membership_data = get_course_membership($assignment_data['course_id'], $user->id);
			}
			
			if($course_membership_data['permissions'] & $bitfields['CLASS_PERMISSIONS']['MANAGEASSIGNMENTS'] || $user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CLASSPERMISSIONSEVERYWHERE']) {
				//Okay, I have permissions. Go ahead and delete the assignment.
				if($_REQUEST['confirm']) {
					if(delete_assignment($_REQUEST['id'])){
						$local_notices[] = array('title'=>'Success','content'=>$strings['SUCCESS_ASSIGNMENT_DELETED']);
					}
					else {
						throw new DatabaseException("Failed to delete assignment. MySQL said: ".$db->error);
					}
				}
				else {
					$local_errors[] = array('title'=>'Warning','content'=>$strings['ASSIGNMENT_DELETE_CONFIRM']."<br />"."<a href=\"assignments.php?mode=delete_assignment&confirm=1&id=".$_REQUEST['id']."\">".$strings['CONFIRM_ACTION']."</a>");
				}
			}
			else {
				$local_errors[] = array('title'=>'Permission Error','content'=>$strings['PERMISSION_DENIED']);
			}
		}
		else {
			$local_errors[] = array('title'=>'Error','content'=>$strings['REQUESTED_ITEM_NOTFOUND']);
		}
		list_assignments();
		break;
	default:
		//If there's no mode, I'm going to list all assignments from all courses.
		list_assignments();
		break;
		

}

?>
