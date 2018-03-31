<?php

	/*

		Пример php-обработчика AJAX в RushCMS

	*/

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';  // Подключение основных настроек RushCMS
	include_once $RootPath.'/rc/php/main_func.php';  // Подключение основных функций RushCMS
	include_once $RootPath.'/rc/'.$ControlDirName.'/mods/about_rcms/data/php/func.php';  // Подключение произвольного php-файла этого модуля

	$AuthArray = Auth('full_info');  // Данные о типе прав пользователя
	$Auth = $AuthArray['auth'];  // Тип прав пользователя
	$RealAuth = $AuthArray['real_auth']; // Реальный тип прав пользователя (только для главных администраторов с типом прав 2)

	if($Auth>1){  // Проверка типа прав пользователя
	
		// Код обработки AJAX-запроса...

		echo json_encode(array(  // Вывод
			
			'ok' => 'ok',
			'out_data' => $OutData,
		
		));

	}
	
?>