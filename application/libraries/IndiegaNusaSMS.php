<?php

/**
 * @Author: Ayatulloh Ahad R
 * @Date:   2021-10-01 03:03:21
 * @Email:   ayatulloh@indiega.net
 * @Last Modified by:   vanz
 * @Last Modified time: 2021-10-01 03:34:34
 * @Description: 
 */

class IndiegaNusaSMS
{
	
	public $apiKey;

	/*base URL untuk main API*/
	public $base_url = 'https://api.nusasms.com/nusasms_api/1.0';

	/*base URL untuk dev API*/
	// public $base_url = 'https://dev.nusasms.com/nusasms_api/1.0';

	public $destination = '6285791555506';

	public $message 	= 'test';
	public $endpoint 		= '';

	

	public function __construct()
	{	
		$CI =& get_instance();
		$CI->config->load('indiega_nusa_sms', TRUE);

		$this->apiKey = $CI->config->item('nusasms_apiKey', 'indiega_nusa_sms');
	}

	/**
	 * Fungsi untuk mengirimkan notifikasi Whatsapp 
	 *
	 * @return void
	 * @author 
	 **/
	public function sentWhatsapp( $destination = 0, $message = null )
	{
		$this->endpoint 	= '/whatsapp/message';
		$this->destination 	= $destination;
		$this->message 		= $message;

		return $this->sentData();
	}


	/**
	 * Fungsi untuk mengirimkan notifikasi SMS 
	 *
	 * @return void
	 * @author 
	 **/
	public function sentSMS( $destination = 0, $message = null )
	{
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author 
	 **/
	private function sentData()
	{

		$curl = curl_init();
		$payload = json_encode(array(
		    // 'sender' => 'YOUR_SENDER',
		    'destination' 	=> $this->destination,
		    'message' 		=> $this->message
		));
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $this->base_url . $this->endpoint,
		    CURLOPT_POST => true,
		    CURLOPT_HTTPHEADER => array(
		        "APIKey:{$this->apiKey}",
		        'Content-Type:application/json'
		    ),
		    CURLOPT_POSTFIELDS => $payload,
		    // CURLOPT_SSL_VERIFYPEER => 0,    // Skip SSL Verification
		));

		$resp = curl_exec($curl);

		if (!$resp)  {
			$return = false;
		} else {
			$return 	= json_decode($resp, true);
		}

		curl_close($curl);
		return $return;
	}

}