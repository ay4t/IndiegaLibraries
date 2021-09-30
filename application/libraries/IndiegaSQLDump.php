<?php

/**
 * @Author: Ayatulloh Ahad R
 * @Date:   2021-10-01 02:48:18
 * @Email:   ayatulloh@indiega.net
 * @Last Modified by:   ay4t
 * @Last Modified time: 2021-10-01 02:50:19
 * @Description: 
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

set_time_limit(0);

class IndiegaSQLDump {
	// Atribut Global
	public $versi_dump = '0.3'; // Versi Library MySQL Dump	
	public $CI = NULL; // Inisialisasi variabel CI
	
	// ----- Konfigurasi Folder Backup Internal. (No Download Required)
	// Knfigurasi path folder internal, jangan lupa untuk mengakhiri 
	// string dengan garis miring ("/") pada atribut $save_path.
	public $save_path = './backup/';
	public $file_name = NULL;
	
	// Konstruktor
	public function __construct()
	{
		// Instansi CI
		$this->CI =& get_instance();
		// Jika ingin mengganti file name, silahkan ganti pada konfigurasi konstruktor
		// dibawah ini
		$this->file_name = 'backup_'.date("dmY_His").'.sql';
	}
	
	// Fungsi dumping database
	public function do_dump($is_internal = false)
	{
		// ----- BEGIN -----
		
		// Set String
		$string = '/*
| MySQL Dump Versi - '.$this->versi_dump.'
*/

';
		// Tampilkan seluruh nama table
		$result = $this->CI->db->list_tables();
		
		// Cek apakah akan disimpan di folder internal?
		if(!$is_internal):
			// Set Header
			header('Content-type: application/sql');
			header('Content-Disposition: attachment; filename="'.$this->file_name.'"');
			
			print($string);
			
			// Looping
			if($result):
				foreach($result as $i => $v):
					// Ambil Data Table Structure
					print($this->get_structure($v));
					// Insert Data
					print($this->table_data($v));
				endforeach;
			endif;
			
		else:
			// Jika 'is_internal' bernilai 'true'
			
			// Looping
			if($result):
				foreach($result as $i => $v):
					// Ambil Data Table Structure
					$string .= $this->get_structure($v);
					// Insert Data
					$string .= $this->table_data($v);
				endforeach;
			endif;
			
			// Simpan Data
			$this->CI->load->helper('file');
			// Cek direktori
			if(!file_exists($this->save_path)) mkdir($this->save_path);
			if(!write_file($this->save_path . $this->file_name, $string)) die('Terjadi ERROR saat menulis data! (ERROR accuired on saving data)');
			else return true;
		endif;
		// ----- END
		$result->free_result();
		
		// Return
		return true;
	}
	
	// Fungsi Insert Table data
	private function table_data($table = NULL)
	{
		// Set String
		$string = '';
		
		// Pilih Table
		$result = $this->CI->db->get($table);
		if($result)
		{
			// Ambil Banyak Data dan Banyak Field
			$banyak_data = $result->num_rows();
			$banyak_field = $result->num_fields();
			
			// Jika ada data
			if($banyak_data > 0)
			{
				$string .= "/* Dumping data untuk tabel `".$table."` */\n";	
				// Ambil tipe field
				$tipe_field = array();
				$nama_field = array();
				$meta = $this->CI->db->field_data($table);
				foreach($meta as $k => $v):
					array_push($tipe_field, $v->type);
					array_push($nama_field, $v->name);
				endforeach;
				
				// Insert SQL
				$string .= "INSERT INTO `".$table."` VALUES\n";
				$j = 0;
				foreach($result->result_array() as $l):
					$string .= "(";
					$i = 0;
					foreach($nama_field as $i => $v):
						if(is_null($l[$v])):
							$string .= "NULL";
						else:
							switch($tipe_field[$i]):
								case 'int':
								case 'float':
									$string .= $l[$v];
								break;
								case 'string':
								case 'varchar':
								case 'date':
								case 'datetime':
								case 'enum':
								case 'text':
								case 'blob':
									$string .= "'".mysql_real_escape_string($l[$v])."'";
								break;
							endswitch;
						endif;
						$i++;
						if($i < $banyak_field) $string .= ', ';
						else $string .= ')';
					endforeach;
				$j++;
				if($j < $banyak_data) $string .= ',
';
				else $string .= ';
';
				endforeach;
			}
		}
		return $string;
	}
	
	// Fungsi Ambil Data Table Structure
	private function get_structure($table = NULL)
	{
		$string = '

		/* Struktur Table untuk table `'.$table.'` */';	
		$string .= "\n\nDROP TABLE IF EXISTS `".$table."`;\n\n";
		// Buat Table
		$sql = "SHOW CREATE TABLE `".$table."`; ";
		$result = $this->CI->db->query($sql)->row_array();
		
		// Looping
		if($result):
			$string .= $result['Create Table'].";\n\n";
		endif;
		return $string;
	}
}