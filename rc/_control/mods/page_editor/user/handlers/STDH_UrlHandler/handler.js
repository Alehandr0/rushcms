function STDH_UrlHandler_init(line, params, insert_val, all_values){

	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_UrlHandler',
			unwrap : true,
			
		}),	
		params_array = rc_main_getParamsFromStr(params),
		content = line.children('.content');
	
	content.children('*').remove();		

	content.append(line_item);
	
	content.find('.url_line > input').val($.trim(insert_val));
	content.find('.link_line > a').attr('href', location.protocol + '//' + location.hostname + $.trim(insert_val));
	
	if(params_array[0]){

		if(all_values[params_array[0]]==1){
			
			content.find('.url_line > input').prop('disabled', true);
			content.find('.check_line input').prop('checked', true);
			
		}
	
	}
	else content.find('.check_line').remove();
	
	content.on('change', '.check_line input', function(event){
		
		var url_input = content.find('.url_line > input');
		
		if($(this).prop('checked')) url_input.prop('disabled', true);
		else url_input.prop('disabled', false);
		
	});
	
	return true;
	
}

function STDH_UrlHandler_getval(line, params){
	
	var content = line.children('.content'),
		url_input = content.find('.url_line > input'),
		check_line = content.find('.check_line'),
		params_array = rc_main_getParamsFromStr(params)
		result = '';
		
	if(check_line.length>0 && params_array[0]){
		
		var autogen_val = 0;
		
		if(check_line.find('input').prop('checked')) autogen_val = 1;
		
		result = {
			
			'multidata' : true,
			'data' : {
				
				'p_url' : $.trim(url_input.val())
				
			}
			
		};
		
		result.data[params_array[0]] = autogen_val;

	}
	else result = $.trim(url_input.val());
	
	return result;
	
}