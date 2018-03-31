<?php

	function STreeGetElemOptions($STreeDefEl, $STree, $ColTable, $HandlersKeysToRemove, $CurrentTreeStId = false){
		
		global $Auth, $FieldDef;
		
		$Result = array();
		
		if($Auth == 2){
			
			include $_SERVER["DOCUMENT_ROOT"].'/options.php';
			
			if(!$MySQLConn) $MySQLConn = DBConnect();
			
			$Columns = array();
			
			$result = $MySQLConn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$DBName' AND TABLE_NAME = '$ColTable';");
			while($row = $result->fetch_assoc()){
				
				$Columns[$row['COLUMN_NAME']] = true;
				
			}
			mysqli_free_result($result);	
			
		}
	
		foreach($STree[$ColTable] as $TreeStId => $Options){
			
			$Options = array_merge($STreeDefEl, $Options);
			
			$Options['permissions'] = array(
			
				'show' => GetPermissionsFromStr($Auth, $Options['permissions'][0]),
				'add_del' => GetPermissionsFromStr($Auth, $Options['permissions'][1])
				
			);
			
			$Options['ch_fold_sort'] = GetSortArrayFromStr($Options['ch_fold_sort']);
			$Options['ch_file_sort'] = GetSortArrayFromStr($Options['ch_file_sort']);
			
			$Options['col_table'] = $ColTable;
			$Options['tree_st_id'] = $TreeStId;
			
			if($Options['parent'] == $ColTable) $Options['collection_folder'] = true;
			else $Options['collection_folder'] = false;
			
			$Options['children'] = array(
			
				'files' => array(
					'show' => array(),
					'add_del' => array(),
				),			
				'folders' => array(
					'show' => array(),
					'add_del' => array(),
				),
			
			);
			
			if($Auth == 2){
				
				$HandlerValues = array(
				
					'name' => '',
					'handler' => '$STDH_TaInput();',
					'check' => '',
					'minimized' => false,
				
				);
				
				if(is_array($HandlersKeysToRemove)) $HandlerValues = array_diff_key($HandlerValues, array_flip($HandlersKeysToRemove));
				
				$FieldsHandlers = array_fill_keys(array_keys($Columns), $HandlerValues);
				
			}
			else{

				$FieldsHandlers = GetFieldsOptions($Auth, $ColTable, $TreeStId, $FieldDef);
				
				if(!$FieldsHandlers) $FieldsHandlers = array();
				
				if(is_array($HandlersKeysToRemove)){
					
					foreach($FieldsHandlers as $HandlerKey => $HandlerValues){
						
						$FieldsHandlers[$HandlerKey] = array_diff_key($HandlerValues, array_flip($HandlersKeysToRemove));
					
					}
					
				}
			
			}
			
			$Options['handlers'] = $FieldsHandlers;
			
			$Result[$TreeStId] = $Options;

			if(is_array($Result[$Options['parent']])){
				
				if($Options['is_folder']) $ArrKey = 'folders';
				else $ArrKey = 'files';
				
				if($Options['permissions']['show']) $Result[$Options['parent']]['children'][$ArrKey]['show'][$TreeStId] = true;
				if($Options['permissions']['add_del']) $Result[$Options['parent']]['children'][$ArrKey]['add_del'][$TreeStId] = true;
				
			}
			
			
		}
		
		if($CurrentTreeStId) $Result = $Result[$CurrentTreeStId];
		
		return $Result;
		
	}
	
	function GetFieldsOptions($Auth, $ColTable, $TreeStId, $FieldDef){

		$Result = FieldsOptions($Auth, $ColTable, $TreeStId);	
		
		if(is_array($Result)){
			
			foreach($Result as $FieldName => $FieldOptions){

				$Result[$FieldName] = array_merge($FieldDef, $FieldOptions);
				
			}			
			
		}
		
		return $Result;
		
	}
	
	function GetPermissionsFromStr($Auth, $Str = '2', $TrueForMAdmin = true){
		
		$Result = false;
		
		$PermissionsArr = array_flip(explode(',', $Str));
		
		if(array_key_exists($Auth, $PermissionsArr) || ($Auth == 2 && $TrueForMAdmin)) $Result = true;
		
		return $Result;
		
	}

	function GetSortArrayFromStr($Str){
		
		$Result = array();
		
		$Pattern = '/{([\s\S]+)([\s]+DESC|)}:{([\s\S]+)}/iU';
		
		preg_match_all($Pattern, $Str, $Matches);
		
		if(is_array($Matches[0])){
		
			foreach($Matches[0] as $Key => $Val){
				
				if(mb_substr_count($Matches[1][$Key], ',')>0){
				
					$Matches[1][$Key] .= $Matches[2][$Key];
					$Matches[2][$Key] = 'none';
					
				}
				
				$Result[] = array(
				
					'order_by' => $Matches[1][$Key],
					'direction' => $Matches[2][$Key],
					'name' => $Matches[3][$Key],
				
				);
				
			}
		
		}
		
		return $Result;
		
	}
	
	function DecodeEncodeRC4FieldsInRow($Row, $RC4ColumnsList, $Type){
		
		if(is_array($RC4ColumnsList)){
			
			foreach($RC4ColumnsList as $ColumnName){
				
				if($Row[$ColumnName]) $Row[$ColumnName] = rc4($Row[$ColumnName], $Type);
				
			}
			
		}
		
		return $Row;
		
	}
	
	function GetStrFromTemplate($Template, $Values, $AddFuncData = false){
		
		static $Mask;
		
		if(!$Mask && !is_array($Mask)){
			
			$Pattern = '/\{([a-zA-Z0-9_-]+)\}/iU';
			preg_match_all($Pattern, $Template, $MatchArr);			
			
			$Mask = $MatchArr[1];
			
		}
		
		$Values = array_intersect_key($Values, array_flip($Mask));
		
		if(is_array($Values) && IsTemplatedStr($Template)){
			
			$Search = $Replace = array();
			
			foreach($Values as $Key => $Value){
				
				$Search[] = '{'.$Key.'}';
				$Replace[] = $Value;
				
			}
			
			$Result = str_ireplace($Search, $Replace, $Template);

			$Pattern = '/\$([a-zA-Z0-9_-]+)\(([\d\D]*)\);/iU';
			preg_match_all($Pattern, $Result, $MatchArr);
			
			if(is_array($MatchArr[0])){
				
				$Search = $Replace = array();
				
				foreach($MatchArr[0] as $Key => $FuncText){
					
					$FuncName = $MatchArr[1][$Key];
					$FuncOptions = $MatchArr[2][$Key];
					
					$Search[] = $FuncText;
					$Replace[] = call_user_func($FuncName, $FuncOptions, $AddFuncData);
					
				}
				
				$Result = str_ireplace($Search, $Replace, $Result);

			}

		}

		return $Result;
		
	}

	function IsTemplatedStr($Str){
		
		$Result = false;
		
		$Pattern = '/(\$([a-zA-Z0-9_-]+)\(([\d\D]*)\);|{[a-zA-Z0-9_-]+})/iU';
		$Result = preg_match($Pattern, $Str);
		
		return $Result;		
		
	}	

	function DelElementsFolders($ElementIdArr, $CollectionTable){
		
		$RootPath = $_SERVER["DOCUMENT_ROOT"];
		$CollectionFolderPath = $RootPath.'/rc/upload/'.$CollectionTable.'/';

		if(is_dir($CollectionFolderPath)){
			
			if(is_array($ElementIdArr)){
			
				foreach($ElementIdArr as $ElementId){
					
					$FolderPath = $CollectionFolderPath.$ElementId.'/';
					
					if(is_dir($FolderPath)) RemoveDir($FolderPath, true);
					
				}
			
			}

			if(dir_is_empty($CollectionFolderPath)) RemoveDir($CollectionFolderPath, true);
		
		}

	}
	
	function dir_is_empty($dir){
		
		$handle = opendir($dir);
		
		while(false !== ($Item = readdir($handle))){
			
			if ($Item != "." && $Item != ".."){
				
				return false;
				
			}
			
		}
		
		return true;
	}	
	
	function GetAllChildrenTreeStId($CollectionSTree, $ParentTreeStId){
		
		$Result = array(
		
			'with_parent' => array(),
			'without_parent' => array(),
			
		);
		
		if(is_array($CollectionSTree)){
		
			$ParentFlag = false;
		
			foreach($CollectionSTree as $TreeStId => $Options){
				
				if($TreeStId==$ParentTreeStId) $ParentFlag = $Options['parent'];
				else if($Options['parent']==$ParentFlag) break;

				if($ParentFlag){
					
					if($TreeStId==$ParentTreeStId) $Result['with_parent'][] = $TreeStId;
					else{
						
						$Result['with_parent'][] = $TreeStId;
						$Result['without_parent'][] = $TreeStId;
						
					}					
					
				}
				
			}
		
		
		}
		
		return $Result;
		
		
	}

	function GetChildrenDataById($PageId, $DataStr, $IncludeThis, $Table){
		
		if(!$MySQLConn) $MySQLConn = DBConnect();

		if($PageId>0 && preg_match('/^[0-9A-Za-z_]+$/', $Table)){
		
			if($DataStr && $DataStr!='*' && mb_substr($DataStr, -1)!=',') $DataStr .= ',';

			$ChildrenData = array();
			
			if(mb_strripos($DataStr, 'id')===false && $DataStr && $DataStr!='*') $DelIdFromResult = true;
			if(stristr($DataStr, 'tree_is_folder')===false && $DataStr!='*') $DelTreeIsFolderFromResult = true;

			if($DataStr!='*') $DataStr .='id, tree_is_folder';
			
			if($IncludeThis){
				
				$IncludeThis = false;
				
				$result = $MySQLConn->query("SELECT $DataStr FROM $Table WHERE id='$PageId'");
				$row = $result->fetch_assoc();
				mysqli_free_result($result);
				
				if($DelIdFromResult) unset($row['id']);
				if($DelTreeIsFolderFromResult) unset($row['tree_is_folder']);				
				
				$ChildrenData[$PageId] = $row;

			}

			$result = $MySQLConn->query("SELECT $DataStr FROM $Table WHERE tree_parent_id='$PageId'");
			while($row = $result->fetch_assoc()){
			
				if($row['tree_is_folder']==1) $ChildrenData += GetChildrenDataById($row['id'], $DataStr, $IncludeThis, $Table);
				
				$Id = $row['id'];
				
				if($DelIdFromResult) unset($row['id']);
				if($DelTreeIsFolderFromResult) unset($row['tree_is_folder']);
				
				$ChildrenData[$Id] = $row;
				
			}
			mysqli_free_result($result);
		
		}

		return $ChildrenData;
		
	}
	
	function ResetUrlToAllChildren($ChildTree, $ParentData, $OldPUrl, $Result = array()){

		$OldUrl = $OldPUrl;

		foreach($ChildTree as $Id => $Data){
			
			if(is_array($Data['children'])){
				
				$OldPUrl = $Data['p_url'];
				
				$Data['p_url'] = $Result[$Id] = ChangeElementUrl($Data, $ParentData, $OldUrl);

				$Result = ResetUrlToAllChildren($Data['children'], $Data, $OldPUrl, $Result);
				
			}
			else{

				$Result[$Id] = ChangeElementUrl($Data, $ParentData, $OldUrl);
				
			}

		}
		
		return $Result;
		
	}	
	
	function GetTreeFromParent($PageId, $Options){
		
		if(!$MySQLConn) $MySQLConn = DBConnect();
		
		if(!$Options['page_id'] && $Options['page_id']!==0){			
			$FirstCallFlag = true;
			$Options['page_id'] = $PageId;
		}
		else $FirstCallFlag = false;
		
		$DefaultOptions = array(
			"include_parent_item" => false,
			"handler_function_name" => "",
			"columns_list" => "",
			"table" => "col_pages",
			"order_by" => "tree_is_folder DESC, tree_cd DESC",
			"where" => "",
			"depth" => "",
			"limit" => "",
		);
		
		$Options = array_merge($DefaultOptions, $Options);
		
		if($Options['columns_list'] && $FirstCallFlag && $Options['columns_list']!='*') $Options['columns_list'] = 'id, '.$Options['columns_list'];
		if($Options['where'] && $FirstCallFlag) $Options['where'] = ' AND '.$Options['where'];
		if($Options['limit']) $Options['limit'] = ' LIMIT '.$Options['limit'];
		
		if($Options['depth'] && !$Options['include_parent_item'] && $FirstCallFlag) $Options['depth']++;
		if($Options['depth']) $Options['current_depth']++;
		
		if($Options['current_depth'] < $Options['depth'] || !$Options['depth']){

			$result = $MySQLConn->query("SELECT ".$Options['columns_list']." FROM ".$Options['table']." WHERE tree_parent_id='$PageId'".$Options['where']." ORDER BY ".$Options['order_by'].$Options['limit']);
			while($row = $result->fetch_assoc()){
				
				if($Options['handler_function_name']) $Map[$row['id']] = call_user_func_array($Options['handler_function_name'], array($row));
				else $Map[$row['id']] = $row;
				
				$Map[$row['id']]['children'] = array();
				$Map[$row['id']]['children'] = GetTreeFromParent($row['id'], $Options);
				
			}
			mysqli_free_result($result);
		
		}

		if($Options['include_parent_item'] && $FirstCallFlag){

			$result = $MySQLConn->query("SELECT ".$Options['columns_list']." FROM ".$Options['table']." WHERE id='$PageId'".$Options['where']." ORDER BY ".$Options['order_by'].$Options['limit']);
			$row = $result->fetch_assoc();
			mysqli_free_result($result);
			
			$Tmp = $Map;
			unset($Map);
			
			if($Options['handler_function_name']) $Map[$row['id']] = call_user_func_array($Options['handler_function_name'], array($row));
			else $Map[$row['id']] = $row;
			$Map[$row['id']]['children'] = $Tmp;
			
		}

		return $Map;
		
	}	

	function GetChainFunValue($ChainStr, $StartValue, $Type, $AddFuncData){
		
		if($Type=='check') $ResultValue = true;
		else $ResultValue = $StartValue;
		
		$ChainStr = trim($ChainStr);
		
		if($ChainStr){
			
			$Pattern = '/\$([a-zA-Z0-9_-]+)\(([\d\D]*)\);/iU';
			preg_match_all($Pattern, $ChainStr, $MatchArr);
			
			foreach($MatchArr[1] as $Key => $FuncName){
				
				if(function_exists($FuncName)){
					
					if($Type=='check'){
						
						$ResultValue = call_user_func($FuncName, $StartValue, $MatchArr[2][$Key], $AddFuncData);

						if(!$ResultValue){
							
							$ResultValue = false;
							break;
							
						}
						
						
					}
					else{
						
						$ResultValue = call_user_func($FuncName, $ResultValue, $MatchArr[2][$Key], $AddFuncData);

					}
					
				}
				
			}
			
		}
		
		return $ResultValue;		
		
	}
	
	function GetAllAutoUpdatedData($STree){
		
		$Result = array();
		
		if(is_array($STree)){
			
			foreach($STree as $CollectionTableName => $CollectionStructure){
				
				if(is_array($CollectionStructure)){
					
					foreach($CollectionStructure as $TreeStId => $ElementOptions){
						
						if($ElementOptions['auto_update']){
						
							$Result[$CollectionTableName][$TreeStId] = $ElementOptions['auto_update'];
							
						}
						
					}
					
				}				
				
			}
			
		}
		
		return $Result;
		
	}	
	
?>