<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/rc/php/main_func.php';
	
	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['real_auth'];

	if($Auth==2){
		
		$MySQLConn = DBConnect();
		
		$result = $MySQLConn->query("SELECT user_rights, tree_name FROM col_users WHERE tree_parent_id='1' AND tree_is_folder='1' AND user_rights > '1'  ORDER BY user_rights");
		while($row = $result->fetch_assoc()){
			
			$Data[$row['user_rights']] = rc4($row['tree_name'], 'decode');
			
		}
		mysqli_free_result($result);		
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'data' => $Data,
		
		));
		
	}
	
?>