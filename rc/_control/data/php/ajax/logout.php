<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/rc/php/main_func.php';
	
	$Auth = Auth();

	if($Auth>1){
		
		Logout();
		
		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
		
		));
		
	}

	/* ==== Доп. функции ====*/
	
?>