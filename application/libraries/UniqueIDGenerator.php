<?php

/**
 * @Author: Ayatulloh Ahad R
 * @Date:   2021-10-28 00:14:01
 * @Email:   ayatulloh@indiega.net
 * @Last Modified by:   ay4t
 * @Last Modified time: 2021-10-28 00:44:45
 * @Description: 
 */

/**
 * UniqueIDGenerator for Codeigniter
 * 
 * library ini saya buat bertujuan untuk membuat ID yang unik dan tidak atau belum pernah digunakan di dalam tabel dan kolom tertentu.
 * 
 * 	Contoh penggunaan
 * 
	$this->load->library('UniqueIDGenerator');

	$a = $this->uniqueidgenerator;
	$a->table_name 	= 'tb_users_invoice';
	$a->column_name	= 'inv_code';
	$a->value		= 'INV00001';

	$b 	= $a->validate();

 */
class UniqueIDGenerator
{

	private $CI;

	/*Nama tabel yang akan digunakan*/
	public $table_name;
	
	/*Nama kolom yang akan digunakan sebagai kolom unik*/
	public $column_name;

	/*Isi dari kolom yang akan dilakukan pengecekan Apakah sudah tersedia di tabel tersebut jika sudah terdapat file yang sama maka akan dibuatkan data yang baru*/
	public $value;

	/*Fungsi generator untuk saat ini masih tersedia dalam metode numerik*/
	public $randomizer = 'numeric';
	public $min = '1';
	public $max = '999999';
	
	function __construct()
	{
		$this->CI = &get_instance();
	}

	/**
	 * Fungsi untuk mem-validasi apakah data tersebut tersedia di dalam tabel dan kolom yang ditunjuk jika terdapat data yang sama maka dibuatkan data yang baru
	 *
	 * @return void
	 * @author Ayatulloh Ahad R - ayatulloh@indiega.net
	 * https://github.com/ay4t
	 **/
	function validate()
	{	
		$this->CI->db->where( $this->column_name , $this->value);
		$query 	= $this->CI->db->get( $this->table_name);
		if ( $query->num_rows() > 0 ) {
			$this->value = $this->generator();
			$result = $this->validate();
		} else {
			$result = $this->value;
		}

		return $result;
	}

	/**
	 * Fungsi generator bertujuan untuk mengenerate dengan metode HASH yang dipilih
	 *
	 * @return void
	 * @author Ayatulloh Ahad R - ayatulloh@indiega.net
	 * https://github.com/ay4t
	 **/
	private function generator()
	{
		if ( $this->randomizer == 'numeric' ) {
			$result = rand( $this->min, $this->max );
		}

		return str_pad($result, 6, "0", STR_PAD_LEFT);
	}

	
}