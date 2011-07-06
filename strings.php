<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

strings.php
Defining common strings.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/

$strings = array(

	"NO_HTTPS_WARNING" => "<div style='float:left;font-size:200%;font-weight:bold;margin-right:10px;'>STOP!</div>You are currently accessing AIStGradebook over a non-secure connection. Data transmitted has not been encrypted and may be exposed to monitoring by a third party. It is strongly recommended that you access the secure version of this site; note that doing so will log you out if you are logged in.",
	"SECURE_SITE_LINK" => "Click here to access the secure version of this page.",

	"BAD_USERNAME" => "The chosen username does not exist in the system.",
	"BAD_PASSWORD" => "The provided password is incorrect. For assistance contact the local administrator.",
	"NO_LOGIN_PERMISSION" => "Your account does not have permission to log in using that method.",
	"LOGIN_SUCCESSFUL" => "Successfully logged in.",
	"LOGOUT_SUCCESSFUL" => "Successfully logged out.",
	"ALREADY_LOGGED_IN" => "You are already logged in. Please logout first.",
	"HOME_LINK" => "<br /><br /><a href=\"index.php\">Home</a>",
	
	"NOT_LOGGED_IN" => "Not logged in",
	"NOT_LOGGED_IN_SECURE" => "<span style=\"color:green;font-weight:bold;\">Secure</span> connection - not logged in",
	"LOGGED_IN_AS" => "Logged in as",
	"LOGGED_IN_AS_SECURE" => "<span style=\"color:green;font-weight:bold;\">Securely</span> logged in as",
	"LOGGED_IN_WITH_X509" => "with <span style=\"color:green;font-weight:bold;\">valid</span> certificate",

	"PERMISSION_DENIED" => "You do not have the necessary permissions to perform this action.",

	"REQUIRED_ARGUMENT_NOTFOUND" => "AIStGradebook, or the user, attempted to call this page or method without a required argument.",
	"REQUESTED_ITEM_NOTFOUND" => "The requested item does not exist.",

	"NO_COURSES" => "No courses exist.",

	"SUCCESS_COURSE_ADDED" => "Course successfully added.",
	"SUCCESS_COURSE_UPDATED" => "Course successfully updated.",
	"SUCCESS_COURSE_DELETED" => "Course successfully deleted.",
	
	"SUCCESS_ASSIGNMENT_ADDED" => "Assignment successfully added.",
	"SUCCESS_ASSIGNMENT_UPDATED" => "Assignment successfully updated.",
	"SUCCESS_ASSIGNMENT_DELETED" => "Assignment successfully deleted.",
	
	"ASSIGNMENT_DELETE_CONFIRM" => "Deleting an assignment is permanent and cannot be undone!",
	
	"CONFIRM_ACTION" => "Click here to confirm.",
	
	"REQUIRED_ITEM_NOTFOUND" => "The requested item depends on an object that no longer exists.",

);

?>
