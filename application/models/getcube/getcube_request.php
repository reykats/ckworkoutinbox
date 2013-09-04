<?php

class getcube_request extends action_generic {
	
	private $x_cube_token = null;
	
	private $url_live = null;
	private $url_test = 'https://dev.getcube.com:65532/rest.svc/API/';
	
	private $url = null;
	
	private $content_type_header = 'Content-Type: application/json';
	
	private $client_user_id = null;
	
	private $user_master_id = null;
	private $company_id = null;
	private $location_id = null;
	
	public function __construct() {
		parent::__construct();
		
		// We do not want to autoload rest_request. (Its only used in the getcube request model)
		$this->load->model('rest/rest_request');
		
		// set the base URL for the api calls
		if ( $this->config->item('workoutinbox_test_server') ) {
			$this->url = $this->url_test;
		} else {
			$this->url = $this->url_live;
		}
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client user's getcube login history
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getUserLoginHistory( $p_client_user_id ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Get the User Master information from getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = "user_login?user_master_id=" . $this->user_master_id;
		$return = $this->perform('this->getcube_get',$api);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could get UserLoginHistory.',null);
		}
		$login_history = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$login_history);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client user's getcube login history for today
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getUserLoginsToday( $p_client_user_id ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Get the Client User's timezone
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getTimezoneForClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$timezone = $return['response']->timezone;
		// echo "$timezone<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// calculate the End of Yesterday in the Client User's timezone.
		// ------------------------------------------------------------------------------------------------------------------
		// Set the server's default timezone
		date_default_timezone_set($timezone);
		$now = time();
		$end_of_yesterday = date('Y-m-d\TH:i:s',mktime(0,0,-1,date('m',$now),date('d',$now), date('Y',$now)));
		// echo "yesterday:$end_of_yesterday<br >";
		// ------------------------------------------------------------------------------------------------------------------
		// Get the User Master information from getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = "user_login";
		$select = array();
		
		$temp = new stdClass();
		$temp->field = 'user_master_id';
		$temp->condition = 'equals';
		$temp->value = $this->user_master_id;
		array_push($select,$temp);
		unset($temp);
		
		$temp = new stdClass();
		$temp->field = 'created';
		$temp->condition = 'greater_than';
		$temp->value = $end_of_yesterday;
		array_push($select,$temp);
		unset($temp);
		
		$return = $this->perform('this->getcube_put',$api,$select);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could Not get UserLoginHistory.',$return['response']);
		}
		$login_history = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$login_history);
	}

	public function getTimezoneForClientUser( $p_client_user_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "if(u.timezone IS NOT NULL,u.timezone,if(cal.timezone IS NOT NULL,cal.timezone,s.timezone)) timezone ";
		$sql .= "FROM ";
		$sql .= "server s, ";
		$sql .= "client_user cu ";
		$sql .= "LEFT OUTER JOIN user u ";
		$sql .= "ON u.id = cu.user_id ";
		$sql .= "LEFT OUTER JOIN calendar cal ";
		$sql .= "ON cal.client_id = cu.client_id ";
		$sql .= "WHERE cu.id = " . $p_client_user_id . " ";
		$sql .= "ORDER BY cu.client_id, cal.timezone ";
		$sql .= "LIMIT 1 ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		
		$row = $query->row();
		
		return $this->return_handler->results(200,"",$row);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client user's getcube Payment Report
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getOrderReport( $p_client_user_id, $p_order_master_id ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Get the payment report from getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = "report/order_detail";
		$select = array();
		
		$temp = new stdClass();
		$temp->field = 'order_master_id';
		$temp->condition = 'equals';
		$temp->value = $p_order_master_id;
		array_push($select,clone $temp);
		unset($temp);
		
		$post_data = new stdClass();
		$post_data->email_address = null;
		$post_data->html = false;
		$post_data->raw_data = true;
		$post_data->csv = false;
		$post_data->sfa = $select;
		
		$return = $this->perform('this->getcube_put',$api,$post_data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could Not get payment report.',$return['response']);
		}
		$paymentReport = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$paymentReport);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client user's getcube Payment Report
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getPaymentReport( $p_client_user_id ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Get the payment report from getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = "report/payment";
		$select = array();
		
		if ( isset($_GET['user_master_id']) && is_numeric($_GET['user_master_id']) ) {
			$temp = new stdClass();
			$temp->field = 'user_master_id';
			$temp->condition = 'equals';
			$temp->value = $_GET['user_master_id'];
			array_push($select,clone $temp);
			unset($temp);
		}
		
		if ( isset($_GET['location_id']) && is_numeric($_GET['location_id']) ) {
			$temp = new stdClass();
			$temp->field = 'location_id';
			$temp->condition = 'equals';
			$temp->value = $_GET['location_id'];
			array_push($select,clone $temp);
			unset($temp);
		}
		
		if ( isset($_GET['customer_id']) && is_numeric($_GET['customer_id']) ) {
			$temp = new stdClass();
			$temp->field = 'customer_id';
			$temp->condition = 'equals';
			$temp->value = $_GET['customer_id'];
			array_push($select,clone $temp);
			unset($temp);
		}
		
		$post_data = new stdClass();
		$post_data->email_address = null;
		$post_data->html = false;
		$post_data->raw_data = true;
		$post_data->csv = false;
		$post_data->sfa = $select;
		
		// echo json_encode($post_data);
		
		$return = $this->perform('this->getcube_post',$api,$post_data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could Not get payment report.',$return['response']);
		}
		$paymentReport = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$paymentReport);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client user's visable getcube Customers
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getCustomers( $p_client_user_id ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Get the customer information from getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = "customer";
		$return = $this->perform('this->getcube_get',$api);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could get locations.',null);
		}
		$login_history = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$login_history);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client user's visable getcube Locations
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getLocations( $p_client_user_id ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Get the Location information from getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = "location";
		$return = $this->perform('this->getcube_get',$api);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could get locations.',null);
		}
		$login_history = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$login_history);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Create a new location
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function createLocation( $p_client_user_id, $data ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// echo json_encode($data);
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Create the New Location in getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = 'location';
		$return = $this->perform('this->getcube_post',$api,$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could Not Create Location.',$return['response']);
		}
		$login_history = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$login_history);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Update an existing location
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function updateLocation( $p_client_user_id, $data ) {
		// echo "getUserLoginHistory $p_client_user_id<br />";
		// echo json_encode($data);
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Update an existing Location in getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = 'location/' . $data->location_id;
		$return = $this->perform('this->getcube_put',$api,$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could Not Update Location.',$return['response']);
		}
		$login_history = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$login_history);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Delete a an existing location
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function deleteLocation( $p_client_user_id, $p_location_id ) {
		// echo "updateLocation $p_client_user_id $p_location_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Delete an existing Location in getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = 'location/' . $p_location_id;
		$return = $this->perform('this->getcube_delete',$api);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could Not Delete Location.',$return['response']);
		}
		$login_history = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$login_history);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client user's getcube user information
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getUserMaster( $p_client_user_id ) {
		// echo "getUserMaster $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// Get the User Master information from getcube
		// ------------------------------------------------------------------------------------------------------------------
		$api = "user_master/" . $this->user_master_id;
		$return = $this->perform('this->getcube_get',$api);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could get UserMaster.',null);
		}
		$user_master = $return['response']->data->data;
		
		return $this->return_handler->results(200,'',$user_master);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the getcube token detail information
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getTokenDetail( $p_client_user_id ) {
		// echo "getTokenDetail $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// Login (if needed) and get token detail
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUser',$p_client_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		$response = new stdClass();
		$response->user_master_id = $this->user_master_id;
		$response->location_id = $this->location_id;
		$response->company_id = $this->company_id;
		return $this->return_handler->results(200,'',$response);
	}
		
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Based on the Client User ID, get a valid GetCube token
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	public function getClientUser( $p_client_user_id ) {
		// echo "getClientUser $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// validate the client_user's getcube authentication token
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->validateToken',$p_client_user_id);
		// print_r($return);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// ------------------------------------------------------------------------------------------------------------------
		// validate the client_user's getcube authentication token
		// ------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getcube_get','token/detail');
		if ( $return['status'] >= 300 ) {
			return $retun;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'getcube User Information not found.',null);
		}
		// ------------------------------------------------------------------------------------------------------------------
		// store off the user's information
		// ------------------------------------------------------------------------------------------------------------------
		$this->company_id = $return['response']->data->data->company_id;
		$this->user_master_id = $return['response']->data->data->user_master_id;
		$this->location_id = $return['response']->data->data->location_id;
		
		return $this->return_handler->results(200,'',null);
	}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Get the client_user's getcube authentication token.  If the token is still valid, then use it, else get a new token.
	// -----------------------------------------------------------------------------------------------------------------------------------------

	public function validateToken( $p_client_user_id ) {
		// echo "validateToken $p_client_user_id<br />";
		// ------------------------------------------------------------------------------------------------------------------
		// store the client_user_id
		// ------------------------------------------------------------------------------------------------------------------
		$this->client_user_id = $p_client_user_id;
		// ------------------------------------------------------------------------------------------------------------------
		// get the getcube_user_master
		// ------------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['client_user_id'] = $p_client_user_id;
		$return = $this->perform('table_workoutdb_getcube_user_master->getForAndKeys',$key);
		// print_r($return);
		if ( $return['status'] > 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'getcube User not found.',null);
		}
		$getcube_user_master = $return['response'][0];
		// ------------------------------------------------------------------------------------------------------------------
		// Does client user have a email and passowrd?
		// ------------------------------------------------------------------------------------------------------------------
		if ( is_null($getcube_user_master->email) || empty($getcube_user_master->email) ) {
			return $this->return_handler->results(400,'getcube User not setup.',null);
		}
		if ( is_null($getcube_user_master->password) || empty($getcube_user_master->password) ) {
			return $this->return_handler->results(400,'getcube User not setup.(2)',null);
		}
		// ------------------------------------------------------------------------------------------------------------------
		// validate current token
		// ------------------------------------------------------------------------------------------------------------------
		$this->x_cube_token = $getcube_user_master->token;
		if ( !is_null($this->x_cube_token) && !empty($this->x_cube_token) ) {
			// echo "token:" . $this->x_cube_token . "<br />";
			$return = $this->perform('this->getcube_get','token/validate');
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $return['status'] == 200 ) {
				// store the token for future use
				$this->x_cube_token = $getcube_user_master->token;
				return $this->return_handler->results(200,'',null);
			}
		}
		// ------------------------------------------------------------------------------------------------------------------
		// login
		// ------------------------------------------------------------------------------------------------------------------
		// echo "email:" . $getcube_user_master->email . " pass:" . $getcube_user_master->password . "<br />";
		$return = $this->perform('this->getcube_get','login',false,$getcube_user_master->email,$getcube_user_master->password);
		// print_r($return);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'Could not login.',null);
		}
		// store the token for future use
		$this->x_cube_token = $return['response']->data->data;
		// ------------------------------------------------------------------------------------------------------------------
		// Save the token to the getcube user_master
		// ------------------------------------------------------------------------------------------------------------------
		$fields = new stdClass();
		$fields->id = $getcube_user_master->id;
		$fields->token = $this->x_cube_token;
		$return = $this->perform('table_workoutdb_getcube_user_master->update',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		return $this->return_handler->results(200,'',null);
	}

	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Make a RESTful GET request to a getcube api
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	protected function getcube_get( $p_api, $p_use_token=true, $p_email = null, $p_password = null ) {
		// translate the parameters into headers
		$headers = array();
		if ( !is_null($p_email) ) {
			$headers[] = "x-cube-email: " . $p_email;
			$headers[] = "x-cube-password: " . $p_password;
		} else if ( $p_use_token ) {
			$headers[] = "x-cube-token: " . $this->x_cube_token;
		}
		
		$headers[] = $this->content_type_header;  // Return the data as a json string.
		
		$url = $this->url . $p_api;
		
		$return = $this->perform('rest_request->get',$url,$headers);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$return['response']->data = json_decode($return['response']->data);  // Convert the data from a json string to an object.
		
		// var_dump($return);
		
		// Error handling
		if ( !$return['response']->data->success ) {
			return $this->return_handler->results(201,"Not Successfull",$return['response']);
		}
		
		return $this->return_handler->results(200,"Successfull",$return['response']);
	}

	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Make a RESTful POST request to a getcube api
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	protected function getcube_post( $p_api, $p_request_body, $p_use_token=true ) {
		// validate the request body
		if ( !is_array($p_request_body) && !is_object($p_request_body) ) {
			return $this->return_handler->results(400,"The request body is invalid",$p_request_body);
		}
		
		// translate the parameters into headers
		$headers = array();
		if ( $p_use_token ) {
			$headers[] = "x-cube-token: " . $this->x_cube_token;
		}
		
		$headers[] = $this->content_type_header;  // Return the data as a json string.
		
		$url = $this->url . $p_api;
		
		$return = $this->perform('rest_request->post',$url,$p_request_body,$headers);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$return['response']->data = json_decode($return['response']->data);  // Convert the data from a json string to an object.
		
		// var_dump($return);
		
		// Error handling
		if ( !$return['response']->data->success ) {
			return $this->return_handler->results(201,"Not Successfull",$return['response']->data->data);
		}
		
		return $this->return_handler->results(200,"Successfull",$return['response']);
	}

	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Make a RESTful PUT request to a getcube api
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	protected function getcube_put( $p_api, $p_request_body, $p_use_token=true ) {
		// validate the request body
		if ( !is_array($p_request_body) && !is_object($p_request_body) ) {
			return $this->return_handler->results(400,"The request body is invalid",$p_request_body);
		}
		
		// translate the parameters into headers
		$headers = array();
		if ( $p_use_token ) {
			$headers[] = "x-cube-token: " . $this->x_cube_token;
		}
		
		$headers[] = $this->content_type_header;  // Return the data as a json string.
		
		$url = $this->url . $p_api;
		
		$return = $this->perform('rest_request->put',$url,$p_request_body,$headers);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$return['response']->data = json_decode($return['response']->data);  // Convert the data from a json string to an object.
		
		// var_dump($return);
		
		// Error handling
		if ( !$return['response']->data->success ) {
			return $this->return_handler->results(201,"Not Successfull",$return['response']->data->data);
		}
		
		return $this->return_handler->results(200,"Successfull",$return['response']);
	}

	// -----------------------------------------------------------------------------------------------------------------------------------------
	// Make a RESTful PUT request to a getcube api
	// -----------------------------------------------------------------------------------------------------------------------------------------
	
	protected function getcube_delete( $p_api, $p_use_token=true ) {
		// translate the parameters into headers
		$headers = array();
		if ( $p_use_token ) {
			$headers[] = "x-cube-token: " . $this->x_cube_token;
		}
		
		$headers[] = $this->content_type_header;  // Return the data as a json string.
		
		$url = $this->url . $p_api;
		
		$return = $this->perform('rest_request->delete',$url,$headers);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$return['response']->data = json_decode($return['response']->data);  // Convert the data from a json string to an object.
		
		// var_dump($return);
		
		// Error handling
		if ( !$return['response']->data->success ) {
			return $this->return_handler->results(201,"Not Successfull",$return['response']->data->data);
		}
		
		return $this->return_handler->results(200,"Successfull",$return['response']);
	}
	
}	