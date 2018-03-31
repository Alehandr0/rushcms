<?php

	/*

		Получение данных элемента 

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
		
		$OpenedElementInData = $_REQUEST['element_data'];
		
		$CollectionTableName = $OpenedElementInData['col_table'];

		$OutData = array();

		$HandlersKeysToRemove = array('check', 'error_code');
		$AllElementsData = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, $HandlersKeysToRemove);

		$OpenedElementData = $AllElementsData[$OpenedElementInData['tree_st_id']];
		$CollectionData = reset($AllElementsData);

		$HandlersList = $OpenedElementData['handlers'];
		
		if(is_array($HandlersList) && !empty($HandlersList)){

			$OpenedElementID = $OpenedElementInData['id'];
			
			$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$OpenedElementID' LIMIT 0,1");
			$row = $result->fetch_assoc();
			mysqli_free_result($result);

			if($row['id']){

				if($row['tree_st_id']==$OpenedElementData['tree_st_id']){

					$OprenedElementTreeParentID = $row['tree_st_id'];
					
					$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$OprenedElementTreeParentID' LIMIT 0,1");
					$ParentElementDBData = $result->fetch_assoc();
					mysqli_free_result($result);

					$row = DecodeEncodeRC4FieldsInRow($row, $CollectionData['rc4_columns'], 'decode');
					
					$Data = array(
					
						'action' => 'show',
						'auth' => $Auth,
						'collection' => $CollectionTableName,
						'tree_st_id' => $row['tree_st_id'],
						'parent_db_data' => $ParentElementDBData,
						'db_data' => $row,
						'in_data' => array(),
					
					);

					$Data['db_data'] = array_merge($row, $OutData);
					
					if($Auth>2) $OutData = DataProcessing($Data)['element_data'];
					else $OutData = $Data['db_data'];
					
					$AlwaysAvailableFields = array(
					
						'id' => true,
						'p_url' => true,
						'tree_is_folder' => true,
						'tree_parent_id' => true,
						'tree_vis' => true,
						'tree_cd' => true,
						'tree_ud' => true,
						'tree_st_id' => true,
					
					);
					
					$HandlersList = array_merge($AlwaysAvailableFields, $HandlersList);
					
					$OutData = array_intersect_key($OutData, array_flip(array_keys($HandlersList)));

				}
				
			}
			else $IsDeleted = true;
			
		}

		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'item_data' => $OutData,
			'is_deleted' => $IsDeleted,
			'test' => $Test,
		
		));

	}
	
?>