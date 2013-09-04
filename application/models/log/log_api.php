<?php

class log_api extends CI_Model {
	// ------------------------------------------------------------------------------------------------------------------------------------------
	// create log file
	// ------------------------------------------------------------------------------------------------------------------------------------------

	// The csv file name
	protected $filename = '../logs/workoutinbox_api_log.csv';
	// The file pointer
	protected $fp;

	public function __construct() {
		parent::__construct();
	}

	public function open() {
		$this->fp = fopen($this->filename, "a");
	}

	public function put( $p_return = '' ) {
		// get the api information
		$url = $this->config->site_url($this->uri->uri_string());
		if ( isset($_SERVER['QUERY_STRING']) && !is_null($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ) {
			$url .= '?' .  $_SERVER['QUERY_STRING'];
		}
		// echo "url: $url<br />";

		// get the api request method for data
		$request_method = $_SERVER['REQUEST_METHOD'];
		// echo "request_method: $request_method<br />";
		
		$data = json_decode(file_get_contents("php://input"));

		// get the controller
		$controller = $this->router->fetch_class(1);
		// echo "controller: $controller<br />";

		$user_id = null;
		$user_first_name = null;
		$user_last_name = null;
		$user_email = null;
		$user_ip = null;
		$user_agent = null;
		$session = array();

		if ( $controller != "zp" ) {
			// get the session user data
			$session = $this->session->all_userdata();
			// print_r($session);

			if ( array_key_exists('user',$session) ) {
				$user = clone $session['user'];
				if ( property_exists($user,'user_id') ) {
					$user_id = $user->user_id;
				}
				if ( property_exists($user,'first_name') ) {
					$user_first_name = $user->first_name;
				}
				if ( property_exists($user,'last_name') ) {
					$user_last_name = $user->last_name;
				}
				if ( property_exists($user,'email') ) {
					$user_email = $user->email;
				}
			}
		}

		if ( array_key_exists('REMOTE_ADDR',$_SERVER) ) {
			$user_ip = $_SERVER['REMOTE_ADDR'];
		}

		// print_r($session);

		$entry = array();
		$entry[] = date('Y/m/d H:i:s');
		$entry[] = $user_id;
		$entry[] = $user_email;
		$entry[] = $user_first_name;
		$entry[] = $user_last_name;
		$entry[] = $user_ip;
		$entry[] = $controller;
		$entry[] = $request_method;
		$entry[] = $url;
		$entry[] = json_encode($data);
		$entry[] = json_encode($p_return);
		$entry[] = json_encode($session);
		// print_r($entry);

		$this->open();

		fputcsv($this->fp, $entry);

		unset($entry);

		$this->close();
	}

	public function close() {
		 fclose($this->fp);
	}
}