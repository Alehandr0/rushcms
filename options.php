<?php

	// Основные настройки RushCMS
	// Документация по RushCMS: https://rush-cms.com/

	$NoCacheTestMode = true;  // Кэш не записывается, если true

	$DBName = 'rcms';  // Название БД
	$DBUser = 'root';  // Имя пользователя БД
	$DBPassword = '';  // Пароль БД
	$DBHost = 'localhost';  // Хост БД
	
	$Lang = 'ru';  // Язык интерфейса административной части RushCMS
	
	$RC4_Key = 'M4PAjwYGbncYBJx4j';  // Ключ шифрования данных
	
	$CreateAbsURL = true;  // Заменяет относительные пути в шаблоне на абсолютные, если true
	$MinifyCacheHTML = true;  // Удаляет ненужные переносы и рустые строки в файлах кэша, если true
	$MaxCacheFiles = 1000;  // Максимальное количество хранимых на сервере файлов кэша

	$ControlUrlName = 'control';  // Адрес пенели управления: http://your.site/[$ControlUrlName]/
	$ControlDirName = '_control';  // Имя каталога со служебной частью RushCMS
	
?>