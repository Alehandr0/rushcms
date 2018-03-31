// События добавления элемента:

	$(document).on('page_editor_add_element', function(event, event_data, add_element_tree_st_id){
		
		// Событие перед добавлением элемента
		
		var col_table = event_data.col_table,
			parent_element_name = event_data.element_name,
			parent_element_id = event_data.id,
			parent_element_is_folder = event_data.is_folder,
			parent_element_tree_st_id = event_data.tree_st_id,
			parent_element_tree_vis = event_data.tree_vis,
			parent_element_options = event_data.options;
		
		var in_data = {};
		
		if(add_element_tree_st_id=='Some_structure_key'){
			
			// ...
			
		}
		else page_editor_add_Element(event_data, add_element_tree_st_id, in_data);  // Вызов page_editor_add_Element(event_data, add_element_tree_st_id, in_data) добавляет элемент
		
	});

	$(document).on('page_editor_add_element_end', function(event, event_data){
		
		// Событие после добавления элемента
		
		var col_table = event_data.col_table,
			add_element_name = event_data.element_name,
			add_element_id = event_data.id,
			add_element_is_folder = event_data.is_folder,
			add_element_tree_st_id = event_data.tree_st_id,
			add_element_tree_vis = event_data.tree_vis,
			add_element_options = event_data.options,
			
			in_data = event_data.in_data,
			out_data = event_data.out_data;

		if(add_element_tree_st_id=='Some_structure_key'){
			
			// ...
			
		}	

	});
	
// События сохранения элемента:

	$(document).on('page_editor_save_element', function(event, event_data){
		
		// Событие перед сохранением элемента
		
		var col_table = event_data.col_table,
			save_element_name = event_data.element_name,
			save_element_id = event_data.id,
			save_element_is_folder = event_data.is_folder,
			save_element_tree_st_id = event_data.tree_st_id,
			save_element_tree_vis = event_data.tree_vis,
			save_element_options = event_data.options,
			
			save_element_row_data = event_data.row_data;
		
		var in_data = {};
		
		if(save_element_tree_st_id=='Some_structure_key'){
			
			// ...
			
		}
		else page_editor_save_Element(event_data, in_data);  // Вызов page_editor_save_Element(event_data, in_data) сохраняет элемент
		
	});

	$(document).on('page_editor_save_element_end', function(event, event_data){
		
		// Событие после сохранения элемента
		
		var col_table = event_data.col_table,
			save_element_name = event_data.element_name,
			save_element_id = event_data.id,
			save_element_is_folder = event_data.is_folder,
			save_element_tree_st_id = event_data.tree_st_id,
			save_element_tree_vis = event_data.tree_vis,
			save_element_options = event_data.options,
			
			in_data = event_data.in_data,
			out_data = event_data.out_data;

		if(save_element_tree_st_id=='Some_structure_key'){
			
			// ...
			
		}

	});

// События удаления элемента:

	$(document).on('page_editor_del_element', function(event, event_data){
		
		// Событие перед удалением элемента
		
		var col_table = event_data.col_table,
			del_element_name = event_data.element_name,
			del_element_id = event_data.id,
			del_element_is_folder = event_data.is_folder,
			del_element_tree_st_id = event_data.tree_st_id,
			del_element_tree_vis = event_data.tree_vis,
			del_element_options = event_data.options;
		
		var in_data = {};	
		
		// ====================================================================
		
			var window_content = rc_main_GetTmpl({
				
				container : '.hidden_user_tmpl',  // user/main.tmpl
				tmpl : 'del_alert',
				
			});
		
			$('body').RS_modalOpen({

				'content' : window_content,
				'max-width' : 450,
				'head' : rc_main_GetLangValue('user_dict', 'del_alert_head'),
				'id' : 'user_del_confirm',
				'buttons' : {
					'ok' : {'alias' : 'Ok', 'disabled' : false},
				},
				'onClick' : function(data){

					page_editor_del_Element(event_data, in_data);
					
					$('#user_del_confirm.RS_modal_window').RS_modalClose();

				}
			});	

		// ====================================================================
		
		
		// if(del_element_tree_st_id=='Some_structure_key'){
			
			// // ...
			
		// }
		// else page_editor_del_Element(event_data, in_data);  // Вызов page_editor_del_Element(event_data, in_data) удаляет элемент
		
	});

	$(document).on('page_editor_del_element_end', function(event, event_data){
		
		// Событие после удаления элемента
		
		var col_table = event_data.col_table,
			del_element_name = event_data.element_name,
			del_element_id = event_data.id,
			del_element_is_folder = event_data.is_folder,
			del_element_tree_st_id = event_data.tree_st_id,
			del_element_tree_vis = event_data.tree_vis,
			del_element_options = event_data.options,
			
			in_data = event_data.in_data,
			out_data = event_data.out_data;
		
		if(del_element_tree_st_id=='Some_structure_key'){
			
			// ...
			
		}

	});
		