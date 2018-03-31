<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/data/php/func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/proc_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/main.php';

	$AuthArray = Auth('full_info');
	$UserId = Auth('user_id');
	$Auth = $AuthArray['auth'];
	$RealAuth = $AuthArray['real_auth'];

	if($Auth>1){
		
		if(!$MySQLConn) $MySQLConn = DBConnect();
		
		$ElementsList = $_REQUEST['elements_list'];

		$AutoUpdElements = GetAllAutoUpdatedData($STree);
		
		if(empty($AutoUpdElements)) $Data = 'no_auto_upd_elements';
		else{
			
			$Data = array();
			
			foreach($AutoUpdElements as $CollectionTableName => $TreeStIdList){
				
				$HandlersKeysToRemove = array('check', 'error_code');
				$AllElementsData = STreeGetElemOptions($STreeDefEl, $STree, $CollectionTableName, $HandlersKeysToRemove);	

				foreach($TreeStIdList as $TreeStId => $AutoUpdFieldName){
					
					$result = $MySQLConn->query("SELECT id FROM $CollectionTableName WHERE tree_st_id='$TreeStId'");
					while($row = $result->fetch_assoc()){
						
						$FolderData = $AllElementsData[$TreeStId];
						
						foreach($FolderData['children']['files']['show'] as $FileTreeStId => $Value){
							
							if($MySQLWhereAdd) $MySQLWhereAdd .= " OR tree_st_id='$FileTreeStId'";
							else $MySQLWhereAdd .= "tree_st_id='$FileTreeStId'";
							
						}
						
						$Count = 0;
						$MustRefresh = false;
						
						if($MySQLWhereAdd){

							$count_result = $MySQLConn->query("SELECT COUNT(id) FROM $CollectionTableName WHERE tree_parent_id='$row[id]' AND $AutoUpdFieldName='0' AND tree_is_folder='0' AND ($MySQLWhereAdd)");
							$count_row = $count_result->fetch_assoc();
							mysqli_free_result($count_result);

							$Count = $count_row['COUNT(id)'];
							
							if(is_array($ElementsList[$row['id']])){
								
								$OldCount = count($ElementsList[$row['id']]);

								if($OldCount!=$Count) $MustRefresh = true;
								else{
									
									foreach($ElementsList[$row['id']] as $FileID){
										
										if($MySQLWhere) $MySQLWhere .= " OR id='$FileID'";
										else $MySQLWhere .= "id='$FileID'";										
										
									}
									
									if($MySQLWhere){
										
										$old_count_result = $MySQLConn->query("SELECT COUNT(id) FROM $CollectionTableName WHERE tree_parent_id='$row[id]' AND $AutoUpdFieldName='0' AND tree_is_folder='0' AND ($MySQLWhere)");
										$old_count_row = $old_count_result->fetch_assoc();
										mysqli_free_result($old_count_result);

										$OldCount = $old_count_row['COUNT(id)'];
										
										if($OldCount!=$Count) $MustRefresh = true;
										
									}
									
								}

							}
							
						}
						
						$Data[] = array(
						
							'id' => $row['id'],
							'col_table' => $CollectionTableName,
							'count' => $Count,
							'must_refresh' => $MustRefresh,
						
						);
						
					}
					mysqli_free_result($result);
					
				}
				
			}

		}
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'data' => $Data,
			'test' => $Test,
		
		));			
		
	}

?>