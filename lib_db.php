<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

lib_db.php
Common database functions.

WARNING: This is draft quality code.
Do not use in a production environment.
*************************/

/*
 * Executes a query and returns an associative array.
 */
function db_query($sql) {
	global $db;
	$sql_result = $db->query($sql);
	if(!$sql_result) {
		return FALSE;
	}
	return strip_slashes_recursive($sql_result->fetch_assoc());
}

/*
 * Executes a query and returns a sql_result object.
 */
function db_query_simple($sql) {
	global $db;
	return $db->query($sql);
}

/*
 * Executes a block query and returns a 2-dimensional array.
 * Outer array is numeric, inner arrays are associative.
 */
function db_block_query($sql) {
	global $db;
	$sql_result = $db->query($sql);
	if(!$sql_result) {
		return FALSE;
	}
	$result = array();
	while($result[] = $sql_result->fetch_assoc());
	//Trim weird blank entries
	for($i=0;$i<sizeof($result);$i++) {
		if(sizeof($result[$i]) == 0) {
			unset($result[$i]);
		}
	}
	return strip_slashes_recursive($result);
}

?>