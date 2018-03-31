function STDH_TaInput_init(line, params, insert_val, all_values){
	
	if(!params) params = '"1"';
	
	var	line_item = rc_main_GetTmpl({
		
			container : '.handlers_tmpl',
			tmpl : 'std_handlers_pack_STDH_TaInput',
			unwrap : true,
			
		}),		
		params_array = rc_main_getParamsFromStr(params),
		head = line.children('.head'),
		content = line.children('.content'),
		autoresize_on_head_open = false;

	content.children('*').remove();

	content.append(line_item);
	
	content.find('textarea').val(insert_val);
	
	if(params_array){
		
		var ta = content.find('textarea');
		
		if(params_array[0]){
			
			if(params_array[0]=='autoresize'){
				
				var wo = content.find('.wrap');
				
				wo.addClass('autoresize');
				
				ta.attr('rows', 1);
				
				autosize(ta);
				
				if(!head.hasClass('open')) autoresize_on_head_open = true;	
				
			}
			else ta.attr('rows', params_array[0]);
			
		}
		if(params_array[1]) ta.attr('placeholder', params_array[1]);
	
	}
	
	if(autoresize_on_head_open){
		
		head.on('click', function(event){
			
			if(!head.hasClass('autoresize_udated')){

				STDH_TaInput_delayedUpdateAutoresizedFields(head, content);
			
			}
			
		});

	}
	
	if(content.find('.wrap.autoresize').length>0){
		
		STDH_TaInput_delayedUpdateAutoresizedFields(head, content);
		
	}

	return true;
	
}

function STDH_TaInput_getval(line, params){
	
	return line.children('.content').find('textarea').val();
	
}

// ============================================================================

	function STDH_TaInput_delayedUpdateAutoresizedFields(head, content){

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