<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tetscrud extends CI_Controller {

	public function index()
	{
		$this->load->library('indiegacrud');

		$a 	= $this->indiegacrud->table('tb_users')->render();
		echo $a;

	}

}

/* End of file Tetscrud.php */
/* Location: ./application/controllers/Tetscrud.php */