<?php

class provisional_user_signup extends generic_api {

	public function __construct() {
		$this->database_name = "bhdb";
		$this->table_name = 'provisional_user';

		parent::__construct();

		// load the access helpers
		$this->load->helper('access');
	}

	public function get($params = array()) {
		$params = (array) $params;

		if ( count($params) == 1 ) {
			if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) ) {
				// ------------------------------------------------------------------
				// getProvisionalUserById
				// ------------------------------------------------------------------
				return $this->getProvisionalUserById($params[0]);
			} else {
				return $this->return_handler->results(400,"invalid URL parameter list",new stdClass());
			}
		} else if ( count($params) == 2 ) {
			if ( !is_null($params[0]) && !empty($params[0]) && is_numeric($params[0]) &&
			     !is_null($params[1]) && !empty($params[1]) ) {
				// ------------------------------------------------------------------
				// getProvisionalUserByIdToken
				// ------------------------------------------------------------------
				return $this->getProvisionalUserByIdToken($params[0],$params[1]);
			} else {
				return $this->return_handler->results(400,"invalid URL parameter list",new stdClass());
			}
		} else {
			return $this->return_handler->results(400,"invalid URL parameter list",new stdClass());
		}
	}

	public function getProvisionalUserById( $p_id ) {
		$sql  = "SELECT * ";
		$sql .= "FROM provisional_user ";
		$sql .= "WHERE id = " . $p_id . " ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() > 0 ) {
			$row = $query->row();

			$entry = new stdClass();
			$entry->id = (int) $row->id;
			$entry->user = new stdClass();
			$entry->user->first_name = $row->first_name;
			$entry->user->last_name = $row->last_name;
			$entry->user->phone = $row->phone;
			$entry->user->email = $row->email;
			$entry->company = new stdClass();
			$entry->company->name = $row->company_name;
			$entry->company->phone = $row->company_phone;
			$entry->company->email = $row->company_email;
			$entry->company->address = $row->company_address;
			$entry->created = (int) $row->created;
			$entry->expire = (int) $row->expire;
			$entry->token = $row->token;

			// print_r($response); echo "<br />";
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(400,"No Entry Found",new stdClass());
		}
	}

	public function getProvisionalUserByIdToken( $p_id,$p_token ) {
		$sql  = "SELECT * ";
		$sql .= "FROM provisional_user ";
		$sql .= "WHERE id = " . $p_id . " ";
		$sql .= "AND token = '" . mysql_real_escape_string($p_token) . "' ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() > 0 ) {
			$row = $query->row();

			if ( $row->expire > time() ) {
				$entry = new stdClass();
				$entry->id = (int) $row->id;
				$entry->user = new stdClass();
				$entry->user->first_name = $row->first_name;
				$entry->user->last_name = $row->last_name;
				$entry->user->phone = $row->phone;
				$entry->user->email = $row->email;
				$entry->company = new stdClass();
				$entry->company->name = $row->company_name;
				$entry->company->phone = $row->company_phone;
				$entry->company->email = $row->company_email;
				$entry->company->address = $row->company_address;
				$entry->created = (int) $row->created;
				$entry->expire = (int) $row->expire;
				$entry->token = $row->token;

				// print_r($response); echo "<br />";
				return $this->return_handler->results(200,"",$entry);
			} else {
				return $this->return_handler->results(400,"This user has expired",new stdClass());
			}
		} else {
			return $this->return_handler->results(400,"No Entry Found",new stdClass());
		}
	}

	public function post( $params = array(), $data ) {
		$params = (array) $params;

		if ( count($params) == 0 ) {
			return $this->post_provisional_user($data);
		} else {
			return $this->return_handler->results(400,"Invalid URL parameter list",new stdClass());
		}
	}

	public function post_provisional_user($data) {
		//echo "data:"; print_r($data); echo "<br />";

		// translate the post data field names into the database table column names
		$this->load->library('translate',array($this->database_name,$this->table_name));
		$columns = $this->translate->fields_to_columns($data);
		//echo "columns:"; print_r($columns); echo "<br />";

		// create a temp password, a token, and an expire date for the provisional user
		$this->generateAccessColumns($columns);
		//echo "columns:"; print_r($columns); echo "<br />";

		// do the manditory fields exist and are they set?
		$return = $this->validate_post_data($columns);
		if ( $return['status'] != 200 ) {
			return $return;
		}

		// does this email already have a user?
		$keys = array('email' => $columns['email']);
		$return = $this->mysql->get_and('user',$keys,'id');
		if ( $return['status'] == 200 ) {
			return $this->return_handler->results(400,"This email already has a login.",new stdClass());
		}

		// does this company already exist?
		$keys = array('name' => $columns['company_name']);
		$return = $this->mysql->get_and('company',$keys,'id');
		if ( $return['status'] == 200 ) {
			return $this->return_handler->results(404,"This company already exists.",new stdClass());
		}

		// create the provisional user
		$return = $this->create_provisional_user($columns);
		unset($temp);
		if ( $return['status'] >= 300 ) {
			return $this->return_handler->results(400,"The provisional user could not be created",new stdClass());
		}
		$provisional_user_id = $return['response']->id;

		// send email notification of provisional user being created
		$this->load->model('email/email');
		$return = $this->email->sendProvisionalUserInfo($provisional_user_id,$columns['password']);
		if ( $return['status'] >= 300 ) {
			return $return;
			return $this->return_handler->results(400,"The provisional user info email could not be sent",new stdClass());
		}

		$response = new stdClass();
		$response->id = $provisional_user_id;
		return $this->return_handler->results(201,"Entry saved",$response);
	}

	public function generateAccessColumns(&$columns) {
		// create a temp password, a token, and an expire date for the provisional user
		$columns['password'] = call_user_func('generate_password',8);
		$columns['token'] = call_user_func('generate_token',10);
		$columns['expire'] = call_user_func('generate_expire_date');
	}

	public function validate_post_data($data) {
		if ( !is_array($data) ) {
			return $this->return_handler->results(400,"The provisional user post data must be an arrray",new stdClass());
		}
		// convert the $data into the table's object
		$this->load->library('mysql_table');
		$this->mysql_table->initialize($this->database_name,$this->table_name,$data);
		// make sure all manditory fields have been set
		if ( !$this->mysql_table->manditory_columns_set() ) {
			return $this->return_handler->results(400,"A manditory field is missing a value.",new stdClass());
		}

		return $this->return_handler->results(200,'',new stdClass());
	}

	public function create_provisional_user($data) {
		$columns = $data;
		$columns['password'] = md5($columns['password']);
		$return = $this->mysql->post_fields($this->table_name,$columns);
		unset($columns);
		return $return;
	}

	public function put( $params = array(), $data ) {
		$params = (array) $params;

		if ( count($params) == 1 ) {
			if ( !is_null($params[0]) && !empty($params[0]) && $params[0] == 'activate' ) {
				return $this->activate_buyer_user($data);
			} else {
				return $this->return_handler->results(400,"Invalid URL parameter list",new stdClass());
			}
		} else {
			return $this->return_handler->results(400,"Invalid URL parameter list",new stdClass());
		}
	}

	public function activate_buyer_user( $data = array() ) {
		// have all the needed field been pass in?
		if ( !isset($data->id) || is_null($data->id) || empty($data->id) || !is_numeric($data->id) ||
		     !isset($data->password) || is_null($data->password) || empty($data->password) ||
		     !isset($data->new_password) || is_null($data->new_password) || empty($data->new_password) ) {
			return $this->return_handler->results(400,"A Manditory field is missing",new stdClass());
		}
		// get the provisional user
		$return = $this->mysql->findOne('provisional_user',$data->id);
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"invalid id",new stdClass());
		}
		$provisional_user = $return['response'];
		// echo "provisional_user:"; print_r($provisional_user); echo "<br />";
		// does the password match?
		if ( $data->password != $provisional_user->password ) {
			return $this->return_handler->results(400,"invalid password",new stdClass());
		}
		// does this email already have a user?
		$keys = array('email' => $provisional_user->email);
		$return = $this->mysql->get_and('user',$keys,'id');
		if ( $return['status'] == 200 ) {
			return $this->return_handler->results(400,"This email already has a login.",new stdClass());
		}
		// does this company already exist?
		$keys = array('name' => $provisional_user->company_name);
		$return = $this->mysql->get_and('company',$keys,'id');
		if ( $return['status'] == 200 ) {
			return $this->return_handler->results(400,"This company already exists.",new stdClass());
		}
		// Create user from provisional user
		$return = $this->createUser($provisional_user,$data->new_password);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$user_id = $return['response']->id;
		// echo "user_id:$user_id -- ";
		// Create company from provisional user's company
		$return = $this->createCompany($provisional_user);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$company_id = $return['response']->id;
		//echo "company_id:$company_id -- ";
		// Create buyer
		$return = $this->createBuyer($user_id,$company_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$buyer_id = $return['response']->id;
		//echo "buyer_id:$buyer_id -- ";

		// ----------------------------------------------------------------------------------------------------
		// log the user in
		// ----------------------------------------------------------------------------------------------------
		return $this->log_user_in($provisional_user->email,$data->new_password);
	}

	public function createUser( $data,$p_password ) {
		// create the entry object
		$entry = new stdClass();
		$entry->email = $data->email;
		$entry->first_name = $data->first_name;
		$entry->last_name = $data->last_name;
		$entry->phone = $data->phone;
		$entry->password = $p_password;
		//echo "entry:"; print_r($entry); echo "<br />";
		// post the entry
		$this->load->model("api/user_api");
		$return = $this->user_api->post_user($entry);
		unset($entry);
		return $return;
	}

	public function createCompany( $data ) {
		// create the entry object
		$entry = new stdClass();
		$entry->name = $data->company_name;
		$entry->phone = $data->company_phone;
		$entry->email = $data->company_email;
		$entry->address = $data->company_address;
		//echo "entry:"; print_r($entry); echo "<br />";
		// post the entry
		$this->load->model("api/client_api");
		$return = $this->company_api->post_client($entry);
		unset($entry);
		return $return;
	}

	public function createClientUser( $p_user_id,$p_company_id ) {
		// create the entry object
		$entry = new stdClass();
		$entry->company_id = $p_company_id;
		$entry->user_id = $p_user_id;
		// echo "entry:"; print_r($entry); echo "<br />";
		// post the entry
		$this->load->model("api/buyer_api");
		$return = $this->buyer_api->post_buyer($entry);
		unset($entry);
		return $return;
	}

	public function log_user_in($p_email,$p_password) {
		// create the login object
		$entry = new stdClass();
		$entry->email = $p_email;
		$entry->password = $p_password;
		//echo 'entry:'; print_r($entry); echo "<br />";
		// post the entry
		$this->load->model('api/login_api');
		$return = $this->login_api->post_login($entry);
		unset($entry);
		return $return;
	}

	public function delete( $params = array() ) {
		$params = (array) $params;

		return $this->return_handler->results(400,"You can not DELETE to this API",new stdClass());
	}
}
?>