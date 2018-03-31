<?php

	/*

		Удаление элемента из коллекции

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
		
		$DelElementInData = $_REQUEST['element_data'];
		$InData = $_REQUEST['in_data'];
		
		$CollectionTableName = $DelElementInData['col_table'];
		$DelElementID = $DelElementInData['id'];
		$DelElementsList = $DelElementInData['del_elements_list'];

		$HandlersKeysToRemove = array('check', 'error_code');
		$AllElementsData = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, $HandlersKeysToRemove);			

		$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$DelElementID' LIMIT 0,1");
		$row = $result->fetch_assoc();
		mysqli_free_result($result);
		
		$DelElementData = $AllElementsData[$row['tree_st_id']];
		$CollectionData = reset($AllElementsData);

		if($DelElementData['permissions']['add_del']){

			if(is_array($DelElementsList)){
				
				foreach($DelElementsList as $DelElementId){
					
					if($DelCheckSQLWhere) $DelCheckSQLWhere .= " OR id='$DelElementId'";
					else $DelCheckSQLWhere = "id='$DelElementId'";
					
				}
				
				if($DelCheckSQLWhere){
					
					$result = $MySQLConn->query("SELECT id, tree_st_id FROM $CollectionTableName WHERE $DelCheckSQLWhere");
					while($del_row = $result->fetch_assoc()){
						
						if($AllElementsData[$del_row['tree_st_id']]['permissions']['add_del']){
							
							$ChildrenList[$del_row['id']] = $del_row['id'];
							
						}
						
					}
					mysqli_free_result($result);
					
				}
				
			}
			else $ChildrenList = GetChildrenDataById($DelElementID, 'id', true, $CollectionTableName);
			
			if(is_array($ChildrenList)){
				
				foreach($ChildrenList as $ElementId => $Values){

					if($SQLWhere) $SQLWhere .= " OR id='$ElementId'";
					else $SQLWhere = "id='$ElementId'";
					
					$DelElements[] = $ElementId; 

				}

				if($SQLWhere){

					$DelElementTreeParentID = $row['tree_parent_id'];

					$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$DelElementTreeParentID' LIMIT 0,1");
					$ParentElementDBData = $result->fetch_assoc();
					mysqli_free_result($result);
					
					$row = DecodeEncodeRC4FieldsInRow($row, $CollectionData['rc4_columns'], 'decode');
					$ParentElementDBData = DecodeEncodeRC4FieldsInRow($ParentElementDBData, $CollectionData['rc4_columns'], 'decode');
					
					$Data = array(
					
						'action' => 'del',
						'auth' => $Auth,
						'collection' => $CollectionTableName,
						'tree_st_id' => $DelElementInData['tree_st_id'],
						'parent_db_data' => $ParentElementDBData,
						'db_data' => $row,
						'in_data' => $InData,
						
						'del_elements_id_list' => $DelElements,
					
					);
					
					if($Auth>2) $ProcessingData = DataProcessing($Data);

					$result = $MySQLConn->query("DELETE FROM $CollectionTableName WHERE $SQLWhere");
					
					DelElementsFolders($DelElements, $CollectionTableName);
					
				}

			}
			
			DelCache();
			
		}
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'in_data' => $ProcessingData['in_data'],
			'out_data' => $ProcessingData['out_data'],
			'test' => $Test,
		
		));		

	}
	
?>