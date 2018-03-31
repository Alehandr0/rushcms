<?php

	/*

		Получение списка файлов

	*/

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/data/php/func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/proc_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/main.php';
	
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/options.php';

	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['auth'];
	$RealAuth = $AuthArray['real_auth'];

	if($Auth>1){
		
		if(!$MySQLConn) $MySQLConn = DBConnect();
		
		$CountPerPage = $ModuleOptions['files_per_request_count'];
		
		$Options = $_REQUEST['options'];
		
		$PageNum = intval($Options['page_num']);
		if(!$PageNum) $PageNum = 1;
		
		$SortIndex = intval($Options['sort_index']);
		
		$OpenedFolderInData = $_REQUEST['element_data'];
		
		$CollectionTableName = $OpenedFolderInData['col_table'];

		$OutData = array();
		$CountData = array('sort_data' => array());
		
		$HandlersKeysToRemove = array('check', 'error_code');
		$AllElementsData = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, $HandlersKeysToRemove);	

		$OpenedFolderData = $AllElementsData[$OpenedFolderInData['tree_st_id']];
		$CollectionData = reset($AllElementsData);
		
		if($OpenedFolderData['permissions']['show'] && count($OpenedFolderData['children']['files']['show'])>0){
			
			$OpenedFolder_Id = $OpenedFolderInData['id'];
			
			$result = $MySQLConn->query("SELECT id FROM $CollectionTableName WHERE id='$OpenedFolder_Id' LIMIT 0,1");
			$row = $result->fetch_assoc();
			mysqli_free_result($result);

			if($row['id']){

				foreach($OpenedFolderData['children']['files']['show'] as $TreeStId => $Value){
					
					if($MySQLWhereAdd) $MySQLWhereAdd .= " OR tree_st_id='$TreeStId'";
					else $MySQLWhereAdd .= "tree_st_id='$TreeStId'";
					
				}
				
				$InnerFilesSortOptions = $OpenedFolderData['ch_file_sort'][$SortIndex];
				
				if(is_array($InnerFilesSortOptions) && !empty($InnerFilesSortOptions)){
					
					$MySQLOrder = $InnerFilesSortOptions['order_by'].' '.$InnerFilesSortOptions['direction'];
					
				}
				else $MySQLOrder = 'tree_name';

				$LimitSQL = "LIMIT ".(($PageNum-1)*$CountPerPage).", $CountPerPage";
				
				$AutoUpdElements = GetAllAutoUpdatedData($STree);
				
				$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE tree_parent_id='$OpenedFolder_Id' AND tree_is_folder='0' AND ($MySQLWhereAdd) ORDER BY $MySQLOrder $LimitSQL");
				while($row = $result->fetch_assoc()){
					
					$row = DecodeEncodeRC4FieldsInRow($row, $CollectionData['rc4_columns'], 'decode');
					$ElementData = $AllElementsData[$row['tree_st_id']];
					
					if(count($ElementData['children']['files']['show'])==0) $IsNoChildren = true;
					else $IsNoChildren = false;
					
					$AutoUpdFieldName = $AutoUpdElements[$CollectionTableName][$OpenedFolderInData['tree_st_id']];
					if($AutoUpdFieldName && $row[$AutoUpdFieldName]==0) $AutoUpdStatus = true;
					else $AutoUpdStatus = false;
				
					$OutData[] = array(
					
						'id' => $row['id'],
						'is_folder' => $row['tree_is_folder'],
						'element_name' => GetStrFromTemplate($ElementData['head_tmpl'], $row, $row),
						'tree_vis' => $row['tree_vis'],
						'tree_st_id' => $row['tree_st_id'],
						'col_table' => $CollectionTableName,
						'no_children' => $IsNoChildren,
						'tree_ud' => $row['tree_ud'],
						'auto_upd_status' => $AutoUpdStatus,
						'auto_upd_element' => false,
					
					);
					
				}
				mysqli_free_result($result);

				$result = $MySQLConn->query("SELECT COUNT(id) FROM $CollectionTableName WHERE tree_parent_id='$OpenedFolder_Id' AND tree_is_folder='0' AND ($MySQLWhereAdd)");
				$row = $result->fetch_assoc();
				mysqli_free_result($result);
				
				$CountData['total_count'] = $row['COUNT(id)'];
				
				$CountData['current_count'] = $CountPerPage*$PageNum;
				if($CountData['current_count']>$CountData['total_count']) $CountData['current_count'] = $CountData['total_count'];
				
				$CountData['current_page'] = $PageNum;
				$CountData['per_page_count'] = $CountPerPage;
				$CountData['sort_data'] = $InnerFilesSortOptions;
				$CountData['sort_variants'] = count($OpenedFolderData['ch_file_sort']);
				$CountData['sort_index'] = $SortIndex;

			}
			else{
			
				$IsDeleted = true;
				
			}
			
		}
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'items' => $OutData,
			'count_data' => $CountData,
			'is_deleted' => $IsDeleted,
			'test' => $Test,
		
		));

	}
	
?>