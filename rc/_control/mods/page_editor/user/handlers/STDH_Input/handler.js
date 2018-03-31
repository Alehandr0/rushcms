function STDH_Input_init(line, params, insert_val, all_values){
	
	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_Input',
			unwrap : true,
			
		}),
		params_array = rc_main_getParamsFromStr(params),
		content = line.children('.content');
		
	content.children('*').remove();

	if(params_array) line_item.filter('input').attr('placeholder', params_array[0]);
	
	content.append(line_item);
	content.find('input').val(insert_val);
	
	return true;
	
}

function STDH_Input_getval(line, params){
	
	return line.children('.content').find('input').val();
	
}