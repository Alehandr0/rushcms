<?php

	function GetGlobalVar($VarName){  // Вывод глобальной переменной по ее имени

		global ${$VarName};
		
		return ${$VarName};
		
	}

	function GetModule($Html){
		
		global $Auth, $Lang, $ModsCSS, $ModsJS, $ModuleHtml, $CurrentModulePath, $ControlDirName;

		$RootPath = $_SERVER["DOCUMENT_ROOT"];
		$ModsDir = $RootPath."/rc/$ControlDirName/mods/";
		
		include $RootPath."/rc/$ControlDirName/options.php";  // Получаем $Modules
		
		$ActiveModuleName = trim($_REQUEST['mod']);
		
		if(!is_dir($ModsDir.$ActiveModuleName) || !$ActiveModuleName || !GetPermissionsFromStr($Auth, $Modules[$ActiveModuleName]['permissions'])){
			
			if(is_array($Modules) && !empty($Modules)){

				foreach($Modules as $ModDirName => $ModOptions){
					
					if(GetPermissionsFromStr($Auth, $ModOptions['permissions'])){
						
						$ActiveModuleName = $ModDirName;
						break;
						
					}
					
				}

			}
			else $ActiveModuleName = 'page_editor';
			
		}
		
		if(is_array($Modules)){
			
			foreach($Modules as $ModDirName => $ModOptions){
				
				if(GetPermissionsFromStr($Auth, $ModOptions['permissions'])){

					$ModuleRootPath = $ModsDir.$ModDirName;

					include $ModsDir.$ModDirName.'/options.php';
					include $ModsDir.$ModDirName.'/data/module_init.php';
					
					$ModName = $ModuleOptions['name'][$Lang];
					if(!$ModName) $ModName = array_shift(array_values($ModuleOptions['name']));
					
					$ModId = $ModuleOptions['id'];
					if(!$ModId) $ModId = $ModDirName;

					if($ModDirName==$ActiveModuleName){
						
						$CurrentModulePath .= $ModDirName;
						
						if(is_array($ModuleInitData['tmpl'])){
							
							foreach($ModuleInitData['tmpl'] as $TmplFilePath){

								$ModsTMPLContent .= file_get_contents($ModsDir.$ModDirName.'/'.$TmplFilePath);
								
							}
							
						}
						
						$ModsTMPLContent .= $ModuleInitData['content'];

						$ModuleHtml = '<div id="module_'.$ModId.'" class="module" data-module-id="'.$ModId.'">'.file_get_contents($ModsDir.$ModDirName.'/data/tmpl/main.tmpl').$ModsTMPLContent.'</div>';

						if(is_array($ModuleInitData['css'])){
							
							foreach($ModuleInitData['css'] as $CssFilePath){

								$ModsCSS .= '<link rel="stylesheet" type="text/css" href="/rc/'.$ControlDirName.'/mods/'.$ModDirName.'/'.$CssFilePath.'" />'."\r\n\t\t";
								
							}
							
						}
						
						if(is_array($ModuleInitData['js'])){
							
							foreach($ModuleInitData['js'] as $JsFilePath){
								
								$ModsJS .= '<script type="text/javascript" src="/rc/'.$ControlDirName.'/mods/'.$ModDirName.'/'.$JsFilePath.'"></script>'."\r\n\t\t";
								
							}
							
						}
						
						$ModClass = 'active';
					
					}
					else $ModClass = 'off';
					
					$ModulesListHtml .= str_ireplace(array('{ModId}', '{ModName}', '{ModClass}'), array($ModId, $ModName, $ModClass), $Html);

				}
				
			}
			
		}

		return $ModulesListHtml;
		
	}
	
	function InitFunction(){
		
		
	}

	function GetPermissionsFromStr($Auth, $Str = '2', $TrueForMAdmin = true){
		
		$Result = false;
		
		$PermissionsArr = array_flip(explode(',', $Str));
		
		if(array_key_exists($Auth, $PermissionsArr) || ($Auth == 2 && $TrueForMAdmin)) $Result = true;
		
		return $Result;
		
	}	

?>