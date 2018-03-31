<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';

	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['auth'];
	$RealAuth = $AuthArray['real_auth'];

	if($Auth>1){
		
		$UploadDir = $_REQUEST['path'];

		$Link = UploadHandler($_FILES, $UploadDir);

		echo json_encode(array(
		
			'success' => true,
			'url' => $Link,

		));			

	}
	
	// ========================================================================

	function UploadHandler($UpFilesData, $UploadDir){

		if(!$UploadDir){
			
			$RelPath = '/rc/upload/';
			$UploadDir = $_SERVER["DOCUMENT_ROOT"].'/rc/upload/';
			
			
		}
		else{
			
			$RelPath = '/rc/upload'.$UploadDir;
			$UploadDir = $_SERVER["DOCUMENT_ROOT"].'/rc/upload'.$UploadDir;

		}
		
		if(!is_dir($UploadDir)) mkdir($UploadDir, 0755, true);

		foreach($UpFilesData as $Key => $Value){
		
			$Ext = mb_strtolower(pathinfo($Value['name'], PATHINFO_EXTENSION));
			
			if(!preg_match('/^(php[0-9]*|phtml)$/', $Ext)){

				$NewFileName = GetUniqFileName($UploadDir, $Ext);

				move_uploaded_file($Value['tmp_name'], $UploadDir.$NewFileName);
				
				$OutData = $RelPath.$NewFileName;
				
			}
			
		}
		
		return $OutData;
		
	}

	function GetUniqFileName($UploadDir, $Ext){
		
		$Flag = false;
		
		while(!$Flag){
			
			$NewFileName = mb_substr(md5(microtime().mt_rand(0,9999)), 0, 10).'.'.$Ext;
			if(!file_exists($UploadDir.$NewFileName)) break;
			
		}

		return $NewFileName;
		
	}	

?>