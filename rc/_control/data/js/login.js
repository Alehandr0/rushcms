$(document).ready(function(){
	
	$('body').on('focusin focusout', '*[placeholder]',  function(event){

		var o = $(this),
			val = $.trim(o.val());
		
		if(event.type=='focusin') o.data('placeholder', o.attr('placeholder')).attr('placeholder', '');
		else o.attr('placeholder', o.data('placeholder'));

	});	
	
	if(getURLParam('error', window.location.href)) $('#login_form').addClass('error');
	
	rslogin_SetLangValues();

});

function rslogin_SetLangValues(){
	
	var dictionary_obj = {
		
			'rs_login' : {
				
				'login' : {'ru' : 'логин', 'en' : 'login'},
				'password' : {'ru' : 'пароль', 'en' : 'password'},
				'submit' : {'ru' : 'Войти', 'en' : 'Enter'},

			}			
		
		}, 
		lang_obj = $('*[data-dict]'),
		current_lang = $('#lang_value').val() || 'en';
	
	lang_obj.each(function(index){
		
		var o = $(this),
			dict_data = $.trim(o.data('dict'));
			
		if(dict_data){
			
			var dict_data = dict_data.split(';'),
				dictionary = $.trim(dict_data[0]),
				word_key = $.trim(dict_data[1]),
				attr_name = $.trim(dict_data[2]);
				
			if( jQuery.isPlainObject(dictionary_obj[dictionary]) ){
				
				var val = dictionary_obj[dictionary][word_key];
				
				if( jQuery.isPlainObject(val) ){
					
					val = val[current_lang] || val[Object.keys(val)[0]] || 'No translation!';

				}
				else val = 'No translation!';
				
				if(attr_name){
					
					if(attr_name=='html') o.html(val);
					else if(attr_name=='value') o.val(val);
					else o.attr(attr_name, val);
					
				}
				else o.html(val);

			}
		
		}
		
	});

}

function getURLParam(name, url){
	
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
	
}