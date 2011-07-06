<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

lib_courses.php
Code for handling courses.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/

/*
 * Adds a course.
 */

function add_course($name, $name_short, $instructor, $term, $description, $info) {
	global $db;
	$sql = "INSERT INTO `courses` SET `course_name`='".$db->escape_string($name)."',`course_name_short`='".$db->escape_string($name_short)
	. "',`instructor`='".$db->escape_string($instructor)."',`term`='".$db->escape_string($term)
	. "',`description`='".$db->escape_string($description)."', `info`='".$db->escape_string($info)
	. "'";
	return db_query_simple($sql);
}

/*
 * Edits a course.
 */

function update_course($id, $name, $name_short, $instructor, $term, $description, $info) {
	global $db;
	$sql = "UPDATE `courses` SET `course_name`='".$db->escape_string($name)."',`course_name_short`='".$db->escape_string($name_short)
	. "',`instructor`='".$db->escape_string($instructor)."',`term`='".$db->escape_string($term)
	. "',`description`='".$db->escape_string($description)."', `info`='".$db->escape_string($info)
	. "' WHERE `id`='".$db->escape_string($id)."'";
	return db_query_simple($sql);
}

/*
 * Deletes a course by id.
 */

function delete_course($id) {
	global $db;
	$sql = "DELETE FROM `courses` WHERE `id`='".$db->escape_string($id)."'";
	return db_query_simple($sql);
}

/*
 * Registers a user with a course.
 */
function register_course_user($user_id, $course_id, $type, $is_active, $permissions) {
	global $db;
	$sql = "INSERT INTO `course_memberships` SET `user_id`='".$db->escape_string($user_id)
	. "', `course_id`='".$db->escape_string($course_id)."', `type`='".$db->escape_string($type)
	. "', `is_active`='".$db->escape_string($is_active)."', `permissions`='"
	. $db->escape_string($permissions) . "'";
	return db_query_simple($sql);
}

/*
 * Unregisters a user with a course.
 */
function unregister_course_user($id) {
	global $db;
	$sql = "DELETE FROM `course_memberships` WHERE `id`='".$db->escape_string($id)."'";
	return db_query_simple($sql);
}

/*
 * Gets all courses a user is registered with.
 * $include_inactive: if TRUE, includes inactive memberships (not implemented yet).
 */
function get_user_courses($user_id, $include_inactive=FALSE) {
	global $db;
	$sql = "SELECT `courses`.*, `course_memberships`.`type`, `course_memberships`.`permissions` FROM `courses` INNER JOIN `course_memberships` "
	. "ON `course_memberships`.`course_id`=`courses`.`id` "
	. "WHERE `course_memberships`.`user_id`='".$db->escape_string($user_id)."'";
	return db_block_query($sql);
}

/*
 * Gets all courses.
 */
function get_all_courses($user_id) {
	global $db;
	$sql = "SELECT `courses`.*, `course_memberships`.`type`, `course_memberships`.`is_active`, `course_memberships`.`permissions` ".
			"FROM `courses` ".
			"LEFT JOIN `course_memberships` ON `course_memberships`.`course_id`=`courses`.`id` ".
			"WHERE EXISTS (SELECT * FROM `course_memberships` WHERE `user_id`='".$db->escape_string($user_id)."' AND `course_id`=`courses`.`id`) ".
			"AND `user_id`='".$db->escape_string($user_id)."' ".
				
			"UNION ".
			
			"SELECT `courses`.*, NULL AS `type`, NULL AS `is_active`, NULL AS `permissions` ".
			"FROM `courses` ".
			"LEFT JOIN `course_memberships` ON `course_memberships`.`course_id`=`courses`.`id` ".
			"WHERE NOT EXISTS (SELECT * FROM `course_memberships` WHERE `user_id`='".$db->escape_string($user_id)."' AND `course_id`=`courses`.`id`) ".
			
			"ORDER BY `id` ASC";
	return db_block_query($sql);
}

/*
 * Gets a specific course by ID.
 */
function get_course($id) {
	global $db;
	$sql = "SELECT * FROM `courses` WHERE `id`='".$db->escape_string($id)."'";
	return db_query($sql);
}

/*
 * Gets a specific course membership record by course ID and user ID.
 */
function get_course_membership($course_id, $user_id) {
	global $db;
	$sql = "SELECT * FROM `course_memberships` WHERE `course_id`='".$db->escape_string($course_id)."'"
	.		" AND `user_id`='".$db->escape_string($user_id)."'";
	return db_query($sql);
}

/*
 * Returns a class list.
 */
function get_class_list($course_id) {
	global $db;
	$sql = "SELECT `users`.`id`, `users`.`name`, `users`.`title`, `course_memberships`.`type` FROM `users` ".
			"INNER JOIN `course_memberships` ON `users`.`id`=`course_memberships`.`user_id` ".
			"WHERE `course_memberships`.`course_id` ='".$db->escape_string($course_id)."' AND `course_memberships`.`is_active`='1' ".
			"ORDER BY `type` DESC, `name` ASC";
	return db_block_query($sql);
}

/*
 * Returns the size of the class list.
 */
function get_class_size($course_id) {
	global $db;
	$sql = "SELECT COUNT(*) AS `count` FROM `users` ".
			"INNER JOIN `course_memberships` ON `users`.`id`=`course_memberships`.`user_id` ".
			"WHERE `course_memberships`.`course_id` ='".$db->escape_string($course_id)."' AND `course_memberships`.`is_active`='1' ";
	$temp = db_query($sql);
	return $temp['count'];
}

?>
