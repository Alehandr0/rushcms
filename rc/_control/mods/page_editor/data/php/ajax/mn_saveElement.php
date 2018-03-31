<?php

	/*

		Сохранение данных элемента

	*/

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/data/php/func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/proc_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/check_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/main.php';

	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['auth'];
	$RealAuth = $AuthArray['real_auth'];

	if($Auth>1){
		
		if(!$MySQLConn) $MySQLConn = DBConnect();
		
		$UpdElementInData = $_REQUEST['element_data'];
		$InData = $_REQUEST['in_data'];
		
		$CollectionTableName = $UpdElementInData['col_table'];
		$UpdElementID = $UpdElementInData['id'];
		
		$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$UpdElementID' LIMIT 0,1");
		$UpdElementRowData = $result->fetch_assoc();
		mysqli_free_result($result);

		$UpdElementCurrentUpdTime = strtotime($UpdElementRowData['tree_ud']);
		$UpdElementThisUserUpdTime = strtotime($UpdElementInData['tree_ud']);

		if(!$UpdElementRowData['id']){
			
			$IsDeleted = true;
			
		}
		else if($UpdElementCurrentUpdTime>$UpdElementThisUserUpdTime && intval($UpdElementInData['changed_element_save_confirm'])==0){
			
			$IsChanged = true;
			
		}
		else if($UpdElementRowData['id']){
			
			$OldElementPURL = $UpdElementRowData['p_url'];

			$AllElementsData = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, array());
			$UpdElementData = $AllElementsData[$UpdElementRowData['tree_st_id']];
			$RowData = $UpdElementInData['row_data'];
			$CollectionData = reset($AllElementsData);
			
			if(!is_array($RowData)) $RowData = array();
			
			if(is_array($UpdElementData['handlers'])){
				
				foreach($UpdElementData['handlers'] as $HandlerColumn => $HandlerOptions){
					
					if($HandlerOptions['handler']!='$FakeHandler();') $CleanedRowData[$HandlerColumn] = $RowData[$HandlerColumn];

				}
				
			}

			if(is_array($CleanedRowData) && !empty($CleanedRowData)){
				
				$ErrorFields = array();
				
				$UpdElementTreeParentID = $UpdElementRowData['tree_parent_id'];
				
				$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$UpdElementTreeParentID' LIMIT 0,1");
				$ParentElementDBData = $result->fetch_assoc();
				mysqli_free_result($result);
				
				$ParentElementDBData = DecodeEncodeRC4FieldsInRow($ParentElementDBData, $CollectionData['rc4_columns'], 'decode');
				$UpdElementRowData = DecodeEncodeRC4FieldsInRow($UpdElementRowData, $CollectionData['rc4_columns'], 'decode');
				
				$Data = array(
				
					'action' => 'save',
					'auth' => $Auth,
					'collection' => $CollectionTableName,
					'tree_st_id' => $UpdElementRowData['tree_st_id'],
					'parent_db_data' => $ParentElementDBData,
					'db_data' => array_merge($UpdElementRowData, $CleanedRowData),
					'old_db_data' => $UpdElementRowData,
					'in_data' => $InData,
				
				);

				foreach($CleanedRowData as $Key => $Value){

					if(!GetChainFunValue($UpdElementData['handlers'][$Key]['check'], $Value, 'check', $Data)){
						
						$ErrorFields[] = $Key;
						
					}	

				}
				
				if(empty($ErrorFields)){
					
					if($Auth>2){

						$ProcessingData = DataProcessing($Data);
						
						$UpdateValues = $ProcessingData['element_data'];
					
					}
					else $UpdateValues = $Data['db_data'];
					
					if(is_array($UpdateValues)){
						
						$RC4List = array_fill_keys($CollectionData['rc4_columns'], 1);

						foreach($UpdateValues as $Key => $Value){
							
							if($Key!='tree_ud'){

								if($RC4List[$Key]) $Value = rc4($Value, 'encode');

							}
							else $Value = date("Y-m-d H:i:s");
							
							$Value = mysqli_real_escape_string($MySQLConn, $Value);

							if($SQLUpd) $SQLUpd .= ", $Key='$Value'";
							else $SQLUpd = "$Key='$Value'";		

						}
						
						if($SQLUpd){

							$result = $MySQLConn->query("UPDATE $CollectionTableName SET $SQLUpd WHERE id='$UpdElementID'");
							
							$result = $MySQLConn->query("SELECT * FROM $CollectionTableName WHERE id='$UpdElementID' LIMIT 0,1");
							$row = $result->fetch_assoc();
							mysqli_free_result($result);
							
							if($CollectionTableName=='col_pages' && $Auth>2){
								
								if($OldElementPURL!=$row['p_url']){
									
									$ElementWithChildrenArray = GetTreeFromParent($UpdElementID, array(
									
										'include_parent_item' => true,
										'columns_list' => '*',
										'table' => 'col_pages'
										
									));
									
									$ParentData = $ElementWithChildrenArray[$UpdElementID];
									unset($ParentData['children']);

									$ChildTree = $ElementWithChildrenArray[$UpdElementID]['children'];
									
									if(is_array($ChildTree)){
										
										$NewPUrlList = ResetUrlToAllChildren($ChildTree, $ParentData, $OldElementPURL);								

										$UpdateDate = date("Y-m-d H:i:s");

										if(is_array($NewPUrlList)){
											
											foreach($NewPUrlList as $Id => $NewUrl){
											
												if($SQLInsRequestPart) $SQLInsRequestPart .= ", ($Id, '$NewUrl', '$UpdateDate')";
												else $SQLInsRequestPart = "($Id, '$NewUrl', '$UpdateDate')";
											
											}
											
											$result = $MySQLConn->query("INSERT INTO col_pages (id, p_url, tree_ud) VALUES $SQLInsRequestPart ON DUPLICATE KEY UPDATE p_url=VALUES(p_url)");
											
										}									

									}

								}
								
							}

							$row = DecodeEncodeRC4FieldsInRow($row, $CollectionData['rc4_columns'], 'decode');

							if(count($UpdElementData['children']['folders']['show'])==0) $IsNoChildren = true;
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
							
							$ElementData = array(
							
								'id' => $row['id'],
								'is_folder' => $row['tree_is_folder'],
								'element_name' => GetStrFromTemplate($UpdElementData['head_tmpl'], $row, $row),
								'tree_vis' => $row['tree_vis'],
								'tree_st_id' => $row['tree_st_id'],
								'col_table' => $CollectionTableName,
								'no_children' => $IsNoChildren,
								'tree_ud' => $row['tree_ud'],
								'auto_upd_status' => $AutoUpdStatus,
								'auto_upd_element' => $AutoUpdElement,
							
							);						
							
							DelCache();
							
						}
						
					}
					
				}
				
			}
			
		}
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'in_data' => $ProcessingData['in_data'],
			'out_data' => $ProcessingData['out_data'],
			'element_data' => $ElementData,
			'error_fields' => $ErrorFields,
			'is_deleted' => $IsDeleted,
			'is_changed' => $IsChanged,
			'test' => $Test,
		
		));		

	}
	
?>