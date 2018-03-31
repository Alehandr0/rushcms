<?php

	// == Передаваемые в UploadHandler() данные описаны ниже

	function UploadHandler($UpFilesData, $Data){
		
		// Пример:

		$UploadDir = $_SERVER["DOCUMENT_ROOT"].'/rc/_tmp/';

		foreach($UpFilesData as $Key => $Value){
		
			$Ext = mb_strtolower(pathinfo($Value['name'], PATHINFO_EXTENSION));
			
			if(!preg_match('/^(php[0-9]*|phtml)$/', $Ext)){

				$NewFileName = GetUniqFileName($UploadDir, $Ext);

				move_uploaded_file($Value['tmp_name'], $UploadDir.$NewFileName);
				
				$OutData[] = $NewFileName;
				
			}
			
		}
		
		return $OutData; // Returns to JS-callback "success"
		
	}
	
	// == Вспомогательные ф-и: ========================================
	
	function GetUniqFileName($UploadDir, $Ext){
		
		$Flag = false;
		
		while(!$Flag){
			
			$NewFileName = '_tmp_'.mb_substr(md5(microtime().mt_rand(0,9999)), 0, 10).'.'.$Ext;
			if(!file_exists($UploadDir.$NewFileName)) break;
			
		}

		return $NewFileName;
		
	}
	
	
	/*
	
>>>>	Описание данных, опадающих в UploadHandler()
		============================================
		
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