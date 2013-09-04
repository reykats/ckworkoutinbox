<?php

class metadata_signup extends generic_api {
	
	public function __construct() {
		parent::__construct();
	}

	public function get( $params = array() ) {
		if ( count($params) == 0 ) {
			// ------------------------------------------------------------------
			// findAll
			// ------------------------------------------------------------------
			return $this->findAll();
		} else {
			return $this->return_handler->results(400,"invalid URL parameter list",new stdClass());
		}
	}
	
	public function findAll() {
		$this->load->model('api/uom_api');
		$measurement = $this->uom_api->get();
		//echo "measurement:"; print_r($measurement);
		
		$attributes = array("measurement_table"=>$measurement['response']
		                    );
				
		return $this->return_handler->results(200,"",$attributes);
	}
	
	public function post($data,$action=false) {
		return $this->return_handler->results(400,"You can not POST to this API",new stdClass());
	}

	public function put($data) {
		return $this->return_handler->results(400,"You can not PUT to this API",new stdClass());
	}

	public function delete($params = array()) {
		return $this->return_handler->results(400,"You can not DELETE to this API",new stdClass());
	}
}
?>