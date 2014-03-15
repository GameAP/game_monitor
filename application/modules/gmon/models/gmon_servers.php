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

class Gmon_servers extends CI_Model {
	
	var $mservers_list = array();
	private $_filter 	= array('game_code' => false, 'host' => false, 'port' => false, 'q_name' => false, 'q_map' => false);
	
	//------------------------------------------------------------------
		
	/**
     * Получение списка серверов
    */
	public function get_list($where = array(), $limit = 99999, $offset = 0)
    {
		if (is_array($where) && !empty($where)) {
			$query = $this->db->where($where);
		}
		
		!$this->_filter['game_code'] OR $this->db->where('game_code', $this->_filter['game_code']);
		!$this->_filter['host'] OR $this->db->where('host', $this->_filter['host']);
		!$this->_filter['port'] OR $this->db->where('port', $this->_filter['port']);
		!$this->_filter['q_name'] OR $this->db->like('q_name', $this->_filter['q_name']);
		!$this->_filter['q_map'] OR $this->db->where('q_map', $this->_filter['q_map']);
		
		$this->db->limit($limit, $offset);
		$query = $this->db->get('gmon_servers');

		if($query->num_rows > 0) {
			
			$this->mservers_list = $query->result_array();
			return $this->mservers_list;
			
		} else {
			return array();
		}
	}
	
	//------------------------------------------------------------------
	
	/**
	 * Задаем фильтр для выборки
	 */
	public function set_filter($filter)
	{
		if (is_array($filter)) {
			
			if (isset($filter['host'])) {
				$explode = explode(':', $filter['host']);
				$filter['host'] = $explode[0];
				$filter['port']	= isset($explode[1]) ? $explode[1] : null;
			}
			
			$this->_filter['game_code'] = (isset($filter['game']) && $filter['game']) ? $filter['game'] : null;
			$this->_filter['host'] 		= (isset($filter['host']) && $filter['host']) ? $filter['host'] : null;
			$this->_filter['port'] 		= (isset($filter['port']) && $filter['port']) ? $filter['port'] : null;
			$this->_filter['q_name'] 	= (isset($filter['name']) && $filter['name']) ? $filter['name'] : null;
			$this->_filter['q_map'] 	= (isset($filter['map']) && $filter['map']) ? $filter['map'] : null;
		}
	}

	//------------------------------------------------------------------
		
	/**
     * Новый сервер
    */
	public function add($data)
    {
		return (bool)$this->db->insert('gmon_servers', $data);
	}
	
	//-----------------------------------------------------------
		
	/**
     * Редактирование сервера
    */
	public function edit($mserver_id, $data)
    {
		$this->db->where('id', $mserver_id);
		return (bool)$this->db->update('gmon_servers', $data);
	}
	
	//-----------------------------------------------------------
		
	/**
     * Удаление сервера
    */
	public function delete($mserver_id)
    {
		return (bool)$this->db->delete('gmon_servers', array('id' => $mserver_id));
	}
	
	//-----------------------------------------------------------
		
	/**
     * Получение количества с строк
    */
	public function get_count_all($where = array())
	{
		if (is_array($where) && !empty($where)) {
			$query = $this->db->where($where);
		}
		
		!$this->_filter['game_code'] OR $this->db->where('game_code', $this->_filter['game_code']);
		!$this->_filter['host'] OR $this->db->like('host', $this->_filter['host']);
		!$this->_filter['port'] OR $this->db->like('port', $this->_filter['port']);
		!$this->_filter['q_name'] OR $this->db->like('q_name', $this->_filter['q_name']);
		!$this->_filter['q_map'] OR $this->db->where('q_map', $this->_filter['q_map']);
		
		return $this->db->count_all_results('gmon_servers');
	}
}
