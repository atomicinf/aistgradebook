<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

common.php
Common file run before everything else.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/

$time_start = microtime(TRUE);

$global_errors = array();
$global_notices = array();
$local_errors = array();
$local_notices = array();

require_once('lib_db.php');
require_once('lib_errorlogging.php');
require_once('constants.php');
require_once('strings.php');
require_once('config.php');
require_once('functions.php');
require_once('lib_auth.php');
require_once('lib_courses.php');
require_once('lib_assignments.php');
require_once('lib_announcements.php');
require_once('../Smarty/libs/Smarty.class.php');

//Connect to the MySQL database

$db = new mysqli($sql_hostname,$sql_username,$sql_password,$sql_database);

//Connection errors are fatal. Display an error message
if (mysqli_connect_error()) { 
?><html>
<head>
<title>AIStGradebook <?php echo AISTGRADEBOOK_VERSION; ?></title>
</head>
<body style="font-family:monospace">
<p>Failed to connect to database: (<?php echo mysqli_connect_errno(); ?>) <?php echo mysqli_connect_error(); ?></p>
</body>
</html>
<?php
	die();
}

//Warn user if I'm not HTTPS
if(@$_SERVER['HTTPS'] != "on") {
	$global_errors[] = array('title'=>'WARNING','content'=>$strings['NO_HTTPS_WARNING']."<br /><br /><a href=\"https://atomicinf.homelinux.org".$_SERVER["PHP_SELF"]."\">".$strings['SECURE_SITE_LINK']."</a>");
}

//Initiate auth
$user = new User();

//Set up the templating engine
$tpl = new Smarty();

$tpl->template_dir = '/var/www/aistgradebook_devel/templates';
$tpl->compile_dir = '/var/www/aistgradebook_devel/templates_c';
$tpl->cache_dir = '/var/www/aistgradebook_devel/cache';
$tpl->config_dir = '/var/www/aistgradebook_devel/configs';

$tpl->left_delimiter = "{%";
$tpl->right_delimiter = "%}";

$tpl->register_function('percent2letter', 'tpl_percent_to_letter');

//assign template vars
$tpl->assign('PAGENAME',$sitename);
$tpl->assign('AISTGRADEBOOK_VERSION',AISTGRADEBOOK_VERSION);
$tpl->assign('TIME_START',$time_start);
$tpl->assign('PAGE_URL',"http://".$_SERVER['SERVER_NAME'].htmlspecialchars($_SERVER['REQUEST_URI']));

$tpl->assign('A_BITFIELDS',$bitfields);

//if https, assign appropriate template vars
$tpl->assign('V_IS_HTTPS',(@$_SERVER['HTTPS'] == "on") ? 1 : 0);
$tpl->assign('V_IS_X509',(@$_SERVER['SSL_CLIENT_VERIFY'] == "SUCCESS" && $user->email == @$_SERVER['SSL_CLIENT_S_DN_Email'] && $user->user_permissions & GLOBAL_PERMISSIONS_X509LOGIN) ? 1 : 0);

//assign the strings table to template
foreach($strings as $key => $value) {
	$tpl->assign('S_'.$key,$value);
}

//if logged in, say so
if($user->id == 0) {
	$tpl->assign('V_IS_LOGGED_IN',0);
}
else {
	$tpl->assign('V_IS_LOGGED_IN',1);
	$tpl->assign('V_USERNAME',$user->username);
	//$tpl->assign('V_USER_TYPE',$user->user_type);
	$tpl->assign('V_USER_FULLNAME',$user->name);
	$tpl->assign('V_USER_FIRSTNAME',strtok($user->name,' '));
	$tpl->assign('V_USER_LASTNAME',strtok(' '));
	$tpl->assign('V_EMAIL',$user->email);
}

//In theory, it shouldn't hurt to export global permission variables now.
//These can be manually overridden later
foreach($bitfields['GLOBAL_PERMISSIONS'] as $key => $value) {
			if($user->user_permissions & $value) {
				$tpl->assign('V_PERMISSIONS_'.$key,1);
			}
}

/*
 * Global errors and notices are used for notifying the user of persistent conditions.
 * They should be only be added by libraries. Files should spawn their own errors
 * or assign to A_LOCAL_ERRORS or A_LOCAL_NOTICES instead.
 */
$tpl->assign('A_GLOBAL_ERRORS',$global_errors);
$tpl->assign('A_GLOBAL_NOTICES',$global_notices);
?>
