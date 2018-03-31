/*
	RSModal - стандартная загрузка файлов RushCMS.
	
	В качестве примера php-обработчика посмотрите data/upload.php и handler.php в каталоге этой библиотеки

	Использование:

	$('.upload_button').rsfileupload({
		
		upload_handler : 'path/to/upload.php', // Путь к php-обработчику загрузки (по умолчанию: [rsfileupload script folder]/data/upload.php)
		multiple : true,  // Если true, то будет включена возможность загрузки нескольких файлов
		data : {'some' : 'data'},  // Произвольные данные, которые будут переданы в php-обработчик загрузки
		
		start : function(button, upload_files_list){  // Событие при начале загрузки
			
			console.log('Ok! File upload starts...');
			console.log(upload_files_list);  // upload_files_list — список загружаемых файлов
			
		},
		success : function(button, out_data){  // Событие при окончании загрузки
			
			console.log('Ok! File succesfully upload!');
			console.log(out_data);  // out_data — данные, отданные php-обработчиком загрузки
			
		}
		
	});

*/

(function(){

	var scripts = document.getElementsByTagName('script'),
		this_script_src = scripts[scripts.length-1].src,
		scriptFolder = this_script_src.substr(0, this_script_src.lastIndexOf('/')+1);

	$.fn.rsfileupload = function(options){

		var o = $(this),
			options = $.extend({
				start: function() {},
				success: function() {},
			}, arguments[0] || {});
		
		o.each(function(index){
			
			var bo = $(this),
				position = bo.css('position').toLowerCase(),
				multiple = '';
				
			if(!position || position=='static' || position=='inherit') bo.css({'position' : 'relative'});
			
			bo.css({'overflow' : 'hidden'});
			
			if(options.multiple) multiple = 'multiple';	

			var io = $('<input type="file" name="rsfileupload" title=" " '+multiple+' />');

			io.css({
				'position' : 'absolute', 
				'right' : '0px', 
				'top' : '0px', 
				'font-size' : '999px', 
				'opacity' : '0', 
				'filter' : 'alpha(opacity=0)', 
				'cursor' : 'pointer',
				'z-index' : '500',
			});
			
			bo.append(io);
			
			io.on('change', function(event){
				
				var files = $(this)[0].files,
					formData = new FormData(),
					upload_files_list = [];
				
				for(var i = 0; i < files.length; i++){
				
					formData.append('file_'+i, files[i]);
					upload_files_list[i] = files[i].name;
					
				}
				
				formData.append('data', JSON.stringify(options.data));
				
				var xhr = new XMLHttpRequest();

				xhr.upload.onprogress = function(event){}

				xhr.onload = xhr.onerror = function() {
					
					if(this.status == 200){
						
						var response = JSON.parse(this.response);
						
						if(typeof options.success === 'function') options.success(bo, response.out_data);

					}
					else {}  // Error

				};
				
				if(typeof options.start === 'function') options.start(bo, upload_files_list);

				if(!options.upload_handler) options.upload_handler = scriptFolder+'/data/upload.php';
				
				xhr.open("POST", options.upload_handler, true);
				xhr.send(formData);
				
			});
				
		});

	};

})();
