$(document).ready(function(){

	ItemToCartButtonCheck();

	SetItemsTitleMinHeight();
	
	CheckCartState();
	
	$('body').on('click', '[data-js-action]', function(event){
		
		var o = $(this);
		
		if(!o.hasClass('disabled')) ActionApply(o, o.data('js-action'));
		
	});
	
	$('body').on('focusin focusout', '*[placeholder]',  function(event){

		var o = $(this),
			val = $.trim(o.val());
		
		if(event.type=='focusin') o.data('placeholder', o.attr('placeholder')).attr('placeholder', '');
		else o.attr('placeholder', o.data('placeholder'));

	});	

});

$(window).resize(function(){

	SetItemsTitleMinHeight();

});

function ActionApply(o, action){
	
	if(action=='to_cart'){
	
		var id = o.closest('.current_item').data('id');
		
		if(id){
			AddToCart(id);
			o.addClass('disabled').text('В корзине');
			CheckCartState();
		}
		
	}
	else if(action=='del_from_cart'){
	
		var cart_item = o.closest('.cart_item'),
			id = cart_item.data('id');
		
		if(id){
			
			RemoveFromCart(id);
			cart_item.remove();
			CheckCartState();
			
		}
		
	}
	else if(action=='cart_buy'){
	
		if(!o.hasClass('end')){
			
			o.addClass('end').text('Таки купить').parent().prev('.form').removeClass('off');
			
		}
		else{
			
			var form = o.parent().prev('.form'),
				form_data = {};

			form.find('input').each(function(index){
				
				var io = $(this),
					val = $.trim(io.val()),
					name = io.attr('name');
					
				io.removeClass('error');
					
				if(name=='username' && val.length<2) io.addClass('error');
				else if(name=='userphone' && val.length<7) io.addClass('error');
				
				form_data[name] = val;
				
			});
			
			var erro = form.find('input.error');
			
			if(erro.length>0){
				
				
			}
			else{
				
				form_data.cart = [];
			
				form.parent().find('.cart_item').each(function(index){
					
					form_data.cart.push($(this).data('id'));
					
				});
			
				o.addClass('disabled');
				
				$.ajax({
					type: "POST",
					url: "/rc/php/ajax/form_handler.php",
					dataType: 'json',
					data: {form_data : form_data},
					success: function(rdata) {
						
						if(rdata.ok){
							
							for(var i in form_data.cart){
								
								RemoveFromCart(form_data.cart[i]);
								
							}
							
							$('.cart_wrap > *:not(.success)').remove();
							$('.cart_wrap > .success').removeClass('off');
							
							CheckCartState();
							
						}
						
					}
				});				
				

				
			}
			
		}
		
	}
	
}

function AddToCart(id){
	
	var cart = getCookie('cart') || false;
	
	if(cart) cart = JSON.parse(cart);
	else cart = {};
	
	cart[id] = true;
	
	cart = JSON.stringify(cart);
	
	setCookie('cart', cart, {
		
		path : '/',
		expires : 86400,
		
	});
	
}

function RemoveFromCart(id){
	
	var cart = getCookie('cart') || false;
	
	if(cart){
		
		cart = JSON.parse(cart);

		if(cart[id]) delete cart[id]; 

		if(jQuery.isEmptyObject(cart)){

			setCookie('cart', '', {
				
				path : '/',
				expires : -1,
				
			});		
			
		}
		else{
			
			cart = JSON.stringify(cart);

			setCookie('cart', cart, {
				
				path : '/',
				expires : 86400,
				
			});		
			
		}
		
	}
	
}

function ItemToCartButtonCheck(){
	
	var current_item = $('.current_item');
	
	if(current_item.length > 0){
		
		var cart = getCookie('cart') || false,
			button = current_item.find('.to_cart_button');
		
		if(cart){
			
			cart = JSON.parse(cart);
			
			if(!cart[current_item.data('id')]) button.removeClass('disabled');
			else button.text('В корзине');

		}
		else button.removeClass('disabled');
		
		button.removeClass('transparent_font');
		
	}
	
}

function SetItemsTitleMinHeight(){
	
	var items_wrap = $('.c_items');
	
	if(items_wrap.length > 0){
	
		var items = items_wrap.children('.item'),
			items_h6 = items.find('h6'),
			title_max_height = 0;
			
		items_h6.css('min-height', 'auto');
		
		items.each(function(index){
			
			var item = $(this),
				item_title_height = item.find('h6').height();
			
			if(item_title_height>title_max_height) title_max_height = item_title_height;

		});
		
		items_h6.css('min-height', title_max_height+'px');
		items.removeClass('transparent');
	
	}
	
}

function CheckCartState(){
	
	var cart_wrap = $('.cart_wrap'),
		cart = getCookie('cart') || false,
		cart_count_o = $('.top_bar > .cart > span'),
		count = 0;
		
	if(cart){
		
		cart = JSON.parse(cart);
		
		for(var i in cart){
		
			count++;
			
		}
		
	}
	
	if(count == 0) count = '';
	else count = '('+count+')';
	
	cart_count_o.text(count);
	
	if(cart_wrap.length > 0){
	
		var cart_items = cart_wrap.find('.cart_item');
		
		if(cart_items.length > 0){
		
			var total_cost = 0;
		
			cart_items.each(function(index){
				
				total_cost += parseInt($(this).find('h3 > span').text().replace(/\s/g, ''));
				
			});
		
			cart_wrap.find('.total > strong').text(number_format(total_cost, 0, '.', ' '));
			cart_wrap.find('.transparent').removeClass('transparent');
			
		}
		else{
			
			$('.cart_items').append('<div class="empty_cart_message">Пусто как в кармане моряка на берегу :(</div>');
			cart_wrap.find('.total, .buy_button_wrap').addClass('off');
			
		}
		
	}
	
}


function getCookie(name){
	
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
	
	return matches ? decodeURIComponent(matches[1]) : undefined;
	
}

function setCookie(name, value, options){
	
	options = options || {};

	var expires = options.expires;

	if(typeof expires == "number" && expires){
		
		var d = new Date();
		
		d.setTime(d.getTime() + expires * 1000);
		
		expires = options.expires = d;
		
	}
	
	if(expires && expires.toUTCString) options.expires = expires.toUTCString();

	value = encodeURIComponent(value);

	var updatedCookie = name + "=" + value;

	for(var propName in options){
		
		updatedCookie += "; " + propName;
		
		var propValue = options[propName];
		
		if (propValue !== true) updatedCookie += "=" + propValue;
		
	}

	document.cookie = updatedCookie;
	
}

function number_format(number, decimals, dec_point, thousands_sep){

	var i, j, kw, kd, km;

	if(isNaN(decimals = Math.abs(decimals))) decimals = 2;
	if(dec_point == undefined) dec_point = ",";
	if(thousands_sep == undefined) thousands_sep = ".";

	i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

	if((j = i.length) > 3) j = j % 3;
	else j = 0;

	km = (j ? i.substr(0, j) + thousands_sep : "");
	kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
	kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");

	return km + kw + kd;
	
}

