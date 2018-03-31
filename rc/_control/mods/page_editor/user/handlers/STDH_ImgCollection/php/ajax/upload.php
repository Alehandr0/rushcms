<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';

	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['auth'];
	$RealAuth = $AuthArray['real_auth'];

	if($Auth>1){

		$Data = json_decode($_REQUEST['data'], true);

		if(!$Data) $Data = false;
		
		$OutData = array();
		
		include "handler.php";

		$OutData = UploadHandler($_FILES, $Data);
		
		echo json_encode(array(
		
			'out_data' => $OutData,
			'test' => $_FILES,

		));

	}	
		
?>