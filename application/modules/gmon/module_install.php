<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource	
 */
 
/**
 * Структура базы данных gmon модуля
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
*/

if(version_compare(AP_VERSION, '0.9-dev') == -1) {
	// Необходима версия GameAP не ниже 0.9-dev
	continue;
}

$this->load->dbforge();

/*----------------------------------*/
/* 				gmon_servers		*/
/*----------------------------------*/
if (!$this->db->table_exists('gmon_servers')) {

	$fields = array(
			'id' => array(
								'type' => 'INT',
								'constraint' => 16, 
								'auto_increment' => true
			),
			
			'game_code' => array(
								'type' => 'CHAR',
								'constraint' => 16, 
								'default' => 'valve',
			),
			
			'game_name' => array(
								'type' => 'CHAR',
								'constraint' => 16, 
								'default' => 0,
			),
			
			'game_engine' => array(
								'type' => 'TINYTEXT',
			),
			
			'server_id' => array(
								'type' => 'INT',
								'constraint' => 16, 
								'default' => 0,
			),
			
			'online' => array(
								'type' => 'INT',
								'constraint' => 1, 
								'default' => 0,
			),
			
			'host' => array(
								'type' => 'TEXT',
			),
			
			'port' => array(
								'type' => 'INT',
								'constraint' => 5,
								'default' => 27015,
			),
			
			'q_name' => array(
								'type' => 'TEXT',
			),
			
			'q_map' => array(
								'type' => 'TEXT',
			),
			
			'q_online_players' => array(
								'type' => 'TEXT',
			),
			
			'q_num_players' => array(
								'type' => 'INT',
								'constraint' => 4, 
								'default' => 0,
			),

			'q_max_players' => array(
								'type' => 'INT',
								'constraint' => 4,
								'default' => 32,
			),
			
			'q_variables' => array(
								'type' => 'TEXT',
								'default' => '',
			),
			
			'description' => array(
								'type' => 'TEXT',
			),
			
			'rating' => array(
								'type' => 'INT',
								'constraint' => 16, 
								'default' => 0,
			),
			
			'commentaries' => array(
								'type' => 'TEXT',
			),
			
			'other_data' => array(
								'type' => 'TEXT',
			),

	);

	$this->dbforge->add_field($fields);
	$this->dbforge->add_key('id', true);
	$this->dbforge->create_table('gmon_servers');
}


