<?php

	$RootPath = $_SERVER["DOCUMENT_ROOT"];

	include_once $RootPath.'/options.php';
	include_once $RootPath.'/rc/php/main_func.php';
	
	$AuthArray = Auth('full_info');
	$Auth = $AuthArray['auth'];
	$RealAuthVal = $AuthArray['real_auth'];

	if($Auth>1) $TemplateName = 'main.tmpl';
	else $TemplateName = 'login.tmpl';

	$PageContent = file_get_contents($RootPath.'/rc/'.$ControlDirName.'/data/tmpl/'.$TemplateName);  // Получаем содержимое файла шаблона
	
	include $RootPath.'/rc/'.$ControlDirName.'/data/php/tmpl_func.php';   // Файл с функциями для обработки шаблонов контрольной панели
	
	ClearTmpDir();
	
	$CurrentModulePath = '/rc/'.$ControlDirName.'/mods/';

	$PageData = array(
	
		'auth' => $Auth,
		'real_auth' => $RealAuthVal,
		'lang' => $Lang,
	
	);
	
	$PageContent = GetThisPageBlocks($PageContent, $PageData);   // Оставляем в шаблоне только относящиеся к этой странице блоки и обрабатываем их
	$PageContent = GetFunctionValues($PageContent, $PageData);   // Обрабатываем функции вывода данных (с префиксом $), которые относятся к этой странице. Кроме некэшируемых функций
	
	echo $PageContent;

	// ===== Функции ============================================================================================================

	function GetThisPageBlocks($PageContent, $PageSQLData){

		$VCATMP = array('TMPLTags' => array(),'TMPLValues' => array(), 'TMPLTagsNC' => array(),'TMPLValuesNC' => array());

		while(!$EndFlag){

			$Pattern = '/<!--block_([a-zA-Z_0-9]+)-->[\s]*<([a-z0-9]*)([\s]+id="(?:[\s]*[\d\D]+)"[\s]*|)([\s]+class="(?:[\d\D]+)"[\s]*|)(?:[\s]+data-block-cond="([\d\D]+)"|)(?:[\s]+data-block-handler="([\d\D]*)\(([\d\D]*)\);"|)(?:[\s]+data-block-nocache="(1)"|)(?:([\s]+[\d\D]+)|)>([\d\D]*)<\/\2>[\s]*<!--block_\1-->/iU';
			preg_match_all($Pattern, $PageContent, $MatchArr);
			
			if(count($MatchArr[0])<1){
				$EndFlag = true;
				break;
			}

			for($i=0;$i<count($MatchArr[1]);$i++){
			
				$TmplId = $MatchArr[1][$i];
				$TagName = $MatchArr[2][$i];
				$IdUndClass = $MatchArr[3][$i].$MatchArr[4][$i];
				
				preg_match('/([\d\D]+)(\=\=|\!\=)([\d\D]+)/', trim($MatchArr[5][$i]), $BlockParamsMatches);
				
				$DataRow = $BlockParamsMatches[1];
				$DataType = $BlockParamsMatches[2];
				$DataValues = '||'.$BlockParamsMatches[3].'||';
				
				$DataHandler = $MatchArr[6][$i];
				$DataHandlerOptions = $MatchArr[7][$i];
				
				$DataNoCachedBlock = $MatchArr[8][$i];

				$CurrentPageVal = str_replace("/", "\/", $PageSQLData[$DataRow]);
				$ExtraParameters = $MatchArr[9][$i];
				$Val = $MatchArr[10][$i];
				$Flag = false;

				if($DataValues!='||||'){
					if( preg_match('/\|\|'.$CurrentPageVal.'\|\|/', $DataValues) ){
						if($DataType=='==') $Flag = true;
					}
					else{
						if($DataType=='!=') $Flag = true;
					}
				}
				else{
					$Flag = true;
				}

				$VCATMP['TMPLTags'][] = $MatchArr[0][$i];

				if($Flag){
					
					if($DataHandler) $Val = call_user_func_array($DataHandler, array($Val, $DataHandlerOptions));

					if($TagName && $TagName!='rush') $VCATMP['TMPLValues'][] = '<'.$TagName.''.$IdUndClass.''.$ExtraParameters.'>'.$Val.'</'.$TagName.'>';
					else $VCATMP['TMPLValues'][] = $Val;

					
				}
				else $VCATMP['TMPLValues'][] = '';
				
				if(!$DataNoCachedBlock){
					
					$VCATMP['TMPLTagsNC'][] = $MatchArr[0][$i];
					$VCATMP['TMPLValuesNC'][] = end($VCATMP['TMPLValues']);
					
				}				
			
			}
			
			$PageContent = str_ireplace($VCATMP["TMPLTags"], $VCATMP["TMPLValues"], $PageContent);
		
		}
		
		return $PageContent;

	}
	
	function GetFunctionValues($PageContent, $PageData){
	
		// Получение значений функций в шаблоне и вставка в него полученных значений

		while(!$Flag){
			
			$Pattern = '/(\$|#)([a-zA-Z0-9_-]+)\(([\d\D]*)\);/iU';
			preg_match_all($Pattern, $PageContent, $MatchArr);
			
			$MatchesCount = count($MatchArr[2]);
			
			if($MatchesCount==0){
				$Flag = true;
				break;
			}

			$VCATMP = array('TMPLTags' => array(),'TMPLValues' => array(), 'TMPLExist' => array());
			
			for($i=0;$i<$MatchesCount;$i++){

				$FirstChar = $MatchArr[1][$i];
				$FuncName = $MatchArr[2][$i];
				$FuncOptionsStr = $MatchArr[3][$i];
		
				$FuncTag = $FirstChar.$FuncName.'('.$FuncOptionsStr.');';
				
				if(!$VCATMP['TMPLExist'][$FuncTag]){
				
					$VCATMP['TMPLTags'][] = $FuncTag;
					$VCATMP['TMPLValues'][] = $FuncRes = call_user_func_array($FuncName, array($FuncOptionsStr));
					
					if($FirstChar!='#'){
						
						$VCATMP['TMPLTags_NC'][] = $FuncTag;
						$VCATMP['TMPLValues_NC'][] = $FuncRes;
						
					}
					
					$VCATMP['TMPLExist'][$FuncTag] = true;
				
				}

			}
			
			$PageContent = str_ireplace($VCATMP["TMPLTags"], $VCATMP["TMPLValues"], $PageContent);
		
		}
		
		return $PageContent;
	
	}
?>