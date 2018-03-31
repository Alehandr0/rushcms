<?php

	/*

		Добавление элемента в коллекцию

	*/

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/data/php/func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/proc_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/main.php';

	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['auth'];
	$RealAuth = $AuthArray['real_auth'];

	if($Auth>1){
		
		if(!$MySQLConn) $MySQLConn = DBConnect();
		
		$ParentElementInData = $_REQUEST['element_data'];
		$TreeStId = trim($_REQUEST['tree_st_id']);
		
		$CollectionTableName = $ParentElementInData['col_table'];

		$OutData = array();
		
		$ParentElementID = $ParentElementInData['id'];
		
		$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$ParentElementID' LIMIT 0,1");
		$ParentElementDBData = $result->fetch_assoc();  // Данные значений БД родительского элемента
		mysqli_free_result($result);
		
		$HandlersKeysToRemove = array('check', 'error_code');
		$AllElementsData = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, $HandlersKeysToRemove);			

		$ParentElementData = $AllElementsData[$ParentElementDBData['tree_st_id']];	
		$AddElementData = $AllElementsData[$TreeStId];
		$CollectionData = reset($AllElementsData);

		if($AddElementData['permissions']['add_del']){
			
			if($AddElementData['is_folder']){
				
				$AddElementType = 'folders';
				$TreeIsFolder = 1;
				
			}
			else{
				
				$AddElementType = 'files';
				$TreeIsFolder = 0;
				
			}
			
			if($ParentElementData['children'][$AddElementType]['add_del'][$TreeStId]){

				$result = $MySQLConn->query("INSERT INTO $CollectionTableName () VALUES ()");  // Вставляем новую пустую запись
				$AddElementId = $MySQLConn->insert_id;  // Получаем id нового элемента в таблице $Table
				
				$CurrentDate = date("Y-m-d H:i:s");
				
				$InsertValues = array(
				
					'id' => $AddElementId,
					'tree_name' => $TreeStId,
					'tree_is_folder' => $TreeIsFolder,
					'tree_parent_id' => $ParentElementInData['id'],
					'tree_vis' => 0,
					'tree_cd' => $CurrentDate,
					'tree_ud' => $CurrentDate,
					'tree_st_id' => $TreeStId,

				);
				
				$ParentElementDBData = DecodeEncodeRC4FieldsInRow($ParentElementDBData, $CollectionData['rc4_columns'], 'decode');
				
				$Data = array(
				
					'action' => 'add',
					'auth' => $Auth,
					'collection' => $CollectionTableName,
					'tree_st_id' => $TreeStId,
					'parent_db_data' => $ParentElementDBData,
					'db_data' => $InsertValues,
				
				);
				
				if($Auth>2){
				
					$ProcessingData = DataProcessing($Data);
					
					$InsertValues = $ProcessingData['element_data'];
				
				}
				else $InsertValues = $Data['db_data'];
				
				$RC4List = array_fill_keys($CollectionData['rc4_columns'], 1);
				
				foreach($InsertValues as $Key => $Value){
					
					if($RC4List[$Key]) $Value = rc4($Value, 'encode');
					
					$Value = mysqli_real_escape_string($MySQLConn, $Value);

					if($SQLUpdRequestPart) $SQLUpdRequestPart .= ", $Key='$Value'";
					else $SQLUpdRequestPart = "$Key='$Value'";

				}
				
				$result = $MySQLConn->query("UPDATE $CollectionTableName SET $SQLUpdRequestPart WHERE id='$AddElementId'");
				
				$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$AddElementId' LIMIT 0,1");
				$row = $result->fetch_assoc();  // Данные значений БД родительского элемента
				mysqli_free_result($result);				
				
				$row = DecodeEncodeRC4FieldsInRow($row, $CollectionData['rc4_columns'], 'decode');
				
				if(count($AddElementData['children']['folders']['show'])==0) $IsNoChildren = true;
				else $IsNoChildren = false;
				
				$AutoUpdStatus = false;
				$AutoUpdElement = false;
				$AutoUpdElements = GetAllAutoUpdatedData($STree);
				
				if($row['tree_is_folder']==0){
				
					
					$AutoUpdFieldName = $AutoUpdElements[$CollectionTableName][$ParentElementDBData['tree_st_id']];
					if($AutoUpdFieldName && $row[$AutoUpdFieldName]==0) $AutoUpdStatus = true;
				
				}
				else{
					
					if($AutoUpdElements[$CollectionTableName][$row['tree_st_id']]) $AutoUpdElement = true;
					
				}
				
				$OutData = array(
				
					'id' => $AddElementId,
					'is_folder' => $row['tree_is_folder'],
					'element_name' => GetStrFromTemplate($AddElementData['head_tmpl'], $row, $row),
					'tree_vis' => $row['tree_vis'],
					'tree_st_id' => $row['tree_st_id'],
					'col_table' => $CollectionTableName,
					'no_children' => $IsNoChildren,
					'tree_ud' => $row['tree_ud'],
					'auto_upd_status' => $AutoUpdStatus,
					'auto_upd_element' => $AutoUpdElement,
				
				);
				
				if($AddElementData['add_folder']) mkdir($RootPath.'/rc/upload/'.$CollectionTableName.'/'.$AddElementId.'/', 0777, true);
				
				DelCache();
				
			}
			
		}
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'element_data' => $OutData,
			'tree_is_folder' => $row['tree_is_folder'],
			'in_data' => $ProcessingData['in_data'],
			'out_data' => $ProcessingData['out_data'],			
			'test' => $Test,
		
		));		

	}
	
?>