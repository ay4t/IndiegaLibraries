<?php

/**
 * @Author: Ayatulloh Ahad R
 * @Date:   2021-10-08 00:05:14
 * @Email:   ayatulloh@indiega.net
 * @Last Modified by:   vanz
 * @Last Modified time: 2021-10-08 00:47:51
 * @Description: 
 */

require_once APPPATH . '/third_party/indiegaCRUD/xcrud/xcrud.php';

class IndiegaCRUD extends Xcrud
{
	
	public $name 	= false;

	function __construct()
	{
		
		$CI = &get_instance();
        $CI->load->library('session', 'database');
        $CI->load->helper('url');

        /*load database configuration*/
        include APPPATH. '/config/database.php';

        /*loader config*/
        $crudConfig 	= new Xcrud_config;
		$crudConfig::$scripts_url 	= base_url('');
		$crudConfig::$dbname 		= $db['default']['database'];
		$crudConfig::$dbuser  		= $db['default']['username'];
		$crudConfig::$dbpass   		= $db['default']['password'];
		$crudConfig::$dbhost   		= $db['default']['hostname'];

        $IndiegaCrud = Xcrud::get_instance($this->name);
        
        return $IndiegaCrud;

	}
}