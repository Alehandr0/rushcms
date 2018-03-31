<?php

	function PregCheck($Value, $CheckParams, $Data){
		
		$CheckParams = GetParamsArrFromStr($CheckParams);
		
		return preg_match($CheckParams[0], $Value);
		
	}
	
	function NotEmptyVal($Value, $CheckParams, $Data){
		
		$Result = false;
		
		if(trim($Value)) $Result = true;
		
		return $Result;
		
	}

?>