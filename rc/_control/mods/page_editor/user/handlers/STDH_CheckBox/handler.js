function STDH_CheckBox_init(line, params, insert_val, all_values){

	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_CheckBox',
			unwrap : true,
			
		}),				
		params_array = rc_main_getParamsFromStr(params),
		content = line.children('.content');
		
	content.children('*').remove();			
	
	content.append(line_item);
	content.find('input').val(params_array[1]);
	content.find('span.label').text(params_array[2]);
	
	if(insert_val==params_array[1]) content.find('input').prop('checked', true);
	
	return true;
	
}

function STDH_CheckBox_getval(line, params){
	
	var params_array = rc_main_getParamsFromStr(params);
	
	return line.children('.content').find('input:checked').val() || params_array[0];
	
}