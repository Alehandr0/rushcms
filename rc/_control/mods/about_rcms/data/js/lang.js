var dict_obj = {
	
	'about_rcms' : {
		
		'main_text' : {
			'ru' : 'RushCMS, версия 0.1.1. Документация: <a href="https://rush-cms.com" tilte="Сайт RushCMS" target="_blank">rush-cms.com</a>',
			'en' : 'RushCMS, version 0.1.1. Documentation: <a href="https://rush-cms.com" title="RushCMS Site" target="_blank">rush-cmc.com</a>'
		},		
		'test_text' : {
			'ru' : 'Тест!',
			'en' : 'Test!'
		},

	}		
	
};

rc_main_UpdateDict(dict_obj);

console.log( rc_main_GetLangValue('about_rcms', 'test_text') );