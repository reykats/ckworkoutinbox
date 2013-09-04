<?php

// controllers can not be auto loaded, so you must require_once the inheritted class
require_once APPPATH.'controllers/generic_controller.php';

class zp extends generic_controller {

	public function __construct() {
		parent::__construct();
	}

	public function _remap($p_params1, $p_params = array()) {
		// var_dump($p_params);
		
		// put the controller and method (not version if it exists) back into the params
		$params = array_merge((array) $this->url_controller, (array) $p_params1, (array) $p_params);
		
		// var_dump($params);

		// if the system is down for maintenance, abort
		$return = $this->testDownForMaintenance();
		// var_dump($return);
		if ( $return['status'] > 200 ) {
			$this->echoReturn($return);
			return;
		}

		$request_method = $_SERVER['REQUEST_METHOD'];
		if ( $request_method == 'POST' ) {
			// zenplanner passes the data in url parameter format.  Translate that into an object.
			$query_string = file_get_contents("php://input");
		} else {
			$return = $this->return_handler->results(400,"POST not used",new stdClass());
			$this->echoReturn($return);
			return;
		}
		
		// echo "string - $query_string";
		
		$this->load->model('zp/zp_membership');
		$this->zp_membership->log_trans($query_string,'Zen Planner Data');
		
		$a = explode('&',$query_string);
		$data = new stdClass();
		foreach ( $a as $entry ) {
			$b = explode('=', $entry);
			$data->{htmlspecialchars(urldecode($b[0]))} = htmlspecialchars(urldecode($b[1]));
		}
		
		// print_r($data);
		
		// process the post data
		$this->load->model('zp/zp_membership');
		$return_save = $this->zp_membership->post($data);
		
		if ( $request_method == "PUT" || $request_method == "POST" ) {
			// log the api call
			$this->load->model('log/log_api');
			$this->log_api->put($return);
		}

		// echo the returned results
		$this->echoReturn($return_save);
	}
}