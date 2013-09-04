<?php

// controllers can not be auto loaded, so you must require_once the inheritted class
require_once APPPATH.'controllers/generic_controller.php';

class w_widget extends generic_controller {
	
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

		$this->load->model('api/api_widget');
		$return = $this->api_widget->process($params);

		// echo the returned results
		$this->echoReturn($return);
	}
}