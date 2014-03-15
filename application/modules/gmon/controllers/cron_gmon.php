<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013-2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

// ------------------------------------------------------------------------

/**
 * CRON дополнение
 *
 * Собирает query данные серверов
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 */
 
/*
 * Для работы этого скрипта необходимо добавить в крон следующее задание
 * php -f /path/to/adminpanel/index.php cron
 * 
 * Cron модуль необходим для работы многих функций АдминПанели.
 * Лучше всего поставить выполнение модуля каждые 5 минут, 
 * но не реже раза в 10 минут.
 * 
*/
class Cron_gmon extends MX_Controller {
	
	var $games_list;
	
	public function __construct()
    {
        parent::__construct();
        
        /* Скрипт можно запустить только из командной строки (через cron) */
        if(php_sapi_name() != 'cli'){
			show_404();
		}
		
		// Загрузка библиотеки Query, для опроса серверов (application/library/Query.php)
		$this->load->library('query');
		
		// Загрузка модели работы с играми (application/models/servers/games.php)
		$this->load->model('servers/games');
		
		// Загрузка модели работы с серверами из базы (application/modules/gmon/models/gmon_servers.php)
		$this->load->model('gmon_servers');
		
		// Загрузка конфигурации
		$this->config->load('gmon_config');
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Функция, выполняющаяся при запуске cron
    */
	private function _get_game_data($game_code = 'valve')
	{
		if (!$this->games_list) {
			$this->games_list = $this->games->get_games_list();
		}
		
		foreach ($this->games_list as $game) {
			if ($game['code'] == $game_code) {
				return $game;
			}
		}
		
		return false;
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Функция, выполняющаяся при запуске cron
    */
	public function index()
	{
		$this->gmon_servers->get_list();
		
		if(version_compare(AP_VERSION, '0.9-dev') == -1) {
			// Необходима версия GameAP не ниже 0.9-dev
			return;
		}
		
		if ($this->config->item('gmon_allow_gameap_servers')) {
			$this->load->model('servers');
			
			// Серверы, имеющиеся в списке мониторинга
			$exists_servers = array();
			
			$this->servers->get_servers_list();
			
			foreach($this->gmon_servers->mservers_list as $mserver) {
				if ($mserver['server_id']) { $exists_servers[] = $mserver['server_id']; }
			}
			
			foreach ($this->servers->servers_list as $server) {
				if (in_array($server['id'], $exists_servers)) {
					// Сервер уже имеется в списках мониторинга
					continue;
				}

				$game_data = $this->_get_game_data($server['game']);
				
				$this->gmon_servers->add(array('game_code' => $server['game'],
												'game_name' => $game_data['name'],
												'game_engine' => strtolower($game_data['engine']),
												'server_id' => $server['id'],
												'host' => $server['server_ip'],
												'port' => $server['server_port'],
											)
				);
			}
			
		}
		
		foreach($this->gmon_servers->mservers_list as $mserver) {
			
			$query['id'] 	= $mserver['id'];
			$query['type'] 	= $mserver['game_engine'];
			$query['host']	= $mserver['host'];
			$query['port']	= $mserver['port'];
			
			try {
				$this->query->set_data($query);
				$base_cvars = $this->query->get_base_cvars();
				$cvars 		= $this->query->get_cvars();
				$players 	= $this->query->get_players();
				
				if ($base_cvars[ $mserver['id'] ]['hostname']) {
					$sql_data['q_name'] 			= $base_cvars[ $mserver['id'] ]['hostname'];
					$sql_data['q_map'] 				= $base_cvars[ $mserver['id'] ]['map'];
					$sql_data['q_num_players'] 		= $base_cvars[ $mserver['id'] ]['players'];
					$sql_data['q_max_players'] 		= $base_cvars[ $mserver['id'] ]['maxplayers'];
					
					$sql_data['q_online_players']	= json_encode($players[ $mserver['id'] ]);
					$sql_data['q_variables']		= json_encode($cvars[ $mserver['id'] ]);
					$sql_data['online']				= 1;
					
					$this->gmon_servers->edit($mserver['id'], $sql_data);
				} else {
					$sql_data['online']				= 0;
				}
				
				$this->gmon_servers->edit($mserver['id'], $sql_data);
			} catch(Exception $e) {
				
			}
		}
		
		try {
			$base_cvars = $this->query->get_base_cvars();
			$cvars 	= $this->query->get_cvars();
			$players 	= $this->query->get_players();
		} catch (Exception $e) {
			
		}

		
	}
	
	
}
