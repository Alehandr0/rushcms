<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];
	
	$LogData = array();

	$LogFileName = $RootPath.'/log_data.php';
	
	if(file_exists($LogFileName)) include $LogFileName;
	
	$LogData = HTMLSpecCharsToAllArrValues($LogData);
	
	echo "<pre>";
	print_r($LogData);
	echo "</pre>";
	
	
	// ================================================================================
	
	
	function HTMLSpecCharsToAllArrValues($Array){
		
		if(is_array($Array)){

			foreach($Array as $Key => $Value){
				
				if(is_array($Value)) $Array[$Key] = HTMLSpecCharsToAllArrValues($Value);
				else $Array[$Key] = htmlspecialchars($Value);

			}
		
		}
		
		return $Array;
		
	}

?>