<?php

class common_perform extends CI_Model {
	
	// ===============================================================================================================================
	// Passing a model of "this->" only works if common_perform is inherited by the child class
	// ===============================================================================================================================

	public function __construct() {
		// echo "-a_common_perform-";
		parent::__construct();
		// echo "-b_common_perform-";
	}

	public function perform() {
		$arguments = func_get_args();

		$model_method = array_shift($arguments);
		$parts = explode('->',$model_method);
		$model = array_shift($parts);
		$method = array_shift($parts);
		$data_type = array_shift($parts);
		
		// echo "perform model:$model method:$method<br />";
		
		$model_name_parts = explode('_',$model);
		$model_type = array_shift($model_name_parts);

		// echo "model_location:$model_location model:$model method:$method<br />";
		// echo "arguments:"; print_r($arguments); echo "<br />";

		if ( $model_type == 'table' ) {
			//
			// Split the model name passed in into type/database/table (model = table_{database}_{table_name})
			$database_name = array_shift($model_name_parts);
			$table_name = implode('_',$model_name_parts);
			//
			// is the database/table in the mysql schema?
			$return = mysql_schema::validTable($database_name,$table_name);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			//
			// store the model location (used to load the model)
			$model_location = $model_type . '/' . $model;
			//
			// does the database/table have a table model file?
			$file_location = APPPATH . 'models/' . $model_location . '.php';
			if ( !file_exists($file_location) ) {
				//
				// if the table model file does not exist, use the default table model and initialize it to the database
				$this->load->model('table/table_default',$model);
				$return = $this->{$model}->initializeTableDefault($database_name,$table_name);
				if ( $return['status'] > 200 ) {
					return $return;
				}
			} else {
				//
				// if the table model file does exist, load the model
				$this->load->model($model_location);
			}
			//
			// Is the model's method callable?
			if ( !is_callable(array($this->{$model},$method),false,$callable_name) ) {
				return $this->return_handler->results(500,$model . "->" . $method . " is an invalid method call.",new stdClass() );
			}
			//
			// call the model's method
			if ( $data_type == "array" ) {
				return call_user_func_array(array($this->{$model},$method), $arguments[0]);
			}
			return call_user_func_array(array($this->{$model},$method), $arguments);
		} else if ( $model_type == 'this' ) {
			//
			// Is this method callable?
			if ( !is_callable(array($this,$method),false,$callable_name) ) {
				return $this->return_handler->results(500,"this->" . $method . " is an invalid method call.",new stdClass() );
			}
			//
			// call the method
			if ( $data_type == "array" ) {
				return call_user_func_array(array($this,$method), $arguments[0]);
			}
			return call_user_func_array(array($this,$method), $arguments);
		} else {
			// echo "load model $model_location$model<br />";
			//
			// store the model location (used to load the model)
			$model_location = $model_type . '/' . $model;
			$model_filename = APPPATH . 'models/' . $model_location . '.php';
			//
			// does the database/table have a table model file?
			if ( !file_exists($model_filename) ) {
				return $this->return_handler->results(500,$model . " is an invald model.",new stdClass() );
			}
			//
			// Create an instance of the requested model
			$this->load->model($model_location);
			//
			// Is the model's method callable?
			if ( !is_callable(array($this->{$model},$method),false,$callable_name) ) {
				return $this->return_handler->results(500,$model . "->" . $method . " is an invalid method call.",new stdClass() );
			}
			//
			// call the model's method
			if ( $data_type == "array" ) {
				return call_user_func_array(array($this->{$model},$method), $arguments[0]);
			}
			return call_user_func_array(array($this->{$model},$method), $arguments);
		}
	}

	function get_data() {
		// if the Request Method is not POST or PUT, return 200 (OK) and an empty object
		if ( $this->request_method != 'POST' && $this->request_method != 'PUT' ) {
			return $this->return_handler->results(200,"",new stdClass());
		}
		
		// Get the POST/PUT data and decode it from json to an array or object
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		
	    // switch and check possible JSON errors
	    switch (json_last_error()) {
	        case JSON_ERROR_NONE:
	            $error = ''; // JSON is valid
	            break;
	        case JSON_ERROR_DEPTH:
	            $error = 'Maximum stack depth exceeded.';
	            break;
	        case JSON_ERROR_STATE_MISMATCH:
	            $error = 'Underflow or the modes mismatch.';
	            break;
	        case JSON_ERROR_CTRL_CHAR:
	            $error = 'Unexpected control character found.';
	            break;
	        case JSON_ERROR_SYNTAX:
	            $error = 'Syntax error, malformed JSON.';
	            break;
	        // only PHP 5.3+
	        case JSON_ERROR_UTF8:
	            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
	            break;
	        default:
	            $error = 'Unknown JSON error occured.';
	            break;
	    }
	
	    if($error !== '') {
	    	return $this->return_handler->results(400,"Invalid JSON Data: " . $error,file_get_contents("php://input"));
	    }
	
	    // everything is OK
	    return $this->return_handler->results(200,"",$data);
	}

	public function SessionIsLoggedInForUser($p_user_id) {
		// get the user's session data
		$user = $this->session->userdata('user');
		// validate the session's User ID with the User ID passed in.
		if ( is_object($user) && property_exists($user,'id') && is_numeric($user->id) && $user->id == $p_user_id ) {
			return true;
		}
		
		return false;
	}

	public function SessionIsStaffForClient($p_client_id) {
		// get the user's session data
		$user = $this->session->userdata('user');
		// validate that the user is a staff member for the client passed in.
		if ( is_object($user) && property_exists($user,'client') && is_arrau($user->client) ) {
			foreach( $user->client as $client ) {
				if ( $client->id == $p_client_id ) {
					if ( $client->role_name = "Staff" ) {
						return true;
					}
					break;
				}
			}
		}
		
		return false;
	}
}