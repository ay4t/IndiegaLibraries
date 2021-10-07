<?php

/**
 *
 * @Author: Ayatulloh Ahad R
 * @Email: ayatulloh@indiega.net
 * @Url: https://github.com/ay4t
 * @Date:   2021-10-05 10:28:22
 * @Last Modified by:  Ayatulloh Ahad R - Device Name: acer
 * @Last Modified time: 2021-10-07 14:57:23
 * @Description:
 *
 * License: 
 *
 */

defined('BASEPATH') OR exit('No direct script access allowed');



class IndiegaROIsystem extends CI_Model {

	private $dev_mode 	= true;

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
	}


	/**
	 * membuat system return of investment yang akan masuk ke dalam tabel tb_walet_balance_roi_list. kemudian akan diproses bonus matching profit berdasarkan status 0
	 *
	 * @return void
	 * @author Ayatulloh Ahad R - ayatulloh@indiega.net
	 **/
	private function run()
	{	

		ini_set('max_execution_time', '0');

		/*system libur jika tersedia*/
		

		//set manual waktu maintenance
		$date_now 		= date('Y-m-d 00:00:00');
		$trx_id			= hash('SHA256', rand().time() );


		$profit_date 	= option('roi_run_date');
		$only_date 		= date('Y-m-d');
		if( $profit_date < $only_date ){

			if ( ! $this->dev_mode ) {
				//tanggal sudah kadalauarsa update offset menjadi 0
				set_option( 'offset_per_roi', 0 );
				set_option( 'roi_run_date', $only_date );
			}
		}

		$limit 			= option('limit_per_roi');
		$get_offset 	= option('offset_per_roi');
		$offset 		= ( ! empty( $get_offset ) )? $get_offset : 0;


		if ( $this->dev_mode ) {
			/*for dev mode*/
			$this->db->where('activation_userid', 958);
			$offset 		= 0;
		}

		$this->db->join('tb_users', 'id = activation_userid', 'inner');
		$this->db->join('tb_packages', 'package_id = activation_package_id', 'inner');
		$this->db->where('activation_datestart <', $date_now );
		$this->db->where('activation_dateend >=', isNow() );
		$this->db->where('active', 1);
		// $this->db->where('lock_profit', 'false');
		$getActivation 	= $this->db->get('tb_activation', $limit, $offset);

		$trxData 		= array();
		$wallet_bonus 	= array();
		$startArray 	= 0;

		/*set default output jika tidak ada data ditampilkan*/
		$output 		= false;

		foreach ($getActivation->result() as $activation) {

			$offset++;
			$percentase_profits 	= $activation->package_profit_start;

			/*menghitung berapa bonus ROI didapat*/
			$calcROIbonus 	= ( $activation->activation_amount * $percentase_profits )/100;

			/*get passive wallet ID*/
			$passiveWallet 	= $this->wallet->getAddress('passive', 'wallet_id', $activation->activation_userid);

			

			$trxData[] 		= 			
				[
					'w_balance_wallet_id'  	=> $passiveWallet,
					'w_balance_amount'  	=> $calcROIbonus,
					'w_balance_type'  		=> 'credit',
					'w_balance_desc'  		=> 'Profit '.$percentase_profits.'% dari aktivasi paket: ' . currency($activation->activation_amount),
					'w_balance_payment'		=> 'bonus',
					'w_balance_date_add'  	=> $date_now,
					'w_balance_txid'  		=> $trx_id				
				];
			

			$startArray2 = $startArray + 1;

			// $user_group 	= $this->ion_auth->get_users_groups( $activation->id )->row();
			if ( $activation->lock_profit == 'false' ) {
				
				$bonusWallet 	= $this->wallet->getAddress('bonus', 'wallet_id', $activation->activation_userid);

				$trxData[] 	= [
					'w_balance_wallet_id'  	=> $bonusWallet,
					'w_balance_amount'  	=> $calcROIbonus,
					'w_balance_type'  		=> 'credit',
					'w_balance_desc'  		=> 'Profit '.$percentase_profits.'% dari aktivasi paket: ' . currency($activation->activation_amount),
					'w_balance_payment'		=> 'bonus',
					'w_balance_date_add'  	=> $date_now,
					'w_balance_txid'  		=> $trx_id
				];

				

				$activation->amount_profits 	= $calcROIbonus;
				$activation->trx_id 			= $trx_id;
				$add_data_push 	= $this->affiliateLevels( $activation );

				/*perulangan untuk membuat data array flat dengan array trxData*/
				foreach ($add_data_push as $valueLalala) {
					$trxData[] 	= $valueLalala;
				}


			}


		}

		if ( $getActivation->num_rows() > 0 ) {

			if ( ! $this->dev_mode ) {
				/*set offset untuk looping selanjutnya*/
				set_option( 'offset_per_roi', $offset );
			}

			$output 	= $trxData;

		}

		return $output;

	}


	/**
	 * required : 	- Object userdata
	 				- $userdata->amount_profits
	 				- $userdata->count_loop
	 *
	 * @return void
	 * @author Ayatulloh Ahad R - ayatulloh@idprogrammer.com
	 **/
	private function affiliateLevels( $userdata = Object)
	{

		// mengecek jumlah packet yang paling banyak levelnya
		/*$this->db->order_by('package_id', 'desc');
		$get_big_packet = $this->db->get('tb_packages',1)->row(); */

		/*$affiliateLevelsPercent 	= [15, 10, 5, 3, 1];
		$jml = count( $affiliateLevelsPercent );*/


		if ( $userdata->leader == 'true' ) {
			return false;
		}

		$jml = 7;
		$date_now 		= date('Y-m-d 00:00:00');

		
		$ref 			= $userdata->referral_id; // get user ref yg sekarang aktivasi

		$start_level 	= 1;
		$no 			= 0;

		/*default output*/
		$trxData 		= array();

		for ($i=1; $i <= $jml ; $i++) { 
			
			$referral_data = userdata(array('id' => $ref));
			if ( $referral_data ){

				
				$referral = $referral_data->id;

				// cek packet si referral
				$this->db->join('tb_packages', 'package_id = activation_package_id', 'inner');
				$get_lending 	= $this->db->get_where('tb_activation', array('activation_userid' => $referral));
				if ( $get_lending->num_rows() > 0 ){

					$referralLending = $get_lending->row();

					//cek apakah referral masih masuk kedalam range kedalaman?
					$range_kedalaman 	= $referralLending->package_profit_array;
					if ( $range_kedalaman >= $i ) {
						
						$get_pair_percentace 	= 5; // mendapatkan 1% dari total amount profit didapat
						$get_total_pair 		= ($userdata->amount_profits * $get_pair_percentace) / 100;
							
						/*echo 'Referral Level ke '.$i.' Username '.$referral_data->username.' ('.$referralLending->package_name.') mendapatkan Matching ROI sebesar '.currency($get_total_pair).' dari ROI ' . currency($userdata->amount_profits);
						echo br();*/


						$affiliate_wallet_id = $this->wallet->getAddress('bonus', 'wallet_id', $referral);
						$trxData[] 	= [
							'w_balance_wallet_id'  	=> $affiliate_wallet_id,
							'w_balance_amount'  	=> $get_total_pair,
							'w_balance_type'  		=> 'credit',
							'w_balance_payment'		=> 'bonus',
							'w_balance_desc'  		=> 'Bonus Matching ROI '.$get_pair_percentace.'% dari '.currency($userdata->amount_profits).'. <br/>Level ke: ' .$i.'. Username: ' . $userdata->username,
							'w_balance_date_add'  	=> $date_now,
							'w_balance_txid'  		=> $userdata->trx_id
						];

					} else {

						/*echo 'Referral Level ke '.$i.' Username '.$referral_data->username.' paket '.$referralLending->package_name.' <strong>tidak masuk range kedalaman Matching ROI</strong>';
						echo br();*/
					}


				} else {

					echo 'User reff: ' .$referral. ' tidak ada paket aktivasi';

				}

				$ref = userdata(array('id' => $referral))->referral_id;

			}
		}

		return $trxData;

	}


	/**
	 * undocumented function
	 *
	 * @return void
	 * @author 
	 **/
	public function insertROI()
	{

		$getROIdata 	= $this->run();

		if ( is_array( $getROIdata ) ) {

			echo "<pre>";
			print_r ($getROIdata);
			echo "</pre>";

			if ( ! $this->dev_mode ) {
				return $this->db->insert_batch( 'tb_wallet_balance', $getROIdata);
			}
			
			
			/*for development only store data to second database */
			// $this->db2 = $this->load->database('db_roi', true);
			// return $this->db2->insert_batch( 'tb_wallet_balance', $getROIdata);
			

			return true;
		} else {
			echo 'insert tidak ada data array !';
			return false;
		}

	}

}

/* End of file IndiegaROIsystem.php */
/* Location: ./application/models/IndiegaROIsystem.php */