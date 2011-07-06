<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

profile.php
Viewing and editing profiles.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/
require_once('common.php');

if(@!$_REQUEST['mode']) {
	$_REQUEST['mode'] = "";
}



?>