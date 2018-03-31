<?php

	parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $URLParamsArray);
	if(mb_substr($_SERVER['REQUEST_URI'], 0, 4)=='/rc/'  || $URLParamsArray['pp'] || !preg_match('/^[A-Za-z0-9-_\/]*$/iU', $_REQUEST['pp'])){
		
		Show404Page();
		exit;
		
	}
	
	include_once 'router.php';
	
	krsort($UniqData);
	$PageMD5 = md5(implode('', $UniqData));   // Вычисляем MD5-хэш совокупности значений массива $UniqData[]

	if(!$NoCacheTestMode){   // Ищем файл кэша для данной страницы по MD5-хэшу совокупности значений массива $UniqData[], который определяет уникальность страницы. Имя файла кэша имеет вид ТипКэша_MD5хэшPageData.cache

		for( $i=1; $i<=2; $i++ ){   // Перебираем все возможные типы кэша для страницы

			$CacheFilePath = $RootPath.'/rc/_cache/'.$i.'_'.$PageMD5.'.cache';
		
			if(file_exists($CacheFilePath)){
				
				$CType = $i;
				
				$FP = fopen($CacheFilePath, 'r');
				if(flock($FP, LOCK_SH)) $PageContent = file_get_contents($CacheFilePath);
				
				$GenFromCache = true;
				break;
				
			}
			
		}
		
		unset($CacheFilePath);
		
	}
	else DelCache();
	
	/*
		Возможные типы кэша:
		
			1 — полностью кэшированная страница (т.е. она не содержит некэшируемых элементов, которые нуждаются в дополнительной обработке каждый раз при получении страницы. Например — элемент который будет какую-нибудь постоянно меняющиеся данные или данные, которые уникальны для каждого пользователя (время захода на страницу и т.п.))
			2 - частично кэшированная страница

	*/

	if(!$CType || $CType==2){
		
		include_once $RootPath.'/rc/tmpl/tmpl_func.php';   // Подключаем пользовательские функции вывода для страниц у которых нет файла кэша или тип кэша равен 2 (частично кэшированные страницы)

		if(!$MySQLConn) $MySQLConn = DBConnect();

		$UniqData['PagePath'] = mysqli_real_escape_string($MySQLConn, $UniqData['PagePath']);
		
		$result = $MySQLConn->query("SELECT * FROM col_pages WHERE p_url='".$UniqData['PagePath']."' AND tree_vis='1' LIMIT 0,1");
		$PageDataSQL = $result->fetch_assoc();
		mysqli_free_result($result);

		if(!$PageDataSQL['id'] || !$PageDataSQL['p_url']){
			
			Show404Page();
			exit;
			
		}
		else{

			foreach($AddData as $Key => $Value){  // Записываем в $PageDataSQL значения AddData (ключи получают префикс ad_). Таким образом этими значениями можно будет пользоваться при обработке шаблона страницы.
				
				$PageDataSQL['ad_'.$Key] = $Value;
				
			}
			
			foreach($UniqData as $Key => $Value){  // Записываем в $PageDataSQL значения UniqData (ключи получают префикс ud_). Таким образом этими значениями можно будет пользоваться при обработке шаблона страницы.
				
				$PageDataSQL['ud_'.$Key] = $Value;
				
			}
		
			$CType = 1;  // Предварительно устанавливаем переменную типа кэша
			
			if(!$GenFromCache) $PageContent = file_get_contents($RootPath.'/rc/tmpl/'.$PageDataSQL['p_tmpl']);  // Получаем содержимое файла шаблона, если $PageContent не получен из кэша
			
			$i = 0;
			$PageContentNC = $PageContent;
			
			while(!$EndFlag){

				$BlocksResultArray = GetThisPageBlocks($PageContent, $PageContentNC, $PageDataSQL);   // Оставляем в шаблоне только относящиеся к этой странице блоки и обрабатываем их

				$PageContent = $BlocksResultArray['page_content'];
				$PageContentNC = $BlocksResultArray['page_content_nc'];
				
				$ValuesResultArray = GetFunctionValues($PageContent, $PageContentNC, $PageDataSQL);   // Обрабатываем функции вывода данных (с префиксом $), которые относятся к этой странице. Кроме некэшируемых функций

				$PageContent = $ValuesResultArray['page_content'];
				$PageContentNC = $ValuesResultArray['page_content_nc'];

				if($BlocksResultArray['no_cached_functions_count']>0 || $ValuesResultArray['no_cached_functions_count']>0) $CType = 2;  // Если встречались некэшируемые функции, то устанавливаем тип кэша 2

				if( ($BlocksResultArray['total_functions_count']+$ValuesResultArray['total_functions_count']) == 0 || $i>10 ){

					$EndFlag = true;
					break;
					
				}
				
				$i++;
				
			}

			if($CreateAbsURL && (!$GenFromCache || $CType == 2)){
				
				$Host = GetSiteMainUrl();
				
				$PageContent = str_replace(array('href="/', 'src="/', 'content="/'), array('href="'.$Host, 'src="'.$Host, 'content="'.$Host), $PageContent);  // Заменяем относительные пути на абсолютные
				
			}

			if(!$NoCacheTestMode && !$GenFromCache){

				// Формируем данные для записи в файл кэша

				if($CType == 2) $InsertPageContent = $PageContentNC;
				else $InsertPageContent = $PageContent;

				if($MinifyCacheHTML) $InsertPageContent = MinifyOutput($InsertPageContent);
				
				if((count(scandir($RootPath.'/rc/_cache/')) - 2)>=$MaxCacheFiles) DelCache();
				
				file_put_contents($RootPath.'/rc/_cache/'.$CType.'_'.$PageMD5.'.cache', $InsertPageContent, LOCK_EX);
				
			}
		
		}
		
	}

	echo $PageContent;   // Выводим страницу

	
	// ===== Функции ============================================================================================================

	function GetThisPageBlocks($PageContent, $PageContentNC, $PageSQLData){

		// Получение шаблона для страницы — исключение всех неподходящих для данной страницы блоков из кода шаблона
		
		/*
			Подключается к тегу с обязательным закрывающим тегом. Все атрибуты элемента должны 
			следовать в порядке: id, class, параметры шаблонизатора (data-tmpl-...), прочие параметры.
			Порядок параметров шаблонизатора строгий и определен ниже. Допускается пропуск любого параметра.
			Если используется псевдотег <rush></rush>, то обертка из этого тега удаляется
			Общий вид шаблона (необязательные параметры указаны в квадратных скобках): <!--block_(id_блока)--><имя_тега [id="id_элемента"] [class="класс_элемента"] [data-tmpl-param="параметр_отбора"] [data-tmpl-values="занчения_параметра_отбора_для_блока"] [data-tmpl-rule="разрешение_или_запрещение_отображения_блока_при_совпадении_параметров(allowed или disallowed)"] [data-tmpl-handler="имя_функции_обработчика_вложенных_данных(параметры функции);"] [прочие_параметры_в_произвольном_порядке(disabled,width и т.д.)]>[вложенные_данные]</имя_тега><!--block_(id_блока)-->
		*/
		
		$Result = array(
		
			'page_content' => '',
			'total_functions_count' => 0,
			'no_cached_functions_count' => 0,
		
		);		
		
		$VCATMP = array('TMPLTags' => array(),'TMPLValues' => array(), 'TMPLTagsNC' => array(),'TMPLValuesNC' => array());

		while(!$EndFlag){

			$Pattern = '/<!--block_([a-zA-Z_0-9]+)-->[\s]*<([a-z0-9]*)([\s]+id="(?:[\s]*[\d\D]+)"[\s]*|)([\s]+class="(?:[\d\D]+)"[\s]*|)(?:[\s]+data-block-cond="([\d\D]+)"|)(?:[\s]+data-block-handler="([\d\D]*)\(([\d\D]*)\);"|)(?:[\s]+data-block-nocache="(1)"|)(?:([\s]+[\d\D]+)|)>([\d\D]*)<\/\2>[\s]*<!--block_\1-->/iU';
			preg_match_all($Pattern, $PageContent, $MatchArr);
			
			if(count($MatchArr[0])<1){
				$EndFlag = true;
				break;
			}

			for($i=0;$i<count($MatchArr[1]);$i++){
				
				$Result['total_functions_count']++;
			
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
				
				if($DataNoCachedBlock) $Result['no_cached_functions_count']++;

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
			$PageContentNC = str_ireplace($VCATMP["TMPLTagsNC"], $VCATMP["TMPLValuesNC"], $PageContentNC);
		
		}
		
		$Result['page_content'] = $PageContent;
		$Result['page_content_nc'] = $PageContentNC;
		
		return $Result;

	}
	
	function GetFunctionValues($PageContent, $PageContentNC, $PageDataSQL){
	
		// Получение значений функций в шаблоне и вставка в него полученных значений
		
		$Result = array(
		
			'page_content' => '',
			'page_content_nc' => '',
			'total_functions_count' => 0,
			'cached_functions_count' => 0,
			'no_cached_functions_count' => 0,
		
		);

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

				if($FirstChar=='$') $Result['cached_functions_count']++;
				else $Result['no_cached_functions_count']++;
				
				$Result['total_functions_count']++;
				
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
			$PageContentNC = str_ireplace($VCATMP["TMPLTags_NC"], $VCATMP["TMPLValues_NC"], $PageContentNC);
		
		}
		
		$Result['page_content'] = $PageContent;
		$Result['page_content_nc'] = $PageContentNC;

		return $Result;
	
	}

	function MinifyOutput($Content){

		$Search = array(
			'/\>[^\S ]+/s',
			'/[^\S ]+\</s',
			'/(\s)+/s',
		);

		$Replace = array(
			'>',
			'<',
			'\\1',
		);
		
		return preg_replace($Search, $Replace, $Content);
	
	}
	
	function GetSiteMainUrl(){
		
		if(isset($_SERVER['HTTPS'])) $Protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
		else $Protocol = 'http';

		return $Protocol.'://'.$_SERVER['SERVER_NAME'].'/';
		
	}	
	
	function Show404Page(){

		header("HTTP/1.0 404 Not Found");
		echo file_get_contents($_SERVER["DOCUMENT_ROOT"].'/rc/tmpl/404.tmpl');
		
	}
	
?>