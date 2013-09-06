<?php

// controllers can not be auto loaded, so you must require_once the inheritted class
require_once APPPATH.'controllers/generic_controller.php';

class activity extends generic_controller {

	public function __construct() {
		parent::__construct();
	}

}