<?php

	/*

		Получение подкаталогов каталога в менеджере

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
		
		$OpenedFolderInData = $_REQUEST['element_data'];
		
		$CollectionTableName = $OpenedFolderInData['col_table'];

		$OutData = array();
		
		$HandlersKeysToRemove = array('check', 'error_code');
		$AllElementsData = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, $HandlersKeysToRemove);	

		$OpenedFolderData = $AllElementsData[$OpenedFolderInData['tree_st_id']];
		$CollectionData = reset($AllElementsData);
		
		if($OpenedFolderData['permissions']['show'] && count($OpenedFolderData['children']['folders']['show'])>0){
			
			$OpenedFolder_Id = $OpenedFolderInData['id'];

			foreach($OpenedFolderData['children']['folders']['show'] as $TreeStId => $Value){
				
				if($MySQLWhereAdd) $MySQLWhereAdd .= " OR tree_st_id='$TreeStId'";
				else $MySQLWhereAdd .= "tree_st_id='$TreeStId'";
				
			}
			
			$InnerFoldersSortOptions = $OpenedFolderData['ch_fold_sort'][0];
			
			if(is_array($InnerFoldersSortOptions) && !empty($InnerFoldersSortOptions)){
				
				$MySQLOrder = $InnerFoldersSortOptions['order_by'].' '.$InnerFoldersSortOptions['direction'];
				
			}
			else $MySQLOrder = 'tree_name';
			
			$AutoUpdElements = GetAllAutoUpdatedData($STree);
		
			$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE tree_parent_id='$OpenedFolder_Id' AND tree_is_folder='1' AND ($MySQLWhereAdd) ORDER BY $MySQLOrder");
			while($row = $result->fetch_assoc()){
				
				$row = DecodeEncodeRC4FieldsInRow($row, $CollectionData['rc4_columns'], 'decode');
				$ElementData = $AllElementsData[$row['tree_st_id']];
				
				if(count($ElementData['children']['folders']['show'])==0) $IsNoChildren = true;
				else $IsNoChildren = false;
				
				$AutoUpdElement = false;
				if($AutoUpdElements[$CollectionTableName][$row['tree_st_id']]) $AutoUpdElement = true;

				$OutData[] = array(
				
					'id' => $row['id'],
					'is_folder' => $row['tree_is_folder'],
					'element_name' => GetStrFromTemplate($ElementData['head_tmpl'], $row, $row),
					'tree_vis' => $row['tree_vis'],
					'tree_st_id' => $row['tree_st_id'],
					'col_table' => $CollectionTableName,
					'no_children' => $IsNoChildren,
					'tree_ud' => $row['tree_ud'],
					'auto_upd_status' => false,
					'auto_upd_element' => $AutoUpdElement,
				
				);				
				
			}
			mysqli_free_result($result);
			
			
		}
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'items' => $OutData,
			'test' => $Test,
		
		));

	}
	
?>