function STDH_Editor_init(line, params, insert_val, all_values){
	
	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_Editor',
			unwrap : true,
			
		}),	
		params_array = rc_main_getParamsFromStr(params),
		content = line.children('.content'),
		col_table = page_editor_getColTableFromLine(line);
		
	content.children('*').remove();					

	content.append(line_item);
	
	var editor = content.find('.editor');
	
	editor.trumbowyg({

		lang: 'ru',
		btns: [
		
			['fullscreen'],
			['viewHTML'],
			['undo', 'redo'], // Only supported in Blink browsers
			['formatting'],
			['strong', 'em', 'del'],
			['foreColor', 'backColor', 'superscript', 'subscript'],
			['link'],
			['insertImage', 'upload'],
			['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
			['unorderedList', 'orderedList'],
			['horizontalRule'],
			['removeformat'],
			
		],
		plugins: {
			upload: {

				serverPath : $('#module_path').val() + '/user/handlers/STDH_Editor/php/file_upload.php',
				urlPropertyName: 'url',
				data : [{name: 'path', value: '/'+col_table+'/'+all_values['id']+'/'}],
			
			}
		}		
		
		
	});
	
	editor.trumbowyg('html', insert_val);
	
	return true;
	
}

function STDH_Editor_getval(line, params){

	return line.children('.content').find('.editor').trumbowyg('html');
	
}