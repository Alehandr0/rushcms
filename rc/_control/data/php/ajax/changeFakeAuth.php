<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/rc/php/main_func.php';
	
	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['real_auth'];

	if($Auth==2){
		
		$MySQLConn = DBConnect();
		
		$FakeAuth = intval($_REQUEST['fake_auth']);
		
		if($FakeAuth>2) SetCookie("user_fake_auth", $FakeAuth, time()+31536000, "/");
		else SetCookie("user_fake_auth", '', time()-31536000, "/");

		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
		
		));
		
	}

	/* ==== Доп. функции ====*/
	
?>