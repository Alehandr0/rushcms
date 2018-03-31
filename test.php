<?php

	// Тестовый файл. Подключается и отключается через .htaccess

	$RootPath = $_SERVER["DOCUMENT_ROOT"];
	
	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/data/php/func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/proc_func.php';
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/page_editor/user/main.php';	


	$Result[] = 'Test page. Use it as you like.';
	$Result[] = array('test' => 1, 'array' => 'value');


	echo '<pre/>';
	print_r($Result);
	
	// ========================================================
	
?>