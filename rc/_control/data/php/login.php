<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';

	$Login = trim(strip_tags($_REQUEST['userlogin']));
	$Pass = trim(strip_tags($_REQUEST['userpass']));
	
	$LoginResult = Login($Login, $Pass);
	
	if($LoginResult) header("Location: /$ControlUrlName");
	else header("Location: /$ControlUrlName/?error=1");

?>