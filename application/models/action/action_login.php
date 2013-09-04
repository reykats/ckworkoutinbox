<?php

class action_login extends action_generic {
	protected $application;

	public function __construct() {
		parent::__construct();
		
		$this->application = $this->uri->segment(3);
	}

	// ==================================================================================================================
	// Login a user with an email and password
	// ==================================================================================================================

	public function loginEmailPassword($data) {
		// echo "action_login->loginEmailPassword data:" . json_encode($data) . "<br />\n";
		if ( !isset($data->email) || empty($data->email) || !isset($data->password) || empty($data->password) ) {
			return $this->return_handler->results(400,"Email and Password must be provided",array());
		}
		
		return $this->perform("this->loginUser",$data);
	}

	// ==================================================================================================================
	// Login a user with an email and password
	// ==================================================================================================================

	public function loginFacebook($data) {
		if ( !isset($data->$data->fb_id) || empty($data->$data->fb_id) ) {
			return $this->return_handler->results(400,"Facebook ID must be provided",array());
		}
		
		return $this->perform("this->loginUser",$data);
	}

	// ==================================================================================================================
	// Login a user with an email and password
	// ==================================================================================================================

	public function loginGoogle($data) {
		if ( !isset($data->$data->google_id) || empty($data->$data->google_id) ) {
			return $this->return_handler->results(400,"Google ID must be provided",array());
		}
		
		return $this->perform("this->loginUser",$data);
	}

	// ==================================================================================================================
	// Login a user
	// ==================================================================================================================

	public function loginUser($data) {
		$return = $this->perform('this->getCalendarsForLogin',$data);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		$user = $return['response'];
		
		// If valid user but not a member of any client, do not log in
		if ( count($user->client) == 0 ) {
			return $this->return_handler->results(400,"You are not authorized for this application.",array());
		}
		// ----------------------------------------------------------------------------------------------
		// Set the user session as logged in
		// ----------------------------------------------------------------------------------------------
		$return = $this->perform('this->login',$user);

		return $return;
	}

	public function getCalendarsForLogin($data,$p_use_alias=true) {
		$sql  = "SELECT user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.email 'user.email', user.timezone 'user.timezone', ";
		$sql .= "user_profile_media_last_entered.id 'user_profile_media_last_entered.id', user_profile_media_last_entered.media_url 'user_profile_media_last_entered.media_url', ";
		$sql .= "client.id 'client.id', client.name 'client.name', client.widget_token 'client.widget_token', ";
		$sql .= "client_user.id 'client_user.id', ";
		$sql .= "client_user_role.id 'client_user_role.id', client_user_role.name 'client_user_role.name', ";
		$sql .= "client_calendar.id 'client_calendar.id.int', client_calendar.name 'client_calendar.name', client_calendar.timezone 'client_calendar.timezone', ";
		$sql .= "location.id 'location.id', location.name 'location.name', ";
		$sql .= "location_calendar.id 'location_calendar.id.int', location_calendar.name 'location_calendar.name', location_calendar.timezone 'location_calendar.timezone', ";
		$sql .= "classroom.id 'classroom.id', classroom.name 'classroom.name', ";
		$sql .= "classroom_calendar.id 'classroom_calendar.id.int', classroom_calendar.name 'classroom_calendar.name', classroom_calendar.timezone 'classroom_calendar.timezone' ";
		$sql .= "FROM user ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered ";
		$sql .= "ON user_profile_media_last_entered.user_id = user.id ";
		$sql .= "LEFT OUTER JOIN client_user ";
		$sql .= "LEFT OUTER JOIN client_user_role ";
		$sql .= "ON client_user_role.id = client_user.client_user_role_id ";
		$sql .= "LEFT OUTER JOIN client ";
		$sql .= "LEFT OUTER JOIN calendar client_calendar ";
		$sql .= "ON client_calendar.client_id = client.id AND client_calendar.location_id IS NULL ";
		$sql .= "LEFT OUTER JOIN location ";
		$sql .= "LEFT OUTER JOIN calendar location_calendar ";
		$sql .= "ON location_calendar.location_id = location.id AND location_calendar.classroom_id IS NULL ";
		$sql .= "LEFT OUTER JOIN classroom ";
		$sql .= "LEFT OUTER JOIN calendar classroom_calendar ";
		$sql .= "ON classroom_calendar.location_id = classroom.id ";
		$sql .= "ON classroom.location_id = location.id ";
		$sql .= "ON location.client_id = client.id ";
		$sql .= "ON client.id = client_user.client_id ";
		$sql .= "ON client_user.user_id = user.id ";
		$sql .= "AND client_user.deleted IS NULL ";
		// $sql .= "AND client_user.client_user_role_id = (SELECT id FROM client_user_role_staff) ";
		
		if ( property_exists($data,'user_id') ) {
			$sql .= "WHERE user.id = " . mysql_real_escape_string($data->user_id) . " ";	
		} else if ( property_exists($data,'fb_id') ) {
			$sql .= "WHERE user.fb_id = '" . mysql_real_escape_string($data->fb_id) . "' ";
		} else if ( property_exists($data,'google_id') ) {
			$sql .= "WHERE user.google_id = '" . mysql_real_escape_string($data->fb_id) . "' ";
		} else if (property_exists($data,'email') && property_exists($data,'password') ) {
			$sql .= "WHERE user.email = '" . mysql_real_escape_string($data->email) . "' ";
			$sql .= "AND user.password = '" . mysql_real_escape_string($data->password) . "' ";
		} else {
			return $this->return_handler->results(400,"You can not login with this data.",new stdClass());
		}
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			if ( property_exists($data,'user_id') ) {
				return $this->return_handler->results(400,"You do not have any Clients.",new stdClass());
			} else if ( property_exists($data,'fb_id') ) {
				return $this->return_handler->results(400,"Invalid Facebook ID",new stdClass());
			} else if ( property_exists($data,'google_id') ) {
				return $this->return_handler->results(400,"Invalid GoogleID",new stdClass());
			} else if (property_exists($data,'email') && property_exists($data,'password') ) {
				return $this->return_handler->results(400,"Invalid Email or Password",new stdClass());
			} else {
				return $this->return_handler->results(400,"Invalid Login Information.",new stdClass());
			}
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->client_user = mysql_schema::getTableAlias('workoutdb','client_user',$p_use_alias);
		$table->client = mysql_schema::getTableAlias('workoutdb','client',$p_use_alias);
		$table->location = mysql_schema::getTableAlias('workoutdb','location',$p_use_alias);
		$table->classroom = mysql_schema::getTableAlias('workoutdb','classroom',$p_use_alias);

		$user = new stdClass();
		$user->id = null;

		foreach ( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
                      
			if ( is_null($user->id) ) {
				$user = clone $row->user;
				$user->media = format_object_with_id($row->user_profile_media_last_entered);
				$user->{$table->client} = array();
				$client = &$user->{$table->client};
				$c = -1;
			}
			if ( !is_null($row->client->id) ) {
				if ( $c < 0 || $client[$c]->id != $row->client->id ) {
					$c++;
					$client[$c] = clone $row->client;
					$client[$c]->client_user = clone $row->client_user;
					$client[$c]->client_user->client_user_role = format_object_with_id($row->client_user_role);
					$client[$c]->calendar = format_object_with_id($row->client_calendar);
					$client[$c]->{$table->location} = array();
					$location = &$client[$c]->{$table->location};
					$l = -1;
				}
			}
			if ( !is_null($row->location->id) ) {
				if ( $l < 0 || $location[$l]->id != $row->location->id ) {
					$l++;
					$location[$l] = clone $row->location;
					$location[$l]->calendar = format_object_with_id($row->location_calendar);
					$location[$l]->{$table->classroom} = array();
					$classroom = &$location[$l]->{$table->classroom};
					$cl = -1;
				}
			}
			if ( !is_null($row->classroom->id) ) {
				if ( $cl < 0 || $classroom[$cl]->id != $row->classroom->id ) {
					$cl++;
					$classroom[$cl] = clone $row->classrom;
					$classroom[$cl]->calendar = format_object_with_id($row->classroom_calendar);
				}
			}
		}

		return $this->return_handler->results(200,"",$user);
	}
	
	public function login ( $p_user ) {
		// ------------------------------------------------------------------
		// Login the User
		// ------------------------------------------------------------------
		// add the session_id to the user object
		$p_user->session_id = $this->session->userdata('session_id');
		// add the application to the user object
		$p_user->application = $this->application;
		
		// set the session to logged in
		$this->session->set_userdata('login_state', TRUE);
		
		// store the user to the session data
		$this->session->set_userdata('user',$p_user);
		// ------------------------------------------------------------------
		// Update the user's last_login
		// ------------------------------------------------------------------
		$fields = new stdClass();
		$fields->id = $p_user->id;
		$fields->last_login = time();
		// print_r($fields);
		$return = $this->perform('table_workoutdb_user->update',$fields);
		if ( $return['status'] >= 300) {
			return $return;
		}
		
		return $this->return_handler->results(200,"",$p_user);
	}

	// ==================================================================================================================
	// Reload the Session data ( should be used after profile is changed or client/location/classroom are changed )
	// ==================================================================================================================

	public function reloadSessionUser() {
		// Get the Session's User data
		$session_user = $this->session->userdata('user');
		
		// Rebuild the Session's User data
		$data = new stdClass();
		$data->user_id = $session_user->id;
		$return = $this->perform('this->getCalendarsForLogin',$data);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		$user = $return['response'];
		
		// If valid user but not a member of any client, do not log in
		if ( count($user->client) == 0 ) {
			return $this->return_handler->results(400,"You are not authorized for this application.",array());
		}
		// ----------------------------------------------------------------------------------------------
		// store the reloaded user to the session data
		// ----------------------------------------------------------------------------------------------
		$this->session->set_userdata('user',$user);

		return $return;
	}

	// ==================================================================================================================
	// Get the data from the Session (Not the database)
	// ==================================================================================================================

	public function getSessionUserData() {
		// echo "getSessionUserdata<br />";
		// ------------------------------------------------------------------
		// Is the user logged in?
		// ------------------------------------------------------------------
		if ( $this->session->userdata('login_state') != true ) {
			return $this->return_handler->results(401,"Not Logged In",new stdClass());
		}
		// ------------------------------------------------------------------
		// get the logged in user's data
		// ------------------------------------------------------------------
		return $this->return_handler->results(200,"",$this->session->userdata('user'));
	}

	// ==================================================================================================================
	// Logout the current session user
	// ==================================================================================================================

	public function logout() {
		// Set login state to false
		$this->session->set_userdata('login_state',FALSE);

		return $this->return_handler->results(200,"You have successfully logout",new stdClass());
	}
}