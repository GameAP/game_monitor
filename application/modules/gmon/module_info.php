<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$module_info['name'] 			= 'Game Monitor';						// Название модуля
$module_info['description'] 	= 'Мониторинг игровых серверов';		// Описание модуля
$module_info['version'] 		= '1.0';								// Версия
$module_info['cron_script'] 	= 'cron_gmon';							// Крон дополнение (файл application/modules/gmon/controllers/cron_gmon.php)
$module_info['show_in_menu'] 	= 1;									// Отображение модуля в меню
$module_info['access'] 			= 'admin';								// В меню будет отображаться только админу
$module_info['developer'] 		= 'ET-NiK';								// Разработчик
$module_info['site'] 			= 'http://hldm.org';					// Сайт Разработчика
$module_info['email'] 			= 'nikita@hldm.org';					// E-Mail разработчика
$module_info['copyright'] 		= '(c) 2014, ET-NiK (http://hldm.org)';	// Копирайт
$module_info['license'] 		= 'MIT';								// Лицензия
