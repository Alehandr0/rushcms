<?php

	$ModuleInitData = array(
		
		'js' => array(
		
			'data/js/main.js',
			'data/js/lang.js',
			'user/events.js',
			'user/lang.js',
			'user/main.js',
		
		),
		'css' => array(
		
			'data/css/main.css',
		
		),
		'tmpl' => array('user/main.tmpl'),
		'content' => '',

	);

	$HandlersDir = $ModuleRootPath."/user/handlers/";  // $ModuleRootPath — корневой каталог модуля. Передается при подключении module_init
	
	include_once $HandlersDir.'add_js_css.php';
	
	if(is_array($AddJsCss['js'])){
	
		foreach($AddJsCss['js'] as $JsFile){
			
			if(file_exists($HandlersDir.$JsFile)) $ModuleInitData['js'][] = 'user/handlers/'.$JsFile;
			
			
		}
		
	}	
	if(is_array($AddJsCss['css'])){
	
		foreach($AddJsCss['css'] as $CssFile){
			
			if(file_exists($HandlersDir.$JsFile)) $ModuleInitData['css'][] = 'user/handlers/'.$CssFile;
			
			
		}
		
	}

	$HandlersItems = scandir($HandlersDir);
	
	if(is_array($HandlersItems)){
		
		foreach($HandlersItems as $Item){
			
			if($Item!='.' && $Item!='..' && is_dir($HandlersDir.$Item)){
				
				if(file_exists($HandlersDir.$Item.'/options.js')) $ModuleInitData['js'][] = 'user/handlers/'.$Item.'/options.js';
				if(file_exists($HandlersDir.$Item.'/handler.js')) $ModuleInitData['js'][] = 'user/handlers/'.$Item.'/handler.js';
				if(file_exists($HandlersDir.$Item.'/handler.css')) $ModuleInitData['css'][] = 'user/handlers/'.$Item.'/handler.css';
				if(file_exists($HandlersDir.$Item.'/handler.tmpl')) $Content .= file_get_contents($HandlersDir.$Item.'/handler.tmpl');
				
			}
			
		}

		if($Content) $ModuleInitData['content'] = '<div class="handlers_tmpl off">'.$Content.'</div>';
		
	}	

?>