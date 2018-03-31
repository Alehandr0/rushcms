<?php

	// == UpFilesData() function parameters are described below

	function UploadHandler($UpFilesData, $Data){
		
		// Example:

		$UploadDir = $_SERVER["DOCUMENT_ROOT"].'/rc/_tmp/';

		foreach($UpFilesData as $Key => $Value){
		
			$Ext = mb_strtolower(pathinfo($Value['name'], PATHINFO_EXTENSION));
			
			if(!preg_match('/^(php[0-9]*|phtml)$/', $Ext)){
				
				$FileSize = getimagesize($Value['tmp_name']);
				
				if($FileSize[0] == $Data['size'][0] && $FileSize[1] == $Data['size'][1]){

					$NewFileName = GetUniqFileName($UploadDir, $Ext);

					move_uploaded_file($Value['tmp_name'], $UploadDir.$NewFileName);
				
				}
				else $NewFileName = 'size_error';
				
				$OutData[] = $NewFileName;
				
			}
			
		}
		
		return $OutData; // Returns to JS-callback "success"
		
	}
	
	// == Other functions: ========================================
	
	function GetUniqFileName($UploadDir, $Ext){
		
		$Flag = false;
		
		while(!$Flag){
			
			$NewFileName = '_tmp_'.mb_substr(md5(microtime().mt_rand(0,9999)), 0, 10).'.'.$Ext;
			if(!file_exists($UploadDir.$NewFileName)) break;
			
		}

		return $NewFileName;
		
	}
	
	
	/*
	
>>>>	UploadHandler() description
		===========================
		
			$Data - data from JS "data" option
			
		=====
	
			$UpFilesData = Array(
			
				[file_0] => Array(
				
					[name] => some.txt
					[type] => text/plain
					[tmp_name] => /tmp/phpYzdqkD
					[error] => 0
					[size] => 375
					
				)
				[file_1] => Array(
				
					[name] => else.txt
					[type] => text/plain
					[tmp_name] => /tmp/phpYzdq13
					[error] => 0
					[size] => 195
					
				)

			)
			
		===========================
	
	*/

?>