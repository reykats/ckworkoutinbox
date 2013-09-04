<?php

class rest_request extends action_generic {

	// Curl transaction elements
	protected $url;
	protected $method;
	protected $requestBody;
	protected $requestLength;
	protected $acceptType;
	protected $responseBody;
	protected $responseInfo;
	
	// Curl headers
	protected $headers = array();
	
	// Curl Handle
	protected $ch;
	
	public function __construct () {
		parent::__construct();
		
		$this->acceptType = "application/json";
	}
	
	public function get($p_url=null,$p_headers=array()) {
		return $this->perform('this->request',$p_url,'GET',null,$p_headers);
	}
	
	public function post($p_url=null, $p_requestBody=null,$p_headers=array()) {
		return $this->perform('this->request',$p_url,'POST',$p_requestBody,$p_headers);
	}
	
	public function put($p_url=null, $p_requestBody=null,$p_headers=array()) {
		return $this->perform('this->request',$p_url,'PUT',$p_requestBody,$p_headers);
	}
	
	public function delete($p_url=null,$p_headers=array()) {
		return $this->perform('this->request',$p_url,'DELETE',null,$p_headers);
	}
	
	protected function request ($p_url=null, $p_method='GET', $p_requestBody=null,$p_headers=array()) {
		// setup your request
		$this->url = $p_url;
		$this->method = $p_method;
		$this->requestBody = $p_requestBody;
		$this->requestLength = 0;
		$this->responseBody = null;
		$this->responseInfo = null;
		$this->headers = $p_headers;
		
		if ( $this->requestBody !== null ) {
			$return = $this->perform('this->buildPostBody');
			if ( $return['status'] > 200 ) {
				return $return;
			}
		}
		
		// execute the restful request
		return $this->perform('this->execute');
	}
	
	protected function execute () {
		$this->ch = curl_init();
		if ( !$this->ch ) {
			return $this->return_handler->results(400,'Could not initialize Curl',null);
		}
		
		switch ( strtoupper($this->method) ) {
			case 'GET':
				return $this->perform('this->executeGet');
			case 'POST':
				return $this->perform('this->executePost');
			case 'PUT' :
				return $this->perform('this->executePut');
			case 'DELETE':
				return $this->perform('this->executeDelete');
			default:
				return $this->return_handler->results(400,'Invalid Curl Method (' . $this->method . ')',null);
		}
	}
	
	protected function buildPostBody ($p_data=null) {
		// if something in p_data use it, else use requestBody
		$p_data = ($p_data !== null) ? $p_data : $this->requestBody;
		/*
		// p_data must be an array
		if ( !is_array($p_data) ) {
			return $this->return_handler->results(400,'Invalid data input for postBody. Array expected',null);
		}
		*/
		// Generates a URL-encoded query string from an array
		// $p_data = http_build_query($p_data, '', '&');
		$p_data = json_encode($p_data);
		
		// store the converted p_data to requestBody
		$this->requestBody = $p_data;
		
		return $this->return_handler->results(200,'',null);
	}
	
	protected function executeGet() {
		return $this->perform('this->doExecute');
	}
	
	protected function executePost() {
		if ( !is_string($this->requestBody) ) {
			$this->buildPostBody();
			if ( $return['status'] > 200 ) {
				return $return;
			}
		}
		
		// Pass the requestBody as the POST data
		if ( !curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->requestBody) ) {
			return $this->return_handler->results(400,'CURLOPT_POSTFIELD could not be set.',null);
		}
		// This is a POST rest request
		if ( !curl_setopt($this->ch, CURLOPT_POST, true) ) {
			return $this->return_handler->results(400,'CURLOPT_POST could not be set.',null);
		}
		
		return $this->perform('this->doExecute');
	}
	
	protected function executePut() {
		if ( !is_string($this->requestBody) ) {
			$this->buildPostBody();
			if ( $return['status'] > 200 ) {
				return $return;
			}
		}
		
		// set the length of the request body
		$this->requestLength = strlen($this->requestBody);
		
		// Open the memory file for read/write
		$fh = fopen('php://memory','rw');
		if ( !$fh ) {
			return $this->return_handler->results(400,'fopen php://memory rw Failed',null);
		}
		// Write the requestBody to memory
		if ( !fwrite($fh, $this->requestBody) ) {
			return $this->return_handler->results(400,'Could not write requestBody to memory.',null);
		}
		// Set the file pointer back to the start of the memory file
		if ( !rewind($fh) ) {
			return $this->return_handler->results(400,'Could not reset memory pointer.',null);
			
		};
		
		// Set the input file to the memory pointer
		if ( !curl_setopt($this->ch, CURLOPT_INFILE, $fh) ) {
			return $this->return_handler->results(400,'CURLOPT_INFILE could not be set.',null);
		}
		// Set the length of the input data in the input file
		if ( !curl_setopt($this->ch, CURLOPT_INFILESIZE, $this->requestLength) ) {
			return $this->return_handler->results(400,'CURLOPT_INFILESIZE could not be set.',null);
		}
		// This is a PUT
		if ( !curl_setopt($this->ch, CURLOPT_PUT, true) ) {
			return $this->return_handler->results(400,'CURLOPT_PUT could not be set.',null);
		}
		
		return $this->perform('this->doExecute');
	}
	
	protected function executeDelete() {
		// This is a PUT
		if ( !curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE') ) {
			return $this->return_handler->results(400,'CURLOPT_CUSTOMREQUEST could not be set.',null);
		}
		
		return $this->perform('this->doExecute');
	}
	
	protected function doExecute() {
		// set the generic Curl options and set the Curl headers
		$return = $this->perform('this->setCurlOpts');
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		// execute the Curl
		$this->responseBody = curl_exec($this->ch);
		
		// Get info about the Curl excecute
		$this->responseInfo = curl_getinfo($this->ch);

		// close the Curl
		curl_close($this->ch);
		
		$response = $this->perform('this->formatResponse');
		
		// Did execute fail?
		if ( !$this->responseBody ) {
			return $this->return_handler->results(400,'Curl execute Failed.',$response);
		}
		// Did get info fail?
		if ( !$this->responseInfo ) {
			return $this->return_handler->results(400,'Get Info on last Curl execute Failed.',$response);
		}
		
		return $this->return_handler->results(200,'',$response['response']);
	}
	
	protected function setCurlOpts () {
		// add the Accept header to the other headers
		$this->headers[] = 'Accept: ' . $this->acceptType;
		
		// Timeout Curl execute after 10 seconds
		if ( !curl_setopt($this->ch, CURLOPT_TIMEOUT, 10) ) {
			return $this->return_handler->results(400,'CURLOPT_TIMEOUT could not be set.',null);
		}
		// Restful Request against this URL
		if ( !curl_setopt($this->ch, CURLOPT_URL, $this->url) ) {
			return $this->return_handler->results(400,'CURLOPT_URL could not be set.',null);
		}
		// Return the transfer as a string of the return value of curl_exec
		if ( !curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true) ) {
			return $this->return_handler->results(400,'CURLOPT_RETURNTRANSFER could not be set.',null);
		}
		// Set the header format to an array of strings.  The strings formatted as ($name . ": " . $value)
		if ( !curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers) ) {
			return $this->return_handler->results(400,'CURLOPT_HTTPHEADER could not be set.',null);
		}
		
		return $this->return_handler->results(200,'',null);
	}
	
	protected function formatResponse() {
		$response = new stdClass();
		$response->data = $this->responseBody;
		$response->info = $this->responseInfo;
		
		return $this->return_handler->results(200,'',$response);
	}
}