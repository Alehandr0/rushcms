<?php

	/*

		Получение списка коллекций для менеджера

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
		
		$OutData = array();
		
		$AutoUpdElements = GetAllAutoUpdatedData($STree);
		
		foreach($STree as $CollectionTableName => $CollectionOptions){
		
			$HandlersKeysToRemove = array('check', 'error_code');
			$STreeData[$CollectionTableName] = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, $HandlersKeysToRemove);
			
			$CollectionData = reset($STreeData[$CollectionTableName]);
			
			if($CollectionData['permissions']['show']){

				$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE tree_parent_id='0' LIMIT 0,1");
				$row = $result->fetch_assoc();
				mysqli_free_result($result);

				if($row['id']){
					
					$row = DecodeEncodeRC4FieldsInRow($row, $CollectionData['rc4_columns'], 'decode');
					
					if(count($CollectionData['children']['folders']['show'])==0) $IsNoChildren = true;
					else $IsNoChildren = false;
					
					$AutoUpdElement = false;
					if($AutoUpdElements[$CollectionTableName][$CollectionData['tree_st_id']]) $AutoUpdElement = true;					
					
					$OutData[] = array(
					
						'id' => $row['id'],
						'is_folder' => $row['tree_is_folder'],
						'element_name' => GetStrFromTemplate($CollectionData['head_tmpl'], $row, $row),
						'tree_vis' => $row['tree_vis'],
						'tree_st_id' => $CollectionData['tree_st_id'],
						'col_table' => $CollectionTableName,
						'no_children' => $IsNoChildren,
						'tree_ud' => $row['tree_ud'],
						'auto_upd_status' => false,
						'auto_upd_element' => $AutoUpdElement,
					
					);
					
				}
			
			}
		
		}

		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'items' => $OutData,
			'stree_data' => $STreeData,
			'test' => $Test,
		
		));

	}
	
?>