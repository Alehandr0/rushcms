<?php

	// Скрипт приема заказа

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';

	$FormData = $_REQUEST['form_data'];

	$UserName = trim(strip_tags($FormData['username']));
	$UserPhone = trim(strip_tags($FormData['userphone']));
	$CartItemsIdList = $FormData['cart'];

	if(mb_strlen($UserName)>1 && mb_strlen($UserPhone)>6 && is_array($CartItemsIdList)){	
	
		$MySQLConn = DBConnect();
	
		foreach($CartItemsIdList as $Id){
			
			if($SQLWhere) $SQLWhere .= " OR id='$Id'";
			else $SQLWhere .= "id='$Id'";
			
		}

		if($SQLWhere){
		
			$result = $MySQLConn->query("SELECT id, tree_name, data_price FROM col_pages WHERE $SQLWhere");
			while($row = $result->fetch_assoc()){

				$List .= '<p>'.htmlspecialchars($row['tree_name']).' ('.number_format($row['data_price'], 0, '.', ' ').' V)</p>';
				
				if($UpdSQLWhere) $UpdSQLWhere .= " OR id='$row[id]'";
				else $UpdSQLWhere .= "id='$row[id]'";
			
			}
			mysqli_free_result($result);		
		
		}
		
		if($List){
			
			$result = $MySQLConn->query("INSERT INTO col_orders (tree_cd, tree_ud, tree_st_id, tree_vis, tree_parent_id) VALUES (NOW(), NOW(), 'Заказ', '1', '1')");  // Создаем новую запись заказа
			$NewOrderId = $MySQLConn->insert_id;  // Получаем id нового заказа
			
			$TreeName = 'Заказ #'.$NewOrderId.' от '.date("d.m.Y");
		
			$OrderDetails = '

				<h1>'.$TreeName.' ('.date("H:i").')</h1>

				<h2>Состав заказа:</h2>

				'.$List.'

				<h2>Данные заказчика:</h2>
				<p>Имя: <strong>'.$UserName.'</strong></p>
				<p>Телефон: <strong>'.$UserPhone.'</strong></p>

			';
			
			$OrderDetails = mysqli_real_escape_string($MySQLConn, $OrderDetails);

			$result = $MySQLConn->query("UPDATE col_orders SET tree_name='$TreeName', data_order='$OrderDetails', data_status='0' WHERE id='$NewOrderId'");
			$result = $MySQLConn->query("UPDATE col_pages SET data_pop=data_pop+1 WHERE $UpdSQLWhere");

		}
	
	
	}

	echo json_encode(array(  // Вывод
		
		'ok' => 'ok',
	
	));

?>