<?php

// controllers can not be auto loaded, so you must require_once the inheritted class
require_once APPPATH.'controllers/generic_controller.php';

class cli extends generic_controller {

	public function __construct() {
		parent::__construct();
	}

	public function _remap($p_url_method, $p_params = array()) {
		// ==================================================================
		// Do not allow this contoller to run from a browser
		// ==================================================================
		if ( isset($_SERVER['REMOTE_ADDR']) ) {
			$return = $this->return_handler->results(400,"This API can not be run from a browser",new stdClass());
		}
		
		// $return = mysql_schema::get();
		// print_r(json_encode($return));
		
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

		$this->load->model('api/api_api');
		$return = $this->api_api->process($params);

		// echo the returned results
		$this->echoReturn($return);
	}
}