$(window).resize(function(){
	
	page_editor_fileOptionsBlockResize();

});

$(document).ready(function(){
	
		page_editor_managerInit();

		page_editor_inputTextAreaAutoResize();

		// События, отменяющие установку min-height для правильного возврата скролла к сохраняемому элементу
		// в page_editor_createEditLinesInContainer

			$(window).on('mousewheel DOMMouseScroll', function(event) {

				if($('body').hasClass('remove_min_height_on_scroll')) $('body').removeClass('remove_min_height_on_scroll').css('min-height', 'auto');
				
			});
			
			document.addEventListener('touchmove', function(e) {

				if($('body').hasClass('remove_min_height_on_scroll')) $('body').removeClass('remove_min_height_on_scroll').css('min-height', 'auto');
			
			}, false);
			
		// ==================================================================================================
		
		$('#module_page_editor > .manager > .container').on('mousewheel DOMMouseScroll', function(e) {

			var e0 = e.originalEvent,
				delta = e0.wheelDelta || -e0.detail;

			this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
			e.preventDefault();
			
		});
		
		$('body').on('click', '#module_page_editor .manager .head', function(event){
			
			var o = $(this),
				to = $(event.target),
				mo = $('#module_page_editor .manager');

			if(!o.hasClass('empty')){
			
				if((!to.hasClass('label') && !to.hasClass('head') && !o.hasClass('no_children')) || (event.ctrlKey && !o.hasClass('no_children'))){
				
					o.toggleClass('open'); 
					
					if(o.hasClass('open') && !o.hasClass('loaded')){
					
						page_editor_loadFolderSubFolders(o);
						
					}

				}
				else{

					if(o.hasClass('on')){

						o.removeClass('on');
						$('#module_page_editor > .edit_wrap').removeClass('on');
						
					}
					else{			
				
						mo.find('.head.on').removeClass('on');
						o.addClass('on');

						var element_data = o.data('element-data');

						if(element_data['auto_upd_element']) page_editor_startAutoUpdCounts();						
						
						page_editor_loadFolderFilesList(o);
						
					}
				
				}
				
				page_editor_setManagerButtonsState();
				
			}
	

		});

		$('body').on('click', '*[data-action]', function(event){
			
			var target = $(event.target);
			
			if(target.data('action')) page_editor_action(target);
			else page_editor_action($(this));

		});
		
		$('body').on('keyup', '#module_page_editor > .edit_wrap input[name="files_search"]', function(event){

			var o = $(this),
				timer = o.data('timer');
				
			if(timer) clearTimeout(timer);
		
			timer = setTimeout(function() { page_editor_applyFilesFilter(); o.data('timer', false); }, 300);
			
			o.data('timer', timer);

		});

});

function page_editor_action(o){
	
	var action_arr = o.data('action').split(':'),
		action_type = action_arr[0],
		action = action_arr[1];
		
	if(!o.hasClass('disabled') || o.hasClass('test')){

		if(action_type=='edit_line'){
			
			if(action=='open_close'){
				
				o.toggleClass('open');
				
			}
			
		}				
		else if(action_type=='file_line'){
			
			if(action=='open_close' && !o.hasClass('open_disabled')){
				
				o.toggleClass('open');
				
				if(o.hasClass('open') && !o.hasClass('loaded')) page_editor_createEditLinesInContainer(o.next('.content'), o, {fast_close : true});
				
			}
			else if(action=='check_line'){
				
				var checked_lines_count = $('#module_page_editor > .edit_wrap').find('input.checkline:checked').length,
					bo = $('#module_page_editor > .edit_wrap > .options_wrap').find('.del_files_button');

				if(checked_lines_count>0) bo.removeClass('disabled');
				else bo.addClass('disabled');
				
			}
			
		}				
		else if(action_type=='files_del'){
			
			if(action=='del'){
				
				var checked_lines = $('#module_page_editor > .edit_wrap').find('input.checkline:checked'),
					del_elements_list = [],
					element_data = false;
				
				checked_lines.each(function(index){
					
					element_data = $(this).closest('.head').data('element-data');
					
					del_elements_list.push(element_data['id']);
					
				});
				
				if(element_data){
				
					var event_element_data = page_editor_getElementEventData(element_data),
						auth = $.trim($('#auth_value').val());
					
					event_element_data['del_elements_list'] = del_elements_list;
					
					if(auth>2) $(document).trigger('page_editor_del_element', event_element_data);
					else page_editor_del_Element(event_element_data, {});

				}
				
			}
			
		}				
		else if(action_type=='manager_button'){
			
			var head_o = page_editor_getActiveManagerElement();
			
			if(head_o.length==1){

				var element_data = head_o.data('element-data'),
					stree_data = $('body').data('stree-data')[element_data['col_table']][element_data['tree_st_id']];
			
				if(action=='add_folder'){
					
					page_editor_showAddElementTypeSelectWindowOrJustDoIt('folders', element_data, stree_data);
					
				}
				else if(action=='add_file'){
					
					page_editor_showAddElementTypeSelectWindowOrJustDoIt('files', element_data, stree_data);
					
				}
				else if(action=='del'){
					
					var event_element_data = page_editor_getElementEventData(element_data),
						auth = $.trim($('#auth_value').val());
					
					if(auth>2) $(document).trigger('page_editor_del_element', event_element_data);
					else page_editor_del_Element(event_element_data, {});
					
				}
				else if(action=='refresh'){
					
					if(element_data['auto_upd_element']) page_editor_startAutoUpdCounts();

					page_editor_loadFolderSubFolders(head_o, true);
					
				}

			}	
			
		}
		else if(action_type=='save_del_element'){
			
			var head_o = o.closest('.e_data_obj'),
				element_data = head_o.data('element-data'),
				auth = $.trim($('#auth_value').val());

			if(action=='save'){

				var event_element_data = page_editor_getSaveEventElementData(head_o, element_data),
					changed_element_save_confirm = 0;
				
				if(o.closest('.content').children('.error_message.changed_file').length > 0) changed_element_save_confirm = 1;
				
				event_element_data['changed_element_save_confirm'] = changed_element_save_confirm;
				
				if(auth>2) $(document).trigger('page_editor_save_element', event_element_data);
				else page_editor_save_Element(event_element_data, {});
				
			}
			else if(action=='del'){
				
				var event_element_data = page_editor_getElementEventData(element_data);
				
				if(auth>2) $(document).trigger('page_editor_del_element', event_element_data);
				else page_editor_del_Element(event_element_data, {});
				
			}
			else if(action=='fast_close'){
				
				o.closest('.content').prev('.head').removeClass('open');
				
			}
			
		}
		else if(action_type=='files_sort'){

			if(action=='select_sort'){
				
				page_editor_selectFilesSortType(o);

			}
			else if(action=='get_more'){
				
				var page_num = o.parent().data('current-page-num') || 2;
				
				page_editor_getFilesPage(page_num);

			}
			else if(action=='get_all'){
				
				var page_num = o.parent().data('current-page-num') || 2;
				
				page_editor_getFilesPage(page_num, function(){
					
					page_editor_action(o);
					
				});

			}
			else if(action=='search_clear'){
			
				$('#module_page_editor > .edit_wrap > .options_wrap').find('input[name="files_search"]').val('');
				
				page_editor_applyFilesFilter();

			}
			
		}
		
	}	
	
}

function page_editor_managerInit(){

	var manager_target = $('.manager > .container > .line');

	manager_target.html('');
	
	$.ajax({
		type: "POST",
		url: $('#module_path').val()+"/data/php/ajax/mn_init.php",
		dataType: 'json',
		data: {},
		success: function(rdata){
			
			$('body').data('stree-data', rdata.stree_data);
			
			for(var key in rdata.items){
				
				page_editor_addManagerElement(manager_target, rdata.items[key], false, true);
				
			}
			
			page_editor_startAutoUpdCounts();
			
		}
		
	});
	
}

function page_editor_getActiveManagerElement(){
	
	return $('#module_page_editor > .manager').find('.head.on');
	
}	

function page_editor_getActiveManagerElementData(){
	
	var head_o = $('#module_page_editor > .manager').find('.head.on'),
		element_data = head_o.data('element-data') || {};

	return element_data;

}	

function page_editor_getElementDataByEditLine(line){
	
	var head_o = line.closest('.e_data_obj'),
		element_data = head_o.data('element-data') || {};

	return element_data;
	
}	
	
function page_editor_addManagerElement(target, element_data = {}, is_new, append){

	var manager_item = rc_main_GetTmpl({
			
			container : '.hidden_tmpl_page_editor',
			tmpl : 'manager_item',
			unwrap : true
			
		}),
		ho = manager_item.filter('.head'),   
		empty_mark = target.children('.head.empty');

	ho.find('.label').html(element_data['element_name']);  
	ho.data('element-data', element_data);
	
	manager_item.attr('data-element-id', (element_data['col_table']+':'+element_data['id']));
	
	if(is_new) ho.addClass('new');  
	
	if(element_data['no_children']) ho.addClass('no_children');	
	if(element_data['tree_vis']==0) ho.addClass('not_visible');	
	
	if(empty_mark.length>0) empty_mark.add(empty_mark.next('.content')).remove();
	
	if(append) target.append(manager_item);  
	else target.prepend(manager_item);
	
	return manager_item;
	
}

function page_editor_resetManagerElement(ho, element_data = {}){
	
	var manager_item = ho.add(ho.next('.content'));
	
	ho.find('.label').html(element_data['element_name']);  
	ho.data('element-data', element_data);
	
	manager_item.attr('data-element-id', (element_data['col_table']+':'+element_data['id']));
	
	ho.removeClass('no_children not_visible');

	if(element_data['no_children']) ho.addClass('no_children');	
	if(element_data['tree_vis']==0) ho.addClass('not_visible');	

}

function page_editor_loadFolderSubFolders(head_o, refresh = false){
	
	var content_o = head_o.next('.content'),
		element_data = head_o.data('element-data') || {};
	
	if(content_o.length>0){
	
		head_o.addClass('loaded');
		content_o.html('');
		
		$.ajax({
			type: "POST",
			url: $('#module_path').val()+"/data/php/ajax/mn_getSubFolders.php",
			dataType: 'json',
			data: {element_data : element_data},
			success: function(rdata){

				if(refresh) content_o.html('');
				
				for(var key in rdata.items){
					
					page_editor_addManagerElement(content_o, rdata.items[key], false, true);
					
				}
				
				page_editor_checkEmptyFolderElement(content_o);
				
				if(refresh) page_editor_loadFolderFilesList(head_o);
				
			}
			
		});		
	
	}
	
}

function page_editor_checkEmptyFolderElement(content_o){

	content_o.find('.head.empty').remove();
	
	if(content_o.find('.head').length==0){
	
		var manager_item = rc_main_GetTmpl({
			
				container : '.hidden_tmpl_page_editor',
				tmpl : 'manager_item',
				unwrap : true
			
			}),
			ho = manager_item.filter('.head');
			
		ho.find('.label').text(rc_main_GetLangValue('page_editor', 'empty_folder'));
		ho.addClass('empty');
		
		content_o.append(manager_item);  
	
	}
	
}

function page_editor_setManagerButtonsState(head_o = false){

	var buttons = $('#module_page_editor > .manager > .buttons > .button'),
		selected_element = $('#module_page_editor > .manager').find('.head.on');
		
	if(head_o !== false) selected_element = head_o;

	buttons.addClass('off disabled');
	
	if(selected_element.length>0){
		
		var is_hidden = false;
		
		selected_element.parents('.content').each(function(index){

			if(!$(this).prev('.head').hasClass('open')) is_hidden = true;
			
		});

		if(!is_hidden){
	
			var element_data = selected_element.data('element-data'),
				stree_data = $('body').data('stree-data')[element_data['col_table']][element_data['tree_st_id']];
			
			if(!jQuery.isEmptyObject(stree_data['children']['files']['add_del'])) buttons.filter('.add_file').removeClass('off');
			if(!jQuery.isEmptyObject(stree_data['children']['folders']['add_del'])) buttons.filter('.add_folder').removeClass('off');
			
			if(stree_data['permissions']['add_del']) buttons.filter('.del').removeClass('off');
			
			buttons.filter('.refresh').removeClass('off');
			
			buttons.removeClass('disabled');
			
			if(!selected_element.hasClass('open')) buttons.filter('.add_folder').addClass('disabled');
		
		}
		else{
			
			selected_element.removeClass('on');
			page_editor_setFilesEditWrapState();
			
		}
		
	}
	
}

function page_editor_setFilesEditWrapState(){

	var edit_wrap_o = $('#module_page_editor > .edit_wrap'),
		fl_o = edit_wrap_o.find('.file_line'),
		opt_wo = edit_wrap_o.children('.options_wrap'),
		mse_o = page_editor_getActiveManagerElement();
	
	if(fl_o.length==0 || mse_o.length==0) edit_wrap_o.removeClass('on');
	else edit_wrap_o.addClass('on');
	
	if(fl_o.not('.folder').length==0) opt_wo.addClass('off');
	else opt_wo.removeClass('off');
	
}

function page_editor_handlerListRemoveFakeHandlersAndToArray(handlers_list){
	
	var result = [];
	
	for(var key in handlers_list){
		
		var handler = handlers_list[key]['handler'];
		
		if(handler!='$FakeHandler();' && handler) result.push(key);
		
	}
	
	return result;
	
}

function page_editor_loadFolderFilesList(head_o, options = {}, callback = false){
	
	var content_o = $('#module_page_editor > .edit_wrap'),
		error_wrap = $('#module_page_editor > .error_wrap'),
		options_o = content_o.find('.options_wrap'),
		element_data = head_o.data('element-data') || {},
		stree_data = $('body').data('stree-data'),
		handlers_list = page_editor_handlerListRemoveFakeHandlersAndToArray(stree_data[element_data['col_table']][element_data['tree_st_id']]['handlers']),
		file_folder_element_ho = content_o.find('.file_line.folder > .head');
	
	if(content_o.length>0){
		
		error_wrap.removeClass('on');
		
		options_o.find('.del_files_button').addClass('disabled');
	
		if(!options.page_num){
			
			content_o.addClass('on').children('.file_line:not(.folder)').remove();
			content_o.find('.file_line.folder > .head').removeClass('open loaded');
			options_o.addClass('off');
		
		}
		
		$.ajax({
			type: "POST",
			url: $('#module_path').val()+"/data/php/ajax/mn_getSubFiles.php",
			dataType: 'json',
			data: {element_data : element_data, options : options},
			success: function(rdata){

				if(rdata.is_deleted){
					
					head_o.addClass('deleted');
					
					var error_message = rc_main_GetTmpl({
							
							container : '.hidden_tmpl_page_editor',
							tmpl : 'error_message_deleted_folder',
							
						});
					
					content_o.removeClass('on');
					
					error_wrap.addClass('on').children('*').remove();
					error_wrap.append(error_message);
					
				}
				else{
					
					head_o.removeClass('deleted');

					if(rdata.items.length>0) options_o.removeClass('off');

					options_o.find('.current_count').text(rdata.count_data['current_count']);
					options_o.find('.total_count').text(rdata.count_data['total_count']);
					if(rdata.count_data['current_count']==rdata.count_data['total_count']) options_o.find('.button').addClass('off');
					else options_o.find('.button').removeClass('off');
					
					options_o.find('.sort_button').text(rdata.count_data['sort_data']['name']).data('sort-type-index', rdata.count_data['sort_index']);
					if(rdata.count_data['sort_data']['direction']=='none') options_o.find('.sort_direction').text('');
					else if(rdata.count_data['sort_data']['direction']) options_o.find('.sort_direction').text('↓');
					else options_o.find('.sort_direction').text('↑');
					
					if(rdata.count_data['sort_variants']<=1) options_o.find('.sort_button').addClass('disabled');
					else options_o.find('.sort_button').removeClass('disabled');
					
					if(!options.page_num) options_o.find('.count_options').data('current-page-num', 2);
					else options_o.find('.count_options').data('current-page-num', (options.page_num+1));

					if(element_data['tree_vis']==0) file_folder_element_ho.addClass('not_visible');
					else file_folder_element_ho.removeClass('not_visible');
					
					file_folder_element_ho.data('element-data', element_data);
					file_folder_element_ho.parent().attr('data-element-id', (element_data['col_table']+':'+element_data['id']));
					file_folder_element_ho.find('.label').html(element_data['element_name']);
					file_folder_element_ho.find('.label').find('.off').removeClass('off');
					file_folder_element_ho.find('.label').find('.on, .new_count').addClass('off');
					
					if(handlers_list.length==0) file_folder_element_ho.addClass('open_disabled');
					else file_folder_element_ho.removeClass('open_disabled');
					
					var append = true,
						target = content_o;
						
					if(options.page_num){
					
						append = false;
						target = content_o.find('.file_line:last');
						
					}

					for(var key in rdata.items){

						page_editor_addFileElement(target, rdata.items[key], false, append);
						
					}
					
					if(typeof callback  === 'function' && rdata.count_data['current_count']<rdata.count_data['total_count']) callback.call();
					else page_editor_fileOptionsBlockResize();
				
				}
				
			}
			
		});		
	
	}
		
	
}

function page_editor_addFileElement(target, element_data = {}, is_new, append = true){

	var file_line = rc_main_GetTmpl({
			
			container : '.hidden_tmpl_page_editor',
			tmpl : 'file_line',
			
		}),
		ho = file_line.find('.head'),
		stree_data = $('body').data('stree-data'),
		handlers_list = page_editor_handlerListRemoveFakeHandlersAndToArray(stree_data[element_data['col_table']][element_data['tree_st_id']]['handlers']),
		permissions = stree_data[element_data['col_table']][element_data['tree_st_id']]['permissions'];

	ho.find('.label').html(element_data['element_name']);  
	ho.data('element-data', element_data);
	
	file_line.attr('data-element-id', (element_data['col_table']+':'+element_data['id']) );
	
	if(is_new) ho.addClass('new');  
	
	if(element_data['tree_vis']==0) ho.addClass('not_visible');
	
	if(element_data['auto_upd_status']) ho.addClass('new_auto_upd_status');
	
	if(handlers_list.length==0) ho.addClass('open_disabled');
	else ho.removeClass('open_disabled');
	
	if(!permissions['add_del']) file_line.find('input.checkline').remove();
	
	if(append) target.append(file_line);
	else target.after(file_line);
	
	return file_line;
	
}

function page_editor_resetFileElement(ho, element_data = {}){
	
	var file_line = ho.parent('.file_line');

	ho.find('.label').html(element_data['element_name']);  
	ho.data('element-data', element_data);
	
	file_line.attr('data-element-id', (element_data['col_table']+':'+element_data['id']) );
	
	ho.removeClass('not_visible');
	if(element_data['tree_vis']==0) ho.addClass('not_visible');
	
	ho.removeClass('new_auto_upd_status');
	if(element_data['auto_upd_status']) ho.addClass('new_auto_upd_status');
	
	if(ho.hasClass('open')) page_editor_createEditLinesInContainer(ho.next('.content'), ho, {fast_close : true, reload : true});
	
}

function page_editor_createEditLinesInContainer(target, head_o, options = {}){
	
	var element_data = head_o.data('element-data'),
		stree_data = $('body').data('stree-data');
	
	if(options.reload){
		
		var handlers_list = stree_data[element_data['col_table']][element_data['tree_st_id']]['handlers'],
			lines_to_open = [],
			lines_to_close = [];
		
		head_o.next('.content').children('.edit_line').each(function(index){
			
			var field_key = $(this).data('key'),
				minimized = handlers_list[field_key]['minimized'];
				
			if(minimized && $(this).children('.head').hasClass('open')) lines_to_open.push(field_key);
			else if(!minimized && !$(this).children('.head').hasClass('open')) lines_to_close.push(field_key);

		});

		var reload_data = {
			
			'body_height' : $('body').outerHeight(),
			'scroll_top' : $(window).scrollTop(),
			'lines_to_open' : lines_to_open,
			'lines_to_close' : lines_to_close,
			
		};
		
	}

	head_o.addClass('loaded');
	target.data('element-data', element_data);

	$.ajax({
		type: "POST",
		url: $('#module_path').val()+"/data/php/ajax/mn_getElementData.php",
		dataType: 'json',
		data: {element_data : element_data},
		success: function(rdata){
			
			if(rdata.is_deleted){
				
				var content_o = head_o.next('.content'),
					error_message = rc_main_GetTmpl({
						
						container : '.hidden_tmpl_page_editor',
						tmpl : 'error_message_deleted_file',
						
					});					
					
				content_o.children('*').remove();
				content_o.append(error_message);

			}
			else{
			
				var element_stree_data = stree_data[element_data['col_table']][element_data['tree_st_id']],
					handlers = element_stree_data['handlers'],
					permissions = element_stree_data['permissions'],
					save_del_block = rc_main_GetTmpl({
						
						container : '.hidden_tmpl_page_editor',
						tmpl : 'save_del_block',
						
					});
					
				target.html('');
				
				if(!jQuery.isEmptyObject(handlers)){
					
					for(var key in handlers){

						page_editor_addEditLine(target, handlers[key], key, rdata.item_data[key], rdata.item_data);
						
					}

				}
				else save_del_block.find('.button.save').addClass('disabled');
				
				if(!permissions['add_del'] || element_stree_data['collection_folder']) save_del_block.find('.button.del').addClass('disabled');

				if(options['fast_close']) save_del_block.find('.fast_close').removeClass('off');
				
				if(target.find('.edit_line').length == 0){
					
					save_del_block = rc_main_GetTmpl({
						
						container : '.hidden_tmpl_page_editor',
						tmpl : 'empty_save_del_block',
						
					});			
					
				}
				
				target.append(save_del_block);
				
				if(options.reload){
					
					$('body').css('min-height', reload_data['body_height']+'px').addClass('remove_min_height_on_scroll');

					$(window).scrollTop(reload_data['scroll_top']);
					
					var edit_lines = head_o.next('.content').children('.edit_line');
					
					for(var i in reload_data['lines_to_open']){
						
						var field_key = reload_data['lines_to_open'][i];
						
						edit_lines.filter('[data-key="'+field_key+'"]').children('.head').addClass('open');
						
					}
					
					for(var i in reload_data['lines_to_close']){
						
						var field_key = reload_data['lines_to_close'][i];
						
						edit_lines.filter('[data-key="'+field_key+'"]').children('.head').removeClass('open');
						
					}
					
				}			
				
				$('#rsmod_select_add_el_type.RS_modal_window').RS_modalClose();
				
				if(options['add_action']){

					$('body').RS_modalOpen({

						'content' : target,
						'max-width' : 878,
						'head' : rc_main_GetLangValue('page_editor', 'new_elem_edit'),
						'id' : 'rsmod_edit_after_add',
						'no_close_on_overlay_click' : true,
						'no_close_button' : true,

					});

					if(options['tree_is_folder']==1){
						
						var content_o = page_editor_getActiveManagerElement().next('.content');
						
						page_editor_addManagerElement(content_o, target.data('element-data'), true, false);
						
					}
					else{

						var content_o = $('.edit_wrap > .options_wrap');

						page_editor_setFilesOptionsNewDeltaCounts(1);
						
						content_o.removeClass('off');
						
						page_editor_addFileElement(content_o, target.data('element-data'), true, false);
						
					}
					
				}

			}
		
		}

	});
	
}

function page_editor_addEditLine(target, handler_data = {}, key, insert_val, all_values = {}){
	
	var edit_line = rc_main_GetTmpl({
			
			container : '.hidden_tmpl_page_editor',
			tmpl : 'edit_line',
			
		}),
		ho = edit_line.find('.head'),   
		matches = handler_data['handler'].match(/\$([a-zA-Z0-9_-]+)\(([\d\D]*)\);/);
		
	
	if(matches){
		
		target.append(edit_line);
		
		var handler_func_name = matches[1],
			handler_func_params = matches[2],
			real_auth = parseInt($('#real_auth_value').val());
	
		if(handler_func_name){
			
			var handler_init_func = window[handler_func_name+'_init'];
			
			if(typeof handler_init_func === 'function'){
				
				var line_name = handler_data['name'] || key;
				
				if(real_auth==2 && line_name!=key) line_name = '('+key+') ' + line_name;
				
				if(handler_data['minimized']) ho.removeClass('open');

				var edit_line_handler_data = {
					
					'func_name' : handler_func_name,
					'func_params' : handler_func_params,
					'column_key' : key,
					
				};
				
				edit_line.attr('data-key', key);
				edit_line.data('handler-data', edit_line_handler_data);
				edit_line.addClass(handler_func_name);
				ho.find('.text').html(line_name);
				
				if(handler_init_func(edit_line, handler_func_params, insert_val, all_values)===false) edit_line.remove();
				
				
			}
			else edit_line.remove();
			
			
		}

	}
	
	return edit_line;	
	
}

function page_editor_showAddElementTypeSelectWindowOrJustDoIt(type, element_data, stree_data){
	
	var add_children_stree_data = stree_data['children'][type]['add_del'];
	
	if(!jQuery.isEmptyObject(add_children_stree_data)){
		
		var window_lines_content = '',
			count = 0,
			sel_tree_st_id = false,
			auth = $.trim($('#auth_value').val()),
			multi_types_element_add_window = rc_main_GetTmpl({
				
				container : '.hidden_tmpl_page_editor',
				tmpl : 'multi_types_element_add_window',
				
			});								
		
		for(var key in add_children_stree_data){
			
			if(!sel_tree_st_id) sel_tree_st_id = key;

			window_lines_content += '<label class="radio"><input type="radio" name="element_type" value="'+key+'" /> <span class="label">'+key+'</span></label>';
			count++;
			
		}
		
		if(count>1){
			
			
			multi_types_element_add_window.find('.content').append(window_lines_content);  
			multi_types_element_add_window.find('.content input[type="radio"]:first').prop('checked', true);  

			$('body').RS_modalOpen({

				'content' : multi_types_element_add_window,
				'max-width' : 450,
				'head' : rc_main_GetLangValue('page_editor', 'add_elem'),
				'id' : 'rsmod_select_add_el_type',
				'buttons' : {
					'ok' : {'alias' : rc_main_GetLangValue('page_editor', 'add_button'), 'disabled' : false},
				},
				'onClick' : function(data){

					sel_tree_st_id = data.content_wrap.find('input[type="radio"]:checked').val();  

					var event_element_data = page_editor_getElementEventData(element_data);

					if(auth>2) $(document).trigger('page_editor_add_element', [event_element_data, sel_tree_st_id]);
					else page_editor_add_Element(event_element_data, sel_tree_st_id, {});

				}

			});										

		}
		else if(sel_tree_st_id){
			
			var event_element_data = page_editor_getElementEventData(element_data);

			if(auth>2) $(document).trigger('page_editor_add_element', [event_element_data, sel_tree_st_id]);
			else page_editor_add_Element(event_element_data, sel_tree_st_id, {});
			
		}
		
	}	

}

function page_editor_add_Element(event_element_data, add_tree_st_id, in_data){
	
	$.ajax({
		type: "POST",
		url: $('#module_path').val()+"/data/php/ajax/mn_addElement.php",
		dataType: 'json',
		data: {element_data : event_element_data, tree_st_id : add_tree_st_id, in_data : in_data},
		success: function(rdata){

			var event_element_data = page_editor_getElementEventData(rdata.element_data),
				auth = $.trim($('#auth_value').val());
			
			event_element_data['in_data'] = rdata.in_data;
			event_element_data['out_data'] = rdata.out_data;
			
			if(auth>2) $(document).trigger('page_editor_add_element_end', event_element_data);
			
			if(jQuery.isPlainObject(event_element_data['options']['handlers'])){
				
				var	edit_lines_wrap = rc_main_GetTmpl({
						
						container : '.hidden_tmpl_page_editor',
						tmpl : 'edit_lines_wrap',
						
					});
					
				edit_lines_wrap.data('element-data', rdata.element_data);
				
				page_editor_createEditLinesInContainer(edit_lines_wrap, edit_lines_wrap, {'add_action' : true, 'tree_is_folder' : rdata.tree_is_folder});

			}
			else{
				
				if(rdata.tree_is_folder==1){
					
					var content_o = page_editor_getActiveManagerElement().next('.content');
					
					page_editor_addManagerElement(content_o, rdata.element_data, true, false);
					
				}
				else{
					
					var content_o = $('.edit_wrap > .options_wrap');

					page_editor_setFilesOptionsNewDeltaCounts(1);
					
					content_o.removeClass('off');
					
					page_editor_addFileElement(content_o, rdata.element_data, true, false);
					
				}				
				
				$('#rsmod_select_add_el_type.RS_modal_window').RS_modalClose();
				
			}

		}
		
	});
	
}

function page_editor_del_Element(element_data, in_data){
	
	$.ajax({
		type: "POST",
		url: $('#module_path').val()+"/data/php/ajax/mn_delElement.php",
		dataType: 'json',
		data: {element_data : element_data, in_data : in_data},
		success: function(rdata){
			
			var auth = $.trim($('#auth_value').val());
			
			if(element_data['del_elements_list']){
				
				for(var i in element_data['del_elements_list']){
					
					var id_to_del = (element_data['col_table']+':'+element_data['del_elements_list'][i]);
					page_editor_removeManagerFoldersAndEditFilesElements(id_to_del);
					
				}
				
			}
			else{
				
				var id_to_del = (element_data['col_table']+':'+element_data['id']);
				page_editor_removeManagerFoldersAndEditFilesElements(id_to_del);
			
			}

			$('#rsmod_edit_after_add.RS_modal_window').RS_modalClose();
			
			element_data['in_data'] = rdata.in_data;
			element_data['out_data'] = rdata.out_data;
			
			if(element_data['tree_is_folder']!=1) page_editor_setFilesOptionsNewDeltaCounts(-1);
			
			var checked_lines_count = $('#module_page_editor > .edit_wrap').find('input.checkline:checked').length,
				bo = $('#module_page_editor > .edit_wrap > .options_wrap').find('.del_files_button');

			if(checked_lines_count>0) bo.removeClass('disabled');
			else bo.addClass('disabled');

			if(auth>2) $(document).trigger('page_editor_del_element_end', element_data);
			
		}
		
	});
	
}

function page_editor_save_Element(save_element_data, in_data){
	
	var save_lines_wrap = save_element_data['obj'];
	
	save_element_data['obj'] = '';
	
	$.ajax({
		type: "POST",
		url: $('#module_path').val()+"/data/php/ajax/mn_saveElement.php",
		dataType: 'json',
		data: {element_data : save_element_data, in_data : in_data},
		success: function(rdata){
			
			var head_o = page_editor_getActiveManagerElement(),
				parent_element_data = head_o.data('element-data');
				
			if(parent_element_data['auto_upd_element']) page_editor_startAutoUpdCounts();
			
			if(rdata.is_deleted){
				
				var head_o_file = $('#module_page_editor > .edit_wrap').find('.file_line[data-element-id="'+(save_element_data['col_table']+':'+save_element_data['id'])+'"]').children('.head'),
					content_o = head_o_file.next('.content'),
					error_message = rc_main_GetTmpl({
						
						container : '.hidden_tmpl_page_editor',
						tmpl : 'error_message_deleted_file',
						
					});					
					
				content_o.children('*').remove();
				content_o.append(error_message);

			}
			else if(rdata.is_changed){

				var head_o_file = $('#module_page_editor > .edit_wrap').find('.file_line[data-element-id="'+(save_element_data['col_table']+':'+save_element_data['id'])+'"]').children('.head'),
					content_o = head_o_file.next('.content'),
					error_message = rc_main_GetTmpl({
						
						container : '.hidden_tmpl_page_editor',
						tmpl : 'error_message_changed_file',
						
					});					

				content_o.prepend(error_message);
				
			}
			else{

				if(rdata.error_fields.length>0){
					
					for(var key in rdata.error_fields){
					
						save_lines_wrap.find('.edit_line[data-key='+rdata.error_fields[key]+']').addClass('error');
						
					}
					
				}
				else{

					save_lines_wrap.prev('.head').addClass('save_indication');
					setTimeout(function() { save_lines_wrap.prev('.head').removeClass('save_indication'); }, 1000);
					
					var head_o_file = $('#module_page_editor > .edit_wrap').find('.file_line[data-element-id="'+(save_element_data['col_table']+':'+save_element_data['id'])+'"]').children('.head'),
						content_o = head_o_file.next('.content'),
						head_o_folder = $('#module_page_editor > .manager').find('.head[data-element-id="'+(save_element_data['col_table']+':'+save_element_data['id'])+'"]'),
						auth = $.trim($('#auth_value').val());

					if(head_o_file.length>0) page_editor_resetFileElement(head_o_file, rdata.element_data);
					if(head_o_folder.length>0) page_editor_resetManagerElement(head_o_folder, rdata.element_data);
					
					$('#rsmod_edit_after_add.RS_modal_window').RS_modalClose();
					
					content_o.children('.error_message').remove();
					
					if(auth>2) $(document).trigger('page_editor_save_element_end', save_element_data);

				}
			
			}
			
		}
		
	});
	
}

function page_editor_getSaveEventElementData(head_o, element_data){

	var event_element_data = page_editor_getElementEventData(element_data),
		row_data = {};
	
	head_o.find('.edit_line').each(function(index){
		
		var edit_line = $(this),
			handler_data = $(this).data('handler-data') || {},
			handler_getval_func = window[handler_data['func_name']+'_getval'];

		if(typeof handler_getval_func === 'function'){

			var val = handler_getval_func(edit_line, handler_data['func_params']);
			
			if(jQuery.isPlainObject(val)){
				
				if(val['multidata']) row_data = $.extend({}, row_data, val['data']);
				else row_data[handler_data['column_key']] = val;
				
			}
			else row_data[handler_data['column_key']] = val;
			
		}

	});
	
	event_element_data['row_data'] = row_data;
	event_element_data['obj'] = head_o;
	
	return event_element_data;
	
}

function page_editor_removeManagerFoldersAndEditFilesElements(id){
	
	var mse_o = page_editor_getActiveManagerElement(),
		n_mse_o = mse_o.next('.content'),
		p_mse_o = mse_o.parent('.content');

	$('*[data-element-id="'+id+'"]').remove();
	page_editor_setManagerButtonsState();
	page_editor_setFilesEditWrapState();
	
	if(p_mse_o.length>0) page_editor_checkEmptyFolderElement(p_mse_o);
	if(n_mse_o.length>0) page_editor_checkEmptyFolderElement(n_mse_o);

}

function page_editor_getElementEventData(element_data){
	
	var stree_data = $('body').data('stree-data'),
		event_element_data = {};
	
	if( jQuery.isPlainObject(stree_data) && jQuery.isPlainObject(element_data) ){

		event_element_data = element_data;
		event_element_data['options'] = stree_data[element_data['col_table']][element_data['tree_st_id']];
		
	}
	
	return event_element_data;

}

function page_editor_setFilesOptionsNewDeltaCounts(delta){
	
	var content_o = $('.edit_wrap > .options_wrap'),
		cco = content_o.find('.current_count'),
		tco = content_o.find('.total_count'),
		current_count = parseInt(cco.text()),
		total_count = parseInt(tco.text());

	cco.text(current_count+delta);
	tco.text(total_count+delta);

}

function page_editor_selectFilesSortType(o){
	
	var current_index = o.data('sort-type-index') || 0,
		head_o = page_editor_getActiveManagerElement(),
		element_data = head_o.data('element-data'),
		current_name = o.text(),
		sort_data = $('body').data('stree-data')[element_data['col_table']][element_data['tree_st_id']]['ch_file_sort'],
		window_lines_content = '',
		select_sort_type = rc_main_GetTmpl({
			
			container : '.hidden_tmpl_page_editor',
			tmpl : 'select_sort_type',
			
		});
	
	for(var key in sort_data){

		var checked = '',
			sort_direction = '↑';

		if(key==current_index) checked = 'checked';
		
		if(sort_data[key]['direction']=='none') sort_direction = '';
		else if(sort_data[key]['direction']) sort_direction = '↓';
	
		window_lines_content += '<label class="radio"><input type="radio" name="element_type" value="'+key+'" '+checked+' /> <span class="label">'+sort_data[key]['name']+' '+sort_direction+'</span></label>';
		
	}						
	
	select_sort_type.find('.content').append(window_lines_content);
	
	$('body').RS_modalOpen({

		'content' : select_sort_type,
		'max-width' : 450,
		'head' : rc_main_GetLangValue('page_editor', 'sort_select'),
		'id' : 'rsmod_select_file_sort_type',
		'buttons' : {
			'ok' : {'alias' : rc_main_GetLangValue('page_editor', 'select_button'), 'disabled' : false},
		},
		'onClick' : function(data){

			sort_index = data.content_wrap.find('input[type="radio"]:checked').val();  
			
			page_editor_loadFolderFilesList(head_o, options = {'sort_index' : sort_index});
			
			$('#rsmod_select_file_sort_type.RS_modal_window').RS_modalClose();

		}

	});
	
}

function page_editor_getFilesPage(page_num, callback = false){
	
	var head_o = page_editor_getActiveManagerElement(),
		sort_index = $('#module_page_editor > .edit_wrap > .options_wrap').find('.sort_button').data('sort-type-index') || 0;

	page_editor_loadFolderFilesList(head_o, {
		
		sort_index : sort_index,
		page_num : page_num,
		
	}, callback);	
	
}

function page_editor_applyFilesFilter(){

	var io = $('#module_page_editor > .edit_wrap input[name="files_search"]'),
		val = io.val(),
		files_o = $('#module_page_editor > .edit_wrap .file_line:not(.folder)');
	
	files_o.addClass('off');
	files_o.filter('*:containsCIN("'+val+'")').removeClass('off');	
	
}

function page_editor_fileOptionsBlockResize(){

	var o = $('#module_page_editor > .edit_wrap > .options_wrap'),
		bo = o.find('.options_block');
	
	o.removeClass('two_lines');
	
	if(bo.eq(0).offset().top!=bo.eq(1).offset().top) o.addClass('two_lines');
	
}

function page_editor_inputTextAreaAutoResize(o = false, min_height = 0){

	if(!o) o = $('textarea.autoresized_textarea');
	
	o.each(function(){
		
		var h = this.scrollHeight;
		
		if(h<min_height) h = min_height;
		
		$(this).css({
			
			'height' : h+'px',
			'overflow-y' : 'hidden',
			
		});
		
	});

	o.on('input', function(){
		
		var h = this.scrollHeight;
		
		if(h<min_height) h = min_height;

		$(this).css({'height' : 'auto'});		
		$(this).css({'height' : h+'px'});
		
	});
	
}

function page_editor_getColTableFromLine(line){
	
	var element_id = line.closest('.file_line').data('element-id'),
		result = '';
	
	if(element_id){
		
		result = element_id.split(':')[0];
		
	}
	
	return result;
	
}

function page_editor_startAutoUpdCounts(){
	
	var old_daemon_timer_id = $('body').data('daemon-timer-id') || false;
	
	if(old_daemon_timer_id) clearInterval(old_daemon_timer_id);

	page_editor_getAutoUpdCounts(false);
	
	var timerId = setInterval(function(){

		page_editor_getAutoUpdCounts(timerId);

	}, 60000);
	
	$('body').data('daemon-timer-id', timerId);
	
}

function page_editor_getAutoUpdCounts(timerId){
	
	var elements_list = {};
	
	if(timerId){
		
		var selected_head_o = page_editor_getActiveManagerElement();
			
		if(selected_head_o.length>0){

			var element_data = selected_head_o.data('element-data');
				
			if(jQuery.isPlainObject(element_data)){

				if(element_data['auto_upd_element']){
					
					var edit_wrap = $('#module_page_editor > .edit_wrap');

					elements_list[element_data['id']] = [];
					
					edit_wrap.find('.file_line > .head.new_auto_upd_status').each(function(index){
						
						var file_element_data = $(this).data('element-data');
						
						elements_list[element_data['id']].push(file_element_data['id']);
						
					});

				}
			
			}
		
		}

	}
	
	$.ajax({
		type: "POST",
		url: $('#module_path').val()+"/data/php/ajax/mn_rcmsDaemon.php",
		dataType: 'json',
		data: {elements_list : elements_list},
		success: function(rdata){

			if(rdata.data == 'no_auto_upd_elements' && timerId) clearInterval(timerId);
			else if($.isArray(rdata.data) || jQuery.isPlainObject(rdata.data)){
				
				var manager = $('#module_page_editor > .manager');
				
				for (var key in rdata.data){
					
					var head_o = manager.find('.head[data-element-id="'+rdata.data[key]['col_table']+':'+rdata.data[key]['id']+'"]');
					
					if(head_o.length > 0){
						
						var label = head_o.children('.label'),
							counter = label.children('span.auto_upd_counter'),
							old_count = 0;
							
						if(rdata.data[key]['must_refresh'] && head_o.hasClass('on')){

							var content_o = $('#module_page_editor > .edit_wrap'),
								error_wrap = $('#module_page_editor > .error_wrap');
								
							error_wrap.children().remove();

							var error_message = rc_main_GetTmpl({

									container : '.hidden_tmpl_page_editor',
									tmpl : 'notice_message_refresh_auto_upd_folder',

								});

							error_wrap.addClass('on').children('*').remove();
							error_wrap.append(error_message);	

							
						}
						
						counter.remove();

						label.append('<span class="auto_upd_counter"> (<span>'+rdata.data[key]['count']+'</span>)</span>');

					}
					
				}
				
			}

		}
		
	});

}

$.expr[":"].containsCIN = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});
