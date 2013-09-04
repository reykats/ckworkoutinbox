<?php

// controllers can not be auto loaded, so you must require_once the inheritted class
require_once APPPATH.'controllers/generic_controller.php';

class w_app extends generic_controller {

	public function __construct() {
		parent::__construct();
	}

	public function _remap($p_params1, $p_params = array()) {
		
		// if the version exists, remove the method from the param list
		if ( !empty($this->url_version) ) {
			$method = array_shift($p_params);
		}
		// put the controller and method (not version if it exists) back into the params
		$params = array_merge((array) $this->url_controller, (array) $this->url_model, (array) $p_params);

		// if the system is down for maintenance, abort
		$return = $this->testDownForMaintenance();
		if ( $return['status'] > 200 ) {
			$this->echoReturn($return);
			return;
		}

		// if the call to this application is a different version than the code base, abort
		$return = $this->testVersion();
		if ( $return['status'] > 200 ) {
			$this->echoReturn($return);
			return;
		}

		// If the user is not logging in and they are not logged in, abort
		$return = $this->testLoggedIn();
		if ( $return['status'] > 200 ) {
			$this->echoReturn($return);
			return;
		}

		// If the user is not logging in and they are logged into a different application, abort
		$return = $this->testLoggedInApplication();
		if ( $return['status'] > 200 ) {
			$this->echoReturn($return);
			return;
		}

		// echo "-A-";
		$this->load->model('api/api_api');
		// echo "-B-";
		$return = $this->api_api->process($params);
		
		$request_method = $_SERVER['REQUEST_METHOD'];
		if ( $request_method == "PUT" || $request_method == "POST" ) {
			// log the api call
			$this->load->model('log/log_api');
			$this->log_api->put($return);
		}

		// echo the returned results
		$this->echoReturn($return);
	}
}