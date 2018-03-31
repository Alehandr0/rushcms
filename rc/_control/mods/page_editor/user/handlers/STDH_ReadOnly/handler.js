function STDH_ReadOnly_init(line, params, insert_val, all_values){
	
	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_ReadOnly',
			unwrap : true,
			
		}),
		content = line.children('.content');
		
	content.children('*').remove();
	
	content.append(line_item).find('.container').data('value', insert_val).html(insert_val);
	
	return true;
	
}

function STDH_ReadOnly_getval(line, params){
	
	return line.children('.content').find('.container').data('value');
	
}