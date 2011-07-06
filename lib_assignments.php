<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

lib_assignments.php
Code for handling assignments.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/

/*
 * Adds an assignment.
 */

function add_assignment($course_id, $name, $code, $description, $max_points, $due_date, $weight) {
	global $db;
	$sql = "INSERT INTO `assignments` SET `course_id`='".$db->escape_string($course_id)
	. "', `assignment_name`='".$db->escape_string($name)."', `assignment_code`='".$db->escape_string($code)
	. "', `description`='".$db->escape_string($description)
	. "', `max_points`='".$db->escape_string($max_points)."',`due_date`='".$db->escape_string($due_date)
	. "', `weight`='".$db->escape_string($weight)."'";
	return db_query_simple($sql);
}

/*
 * Edits assignment details.
 */

function edit_assignment($id, $course_id, $name, $code, $description, $max_points, $due_date, $weight) {
	global $db, $local_notices;
	$sql = "UPDATE `assignments` SET `course_id`='".$db->escape_string($course_id)
	. "', `assignment_name`='".$db->escape_string($name)."', `assignment_code`='".$db->escape_string($code)
	. "', `description`='".$db->escape_string($description)
	. "', `max_points`='".$db->escape_string($max_points)."',`due_date`='".$db->escape_string($due_date)
	. "', `weight`='".$db->escape_string($weight)."' WHERE `id`='".$db->escape_string($id)."'";
	//$local_notices[] = array('title'=>'SQL Debug','content'=>$sql);
	return db_query_simple($sql);
}

/*
 * Deletes an assignment.
 */

function delete_assignment($id) {
	global $db;
	$sql = "DELETE FROM `assignments` WHERE `id`='".$db->escape_string($id)."'";
	return db_query_simple($sql);
}

/*
 * Retrieves a specific assignment by id, includes course name.
 */

function get_assignment($id) {
	global $db;
	$sql = "SELECT `assignments`.*,`courses`.`course_name` FROM `assignments` LEFT JOIN `courses` ON `courses`.`id`=`assignments`.`course_id` WHERE `assignments`.`id`='".$db->escape_string($id)."'";
	return db_query($sql);
}

/*
 * Adds an assignment grade.
 */
function add_assignment_grade($assignment_id, $user_id, $points, $is_overdue, $comments) {
	global $db, $local_notices;
	//$local_notices[] = array('title'=>'add_assignment_grade() Debug','content'=>"assignment_id=$assignment_id, user_id=$user_id, points=$points, is_null(points)=".(int)is_null($points).", is_overdue=$is_overdue, comments=$comments");
	$sql = "INSERT INTO `assignment_grades` SET `assignment_id`='".$db->escape_string($assignment_id)
	."', `user_id`='".$db->escape_string($user_id)."', `unique_id`='".$db->escape_string($assignment_id)."-".$db->escape_string($user_id)."', `points`=".($points === NULL ? 'NULL' : $db->escape_string($points))
	.", `is_overdue`='".$db->escape_string($is_overdue)."', `comments`='".$db->escape_string($comments)."' ON DUPLICATE KEY UPDATE `points`=".($points === NULL ? 'NULL' : $db->escape_string($points))
	.", `is_overdue`='".$db->escape_string($is_overdue)."', `comments`='".$db->escape_string($comments)."'";
	//$local_notices[] = array('title'=>'SQL Debug','content'=>$sql);
	return db_query_simple($sql);
}

/*
 * Edits an assignment grade.
 */

function edit_assignment_grade($id, $points, $is_overdue, $comments) {
	global $db;
	$sql = "UPDATE `assignment_grades` SET `points`=".($points === NULL ? 'NULL' : $db->escape_string($points))
	.", `is_overdue`='".$db->escape_string($is_overdue)."', `comments`='".$db->escape_string($comments)."' WHERE `id`='".$db->escape_string($id)
	."'";
	return db_query_simple($sql);
}

/*
 * Deletes an assignment grade.
 */

function delete_assignment_grade($id) {
	global $db;
	$sql = "DELETE FROM `assignment_grades` WHERE `id`='".$db->escape_string($id)."'";
	return db_query_simple($sql);
}

/*
 * Gets all assignments in a course.
 * If $order, then fetch upcoming assignments; otherwise fetch recent ones.
 */

function get_course_assignments($course_id, $order = 0, $count=20, $start=0) {
	global $db;
	$sql = "SELECT `assignments`.* FROM `assignments`";
	$sql .= " WHERE `course_id`='".$db->escape_string($course_id)."'";
	$sql .= (($order) ? " AND `due_date` > ".time() : " AND `due_date` < ".time());
	$sql .= (($order) ? " ORDER BY `due_date` ASC" : " ORDER BY `due_date` DESC");
	if($count) {
		$sql .= " LIMIT ".$start.", ".$count;
	}
	return db_block_query($sql);
}

/*
 * Gets all assignments completed by a student in a given course.
 */

function get_student_assignments($user_id, $course_id, $count=NULL, $start=0)
{
	global $db;
	$sql = "SELECT `assignments`.*, `assignment_grades`.*, `courses`.`course_name`, `courses`.`course_name_short` ".
	"FROM `assignments` ".
	"INNER JOIN `assignment_grades` ON `assignment_grades`.`assignment_id`=`assignments`.`id` ".
	"INNER JOIN `course_memberships` ON `course_memberships`.`course_id`=`assignments`.`course_id` ".
	"INNER JOIN `courses` ON `course_memberships`.`course_id`=`courses`.`id` ".
	"WHERE `course_memberships`.`user_id`='".$db->escape_string($user_id)."' ".
	"AND `course_memberships`.`course_id`='".$db->escape_string($course_id)."' ".
	"AND `course_memberships`.`type`='0'";
	if($count) {
		$sql .= " LIMIT ".$start.", ".$count;
	}
	return db_block_query($sql);
}

/*
 * Gets the next few upcoming assignments for a user (index page style).
 * (This function is currently agnostic of whether or not an assignment is completed)
 * Note that it's the function caller's responsibility to differentiate between student and
 * instructor-type assignments.
 */

function get_upcoming_student_assignments_short($user_id, $count=20)
{
	global $db;
	$sql = "SELECT `assignments`.`id`, `assignments`.`assignment_name`, `assignments`.`due_date`, `courses`.`id` AS `course_id`, ".
	"`courses`.`course_name_short`, `course_memberships`.`type`, `course_memberships`.`permissions` ".
	"FROM `assignments` INNER JOIN `course_memberships` ".
	"ON `course_memberships`.`course_id`=`assignments`.`course_id` ".
	"INNER JOIN `courses` ON `course_memberships`.`course_id`=`courses`.`id` ".
	"WHERE `course_memberships`.`user_id`='".$db->escape_string($user_id)."' AND ".
	"`assignments`.`due_date` > UNIX_TIMESTAMP() ORDER BY `assignments`.`due_date` ".
	"ASC LIMIT ".$db->escape_string($count);
	return db_block_query($sql);
}

/*
 * Gets all overdue assignments belonging to a student.
 * Assignments are overdue only if they are past due, marked overdue by an instructor,
 * and not assigned a grade.
 */

function get_overdue_student_assignments($user_id, $count=NULL, $start=0)
{
	global $db;
	$sql = "SELECT `assignments`.`id`, `assignments`.`assignment_name`, `assignments`.`due_date`, `courses`.`id` AS `course_id`, `courses`.`course_name_short` ".
	"FROM `assignments` ".
	"INNER JOIN `assignment_grades` ON `assignment_grades`.`assignment_id`=`assignments`.`id` ".
	"INNER JOIN `course_memberships` ON `course_memberships`.`course_id`=`assignments`.`course_id` ".
	"INNER JOIN `courses` ON `course_memberships`.`course_id`=`courses`.`id` ".
	"WHERE `assignment_grades`.`user_id`='".$db->escape_string($user_id)."' AND `assignments`.`due_date` < UNIX_TIMESTAMP() ".
	"AND `course_memberships`.`user_id`='".$db->escape_string($user_id)."' AND `assignment_grades`.`is_overdue`='1' AND `assignment_grades`.`points` IS NULL ".
	"AND `course_memberships`.`type`='0'";
	if($count) {
		$sql .= " LIMIT ".$start.", ".$count;
	}
	return db_block_query($sql);
}

/*
 * SELECT `assignments`.*, `courses`.`course_name` FROM `assignments`
LEFT JOIN `course_memberships` ON `course_memberships`.`course_id` = `assignments`.`course_id`
LEFT JOIN `courses` ON `courses`.`id` = `course_memberships`.`course_id`
WHERE `course_memberships`.`user_id`=2 AND `course_memberships`.`type` = 0
ORDER BY `course_id` ASC, `due_date` DESC
 */

/*
 * Returns a gradebook array for a course.
 * 
 * The gradebook array has three keys: "students_list", "assignments_list", and "grade_table".
 * students_list is an array with keys being user IDs and values being student names.
 * assignments_list is an array with keys being assignment IDs and values being assignment names.
 * grade_table is a two-dimensional array defined as $grade_table[$user_id][$assignment_id].
 */

function get_gradebook($course_id) {
	global $db, $user;
	$sql =	"SELECT `assignment_grades`.*, `assignments`.`assignment_code`, ".
			"`assignments`.`max_points`, `assignments`.`weight`, `users`.`name` ".
			"FROM `assignment_grades` ".
			"INNER JOIN `assignments` ON `assignment_grades`.`assignment_id`=`assignments`.`id` ".
			"INNER JOIN `users` ON `assignment_grades`.`user_id`=`users`.`id` ".
			"WHERE `assignments`.`course_id`=1 ".
			"ORDER BY `user_id` ASC, `assignment_id` ASC;";
	$gradebook_raw = db_block_query($sql);
}

/*
 * Returns a specific grade by assignment_id and user_id.
 */

function get_assignment_grade($assignment_id, $user_id) {
	global $db, $user;
	$sql =	"SELECT `assignment_grades`.* FROM `assignment_grades` " .
			"WHERE `assignment_id` = '" . $db->escape_string($assignment_id) . "'" .
			"AND `user_id` = '" . $db->escape_string($user_id) . "'";
	return db_query($sql);
}

/*
 * Returns a student's average grade in a course.
 */

function get_student_grade($user_id, $course_id) {
	//Known bug: this function returns an inaccurate grade when a grade is entered and later nulled.
	//Will be fixed later.
	global $db, $user;
	$sql = "SELECT AVG(`assignment_grades`.`points`*`assignments`.`weight`) / AVG(`assignments`.`max_points`*`assignments`.`weight`) * 100 AS `grade_percent` FROM `assignment_grades` INNER JOIN `assignments` ON `assignments`.`id` = `assignment_grades`.`assignment_id` WHERE `assignment_grades`.`user_id` = '".$db->escape_string($user_id)."' AND `assignments`.`course_id` = '".$db->escape_string($course_id)."' AND `assignment_grades`.`points` IS NOT NULL";
	return db_query($sql);
}

/*
 * Gets the number of grades entered for an assignment.
 */
function get_number_graded($assignment_id) {
	global $db, $user, $local_notices;
	//$sql = "SELECT COUNT(`points`) AS `count` FROM `assignment_grades` WHERE `assignment_id`='".$db->escape_string($assignment_id)."'";
	$sql = "SELECT COUNT(`points`) AS `count` FROM `users` INNER JOIN `course_memberships` ON `users`.`id`=`course_memberships`.`user_id` INNER JOIN `assignment_grades` ON `assignment_grades`.`user_id`=`users`.`id` INNER JOIN `assignments` ON `assignment_grades`.`assignment_id`=`assignments`.`id` INNER JOIN `courses` ON `courses`.`id`=`course_memberships`.`course_id` AND `courses`.`id`=`assignments`.`course_id` WHERE `assignment_grades`.`assignment_id`='".$db->escape_string($assignment_id)."' AND `assignment_grades`.`points` IS NOT NULL";
	//$local_notices[] = array("title"=>"SQL Debug","content"=>$sql);
	$temp = db_query($sql);
	return $temp['count'];
}

/*
 * Translate numerical grades into letter grades.
 * 
 * Takes a percentage (0 to 100) as input. An input of boolean FALSE is assumed to represent infinity.
 * Returns a string with letter grade or a dash (-) as output.
 */

function percent_to_letter_grade($input) {
	//Right now the conversion table is hardcoded.
	//Eventually we want the ability to get the table from a per-course database entry
	
	if($input === NULL) {
		return "-";
	}
	
	$grade_conversion_table = array(
		array("A+", 97),
		array("A", 93),
		array("A-", 90),
		array("B+", 87),
		array("B", 83),
		array("B-", 80),
		array("C+", 77),
		array("C", 73),
		array("C-", 70),
		array("D", 60),
		array("F", 0),
	);
	
	//Handle the infinite / NaN case
	if($input === false) {
		return "-";
	}
	
	foreach($grade_conversion_table as $item) {
		if($item[1] <= $input) {
			return $item[0];
		}
	}
	
	//Unexpected case
	return "--";
}

/* Smarty wrapper around percent_to_letter_grade */
function tpl_percent_to_letter($params, &$smarty) {
	return percent_to_letter_grade($params['percent']);
}

?>
