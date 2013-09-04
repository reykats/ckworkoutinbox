<?php
// controllers can not be auto loaded, so you must require_once the inheritted class
require_once APPPATH.'controllers/generic_controller.php';

class mobile extends generic_controller {

	public function __construct() {
		parent::__construct();
	}
	

	public function _remap($p_params1, $p_params = array()) {
		$return = $this->return_handler->results(400,"Please download the latest software update!",new stdClass());
		echo json_encode($return);
	}
}
