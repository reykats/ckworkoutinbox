<?php

class return_handler {
	
	public function __construct() {
		// echo "-return_handler-";
	}
	
	public function results($p_status,$p_message,$p_response) {
		$return = array();
		$return['status'] = $p_status;
		$return['message'] = $p_message;
		$return['response'] = $p_response;
		
		return $return;
	}
	
}