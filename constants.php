<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

constants.php
Defining common constants.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/

$bitfields = array();

define('AISTGRADEBOOK_VERSION','0.1.9');

define('GLOBAL_PERMISSIONS_LOGIN',							1);
define('GLOBAL_PERMISSIONS_MANAGECOURSES',					2); //Admin
define('GLOBAL_PERMISSIONS_MANAGEUSERS',					4); //Admin
define('GLOBAL_PERMISSIONS_SENDMESSAGE',					8);
define('GLOBAL_PERMISSIONS_CLASSPERMISSIONSEVERYWHERE',		16); //Admin
define('GLOBAL_PERMISSIONS_SEEALLCOURSES',					32); //Admin
define('GLOBAL_PERMISSIONS_X509LOGIN',						64);
define('GLOBAL_PERMISSIONS_CREATEGLOBALANNOUNCEMENT',		128);

$bitfields['GLOBAL_PERMISSIONS'] = array(

'LOGIN'								=> 1,
'MANAGECOURSES'						=> 2, //Admin
'MANAGEUSERS'						=> 4, //Admin
'SENDMESSAGE'						=> 8,
'CLASSPERMISSIONSEVERYWHERE'		=> 16, //Admin
'SEEALLCOURSES'						=> 32, //Admin
'X509LOGIN'							=> 64,
'CREATEGLOBALANNOUNCEMENT'			=> 128,
'CREATEPRIORITYANNOUNCEMENT'		=> 256,

);

define('CLASS_PERMISSIONS_VIEWOWNGRADES',					1);
define('CLASS_PERMISSIONS_VIEWALLGRADES',					2); //Instructor
define('CLASS_PERMISSIONS_SETGRADES',						4); //Instructor
define('CLASS_PERMISSIONS_VIEWASSIGNMENTSDETAILED',			8);
define('CLASS_PERMISSIONS_MANAGEASSIGNMENTS',				16); //Instructor
define('CLASS_PERMISSIONS_CREATEANNOUNCEMENT',				32); //instructor
define('CLASS_PERMISSIONS_VIEWCLASSLIST',					64);

$bitfields['CLASS_PERMISSIONS'] = array(

'VIEWOWNGRADES'				=> 1,
'VIEWALLGRADES'				=> 2,
'SETGRADES'					=> 4,
'VIEWASSIGNMENTSDETAILED'	=> 8,
'MANAGEASSIGNMENTS'			=> 16,
'CREATEANNOUNCEMENT'		=> 32,
'VIEWCLASSLIST'				=> 64,

);

define('COURSEMEMBERSHIPS_TYPE_STUDENT', 0);
define('COURSEMEMBERSHIPS_TYPE_ASSISTANT', 1);
define('COURSEMEMBERSHIPS_TYPE_INSTRUCTOR', 2);

define('USER_FLAGS_MUSTCHANGEPASSWORD',						1);
define('USER_FLAGS_CANCHANGEUSERNAME',						2);

define('BAD_USERNAME',-1);
define('BAD_PASSWORD',-2);
define('NO_LOGIN_PERMISSION',-3);

?>
