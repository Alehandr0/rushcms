<?php

	function GetPageUrl($ParentElementData, $ElementName, $ElemetntId){
		
		if(!$MySQLConn) $MySQLConn = DBConnect();
		
		if(!is_array($ParentElementData)) $PageURL = '/';
		else{
		
			$PageURL = $ParentElementData['p_url'].TranslitStr($ElementName, true).'/';
			
			$result = $MySQLConn->query("SELECT id FROM col_pages WHERE p_url='$PageURL' AND id!='$ElemetntId' LIMIT 0,1");
			$row = $result->fetch_assoc();
			mysqli_free_result($result);
			
			if($row['id']) $PageURL = $ParentElementData['p_url'].TranslitStr($ElementName, true).'-'.$ElemetntId.'/';
		
		}
		
		return $PageURL;
		
	}
		
		function TranslitStr($Str, $ForUrl = false){
			
			// Функция транслитерирует $Str
			
			$Conv = array(
				'а' => 'a',   'б' => 'b',   'в' => 'v',
				'г' => 'g',   'д' => 'd',   'е' => 'e',
				'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
				'и' => 'i',   'й' => 'y',   'к' => 'k',
				'л' => 'l',   'м' => 'm',   'н' => 'n',
				'о' => 'o',   'п' => 'p',   'р' => 'r',
				'с' => 's',   'т' => 't',   'у' => 'u',
				'ф' => 'f',   'х' => 'h',   'ц' => 'c',
				'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
				'ь' => '', 	  'ы' => 'y',   'ъ' => '',
				'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
				'А' => 'A',   'Б' => 'B',   'В' => 'V',
				'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
				'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
				'И' => 'I',   'Й' => 'Y',   'К' => 'K',
				'Л' => 'L',   'М' => 'M',   'Н' => 'N',
				'О' => 'O',   'П' => 'P',   'Р' => 'R',
				'С' => 'S',   'Т' => 'T',   'У' => 'U',
				'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
				'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
				'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
				'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
			);	

			$Str = strtr($Str, $Conv);
			
			if($ForUrl){
			
				$Str = strtolower($Str);
				$Str = preg_replace('~[^-a-z0-9_]+~u', '-', $Str);
				$Str = trim($Str, "-");
			
			}

			return $Str;		
			
		}
		
	// ========================================================================
	
	function ImgArrayProcessing($ColTable, $OldElementData, $OldImgDataFromDB, $NewImgDataArray){

		$RootPath = $_SERVER["DOCUMENT_ROOT"];
		
		$UploadDir = "$RootPath/rc/_tmp";
		$ElementDir = "$RootPath/rc/upload/$ColTable/$OldElementData[id]";
		
		$OldImgDataFromDB = json_decode($OldImgDataFromDB, true);
		if(!is_array($OldImgDataFromDB)) $OldImgDataFromDB = array();
		
		if(!is_array($NewImgDataArray)) $NewImgDataArray = array();
		
		$Data = array(
		
			'upload_dir' => $UploadDir,
			'element_dir' => $ElementDir,
		
		);
		$NewImgDataArray = RenameAllTmpImgInImgArray($NewImgDataArray, 'img', $Data);
		
		$OnlyImgFromNewArray = GetAllArrayValuesByKey($NewImgDataArray, 'img');
		$OnlyImgFromOldImgArray = GetAllArrayValuesByKey($OldImgDataFromDB, 'img');
		
		$FilesToDel = array_diff($OnlyImgFromOldImgArray, $OnlyImgFromNewArray);
		
		foreach($FilesToDel as $Img){
			
			if(file_exists("$ElementDir/$Img")) unlink("$ElementDir/$Img");
			
		}
		
		if(empty($NewImgDataArray) || !$NewImgDataArray) $NewImgDataArray = '';
		
		return $NewImgDataArray;
		
	}
	
		function RenameAllTmpImgInImgArray($Array, $SearchKey, $Data){

			foreach($Array as $Key => $Value){
				
				if(is_array($Value)){
					
					$Array[$Key] = RenameAllTmpImgInImgArray($Value, $SearchKey, $Data);
					
				}
				else{
					
					if($Key==$SearchKey){
						
						if(mb_substr($Value, 0, 5)=='_tmp_'){
						
							$NewFileName = mb_substr($Value, 5);
							
							if(file_exists("$Data[element_dir]/$NewFileName")){
						
								$Ext = mb_strtolower(pathinfo($NewFileName, PATHINFO_EXTENSION));
								
								$NewFileName = GetUniqFileName($Data['element_dir'], $Ext);
							
							}
							
							if(file_exists("$Data[upload_dir]/$Value")){
							
								rename("$Data[upload_dir]/$Value", "$Data[element_dir]/$NewFileName");
								chmod("$Data[element_dir]/$NewFileName", 0755);
							
							}
							
							$Array[$Key] = $NewFileName;
							
						}						

					}
					
				}
				
			}
			
			return $Array;
			
		}	
	
		function GetAllArrayValuesByKey($Array, $SearchKey){
			
			$Result = array();

			foreach($Array as $Key => $Value){
				
				if(is_array($Value)){
					
					$Result = array_merge($Result, GetAllArrayValuesByKey($Value, $SearchKey));
					
				}
				else{
					
					if($Key==$SearchKey) $Result[] = $Value;
					
				}
				
			}
			
			return $Result;
			
		}
		
		function GetUniqFileName($Dir, $Ext){
			
			$Flag = false;
			
			while(!$Flag){
				
				$NewFileName = '_tmp_'.mb_substr(md5(microtime().mt_rand(0,9999)), 0, 10).'.'.$Ext;
				if(!file_exists("$Dir/$NewFileName")) break;
				
			}

			return $NewFileName;
			
		}		

?>