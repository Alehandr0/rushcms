$(document).ready(function(){
	
	rc_main_SetWrapMinHeight();

	rc_main_DictInit();
	
	rc_main_DisableHoverForMobile();
	
	$('body').on('focusin focusout', '*[placeholder]:not(.no_autoremove_placeholder)',  function(event){

		var o = $(this),
			val = $.trim(o.val());
		
		if(event.type=='focusin') o.data('placeholder', o.attr('placeholder')).attr('placeholder', '');
		else o.attr('placeholder', o.data('placeholder'));

	});
	
	$('body').on('click', '#modules_change > .item',  function(event){
		
		// Событие — клик по меню выбора модуля
		
		if($('#modules_change > .item.off').length>0) $('#modules_change > .item').removeClass('off borderless');
		else if($(this).hasClass('active')) $('#modules_change > .item').not($(this)).addClass('off borderless');
		else window.location.href = '?mod='+$(this).data('module-id');
	
	});
	
	$('body').on('click', '#auth_bar > .logout',  function(event){
		
		// Выход пользователя

		$.ajax({
			type: "POST",
			url: "/rc/"+$('#control_dir').val()+"/data/php/ajax/logout.php",
			dataType: 'json',
			data: {},
			success: function(rdata) {

				window.location.href = '/';

			}
			
		});
	
	});	
	
	$('body').on('click', '#auth_bar > .change_auth',  function(event){
		
		// Открытие/закрытие меню смены прав
		
		var io = $('#auth_change_menu > .item');
		
		if(io.length==0){

			$.ajax({
				type: "POST",
				url: "/rc/"+$('#control_dir').val()+"/data/php/ajax/getAuthGroups.php",
				dataType: 'json',
				data: {},
				success: function(rdata) {
					
					var o = $('#auth_change_menu');

					for(var key in rdata.data){
					
						o.append('<div class="item" data-auth="'+key+'">'+rdata.data[key]+' ('+key+')</div>');
						
					}

				}
				
			});
			
			$(this).addClass('on');
		
		}
		else{
		
			io.remove();
			$(this).removeClass('on');
			
		}
	
	});	
	
	$('body').on('click', '#auth_change_menu > .item',  function(event){
		
		var o = $(this),
			fake_auth = o.data('auth');
			
		$.ajax({
			type: "POST",
			url: "/rc/"+$('#control_dir').val()+"/data/php/ajax/changeFakeAuth.php",
			dataType: 'json',
			data: { fake_auth : fake_auth },
			success: function(rdata) {
				
				window.location.reload();

			}
			
		});
		
	});
	
	$('body').on('click', '#load_screen:not(.clicked)',  function(event){
		
		var o = $(this);
		
		o.addClass('clicked');
		
		setTimeout(function() { o.removeClass('clicked') }, 1000);
		
	});	

});

$(window).resize(function(){
	
	rc_main_SetWrapMinHeight();	

});

$(document).ajaxSend(function(event, jqxhr, settings) {

	if(!settings.background_mode){  // Если выполняемый AJAX не имеет в settings параметра background_mode со значением true. Например: $.ajax({type: "POST", url: "test.php", background_mode: true ... });

		var lso = $('#load_screen'),
			lso_counter = lso.data('load-screen-counter') || 0,
			baro = lso.find('.bar_line');
		
		if(lso_counter<=0){
			lso.removeClass('off');
			setTimeout(function() { if(!lso.hasClass('off')) baro.addClass('on') }, 300);
		}
		
		lso.data('load-screen-counter', lso_counter+1);
	
	}
	
});

$(document).ajaxSuccess(function(event, jqxhr, settings) {
	
	if(!settings.background_mode){  // Если выполняемый AJAX не имеет в settings параметра background_mode со значением true. Например: $.ajax({type: "POST", url: "test.php", background_mode: true ... });
	
		var lso = $('#load_screen'),
			lso_counter = lso.data('load-screen-counter') || 0,
			baro = lso.find('.bar_line');
		
		lso_counter--;
		if(lso_counter<0) lso_counter = 0;
		
		lso.data('load-screen-counter', lso_counter);

		if(lso_counter<=0){
			lso.addClass('off');
			baro.removeClass('on');
		}
	
	}
	
});	

$(document).ajaxError(function( event, request, settings ) {

	if(!settings.background_mode){  // Если выполняемый AJAX не имеет в settings параметра background_mode со значением true. Например: $.ajax({type: "POST", url: "test.php", background_mode: true ... });
	
		var lso = $('#load_screen'),
			lso_counter = lso.data('load-screen-counter') || 0,
			baro = lso.find('.bar_line');
		
		lso_counter--;
		if(lso_counter<0) lso_counter = 0;
		
		lso.data('load-screen-counter', lso_counter);

		if(lso_counter<=0){
			lso.addClass('off');
			baro.removeClass('on');
		}
		
		var	ajax_error_window = rc_main_GetTmpl({
				
				container : '.hidden_tmpl',
				tmpl : 'ajax_error_window',
				
			});			
		
		$('body').RS_modalOpen({

			'content' : ajax_error_window,
			'max-width' : 450,
			'head' : rc_main_GetLangValue('rc_main', 'ajax_error_window_head'),
			'id' : 'rc_main_ajax_error_message',
			'buttons' : {
				'ok' : {'alias' : 'Ok', 'disabled' : false},
			},
			'onClick' : function(data){

				$('#rc_main_ajax_error_message.RS_modal_window').RS_modalClose();

			}

		});		
	
	}	

});

function rc_main_GetTmpl(options = {}){
	
	var tmpl_html = $('<span/>');
	
	if(options.container){
		
		var tmpl = $(options.container).find('*[data-tmpl-name="'+options.tmpl+'"]');

		if(tmpl.length > 0){

			tmpl_html = tmpl.clone().removeAttr('data-tmpl-name');
			
			if(options.unwrap) tmpl_html = tmpl_html.children();			
			
		}
		
	}
	
	return tmpl_html;
	
}

function rc_main_getParamsFromStr(str){
	
	// Функция получения массива параметров из строки вида ['параметр_1', 'параметр_2', ...]

	str = $.trim(str);
	
	var result = str.split(/\"\s*,\s*\"/);

	if(result.length<2 && result[0]=='') result = false;
	else{
		
		var li = result.length-1;
		
		result[0] = result[0].substring(1);
		result[li] = result[li].substring(0,result[li].length-1);
		
	}
	
	return result;
	
}

function rc_main_SetWrapMinHeight(){
	
	var wrap = $('#module > .wrap'),
		mh = $(window).height() - wrap.offset().top - 50;
	
	wrap.css('min-height', mh + 'px');

}

function rc_main_DisableHoverForMobile(){
	
	// Функция отключает css-правило :hover для мобильных устройств

	if($.browser.mobile){  // Проверяем мобильное ли это устройство и удаляем из document.styleSheets все :hover

		for (var si in document.styleSheets) {
			var styleSheet = document.styleSheets[si];
			if (!styleSheet.rules) continue;

			for (var ri = styleSheet.rules.length - 1; ri >= 0; ri--) {
				if (!styleSheet.rules[ri].selectorText) continue;

				if (styleSheet.rules[ri].selectorText.match(':hover')) {
					styleSheet.deleteRule(ri);
				}
			}
		}
		
	}
	
}

(function(a){(jQuery.browser=jQuery.browser||{}).mobile=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);

// === Функции словаря ===

function rc_main_DictInit(){
	
	// Подключение словаря с вариантами словосочетаний для разных языков
	
	var new_words = {
		
		'rc_main' : {
			
			'change_rights' : {'ru' : 'смена прав', 'en' : 'change rights'},
			'logout' : {'ru' : 'выход', 'en' : 'logout'},
			'loading' : {'ru' : 'Идет загрузка', 'en' : 'Loading'},
			'wait' : {'ru' : 'Пожалуйста, подождите', 'en' : 'Please wait'},
			'ajax_error_window_head' : {
				'ru' : 'Ошибка AJAX',
				'en' : 'AJAX error'
			},
			'ajax_error_window_text' : {
				'ru' : 'Произошла ошибка обработки AJAX-запроса. Это могло быть вызвано потерей интернет-соединения или ошибкой в обработчике запроса на стороне сервера. Рекомендуем обновить страницу',
				'en' : 'An error occurred processing the AJAX request. This could be caused by a loss of the Internet connection or a server-side request handler error. We recommend that you refresh the page'
			},

		}		
		
	};
	
	rc_main_UpdateDict(new_words);
	
}


function rc_main_UpdateDict(new_words){
	
	// Обновление словаря
	
	var dictionary_obj = $('body').data('dict') || {};  // Список объектов-словарей системы
	
	if( jQuery.isPlainObject(new_words) ){  // Если входные данные — объект
		
		for(var dictionary_id in new_words){  // Перебираем входные данные
			
			if( jQuery.isPlainObject(dictionary_obj[dictionary_id]) ){  // Если такой подсловарь уже есть
				
				for(var word_key in new_words[dictionary_id]){
					
					dictionary_obj[dictionary_id][word_key] = new_words[dictionary_id][word_key];  // Обновляем старые или записываем новые значения
					
				}
				
			}
			else dictionary_obj[dictionary_id] = new_words[dictionary_id];  // Если такого словаря нет, то добавляем его значения целиком
			
			
		}

	}

	$('body').data('dict', dictionary_obj); // Записываем новую версию словаря

	rc_main_SetLangValues();
	
}

function rc_main_SetLangValues(){

	var dictionary_obj = $('body').data('dict') || {},  // Список объектов-словарей системы
		lang_obj = $('*[data-dict]'),
		current_lang = $('#lang_value').val();
	
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

function rc_main_GetLangValue(dictionary, word_key){
	
	var dictionary_obj = $('body').data('dict') || {},  // Список объектов-словарей системы
		current_lang = $('#lang_value').val(),
		val = 'No translation!';
	
	if( jQuery.isPlainObject(dictionary_obj[dictionary])){
		
		var val = dictionary_obj[dictionary][word_key] || 'No translation!';
		
		if( jQuery.isPlainObject(val) ){
			
			val = val[current_lang] || val[Object.keys(val)[0]] || 'No translation!';
			
		}
	
	}
	
	return val;
	
}

function rc_main_GetMainData(){
	
	var mdo = $('input.rc_main_data'),
		result = {};
	
	mdo.each(function(index){
		
		var o = $(this);
		
		result[o.attr('name')] = o.val();
		
		
	});
	
	return result;
	
}