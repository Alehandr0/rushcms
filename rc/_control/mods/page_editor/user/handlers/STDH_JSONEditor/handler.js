function STDH_JSONEditor_init(line, params, insert_val, all_values){

	var	params_array = rc_main_getParamsFromStr(params),
		head = line.children('.head'),
		content = line.children('.content'),
		data_tmpl = STDH_JSONEditor_options[params_array[0]],
		autoresize_on_head_open = false;
		
	if(!insert_val) insert_val = {};
	else insert_val = JSON.parse(insert_val);

	content.children('*').remove();
	
	for(var key in data_tmpl){
		
		var	line_item = rc_main_GetTmpl({
			
				container : '.handlers_tmpl',
				tmpl : 'std_handlers_pack_STDH_JSONEditor',
				unwrap : true,
				
			}),
			label = line_item.find('p.label'),
			wo = line_item.find('.wrap'),
			ta = line_item.find('textarea'),
			alias = data_tmpl[key]['alias'] || key,
			placeholder = data_tmpl[key]['placeholder'] || '',
			default_val = data_tmpl[key]['default_val'] || '',
			autoresize = data_tmpl[key]['autoresize'] || false,
			val = insert_val[key] || false;
		
		if(!val) val = default_val;
			
		label.text(alias);
		
		ta.attr('placeholder', placeholder);
		ta.attr('name', 'STDH_JSONEditor_'+key);
		ta.data('key', key);
		
		ta.val(val);

		content.append(line_item);
		
		if(autoresize){
			
			wo.addClass('autoresize');
			
			ta.attr('rows', 1);
			
			autosize(ta);
			
			if(!head.hasClass('open')) autoresize_on_head_open = true;

		}
		
	}
	
	if(autoresize_on_head_open){
		
		head.on('click', function(event){
			
			if(!head.hasClass('autoresize_udated')){

				STDH_JSONEditor_delayedUpdateAutoresizedFields(head, content)
			
			}
			
		});

	}

	if(content.find('.wrap.autoresize').length>0){
		
		STDH_JSONEditor_delayedUpdateAutoresizedFields(head, content);
		
	}
	
	return true;
	
}

function STDH_JSONEditor_getval(line, params){
	
	var result = {},
		count = 0;
	
	line.find('textarea').each(function(index){
		
		var ta = $(this),
			key = ta.data('key');
		
		if(key){
			
			result[key] = $.trim(ta.val());
			
			count++;
			
		}

	});
	
	if(count>0) result = JSON.stringify(result);
	else result = '';
	
	return result;
	
}

function STDH_JSONEditor_delayedUpdateAutoresizedFields(head, content){
	
	if(content.css('display')=='none'){

		var timerId = setInterval(function(){
			
			if(content.css('display')!='none'){
				
				clearInterval(timerId);
			
				autosize.update(content.find('.wrap.autoresize > textarea'));
				
				head.addClass('autoresize_udated');
				
			}

		}, 10);	
	
	}
	else autosize.update(content.find('.wrap.autoresize > textarea'));
	
}
