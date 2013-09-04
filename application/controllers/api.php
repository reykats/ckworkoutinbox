<?php

// =============================================================================================================================================================================
// 
// API call rules:
// 
//   *) The request method + the URL before the "?" MUST be Unque.
//   *) The 1st numeric parameter in the URL starts the name/value pairs (numeric param position minus 1).
//   *) Once name/value pairs start in the URL there are just name/value pairs.
//   *) name/value pairs in the URL are used to pass mandatory data for GET and DELETE APIs
//   *) "php://input" data is used to pass mandatory data for POST and PUT APIs
//   *) $_GET is used to pass optional data to GET APIs
//
// =============================================================================================================================================================================

class api extends CI_Controller {
	// Get the User's Session data
	protected $user = null;

	// Based on the server/host, is this a test
	protected $test_mode = true;
	
	// The Server/Host name - alpha, beta, demo, prod
	protected $server = null;
	
	// URI segments
	protected $controller = null;
	protected $version = null;
	protected $application = null;
	protected $params = array();  // The URL $params array after the application.
	
	// GET, POST, PUT, or DELETE
	protected $request_method = null;
	
	// The _GET and POST data
	protected $data = null;
	
	// The total list of valid APIs
	protected $api_list = array();
	
	// The unique api call key
	protected $api_call = null;
	// The api variable list - a list of _GET and POST data variables and their values.
	protected $api_var = null;
	
	// A link to the Current API
	protected $api = null;

	public function __construct() {
		parent::__construct();
		
		// load the common_perform model	
		$this->load->model("common/common_perform");
		
		// Store off the User's Session data
		$this->user = $this->session->userdata('user');

		// Setup class variables test_mode and server based on whether you are running on the production system or a development system.
		if ( isset($_SERVER['SERVER_NAME']) ) {
			$host = $_SERVER['SERVER_NAME'];
			$name = explode('.', $host);
			if ( $host == "workoutinbox.com" || $host == "www.workoutinbox.com" ) {
				// production
				$this->test_mode = false;
				$this->server = 'prod';
			} else {
				$name = explode('.',$host);
				if ( $name[1] == "workoutinbox" && $name[2] == "com" ) {
					// known development
					$this->test_mode = true;
					$this->server = $name[0];
				}
			}
		} else {
			$host = php_uname('n');
			if ( $host == 'workoutinbox.com' ) {
				// production
				$this->test_mode = false;
				$this->server = 'prod';
			} else {
				// known development
				$this->test_mode = true;
				$this->server = $host;
			}
		}
		// echo "test_mode:" . $this->test_mode . " server:" . $this->server . "<br />\n";
		
		// Parse and store the URI segments
		$segment = $this->uri->segment_array();
		
		$this->controller = array_shift($segment);
		$this->version = array_shift($segment);
		$this->application = array_shift($segment);
		
		$this->params = $segment;
		
		// Get and store the request method
		$this->request_method = $_SERVER['REQUEST_METHOD'];
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------------
		// Do not get data (php_input or $_GET), if there is an error there is no way to return error.
		// -----------------------------------------------------------------------------------------------------------------------------------------------------
	}

	public function _remap($p_params1, $p_params = array()) {
		// echo "api.php p_params1: $p_params1 " . json_encode($p_params) . "<br />\n";
		// echo "server: " . $this->server . "<br />\n";

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
		
		$return = $this->process($this->params);
		
		if ( $this->request_method == "PUT" || $this->request_method == "POST" ) {
			// log the api call
			$this->load->model('log/log_api');
			$this->log_api->put($return);
		}
		
		// echo the returned results
		$this->echoReturn($return);
		
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
		if ( !is_null($this->version) && !empty($this->version) && $this->version != $this->config->item('workoutinbox_backend_version') ) {
			return $this->return_handler->results($this->config->item('invalid_version_status'),$this->config->item('invalid_version_message'),new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function process($params) {
		// echo "DecodeParams-";
		// Get the api_call and api_var from the params
		$return = $this->decode_params();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// echo "GetAPIList-";
		// Get the list of valied APIs
		$return = $this->get_api_list();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// echo "DecodeParams-";
		// Get the API list entry for the API call
		$return = $this->find_api();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// You are done with api_list, so remove it from memory
		unset($this->api_list);
		
		if ( $this->request_method == "POST" || $this->request_method == "PUT" ) {
			
			// Get the POST/PUT data
			$return = $this->get_data();
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			// Store the name/value pairs of the mandatory data
			$return = $this->get_mandatory_data();
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		
		// echo "Validate-";
		// Perform the API validate performs
		$return = $this->validate();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// echo "Perform-";
		// Perform the API
		$return = $this->perform_api();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		return $return;
	}

	public function decode_params() {
		// Initialize api_call (api call unique key) and api_var (api variable list)
		$this->api_call = $this->request_method;
		$this->api_var = new stdClass();
		// Has the key/value pair section of the params strated?
		$key_value_found = false;
		$i = 0;
		$params_length = count($this->params);
		while ( $i < $params_length ) {
			$next = $i + 1;
			if ( !$key_value_found && $next < $params_length && is_numeric($this->params[$next]) ) {
				// The key/value pair section has started.
				$key_value_found = true;
			}
			$this->api_call .= "/" . $this->params[$i];
			if ( $key_value_found ) {
				$this->api_var->{$this->params[$i]} = $this->params[$next];
				$this->api_call .= "/{}";
				$i++;
			}
			$i++;
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}

	public function get_api_list() {
		// Get the list of valid API calls for the system
		$input = file_get_contents("../private/application/config/schema/api.json");
		if ( $input == false ) {
			return $this->return_handler->results(400,"Could not load Valid API List.",new stdClass());
		}
		$this->api_list = json_decode($input);
		
		// check for json_decode error
		$return = $this->check_json_error();
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"get API list : " . $return['message'], $return['response']);
		}
		return $return;
	}
	
	public function find_api() {
		// find the api call (unique key) in the valid api call list
		foreach( $this->api_list as $current_api ) {
			if ( $this->api_call == $current_api->api_call ) {
				// Found.
				$this->api = clone $current_api;
				return $this->return_handler->results(200,"",new stdClass());
			}
		}
		// Not found.
		return $this->return_handler->results(400,"Not a valid API Call",$this->api_call);
	}

	function get_data() {
		// Get the POST/PUT data and decode it from json to an array or object
		//
		$input = file_get_contents("php://input");
		
		if ( $input == false ) {
			return $this->return_handler->results(400,"Could not load php://input.",new stdClass());
		}
		
		$this->data = json_decode($input);
		
		// check for json_decode error
		return $this->check_json_error();
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"get php://input : " . $return['message'], $return['response']);
		}
		return $return;
	}
	
	protected function get_mandatory_data() {
		// Add the mandatory field/values in data to api_var
		//
		foreach( $this->api->mandatory_data as $field ) {
			if ( property_exists($this->data,$field) ) {
				$this->api_var->{$field} = $this->data->{$field};
			} else {
				return $this->return_handler->results(400,$field . " NOT passed in POST/PUT data.",new stdClass());
			}
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function validate() {
		// Perform the list of validation methods
		//
		foreach( $this->api->validate as $validate ) {
			$params = explode("/",$validate);
			// Get the method to perform
			$perform = array_shift($params);
			// Convert the list of variable names into a list of values
			$param_values = array();
			foreach( $params as $param ) {
				if ( !array_key_exists($param,$this->api_var) ) {
					return $this->return_handler->results(400,$perform . " requires " . $param,new stdClass());
				}
				$param_values[] = $this->api_var->$param;
			}
			if ( !is_callable(array($this,$perform),false,$callable_name) ) {
				return $this->return_handler->results(500,"this->" . $perform . " is an invalid method call.",new stdClass() );
			}
			// perform the validation method with the values
			$return = $this->{$perform}($param_values);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function perform_api() {
		// echo "api.php perform_api\n";
		// Perform the API
		//
		$perform_params = explode("/",$this->api->perform);
		// get the model->method to perform
		$perform = array_shift($perform_params);
		// Convert the list of variable names into a list of values
		$param_values = array();
		foreach( $perform_params as $param ) {
			if ( !array_key_exists($param,$this->api_var) ) {
				$temp = explode('=',$param);
				if ( count($temp) != 2 ) {
					return $this->return_handler->results(400,$perform . " requires " . $param,new stdClass());
				}
				$param_values[] = $temp[1];
			} else {
				$param_values[] = $this->api_var->{$param};
			}
		}
		// echo "perform:" . $perform . "<br />\n";
		// var_dump($param_values); echo "<br />\n";
	
		if ( $this->request_method == "POST" || $this->request_method == "PUT" ) {
			// perform the model->method with the data
			$return = $this->common_perform->perform($perform,$this->data);
		} else {
			// perform the model->method with the values
			$return = $this->common_perform->perform($perform . '->array',$param_values);
		}
		
		return $return;
	}
	
	protected function SessionIsLoggedIn() {
		// Is the User logged in?
		if ( $this->session->userdata('login_state') != true ) {
			return $this->return_handler->results(401,"Use are not logged  in.",new stdClass());
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function SessionUserIsSupport( $params ) {
		// Is the logged in user support@workoutinbox.com
		if ( $this->user->id != 403 ) {
			return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function SessionUserIsUserOrStaffOfUser ( $params ) {
		$return = $this->SessionUserIsUser($params);
		if ( $return['status'] > 200 ) {
			$return = $this->SessionUserIsStaffOfUser($params);
		}
		return $return;
	}
	
	protected function SessionUserIsUser( $params ) {
		// Get the user from the parameters passed in
		$user_id = array_shift($params);

		// Is the logged in user the user of the data to be looked up?
		if ( $this->user->id != $user_id ) {
			return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function SessionUserIsUserOfFacebook( $params ) {
		// Get the facebook id from the parameters passed in
		$facebook_id = array_shift($params);
		
		$return = $this->common_perform->perform('action_user->getForFacebookID',$facebook_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function SessionUserIsStaffOfClient( $params ) {
		// Get the Client_id from the parameters passed in
		$client_id = array_shift($params);
		
		$ok = false;
		foreach( $this->user->client as $session_client ) {
			// Is the session user staff for this client?
			// Does the data's user have access to this client?
			if ( $session_client->client_user->client_user_role->id == 1 && $session_client->id == $client_id ) {
				$ok = true;
				break;
			}
		}
		
		if ( !$ok ) {
			return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
		}
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function SessionUserIsStaffOfCalendar( $params ) {
		// Get the Client_id from the parameters passed in
		$calendar_id = array_shift($params);
		
		$ok = false;
		foreach( $this->user->client as $session_client ) {
			// Is the session user staff for this client?
			if ( $session_client->client_user->client_user_role->id == 1 ) {
				foreach( $session_client->location as $session_location ) {
					if ( $session_location->calendar->id == $calendar_id ) {
						return $this->return_handler->results(200,"",new stdClass());
					}
				}
			}
		}
		
		return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
	}
	
	protected function SessionUserIsStaffOfUser( $params ) {
		// Get the user_id from the parameters passed in
		$user_id = array_shift($params);
		
		// Get the clients the user can access and with what role
		$return = $this->common_perform->perform('action_user->getClientUserRolesForUser',$user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"This is not an active User.",new stdClass());
		}
		$clients = $return['response'];
		
		$ok = false;
		foreach( $this->user->client as $session_client ) {
			// Is the session user staff for this client?
			// Does the data's user have access to this client?
			if ( $session_client->client_user->client_user_role->id == 1 && key_exists($session_client->id,$clients) ) {
				return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
			}
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	protected function SessionUserIsStaffOfCalendarEntry( $params ) {
		// Get the calendar_entry_id from the parameters passed in
		$calendar_entry_id = array_shift($params);
		
		$return = $this->common_perform->perform('action_calendar->getForCalendarEntry',$calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
		}
		
		$calendar = clone $return['response'];
		
		$ok = false;
		foreach( $this->user->client as $client ) {
			if ( $client->id == $calendar->client_id ) {
				if ( $client->client_user->client_user_role->id == 1 ) {
					return $this->return_handler->results(400,"You do not have access to this data",new stdClass());
				}
			}
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	public function check_json_error() {
	    // switch and check possible JSON errors
	    switch (json_last_error()) {
	        case JSON_ERROR_NONE:
				return $this->return_handler->results(200,"",new stdClass());
	            break;
	        case JSON_ERROR_DEPTH:
				return $this->return_handler->results(400,"Maximum stack depth exceeded.",new stdClass());
	            break;
	        case JSON_ERROR_STATE_MISMATCH:
				return $this->return_handler->results(400,"Underflow or the modes mismatch.",new stdClass());
	            break;
	        case JSON_ERROR_CTRL_CHAR:
				return $this->return_handler->results(400,"Unexpected control character found.",new stdClass());
	            break;
	        case JSON_ERROR_SYNTAX:
				return $this->return_handler->results(400,"Syntax error, malformed JSON.",new stdClass());
	            break;
	        // only PHP 5.3+
	        case JSON_ERROR_UTF8:
				return $this->return_handler->results(400,"Malformed UTF-8 characters, possibly incorrectly encoded.",new stdClass());
	            break;
	        default:
				return $this->return_handler->results(400,"Unknown JSON error occured.",new stdClass());
	            break;
	    }
	}
	
	public function echoReturn($p_return) {
		// view_image has already displayed the image.
		if ( $p_return['status'] > 200 || !isset($this->params[1]) || $this->params[1] != 'view_image' ) {
			if ( isset($_SERVER['SERVER_NAME']) ) {
				// set the return content type header to "application/json"
				header('Content-type: application/json');
			}
			// echo the returned results
			echo json_encode($p_return);
		}
	}
}