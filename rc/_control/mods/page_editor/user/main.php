<?php

	include_once "proc_func.php";  // Вспомогательные функции обработки данных
	include_once "check_func.php";  // Функции проверки (валидации) данных
	
	// =========================================================

	// Описание дерева (структуры) коллекций:

	$STreeDefEl = array(

		'parent' => '',
		'is_folder' => 1,
		'permissions' => array('2', '2'),
		'head_tmpl' => '{tree_name}',
		'ch_fold_sort' => '{tree_name}:{По имени};',
		'ch_file_sort' => '{tree_name}:{По имени};',
		'add_folder' => false,
		'auto_update' => false,
		'rc4_columns' => array(),

	);
	
	$STree['col_pages'] = array(
	
		'Страницы сайта' => array(
		
			'parent' => 'col_pages',
			'permissions' => array('2,3', '2'),
		
		),
			'Главная' => array(
			
				'parent' => 'Страницы сайта',
				'permissions' => array('2,3', '2'),

			),
				'Блог' => array(
				
					'parent' => 'Главная',
					'permissions' => array('2,3', '2'),
					'ch_file_sort' => '{tree_cd DESC}:{По дате создания};{tree_name}:{По имени};',

				),
					'Запись блога' => array(
					
						'is_folder' => 0,
						'parent' => 'Блог',
						'permissions' => array('2,3', '2,3'),
						'add_folder' => true,

					),
				'Каталог' => array(
				
					'parent' => 'Главная',
					'permissions' => array('2,3', '2'),
					'ch_file_sort' => '{data_pop DESC}:{По популярности};',

				),
					'Товар' => array(
					
						'is_folder' => 0,
						'parent' => 'Каталог',
						'permissions' => array('2,3', '2,3'),
						'add_folder' => true,

					),
				'Контакты' => array(
				
					'is_folder' => 0,
					'parent' => 'Главная',
					'permissions' => array('2,3', '2'),

				),
				'Корзина' => array(
				
					'is_folder' => 0,
					'parent' => 'Главная',
					'permissions' => array('2,3', '2'),

				),

	);
	
	$STree['col_users'] = array(

		'Пользователи' => array(
		
			'parent' => 'col_users',
			'rc4_columns' => array('tree_name', 'user_login'),
			'ch_fold_sort' => '{user_rights}:{По значению прав};',
		
		),
			'Группа пользователей' => array(
			
				'parent' => 'Пользователи',
				'head_tmpl' => '{tree_name} <span class="on" style="color:#f00;">({user_rights})</span><span class="off" style="color:#8bc34a;font-weight:400;">({user_rights})</span>',

			),
				'Пользователь' => array(
					
					'is_folder' => 0,
					'parent' => 'Группа пользователей',

				),

	);

	$STree['col_orders'] = array(

		'Заказы' => array(
		
			'parent' => 'col_users',
			'ch_file_sort' => '{tree_cd DESC}:{По дате создания};',
			'permissions' => array('2,3,4', '2'),
			'auto_update' => 'data_status',
		
		),
			'Заказ' => array(
				
				'is_folder' => 0,
				'parent' => 'Заказы',
				'permissions' => array('2,3,4', '2'),
				
			),

	);

	
	// =========================================================
	
	// Назначение примитивных обработчиков, описание полей


	$FieldDef = array(

		'name' => '',
		'handler' => '',
		'check' => '',
		'minimized' => false,

	);
	
	function FieldsOptions($Auth, $ColTable, $TreeStId){

		if($ColTable=='col_pages'){
		
			if($TreeStId=='Товар'){
				
				if($Auth==3){
					
					$FieldsDescription = array(
					
						'tree_vis' => array(
							'name' => 'Публикация товара на сайте',
							'handler' => '$STDH_CheckBox("0", "1", "Страница опубликована");',
							'check' => '$PregCheck("/^(0|1)$/");',
						),						
						'p_url' => array(
							'name' => 'URL страницы записи блога',
							'handler' => '$STDH_UrlHandler("data_url_autogen");',
							'check' => '$PregCheck("/^(\/[0-9A-Za-z\/_-]+\/|)$/");',
						),
							'data_url_autogen' => array(
								'name' => '',
								'handler' => '$FalseHandler();',
								'check' => '$PregCheck("/^(0|1)$/");',
							),					
						'tree_name' => array(
							'name' => 'Название товара',
							'handler' => '$STDH_TaInput();',
							'check' => '$NotEmptyVal();',
						),					
						'data_price' => array(
							'name' => 'Цена',
							'handler' => '$STDH_Input("Например, 5000");',
							'check' => '$PregCheck("/^[0-9]*$/");',
						),					
						'data_img' => array(
							'name' => 'Изображение товара',
							'handler' => '$STDH_ImgCollection("1");',
						),					
						'data_content' => array(
							'name' => 'Описание товара',
							'handler' => '$STDH_TextEditor("html");',
						),					

					);

				}
			
			}		
			else if($TreeStId=='Запись блога'){
				
				if($Auth==3){
					
					$FieldsDescription = array(
				
						'tree_vis' => array(
							'name' => 'Публикация записи блога на сайте',
							'handler' => '$STDH_CheckBox("0", "1", "Страница опубликована");',
							'check' => '$PregCheck("/^(0|1)$/");',
						),						
						'p_url' => array(
							'name' => 'URL страницы записи блога',
							'handler' => '$STDH_UrlHandler("data_url_autogen");',
							'check' => '$PregCheck("/^(\/[0-9A-Za-z\/_-]+\/|)$/");',
						),
							'data_url_autogen' => array(
								'name' => '',
								'handler' => '$FalseHandler();',
								'check' => '$PregCheck("/^(0|1)$/");',
							),
						'tree_name' => array(
							'name' => 'Название записи блога',
							'handler' => '$STDH_TaInput();',
							'check' => '$NotEmptyVal();',
						),										
						'data_content' => array(
							'name' => 'Содержимое записи блога',
							'handler' => '$STDH_Editor();',
						),						

					);

				}
			
			}			

			if($TreeStId!='Страницы сайта'){
				
				if($Auth==3){
				
					/*
						Обработчик для редактирования мета-тегов и page_title
						Для группы пользователей с типом прав «3»
						Для всех типов элементов (кроме самой коллекции)
						
					*/
					
					if(!is_array($FieldsDescription)) $FieldsDescription = array();

					$FieldsDescription = array_merge(
					
						array(
							'data_meta' => array(
								'name' => 'Мета-теги и имя страницы',
								'handler' => '$STDH_JSONEditor("meta_tmpl");',
								'minimized' => true,
							)
						),
						$FieldsDescription
					
					);
			
				}
				
			}
			
		
		}
		else if($ColTable=='col_orders'){
			
			if($TreeStId=='Заказ'){
				
				if($Auth==3 || $Auth==4){
					
					$FieldsDescription = array(

						'data_status' => array(
							'name' => 'Статус заказа',
							'handler' => '$STDH_CheckBox("0", "1", "Заказ обработан");',
							'check' => '$PregCheck("/^(0|1)$/");',
						),						
						'data_order' => array(
							'name' => 'Текст заказа',
							'handler' => '$STDH_ReadOnly();',
						),						

					);

				}
			
			}			
			
		}
		
		return $FieldsDescription;
		
	}

	// =========================================================
	
	// Обработка данных при различных событиях	

	function DataProcessing($Data){
		
		$Action = $Data['action'];
		$Auth = $Data['auth'];
		$ColTable = $Data['collection'];
		$TreeStId = $Data['tree_st_id'];
		$ParentData = $Data['parent_db_data'];
		$ElementData = $Data['db_data'];
		$OldElementData = $Data['old_db_data'];
		$DelElementsIdList = $Data['del_elements_id_list'];
		$InData = $Data['in_data'];
		$OutData = array();

		if($ColTable=='col_pages'){
		
			if($TreeStId=='Товар'){

				if($Action=='save'){
					
					if($Auth==3 || $Auth==4){

						$ElementData['data_img'] = ImgArrayProcessing($ColTable, $OldElementData, $OldElementData['data_img'], $ElementData['data_img']);
						if($ElementData['data_img']) $ElementData['data_img'] = json_encode($ElementData['data_img'], JSON_UNESCAPED_UNICODE);
						
						if($ElementData['data_url_autogen']==1) $ElementData['p_url'] = GetPageUrl($ParentData, $ElementData['tree_name'], $ElementData['id']);
						
					}
					
				}

			}
			else if($TreeStId=='Запись блога'){

				if($Action=='save'){

					if($Auth==3 || $Auth==4){
				
						if($ElementData['data_url_autogen']==1) $ElementData['p_url'] = GetPageUrl($ParentData, $ElementData['tree_name'], $ElementData['id']);
				
					}
					
				}
				
			}
			
			// ================================
			
			if($Auth==3 || $Auth==4){
			
				if($Action=='add'){
					
					$ElementData['p_url'] = GetPageUrl($ParentData, $ElementData['tree_name'], $ElementData['id']);
					
				}
			
			}
		
		}
		else if($ColTable=='col_orders'){

			if($TreeStId=='Заказ'){

				if($Action=='save'){
					
					// На случай, если хитрый пользователь передаст data_order для которого может 
					// быть назначен только обработчик «данные только для чтения» (STDH_ReadOnly)
					
					$ElementData['data_order'] = $OldElementData['data_order'];
					
				}

			}		
			
		}
		
		return array(

			'element_data' => $ElementData,
			'in_data' => $InData,  // Произвольные пользовательские данные из js события
			'out_data' => $OutData,  // Произвольные пользовательские данные для отдачи в js-обработчик события
		
		);
		
	}
	
	// =========================================================
	
	// Функция смены URL элемента при смене URL его родителя	
	
	function ChangeElementUrl($ElementData, $ParentElementData, $OldParentElementUrl){

		$OldElementURL = $Result = $ElementData['p_url'];
		
		$NewParentElementURL = $ParentElementData['p_url'];

		if(mb_substr($OldElementURL, 0, mb_strlen($OldParentElementUrl))==$OldParentElementUrl){
			
			$Result = $NewParentElementURL.mb_substr($OldElementURL, mb_strlen($OldParentElementUrl));
			
		}

		return $Result;

	}	
	
?>