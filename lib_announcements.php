<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

lib_announcements.php
Code for handling announcements.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/

/*
 * Creates a new announcement for a particular course.
 */
function add_announcement($course_id,$title,$text,$priority = 0) {
	global $db, $user, $local_notices, $bitfields;
	//Check for proper permissions on global announcements - spoofing could potentially be problematic.
	if(!($user->user_permissions & $bitfields['GLOBAL_PERMISSIONS']['CREATEGLOBALANNOUNCEMENT']) && $course_id == 0) {
		return false;
	}
	else {
		$sql = "INSERT INTO `announcements` SET `announcement_course`='".$db->escape_string($course_id)."', "
		.      "`announcement_title` = '".$db->escape_string($title)."', `announcement_time` = '".time()."', "
		.      "`announcement_priority` = '".$db->escape_string($priority)."', `announcement_text` = '".$db->escape_string($text)."', "
		.      "`user_id`='".$db->escape_string($user->id)."'";
		//$local_notices[] = array('title'=>'SQL Debug','content'=>$sql);
		return db_query_simple($sql);
	}
}

/*
 * Edits an announcement details.
 */
function edit_announcement($id,$title,$text,$priority = 0) {
	global $db, $user;
	//Check for proper permissions on global announcements - spoofing could potentially be problematic.
	if(!($user->permissions & $bitfields['GLOBAL_PERMISSIONS']['CREATEGLOBALANNOUNCEMENT']) && $course_id == 0) {
		return false;
	}
	else {
		$sql = "INSERT INTO `announcements` SET `announcement_title` = '".$db->escape_string($title).", `announcement_time` = '".time()."', "
		.      "`announcement_priority` = '".$db->escape_string($priority)."', `announcement_text` = '".$db->escape_string($text)."', "
		.      "`user_id`='".$db->escape_string($user->id)."' WHERE `id`='".$db->escape_string($id)."'";
		return db_query_simple($sql);
	}
}
 
/*
 * Deletes an announcement from the record.
 */

function delete_announcement($id) {
	global $db;
	$sql = "DELETE FROM `announcements` WHERE `id`='".$db->escape_string($id)."'";
	return db_query_simple($sql);
}
 
/*
 * Gets the last few announcements for a particular user.
 */
 function get_announcements_user($user_id, $limit=8, $start=0)
 {
 	global $db;
 	$sql = "SELECT `announcements`.*, `courses`.`course_name`, `users`.`name` AS `announcement_poster` FROM `announcements` ".
			"LEFT JOIN `course_memberships` ON `course_memberships`.`course_id`=`announcements`.`announcement_course` ".
 			"LEFT JOIN `courses` ON `courses`.`id` = `course_memberships`.`course_id` ".
			"LEFT JOIN `users` ON `users`.`id` = `announcements`.`user_id` ".
			"WHERE `announcement_course`=0 OR `course_memberships`.`user_id`='".$db->escape_string($user_id)."' ".
			"ORDER BY `announcement_time` DESC ".
			"LIMIT ".$start.", ".$limit;
 	return db_block_query($sql);
 }
 
 /*
  * Gets the last few announcements for a particular course.
  */
 function get_announcements_course($course_id, $limit=8, $start=0)
 {
 	global $db;
 	$sql =	"SELECT `announcements`.*, `courses`.`course_name`, `users`.`name` AS `announcement_poster` FROM `announcements` ".
			"LEFT JOIN `courses` ON `courses`.`id` = `announcements`.`announcement_course` ".
			"LEFT JOIN `users` ON `users`.`id` = `announcements`.`user_id` ".
			"WHERE `announcement_course`='0' AND `announcement_priority`='1' OR `announcement_course`='".$db->escape_string($course_id)."' ".
			"ORDER BY `announcement_time` DESC ".
			"LIMIT ".$db->escape_string($start).", ".$db->escape_string($limit);
 	return db_block_query($sql);
 }
 
/*
 * Gets the last few announcements unconditionally.
 */
 function get_announcements_all($limit=8, $start=0)
 {
 	global $db;
 	$sql = "SELECT * FROM `announcements` ".
			"WHERE 1 ".
			"ORDER BY `announcement_time` DESC ".
			"LIMIT ".$limit;
 	return db_block_query($sql);
 }
 
 
