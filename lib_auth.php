<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

lib_auth.php
Defines the user object.

WARNING: This is draft quality code.
Do not use in a production environment.
*************************/

require_once('constants.php');
require_once('config.php');

//$global_notices[] = array('title'=>'Debug','content'=>'lib_auth.php');

class User {
	public $id;
	public $username;
//	public $user_type;
	public $password;
	public $email;
	public $user_permissions;
	public $title;
	public $name;
	
	/*
	 * Loads the existing session if present, otherwise creates a blank session. 
	 **/
	public function __construct() {
		global $db, $global_notices;
		//$global_notices[] = array('title'=>'Debug','content'=>'Making a User');
		//Attempt to load existing session data
		session_name(SESSIONNAME);
		session_start();
		if(@$_SESSION['user_id']) {
			//$global_notices[] = array('title'=>'Debug','content'=>'User ID found');
			//Prevent crossover between HTTP and HTTPS sessions
			if(($_SESSION['https'] && @$_SERVER['HTTPS'] == "on") || (!$_SESSION['https'] && @$_SERVER['HTTPS'] != "on")) {
				//$global_notices[] = array('title'=>'Debug','content'=>'SSL mode is consistent');
				//Prevent crossover between X.509 and regular logins
				if(($_SESSION['x509'] && @$_SERVER['SSL_CLIENT_VERIFY'] == "SUCCESS") || (!$_SESSION['x509']/* && @$_SERVER['SSL_CLIENT_VERIFY'] != "SUCCESS"*/)) {
					//$global_notices[] = array('title'=>'Debug','content'=>'X.509 mode is consistent, loading user data');
					//Existing session ID found. Load details
					//Has the session expired?
					/*if(time() > $_SESSION['expire_time']) {
						//Destroy the current session
						session_destroy();
						//Start a blank session
						session_start();
					}*/
					//Else load up existing user data
					//else {
					
					//Add extra layer of protection for X.509 authers to prevent certificate spoofing
					if($_SESSION['x509']) {
						$sql_result = $db->query("SELECT * FROM `users` WHERE `id`='".$db->escape_string($_SESSION['user_id'])."' AND `email`='".$db->escape_string($_SERVER['SSL_CLIENT_S_DN_Email'])."'")->fetch_assoc();
					}
					else {
						$sql_result = $db->query("SELECT * FROM `users` WHERE `id`='".$db->escape_string($_SESSION['user_id'])."'")->fetch_assoc();
					}
					/*`email`='".$db->escape_string($_SERVER['SSL_CLIENT_S_DN_Email'])."'*/
					if($sql_result) {
						if(!$_SESSION['x509']) {							
							foreach($sql_result as $key => $value) {
								$this->$key = $value;
							}	
					//		$global_notices[] = array('title'=>'Auth Debug (65)','content'=>'<pre>'.print_r($this,true).'</pre>');
							return;
						}
						else if(!($this->user_permissions & GLOBAL_PERMISSIONS_X509LOGIN) && $_SESSION['x509']) {
							
						}
						else {
							foreach($sql_result as $key => $value) {
								$this->$key = $value;
							}
							$_SESSION['user_id'] = $this->id;
							$_SESSION['https'] = (@$_SERVER['HTTPS'] == "on") ? TRUE : FALSE;
							$_SESSION['x509'] = (@$_SERVER['SSL_CLIENT_VERIFY'] == "SUCCESS" && $this->user_permissions & GLOBAL_PERMISSIONS_X509LOGIN) ? TRUE : FALSE;
					//		$global_notices[] = array('title'=>'Auth Debug (78)','content'=>'<pre>'.print_r($this,true).'</pre>');
							return;
						}
					}
			//}
				}
			}			
		}
		//Attempts to generate a session data based on client certificate, if present
		if(@$_SERVER['HTTPS'] == "on" && @$_SERVER['SSL_CLIENT_VERIFY'] == "SUCCESS") {
			$sql_result = $db->query("SELECT * FROM `users` WHERE `email`='".$db->escape_string($_SERVER['SSL_CLIENT_S_DN_Email'])."' AND `user_permissions` & ".GLOBAL_PERMISSIONS_X509LOGIN)->fetch_assoc();
			if($sql_result) {
				foreach($sql_result as $key => $value) {
					$this->$key = $value;
				}
				$_SESSION['user_id'] = $this->id;
				$_SESSION['https'] = (@$_SERVER['HTTPS'] == "on") ? TRUE : FALSE;
				$_SESSION['x509'] = (@$_SERVER['SSL_CLIENT_VERIFY'] == "SUCCESS" && $this->user_permissions & GLOBAL_PERMISSIONS_X509LOGIN) ? TRUE : FALSE;
			//	$global_notices[] = array('title'=>'Auth Debug (96)','content'=>'<pre>'.print_r($this,true).'</pre>');
				return;
			}
		}
		
		//No valid session data. Create a new session with default values
		$this->id = 0;
		$this->username = "";
		$this->name = "Anonymous";
		$this->title = "";
		$this->password = "";
		$this->email = "";
		$this->user_permissions = 0;
//		$this->user_type = 0;
		$_SESSION['user_id'] = $this->id;
		$_SESSION['https'] = (@$_SERVER['HTTPS'] == "on") ? TRUE : FALSE;
		$_SESSION['x509'] = (@$_SERVER['SSL_CLIENT_VERIFY'] == "SUCCESS") ? TRUE : FALSE;
		
		
	}
	
	/*
	 * Logs in.
	 * 
	 */
	public function login($username,$password) {
		global $db;
		$sql_result = $db->query("SELECT * FROM `users` WHERE `username`='".$db->escape_string($username)."'")->fetch_assoc();
		//No such username?
		if(!$sql_result) {
			//echo 'lols bad username<br />';
			return BAD_USERNAME;
		}
		//Bad password?
		if(hash('sha512',$password) != $sql_result['password']) {
			return BAD_PASSWORD;
		}
		//No permission to login
		if(!($sql_result['user_permissions'] & GLOBAL_PERMISSIONS_LOGIN)) {
			return NO_LOGIN_PERMISSION;
		}
		
		//Populate the user object
		foreach($sql_result as $key => $value) {
			$this->$key = $value;
		}
		
		$_SESSION['user_id'] = $this->id;
		$_SESSION['https'] = (@$_SERVER['HTTPS'] == "on") ? TRUE : FALSE;
		$_SESSION['x509'] = (@$_SERVER['SSL_CLIENT_VERIFY'] == "SUCCESS" && $this->user_permissions & GLOBAL_PERMISSIONS_X509LOGIN) ? TRUE : FALSE;
		
		return 1;
	}
	
	/*
	 * Logs out.
	 */
	
	public function logout() {
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
	    	setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		$this->id = 0;
		$this->username = "";
		$this->name = "Anonymous";
		$this->title = "";
		$this->password = "";
		$this->email = "";
		$this->user_permissions = 0;
//		$this->user_type = 0;
	}
	
	/*
	 * Saves the session data.
	 */
	public function __destruct() {
		session_write_close();
	}
}
