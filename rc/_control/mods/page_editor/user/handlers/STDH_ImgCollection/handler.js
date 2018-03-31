function STDH_ImgCollection_init(line, params, insert_val, all_values){
	
	line.children('.content').children('*').remove();
	
	if(!params) params = '"1"';
	
	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_ImgCollection',
			unwrap : true,
			
		}),		
		params_array = rc_main_getParamsFromStr(params),
		element_data = page_editor_getElementDataByEditLine(line),
		col_table = element_data.col_table;
		
	var current_template = STDH_ImgCollection_data_templates[params_array[0]],
		current_template_data = GetInsertPrototypeAndFieldsNames(current_template),
		insert_proto = JSON.stringify(current_template_data['insert_proto']),
		fields_names = current_template_data['fields_names'];
		
	if(insert_val) insert_val = JSON.parse(insert_val);
	else insert_val = {0 : JSON.parse(insert_proto)};
	
	line_item.data('items-data', {
		
		max_count : current_template['max_count'],
		insert_proto : insert_proto,
		fields_names : fields_names,
		images_data : insert_val,
		current_position : [0,0],

	});
	
	CreateEditCard();
	UpdateEditCardButtonsState();

	line_item.on('keyup', '.item > .desc_column textarea', function(event){

		UpdateEditCardData();
		
	});
		
	line_item.on('click', '[data-action]:not(.disabled)', function(event){
		
		var o = $(this),
			action_type = o.data('action'),
			line = o.closest('.edit_line'),
			items_data = line_item.data('items-data'),
			max_count = items_data['max_count'],
			insert_proto = items_data['insert_proto'],
			images_data = items_data['images_data'],
			current_position = items_data['current_position'];
			
		if(action_type!='del_item') UpdateEditCardData();
			
		if(action_type=='next_img'){

			current_position[1]++;
			if(!jQuery.isPlainObject(images_data[current_position[0]][current_position[1]])) current_position[1] = 0;

		}
		else if(action_type=='prev_img'){

			current_position[1]--;
			if(current_position[1]<0) current_position[1] = GetObjLength(images_data[current_position[0]]) - 1;				
			
		}
		else if(action_type=='next_item'){

			current_position[0]++;
			if(!$.isArray(images_data[current_position[0]])) current_position[0] = 0;
		
		}
		else if(action_type=='prev_item'){

			current_position[0]--;
			if(current_position[0]<0) current_position[0] = GetObjLength(images_data) - 1;

		}
		else if(action_type=='add_item'){

			images_data[GetObjLength(images_data)] = JSON.parse(insert_proto);
		
			current_position[0] = GetObjLength(images_data) - 1;
			current_position[1] = 0;
		
		}
		else if(action_type=='del_item'){
			
			images_data[GetObjLength(images_data)] = JSON.parse(insert_proto);

			delete images_data[current_position[0]];
			
			var i = 0,
				new_images_data = {};
			
			for(var key in images_data){
				
				new_images_data[i] = images_data[key];
				i++;
				
			}
			
			images_data = new_images_data;
			
			current_position[0]--;
			
			if(current_position[0]<0) current_position[0] = 0;
		
		}
		else if(action_type=='up_item'){

			var tmp = images_data[current_position[0]-1];
			
			images_data[current_position[0]-1] = images_data[current_position[0]];
			images_data[current_position[0]] = tmp;
		
			current_position[0]--;
		
		}

		items_data['images_data'] = images_data;
		items_data['current_position'] = current_position;
		
		line_item.data('items-data', items_data);

		CreateEditCard();
		UpdateEditCardButtonsState();

	});

	line.children('.content').append(line_item);
	page_editor_inputTextAreaAutoResize(line_item.children('.item').find('textarea'), 25);

	function UpdateEditCardButtonsState(){
		
		var item = line_item.children('.item'),
			items_data = line_item.data('items-data'),
			images_data = items_data['images_data'],
			max_count = items_data['max_count'],
			current_position = items_data['current_position'],
			this_image_data = images_data[current_position[0]][current_position[1]],
			img_buttons = item.find('.image_column .button:not(.download)'),
			item_buttons = item.find('.desc_column .button'),
			items_count = GetObjLength(images_data),
			images_count = GetObjLength(images_data[0]);
			
		img_buttons.addClass('off');
		item_buttons.filter(':not(.del)').addClass('disabled');
		item.find('.desc_column').children('h3, .control').removeClass('off');
			
		if(items_count<max_count) item_buttons.filter('.add').removeClass('disabled');
		if(items_count>1) item_buttons.filter('.prev, .next').removeClass('disabled');
		if(images_count>1) img_buttons.filter('.prev, .next').removeClass('off');
		if(items_count>1 && current_position[0]>0) item_buttons.filter('.up').removeClass('disabled');
		
		item_buttons.filter('.first').removeClass('first');
		item_buttons.filter('*:not(.disabled):first').addClass('first');
		
		if(item_buttons.filter(':not(.disabled)').length==0){

			item.find('.desc_column').children('h3, .control').addClass('off');
		
		}
		
	}
	
	function UpdateEditCardData(){
		
		var item = line_item.children('.item'),
			items_data = line_item.data('items-data'),
			images_data = items_data['images_data'],
			current_position = items_data['current_position'],
			this_image_data = images_data[current_position[0]][current_position[1]];
			
		item.find('.desc_column > .line textarea').each(function(index){
			
			var ta = $(this),
				val = $.trim(ta.val()),
				key = ta.attr('name');
				
			this_image_data[key] = val;
			
		});
		
		this_image_data['img'] = item.find('.image_column > .img_wrap > img').data('img') || '';
		
		images_data[current_position[0]][current_position[1]] = this_image_data;
		
		items_data['images_data'] = images_data;
		
		line_item.data('items-data', items_data);
		
	}
	
	function CreateEditCard(){
		
		var items_data = line_item.data('items-data'),
			images_data = items_data['images_data'],
			fields_names = items_data['fields_names'],
			current_position = items_data['current_position'],
			tmpl = line_item.find('.tmpl > .item').clone(),
			this_image_data = images_data[current_position[0]][current_position[1]],
			w = 200,
			ww = $(window).width();
			
		if(ww<=600) w = 0;
		
		for(var key in this_image_data){
			
			if(key=='img'){
				
				if(this_image_data[key]){
					
					var img = this_image_data[key],
						path = '/rc/_tmp/';

					if(img.substring(0, 5)!='_tmp_'){
						
						path = '/rc/upload/'+col_table+'/'+all_values['id']+'/';
						
					}
				
					tmpl.find('.image_column > .img_wrap > img')
						.data('img', img)
						.attr('src', path+img);
					
				}
				else{
					
					tmpl.find('.image_column > .img_wrap > img').addClass('off')
						.data('img', this_image_data[key]);
						
					tmpl.find('.image_column > .img_wrap').addClass('noimg');
					
				}
				
			}
			else if(key=='size'){
				
				tmpl.find('.image_column > p')
					.text(this_image_data[key][0]+'x'+this_image_data[key][1]+' px');
				
				if(w>0) var h = (w*(this_image_data[key][1]/this_image_data[key][0])) + 'px';
				else h = 'auto';
					
				tmpl.find('.image_column > .img_wrap').css({
					
					'height' : h
					
				});				
				
			}
			else{
				
				var label = fields_names[current_position[1]][key] + ' ('+key+')';
				
				tmpl.children('.desc_column').append(
				
					'<div class="line">'+
						'<div class="label">'+label+'</div>'+
						'<textarea type="text" name="'+key+'" rows="1">'+this_image_data[key]+'</textarea>'+
					'</div>'
				
				);
				
			}
			
		}
		
		tmpl.find('.desc_column > h3 > span').text(current_position[0]+1);

		line_item.children('.item').remove();
		line_item.append(tmpl);
		
		tmpl.find('.button.download').rsfileupload({

			data : {'size' : this_image_data.size},
			upload_handler : $('#module_path').val() + '/user/handlers/STDH_ImgCollection/php/ajax/upload.php',
			success : function(button, out_data){
				
				if(out_data=='size_error'){
					
					var modal_window = rc_main_GetTmpl({
		
						container : '.handlers_tmpl',
						tmpl : 'std_handlers_pack_STDH_ImgCollection_modal',
						unwrap : true,
						
					});
					
					$('body').RS_modalOpen({

						'content' : modal_window,
						'max-width' : 450,
						'head' : 'Ошибка',
						'id' : 'STDH_ImgCollection_size_error',
						'buttons' : {
							'ok' : {'alias' : 'Ok', 'disabled' : false},
						},
						'onClick' : function(data){

							$('#STDH_ImgCollection_size_error.RS_modal_window').RS_modalClose();
						}

					});						
					
				}
				else{

					line_item.data('items-data', items_data);
					
					button.closest('.image_column')
						  .find('.img_wrap').removeClass('noimg')
						  .children('img').attr('src', '/rc/_tmp/'+out_data[0]).removeClass('off').data('img', out_data[0]);
						  
					UpdateEditCardData();
				
				}
				
			}
			
		});

		page_editor_inputTextAreaAutoResize(line_item.children('.item').find('textarea'), 25);

	}
	
	function GetObjLength(o){
		
		var count = 0,
			i;

		for(i in o){
			if (o.hasOwnProperty(i)) count++;
		}

		return count;
		
	}
	
	function GetInsertPrototypeAndFieldsNames(current_data_template){
	
		var insert_proto = [],
			fields_names = {};
	
		for(var i in current_data_template.images){
			
			insert_proto[i] = {
				
				img : '',
				size : current_data_template.images[i]['size'],
				
			};
			
			fields_names[i] = {};
			
			var common_fields = current_data_template['common_fields'] || [],
				common_fields_names = current_data_template['common_fields_names'] || [],
				this_fields = current_data_template.images[i]['fields'] || [],
				this_fields_names = current_data_template.images[i]['fields_names'] || [],
				fields_arr = common_fields.concat(this_fields),
				fields_names_arr = common_fields_names.concat(this_fields_names);
			
			if($.isArray(fields_arr)){
				
				for(var key in fields_arr){
				
					if($.trim(fields_arr[key])){
						
						insert_proto[i][$.trim(fields_arr[key])] = '';
						
						fields_names[i][$.trim(fields_arr[key])] = fields_names_arr[key];
						
					}
					
				}
			
			}
			
		}

		return {
			
			insert_proto : insert_proto,
			fields_names : fields_names,
			
		};
		
	}

	return true;

}

function STDH_ImgCollection_getval(line, params){
	
	var val = line.children('.content').children('.wrap').data('items-data')['images_data'],
		result = {};
	
	for(var i in val){
		
		for(var m in val[i]){
			
			if(val[i][m]['img']){
				
				if(!result[i]) result[i] = {};
				
				result[i][m] = val[i][m];
				
			}
			
		}
		
	}
	
	return result;
	
}
