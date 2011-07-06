<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

login.php
The login page.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/
require_once('common.php');

if(@$_POST['method'] == 'login') {
	
	//Attempt to login with the specified credentials
	
	$status = $user->login($_POST['username'],$_POST['password']);
	
	if($status == BAD_USERNAME) {
		$tpl->assign('status','BAD_USERNAME');
	}
	else if($status == BAD_PASSWORD) {
		$tpl->assign('status','BAD_PASSWORD');
	}
	else if($status == NO_LOGIN_PERMISSION) {
		$tpl->assign('status','NO_LOGIN_PERMISSION');
	}
	else {
		$tpl->assign('status','SUCCESS');
	}
	
}

else if(@$_REQUEST['method'] == 'logout') {
	
	//Log out
	$user->logout();
	
}

//update the template vars
if($user->id == 0) {
	$tpl->assign('V_IS_LOGGED_IN',0);
}
else {
	$tpl->assign('V_IS_LOGGED_IN',1);
	$tpl->assign('V_USERNAME',$user->username);
	$tpl->assign('V_USER_FULLNAME',$user->name);
	$tpl->assign('V_USER_FIRSTNAME',strtok($user->name,' '));
	$tpl->assign('V_USER_LASTNAME',strtok(' '));
	$tpl->assign('V_EMAIL',$user->email);
}

//print_r($user);

$tpl->display('login.html');

?>