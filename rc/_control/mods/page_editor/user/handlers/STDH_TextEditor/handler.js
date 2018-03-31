function STDH_TextEditor_init(line, params, insert_val, all_values){
	
	if(!params) params = '"html"';
		
	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_TextEditor',
			unwrap : true,
			
		}),
		params_array = rc_main_getParamsFromStr(params),
		content = line.children('.content');

	content.children('*').remove();
	
	content.append(line_item);
	
	var editor = ace.edit(content.children('.editor')[0]);
	
	content.children('.editor').data('editor', editor);

	editor.setTheme("ace/theme/tomorrow");
	editor.getSession().setMode("ace/mode/"+params_array[0]);
	editor.$blockScrolling = Infinity;
	editor.setAutoScrollEditorIntoView(true);
	editor.setOption("maxLines", 50);
	editor.setOption("minLines", 5);
	editor.getSession().setUseWorker(false);

	editor.setValue($.trim(insert_val), -1);
	
	return true;
	
}

function STDH_TextEditor_getval(line, params){
	
	var editor = line.children('.content').children('.editor').data('editor');

	return editor.getValue();
	
}