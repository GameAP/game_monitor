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

class Gmon extends MX_Controller {
	
	var $tpl_data = array();
	
	public function __construct()
    {
        parent::__construct();
        
		$this->config->load('gmon_config');		// Загрузка конфигурации
		$this->load->model('users');			// Загрузка модели работы с пользователями
		
		$this->lang->load('gmon');				// Загрузка языкового файла (application/modules/gmon/language/russian/gmon_lang.php)
		$this->lang->load('adm_servers');		// Загрузка языкового файла (application/language/russian/adm_servers.php)
		$this->lang->load('server_control');	// Загрузка языкового файла (application/language/russian/server_control.php)
        
		$this->users->check_user();
		
		/* Пользователь не авторизован, а доступ таким запрещен,
		 * перенаправляем на страницу авторизации*/
		if (!$this->config->item('gmon_allow_not_auth') && !$this->users->auth_id) {
			redirect('auth');
		}
		
		// Загрузка модели работы с серверами из базы (application/modules/gmon/models/gmon_servers.php)
		$this->load->model('gmon_servers');
		
		$this->tpl_data['title'] 	= lang('gmon_title');
		$this->tpl_data['heading'] 	= lang('gmon_heading');
		$this->tpl_data['content'] 	= '';
		
		if ($this->users->auth_id) {
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, true);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
		}
	}
	
	// -----------------------------------------------------------------
	
	/**
	 *  Отображение информационного сообщения
	 */
    private function _show_message($message = false, $link = false, $link_text = false)
    {
        
        if (!$message) {
			$message = lang('error');
		}
		
        if (!$link) {
			$link = 'javascript:history.back()';
		}
		
		if (!$link_text) {
			$link_text = lang('back');
		}

        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, true);
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    // -----------------------------------------------------------------
	
	/**
	 * Получение списка серверов, для вставки в шаблон
	 */
    private function _get_tpl_list($where = array(), $limit = 99999, $offset = 0)
    {
		$tpl_data = array();
		$this->gmon_servers->get_list($where, $limit, $offset);
		
		foreach ($this->gmon_servers->mservers_list as &$mserver) {
			
			$mserver['q_online_players'] = ($json_decode = json_decode($mserver['q_online_players'], true)) ? $json_decode : array();
			$mserver['q_variables'] = ($json_decode = json_decode($mserver['q_variables'], true)) ? $json_decode : array();
			$mserver['commentaries'] = ($json_decode = json_decode($mserver['commentaries'], true)) ? $json_decode : array();
			
			$cvars = array();
			
			foreach($mserver['q_variables'] as $key => $value) {
				$cvars[] = array('cv_name' => $key, 'cv_value' => $value);
			}
			
			$tpl_data[] = array('mserver_id' => 		$mserver['id'],
									'game_code' => 		$mserver['game_code'],
									'host' => 			$mserver['host'],
									'port' => 			$mserver['port'],
									'hostname' => 		$mserver['q_name'],
									'map' => 			$mserver['q_map'],
									'online_players' => $mserver['q_online_players'],
									'num_players' => 	$mserver['q_num_players'],
									'max_players' => 	$mserver['q_max_players'],
									'description' => 	$mserver['description'],
									'cvars' => 			$cvars,
									'rating' => 		$mserver['rating'],
									'commentaries' => 	$mserver['commentaries'],
									
			);
		}
		
		return $tpl_data;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение данных одного сервера
	 */
	private function _get_tpl_single($mserver_id = 0)
	{
		$mservers = $this->_get_tpl_list(array('id' => $mserver_id), 1);
		
		if (empty($mservers)) {
			return false;
		}
		
		return $mservers[0];
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение данных фильтра для вставки в шаблон
	 */
	private function _get_tpl_filter($filter = false)
	{
		if (empty($this->games->games_list)) {
			$this->games->get_games_list();
		}
		
		$games_option[0] = '---';
		foreach($this->games->games_list as &$game) {
			$games_option[ $game['code'] ] = $game['name'];
		}
		
		$tpl_data['filter_name']			= isset($filter['name']) ? $filter['name'] : '';
		$tpl_data['filter_host']			= isset($filter['host']) ? $filter['host'] : '';
		
		$default = isset($filter['game']) ? $filter['game'] : null;
		$tpl_data['filter_games_dropdown'] 	= form_dropdown('filter_game', $games_option, $default);
		
		return $tpl_data;
	}
    
    // -----------------------------------------------------------------
	
	/**
	 * Главная страница
	 */
	public function index($offset = 0)
	{
		$this->load->library('session');
		$this->load->model('servers/games');
		
		$filter = $this->session->all_userdata();
		
		$filter['game'] = isset($filter['game']) ? $filter['game'] : 'cstrike';
		
		$local_tpl_data = $this->_get_tpl_filter($filter);
		$this->gmon_servers->set_filter($filter);
		
		/* Постраничная навигация */
		$config['base_url'] = site_url('gmon/page');
		$config['uri_segment'] = 3;
		$config['total_rows'] = $this->gmon_servers->get_count_all(array('online' => 1));
		$config['per_page'] = 50;
		$config['full_tag_open'] = '<p id="pagination">';
		$config['full_tag_close'] = '</p>';
		
		$this->pagination->initialize($config); 
		$local_tpl_data['pagination'] = $this->pagination->create_links();
		
		$local_tpl_data['mservers_list'] = $this->_get_tpl_list(array('online' => 1), $config['per_page'], $offset);

		$this->tpl_data['content'] = $this->parser->parse('servers_list.html', $local_tpl_data, true);
		
		if ($this->config->item('gmon_gameap_template')) {
			$this->parser->parse('main.html', $this->tpl_data);
		} else {
			$this->parser->parse('main_gmon.html', $this->tpl_data);
		}
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Удаление сервера
	 */
	public function delete($mserver_id = 0, $confirm = '') 
	{
		if (!$mserver_id) {
			// Не задан сервер, возвращаем к списку
			redirect('gmon');
		}
		
		if (!$this->users->auth_data['is_admin']) {
			// Не админ
			redirect('gmon');
		}
		
		if (!$this->gmon_servers->get_list(array('id' => $mserver_id))) {
			$this->_show_message(lang('adm_servers_server_not_found'));
			return false;
		}
		
		if($confirm == $this->security->get_csrf_hash()) {
			$this->gmon_servers->delete($mserver_id);
			$this->_show_message(lang('adm_servers_delete_server_successful'), site_url('gmon'), lang('next'));
			return true;
		} else {
			/* Пользователь не подвердил намерения */
			$confirm_tpl['message'] 		= lang('gmon_delete_confirm');
			$confirm_tpl['confirmed_url'] 	= site_url('gmon/delete/' . $mserver_id . '/' . $this->security->get_csrf_hash());
			$this->tpl_data['content'] 		.= $this->parser->parse('confirm.html', $confirm_tpl, true);
		}
		
		if ($this->config->item('gmon_gameap_template')) {
			$this->parser->parse('main.html', $this->tpl_data);
		} else {
			$this->parser->parse('main_gmon.html', $this->tpl_data);
		}
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Задает фильтр для списка серверов
	 */
	public function set_filter()
    {
		// Загрузка библиотеки работы с формами (system/library/Form_validation.php)
		$this->load->library('form_validation');
		
		// Загрузка библиотеки работы с сессиями (system/library/Session.php)
		$this->load->library('session');
		
		$this->form_validation->set_rules('filter_name', lang('name'), 'trim|xss_clean');
		$this->form_validation->set_rules('filter_host', lang('host'), 'trim|xss_clean');
		$this->form_validation->set_rules('filter_game', lang('game'), 'trim|xss_clean');
		
		if($this->form_validation->run() == false) {
			
			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

		} else {
			$reset = (bool) $this->input->post('reset');
			
			if (!$reset) {
				$filter['name'] = $this->input->post('filter_name');
				$filter['host']	= $this->input->post('filter_host');
				$filter['game'] = $this->input->post('filter_game');
				
				$this->session->set_userdata($filter);
			} else {
				$filter = array('name' => '', 'host' => '', 'game' => '');
				$this->session->unset_userdata($filter);
			}
		}
		
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Информация о сервере
	 */
	public function server_info($mserver_id = 0)
	{
		if (!$mserver_id) {
			// Не задан сервер, возвращаем к списку
			redirect('gmon');
		}
		
		if (!$local_tpl_data = $this->_get_tpl_single($mserver_id)) {
			$this->_show_message(lang('adm_servers_server_not_found'));
			return false;
		}
		
		$this->tpl_data['content'] = $this->parser->parse('server_info.html', $local_tpl_data, true);
		
		if ($this->config->item('gmon_gameap_template')) {
			$this->parser->parse('main.html', $this->tpl_data);
		} else {
			$this->parser->parse('main_gmon.html', $this->tpl_data);
		}
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Переопределение метода для page
	*/
	public function _remap($method, $params = array())
	{
		if ($method == 'page' or $method == 'index') {
			return call_user_func_array(array($this, 'index'), $params);
		}
		
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
		
		show_404();
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Главная страница
	 */
	public function add_server()
	{
		/* Условие ниже проверяет на два условия
		 * 1. Авторизован ли пользователь и является ли он админом
		 * 2. Разрешено ли добавление серверов простыми пользователями 
		 * 
		 * Если пользователь не админ и добавление серверов запрещено, то отображается сообщение
		 * о невозможности добавить сервер
		*/
		if (!($this->users->auth_id && $this->users->auth_data['is_admin'])
			&& !$this->config->item('gmon_allow_add_servers')) 
		{
			// Добавление новых серверов обычным пользователям запрещено
			$this->_show_message(lang('gmon_error_add_denied'));
			return false;
		}
		
		/* Условие ниже проверяет на два условия
		 * 1. Авторизован ли пользователь
		 * 2. Разрешено ли добавление серверов анонимными пользователями
		 * 
		 * Если пользователь не авторизован и добавление запрещено, то отображаем сообщение об ошибке
		*/
		if (!$this->users->auth_id && !$this->config->item('gmon_allow_add_server_not_auth')) {
			// Добавление новых серверов обычным пользователям запрещено
			$this->_show_message(lang('gmon_error_add_no_auth_denied'));
			return false;
		}
		
		// Загрузка модели работы с играми (application/models/servers/games.php)
		$this->load->model('servers/games');
		
		// Загрузка библиотеки работы с формами (system/library/Form_validation.php)
		$this->load->library('form_validation');
		
		// Загрузка библиотеки Query, для опроса серверов (application/library/Query.php)
		$this->load->library('query');
		
		/* Правила для формы */
		$this->form_validation->set_rules('game_code', 'game', 'trim|required|max_length[64]|xss_clean');
		$this->form_validation->set_rules('host', 'host', 'trim|required|max_length[128]|xss_clean');
		$this->form_validation->set_rules('description', lang('description'), 'trim|xss_clean');
		
		if($this->form_validation->run() == false) {
			
			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}
			
			// Получение списка игр, который пойдет в шаблон
			$local_tpl_data['games_list'] = $this->games->tpl_data_games();
			
			$this->tpl_data['content'] = $this->parser->parse('add_server.html', $local_tpl_data, true);
		} else {
			// Форма отправлена проверяем ее
			
			$sql_data['game_code'] 		= $this->input->post('game_code');
			$sql_data['description'] 	= $this->input->post('description');

			if (!$this->games->get_games_list(array('code' => $sql_data['game_code']))) {
				$this->_show_message(lang('adm_servers_game_not_found'));
				return false;
			}
			
			$sql_data['game_name'] 		= $this->games->games_list[0]['name'];
			$sql_data['game_engine'] 	= strtolower($this->games->games_list[0]['engine']);
			
			$explode = explode(':', $this->input->post('host'));
			$sql_data['host'] = $explode[0];
			$sql_data['port'] = isset($explode[1]) ? $explode[1] : 27015;
			
			// Проверка сервера на существование
			if ($this->gmon_servers->get_list(array('host' => $sql_data['host'], 'port' => $sql_data['port']))) {
				$this->_show_message(lang('gmon_server_exists'));
				return false;
			}
			
			// Опрос сервера
			try {
				$query['id'] 	= 'query';
				$query['type'] 	= $sql_data['game_engine'];
				$query['host']	= $sql_data['host'];
				$query['port']	= $sql_data['port'];
				$this->query->set_data($query);
			
				$base_cvars = $this->query->get_base_cvars();
			} catch (Exception $e) {
				// К серверу подключиться не удалось, возможно указан неверно хост
				$this->_show_message(lang('gmon_server_connect_error'));
				return false;
			}
			
			if (!$base_cvars['query']['hostname']) {
				// Данные query не получены
				$this->_show_message(lang('gmon_server_connect_error'));
				return false;
			}
			
			if (($sql_data['game_engine'] == 'source' OR $sql_data['game_engine'] == 'goldsource') &&
				$base_cvars['query']['game_code'] != $sql_data['game_code']) 
			{
				// Сервер не относится к игре (работает с Source и GoldSrc)
				$this->_show_message(lang('gmon_error_server_not_match', $this->games->games_list[0]['name']));
				return false;
			}
			
			// Все проверки пройдены, можно добавлять сервер
			$sql_data['q_name'] 			= $base_cvars['query']['hostname'];
			$sql_data['q_map'] 				= $base_cvars['query']['map'];
			$sql_data['q_num_players'] 		= $base_cvars['query']['players'];
			$sql_data['q_max_players'] 		= $base_cvars['query']['maxplayers'];
			$sql_data['online']				= 1;

			$this->gmon_servers->add($sql_data);
			
			$this->_show_message(lang('adm_servers_add_server_successful'), site_url('gmon'), lang('next'));
			return true;
		}

		if ($this->config->item('gmon_gameap_template')) {
			$this->parser->parse('main.html', $this->tpl_data);
		} else {
			$this->parser->parse('main_gmon.html', $this->tpl_data);
		}
	}
	
}
