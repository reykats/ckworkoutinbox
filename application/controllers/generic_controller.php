<?php

class generic_controller extends CI_Controller {
	
	// url elements
	protected $url_controller = '';
	protected $url_version = '';
	protected $url_model = '';
	protected $url_action = '';
	
	// backend code base version
	protected $app_version = '';
	
	public function __construct() {
		parent::__construct();
		
		// get the url information
		$this->url_controller = $this->uri->segment(1);
		$this->app_version = $this->config->item('workoutinbox_backend_' . $this->url_controller . '_version');
		if ( empty($this->app_version) ) {
			$this->url_version = '';
			$this->url_model = $this->uri->segment(2);
			$this->url_action = $this->uri->segment(3);
		} else {
			$this->url_version = $this->uri->segment(2);
			$this->url_model = $this->uri->segment(3);
			$this->url_action = $this->uri->segment(4);
		}
	}
	
	public function echoReturn($p_return) {
		if ( $this->url_action != 'view_image' ) {
			if ( isset($_SERVER['SERVER_NAME']) ) {
				// set the return content type header to "text/html"
				header('Content-type: application/json');
			}
			// echo the returned results
			echo json_encode($p_return);
		}
	}
	
	public function testDownForMaintenance() {
		// ==================================================================
		// Down For Maintenance
		// ==================================================================
		if ( $this->config->item('down_for_maint') ) {
			return $this->return_handler->results($this->config->item('down_for_maint_status'),$this->config->item('down_for_maint_message'),new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	public function testVersion() {
		// ==================================================================
		// Is the frontend the same version as the backend
		// ==================================================================
		// if the config file has a version for this application and it does not match the URL's imbedded version, abort!
		if ( !empty($this->app_version) && $this->app_version != $this->url_version ) {
			return $this->return_handler->results($this->config->item('invalid_version_status'),$this->config->item('invalid_version_message'),new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	public function testLoggedIn() {
		// ==================================================================
		// Is session logged in?
		// ==================================================================
		if ( $this->url_model != 'login' && $this->session->userdata('login_state') != true ) {
			return $this->return_handler->results(401,"Session Expired",new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	public function testLoggedInApplication() {
		// ==================================================================
		// Is session logged into another application?
		// ==================================================================
		// let through model = 'login' and request method != 'GET'
		if ( $this->url_model != 'login' || 
		     ($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'PUT' && $_SERVER['REQUEST_METHOD'] != 'DELETE') ) {
		    // get the logged in user's session's user information (captured at login)
			$user = $this->session->userdata('user');
			if ( $this->session->userdata('login_state') == true && ( !property_exists($user,'application') || $user->application != $this->url_controller) ) {
				return $this->return_handler->results(401,"You are not logged into the correct application.",new stdClass());
			}
		}
		return $this->return_handler->results(200,"",new stdClass());
	}
}