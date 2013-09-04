<?php

class action_user extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// get a single user's information using their user_id
	// ==================================================================================================================
	
	public function getForId( $p_id, $p_use_alias=true ) {
		// echo "action_user->getForId id:$p_id<br />";
		//
		// initialize the response data
		$entry = null;
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the record entries
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.phone 'user.phone', user.email 'user.email', ";
		$sql .= "user.password 'user.password', user.timezone 'user.timezone', user.token 'user.token', user.token_expire 'user.token_expire', ";
		$sql .= "user.birthday 'user.birthday', user.gender 'user.gender', user.address 'user.address', ";
		$sql .= "user.send_log_notification 'user.send_log_notification', user.anonymous_on_leaderboard 'user.anonymous_on_leaderboard', ";
		$sql .= "user.height 'user.height', user.height_uom_id 'user.height_uom_id', user.weight 'user.weight', user.weight_uom_id 'user.weight_uom_id', ";
		$sql .= "user.about_me 'user.about_me', user.fb_id 'user.fb_id', ";
		$sql .= "user_profile_media_last_entered.id 'user_profile_media_last_entered.id', user_profile_media_last_entered.media_url 'user_profile_media_last_entered.media_url' ";
		$sql .= "FROM user ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered ";
		$sql .= "ON user_profile_media_last_entered.user_id = user.id ";
		$sql .= "WHERE user.id = " . $p_id . " ";
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";
		
		// load the user entry
		$user = clone $row->user;
		unset($user->password);
		$user->has_password = !is_null($row->user->password);
		$user->timezone_offset = format_timezone_offset($row->user->timezone);
		$user->media = format_object_with_id($row->user_profile_media_last_entered);
			
		return $this->return_handler->results(200,"",$user);
	}
	
	// ==================================================================================================================
	// get the user for a Unique Key
	// ==================================================================================================================
	
	public function getForEmail( $p_email, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.phone 'user.phone', user.email 'user.email', ";
		$sql .= "user.timezone 'user.timezone', user.token 'user.token', user.token_expire 'user.token_expire', ";
		$sql .= "user.birthday 'user.birthday', user.gender 'user.gender', user.address 'user.address', ";
		$sql .= "user.send_log_notification 'user.send_log_notification', user.anonymous_on_leaderboard 'user.anonymous_on_leaderboard', ";
		$sql .= "user.height 'user.height', user.height_uom_id 'user.height_uom_id', user.weight 'user.weight', user.weight_uom_id 'user.weight_uom_id', ";
		$sql .= "user.about_me 'user.about_me', user.fb_id 'user.fb_id' ";
		$sql .= "FROM user ";
		$sql .= "WHERE user.email = " . $p_email . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";
		
		// load the user entry
		$user = clone $row->user;
		$user->timezone_offset = format_timezone_offset($row->user->timezone);
			
		return $this->return_handler->results(200,"",$user);
	}
	
	// ==================================================================================================================
	// get the user for a Unique Key
	// ==================================================================================================================
	
	public function getForFacebookID( $p_fb_id, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.phone 'user.phone', user.email 'user.email', ";
		$sql .= "user.timezone 'user.timezone', user.token 'user.token', user.token_expire 'user.token_expire', ";
		$sql .= "user.birthday 'user.birthday', user.gender 'user.gender', user.address 'user.address', ";
		$sql .= "user.send_log_notification 'user.send_log_notification', user.anonymous_on_leaderboard 'user.anonymous_on_leaderboard', ";
		$sql .= "user.height 'user.height', user.height_uom_id 'user.height_uom_id', user.weight 'user.weight', user.weight_uom_id 'user.weight_uom_id', ";
		$sql .= "user.about_me 'user.about_me', user.fb_id 'user.fb_id' ";
		$sql .= "FROM user ";
		$sql .= "WHERE user.fb_id = " . $p_fb_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";
		
		// load the user entry
		$user = clone $row->user;
		$user->timezone_offset = format_timezone_offset($row->user->timezone);
			
		return $this->return_handler->results(200,"",$user);
	}
	
	// ==================================================================================================================
	// get the user for a Unique Key
	// ==================================================================================================================
	
	public function getForGoogleID( $p_google_id, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.phone 'user.phone', user.email 'user.email', ";
		$sql .= "user.timezone 'user.timezone', user.token 'user.token', user.token_expire 'user.token_expire', ";
		$sql .= "user.birthday 'user.birthday', user.gender 'user.gender', user.address 'user.address', ";
		$sql .= "user.send_log_notification 'user.send_log_notification', user.anonymous_on_leaderboard 'user.anonymous_on_leaderboard', ";
		$sql .= "user.height 'user.height', user.height_uom_id 'user.height_uom_id', user.weight 'user.weight', user.weight_uom_id 'user.weight_uom_id', ";
		$sql .= "user.about_me 'user.about_me', user.fb_id 'user.fb_id' ";
		$sql .= "FROM user ";
		$sql .= "WHERE user.google_id = " . $p_google_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";
		
		// load the user entry
		$user = clone $row->user;
		$user->timezone_offset = format_timezone_offset($row->user->timezone);
			
		return $this->return_handler->results(200,"",$user);
	}

	// ==================================================================================================================
	// get the user for a client_user_id
	// ==================================================================================================================
	
	public function getForClientUser( $p_client_user_id, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.phone 'user.phone', user.email 'user.email', ";
		$sql .= "user.timezone 'user.timezone', user.token 'user.token', user.token_expire 'user.token_expire', ";
		$sql .= "user.birthday 'user.birthday', user.gender 'user.gender', user.address 'user.address', ";
		$sql .= "user.send_log_notification 'user.send_log_notification', user.anonymous_on_leaderboard 'user.anonymous_on_leaderboard', ";
		$sql .= "user.height 'user.height', user.height_uom_id 'user.height_uom_id', user.weight 'user.weight', user.weight_uom_id 'user.weight_uom_id', ";
		$sql .= "user.about_me 'user.about_me', user.fb_id 'user.fb_id' ";
		$sql .= "FROM client_user, ";
		$sql .= "user ";
		$sql .= "WHERE client_user.id = " . $p_client_user_id . " ";
		$sql .= "AND user.id = client_user.user_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";
		
		// load the user entry
		$user = clone $row->user;
		$user->timezone_offset = format_timezone_offset($row->user->timezone);
			
		return $this->return_handler->results(200,"",$user);
	}

	// ==================================================================================================================
	// get the user for a participation_id
	// ==================================================================================================================
	
	public function getForParticipation( $p_calendar_event_participation_id, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.phone 'user.phone', user.email 'user.email', ";
		$sql .= "user.timezone 'user.timezone', user.token 'user.token', user.token_expire 'user.token_expire', ";
		$sql .= "user.birthday 'user.birthday', user.gender 'user.gender', user.address 'user.address', ";
		$sql .= "user.send_log_notification 'user.send_log_notification', user.anonymous_on_leaderboard 'user.anonymous_on_leaderboard', ";
		$sql .= "user.height 'user.height', user.height_uom_id 'user.height_uom_id', user.weight 'user.weight', user.weight_uom_id 'user.weight_uom_id', ";
		$sql .= "user.about_me 'user.about_me', user.fb_id 'user.fb_id' ";
		$sql .= "FROM calendar_event_participation, ";
		$sql .= "client_user, ";
		$sql .= "user ";
		$sql .= "WHERE calendar_event_participation.id = " . $p_calendar_event_participation_id . " ";
		$sql .= "AND client_user.id = calendar_event_participation.client_user_id ";
		$sql .= "AND user.id = client_user.user_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";
		
		// load the user entry
		$user = clone $row->user;
		$user->timezone_offset = format_timezone_offset($row->user->timezone);
			
		return $this->return_handler->results(200,"",$user);
	}

	// ==================================================================================================================
	// get the user for a workout_log_id (User dump - fields not formatted or cast)
	// ==================================================================================================================
	
	public function getForWorkoutLog( $p_workout_log_id, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "user.id 'user.id', user.first_name 'user.first_name', user.last_name 'user.last_name', user.phone 'user.phone', user.email 'user.email', ";
		$sql .= "user.timezone 'user.timezone', user.token 'user.token', user.token_expire 'user.token_expire', ";
		$sql .= "user.birthday 'user.birthday', user.gender 'user.gender', user.address 'user.address', ";
		$sql .= "user.send_log_notification 'user.send_log_notification', user.anonymous_on_leaderboard 'user.anonymous_on_leaderboard', ";
		$sql .= "user.height 'user.height', user.height_uom_id 'user.height_uom_id', user.weight 'user.weight', user.weight_uom_id 'user.weight_uom_id', ";
		$sql .= "user.about_me 'user.about_me', user.fb_id 'user.fb_id' ";
		$sql .= "FROM workout_log, ";
		$sql .= "calendar_event_participation, ";
		$sql .= "client_user, ";
		$sql .= "user ";
		$sql .= "WHERE workout_log.id = " . $p_workout_log_id . " ";
		$sql .= "AND calendar_event_participation.id = workout_log.calendar_event_participation_id ";
		$sql .= "AND client_user.id = calendar_event_participation.client_user_id ";
		$sql .= "AND user.id = client_user.user_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";
		
		// load the user entry
		$user = clone $row->user;
		$user->timezone_offset = format_timezone_offset($row->user->timezone);
			
		return $this->return_handler->results(200,"",$user);
	}

	// ==================================================================================================================
	// Get a list of clients that a user has access to and what their role is for each client
	// ==================================================================================================================
	
	public function getClientUserRolesForUser( $p_user_id, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "client_user.client_id 'client_user.client_id', ";
		$sql .= "client_user.id 'client_user.id', ";
		$sql .= "client_user.client_user_role_id 'client_user.client_user_role_id' ";
		$sql .= "FROM ";
		$sql .= "client_user ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " ";
		$sql .= "AND client_user.deleted IS NULL ";
		$sql .= "ORDER BY client_user.client_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0 ) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$client = array();

		foreach ( $rows as $row ) {
		
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
	     	// echo json_encode($row) . "<br /><br />\n\n";
	     	
	     	$client[$row->client_user->client_id] = new stdClass();
			$client[$row->client_user->client_id]->client_user_id = $row->client_user->id;
			$client[$row->client_user->client_id]->client_user_role_id = $row->client_user->client_user_role_id;
		}
			
		return $this->return_handler->results(200,"",$client);
	}

	// ==================================================================================================================
	// Update a user
	// ==================================================================================================================

	public function update($p_fields) {
		// echo "action_member->update fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// User Id is mandatory
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'id') || is_null($p_fields->id) || empty($p_fields->id) || !is_numeric($p_fields->id) ) {
			return $this->return_handler->results(400,"Id must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Update the User
		// -------------------------------------------------------------------------------------------------------------
		// echo "user:"; print_r($p_fields); echo "<br />";
		// Update the entry
		$return = $this->perform('table_workoutdb_user->update',$p_fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// --------------------------------------------------------------------------------------------------------------
		// Create a New User Profile Media (if media->id is null or empty, create a new entry)
		// --------------------------------------------------------------------------------------------------------------
		if ( property_exists($p_fields,'media') && is_object($p_fields->media) &&
		     property_exists($p_fields->media,'url') && !is_null($p_fields->media->url) && !empty($p_fields->media->url) &&
		     (!property_exists($p_fields->media,'id') || is_null($p_fields->media->id) || empty($p_fields->media->id)) ) {
		    // create the fields to post
			$fields = new stdClass();
			$fields->url = $p_fields->media->url;
			$fields->user_id = $p_fields->id;
			// POST the fields
			$return = $this->perform('table_workoutdb_user_profile_media->insert',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			// echo "inserted user_profile_media:"; print_r($return);

			unset($fields);
		}

		return $return;
	}

	// ==================================================================================================================
	// Update a user password
	// ==================================================================================================================

	public function updatePassword($p_fields) {
		// echo "action_member->updatePassword fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// User Id is mandatory
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'id') || is_null($p_fields->id) || empty($p_fields->id) || !is_numeric($p_fields->id) ) {
			return $this->return_handler->results(400,"Id must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the User Id from the Client User entry
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_user->getForId',$p_fields->id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$user = $return['response'];
		// echo "user current:"; print_r($user); echo "<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Validate mandatory fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'old_password') || is_null($p_fields->old_password) || empty($p_fields->old_password) ) {
			return $this->return_handler->results(400,"Old Password must be provided",new stdClass());
		}
		if ( !property_exists($p_fields,'new_password') || is_null($p_fields->new_password) || empty($p_fields->new_password) ) {
			return $this->return_handler->results(400,"New Password must be provided",new stdClass());
		}
		if ( $p_fields->old_password != $user->password ) {
			return $this->return_handler->results(400,"Old Password is invalid",new stdClass());
		}
		if ( $p_fields->old_password == $p_fields->new_password ) {
			return $this->return_handler->results(400,"You did not change your password",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Update the User
		// -------------------------------------------------------------------------------------------------------------
		// echo "user:"; print_r($p_fields); echo "<br />";
		// create oject for the update
		$fields = new stdClass();
		$fields->id = $p_fields->id;
		$fields->password = $p_fields->new_password;
		// Update the entry
		$return = $this->perform('table_workoutdb_user->update',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		return $return;
	}

	// ==================================================================================================================
	// generate a new token and (if needed) token expire date for a user.
	// ==================================================================================================================
	
	public function setToken( $p_email ) {
		// echo "action_user->setToken fields:$p_email<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Validate mandatory fields
		// -------------------------------------------------------------------------------------------------------------
		if ( is_null($p_email) || empty($p_email) ) {
			return $this->return_handler->results(400,"email must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the user
		// -------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['email'] = $p_email;
		$return = $this->perform('table_workoutdb_user->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Invalid email",new stdClass());
		}
		$user = $return['response'][0];
		// echo "user:"; print_r($user); echo "<br />";
		// ------------------------------------------------------------------
		// Create a new token
		// ------------------------------------------------------------------
		$this->load->helper('access');
		$token = generate_token($length=10);  // generate_token is function in helper access
		// ------------------------------------------------------------------
		// if the password is set, give the token a 1 week expire date
		// ------------------------------------------------------------------
		$token_expire = null;
		if ( is_null($user->password) || empty($user->password) ) {
			$response->token_expire = generate_expire_date();  // generate_expire_date is function in helper access
		}
		// ------------------------------------------------------------------
		// Update the user's password with the new password
		// ------------------------------------------------------------------
		$data = new stdClass();
		$data->id = $user->id;
		$data->token = $token;
		$data->token_expire = $token_expire;
		
		$return = $this->perform('table_workoutdb_user->update',$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$user->token = $token;
		$user->token_expire = $token_expire;
		// ------------------------------------------------------------------
		// Send a reset password email to the user
		// ------------------------------------------------------------------
		$return = $this->perform('email_reset_password->sendEmail',$user);
		// print_r($return);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		return $this->return_handler->results(200,"Please check your email.",new stdClass());
	}

	// ==================================================================================================================
	// Validate a user's for email and token
	// ==================================================================================================================

	public function validateToken($p_token) {
		$return = $this->perform('this->decryptToken',$p_token);
		return $this->return_handler->results($return['status'],$return['message'],new stdClass());
	}

	public function decryptToken($p_token) {
		// echo "action_user->decryptToken $p_token<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Validate mandatory fields
		// -------------------------------------------------------------------------------------------------------------
		if ( is_null($p_token) || empty($p_token) ) {
			return $this->return_handler->results(400,"Token must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// decrypt the token into an email and activation token
		// -------------------------------------------------------------------------------------------------------------
		$this->load->helper('access');
		$decrypt = decrypt_email_token($p_token);  // decrypt_email_token is function in helper access
		if ( is_bool($decrypt) && !$decrypt ) {
			return $this->return_handler->results(400,"Invalid token",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the user
		// -------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['email'] = $decrypt->email;
		$return = $this->perform('table_workoutdb_user->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Invalid email or token",new stdClass());
		}
		$user = $return['response'][0];
		// echo "user:"; print_r($user); echo "<br />";
		// -------------------------------------------------------------------------------------------------------------
		// Validate token
		// -------------------------------------------------------------------------------------------------------------
		if ( $decrypt->token != $user->token ) {
			return $this->return_handler->results(400,"Invalid email or token",new stdClass());
		}
		if ( !is_null($user->token_expire) && time() > $user->token_expire ) {
			return $this->return_handler->results(400,"Token has expired",new stdClass());
		}
		// add the user id to the decrypt object
		$decrypt->id = $user->id;
		return $this->return_handler->results(200,"",$decrypt);
	}

	// ==================================================================================================================
	// Update a user password for email and token
	// ==================================================================================================================

	public function updatePasswordLoginForToken($p_fields) {
		// echo "action_user->updatePasswordForEmailToken fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// Validate mandatory fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'token') || is_null($p_fields->token) || empty($p_fields->token) ) {
			return $this->return_handler->results(400,"Token must be provided",new stdClass());
		}
		if ( !property_exists($p_fields,'password') || is_null($p_fields->password) || empty($p_fields->password) ) {
			return $this->return_handler->results(400,"Password must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// The data->token is the email and user's login token encrypted
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->decryptToken',$p_fields->token);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$decrypt = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Update the User
		// -------------------------------------------------------------------------------------------------------------
		// create oject for the update
		$fields = new stdClass();
		$fields->id = $decrypt->id;
		$fields->password = $p_fields->password;
		$fields->token = null;
		$fields->token_expire = null;
		// Update the entry
		$return = $this->perform('table_workoutdb_user->update',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// Log the user in
		// -------------------------------------------------------------------------------------------------------------
		unset($fields);
		$fields = new stdClass();
		$fields->email = $decrypt->email;
		$fields->password = $p_fields->password;
		return $this->perform('action_login->loginUser',$fields);
	}

	// ==================================================================================================================
	// Update a user password for email and token
	// ==================================================================================================================

	public function updateFacebookLoginForToken($p_fields) {
		// echo "action_user->updatePasswordForEmailToken fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// Validate mandatory fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'token') || is_null($p_fields->token) || empty($p_fields->token) ) {
			return $this->return_handler->results(400,"Token must be provided",new stdClass());
		}
		if ( !property_exists($p_fields,'fb_id') || is_null($p_fields->fb_id) || empty($p_fields->fb_id) ) {
			return $this->return_handler->results(400,"Facebook ID must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Validate email and token
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->decryptToken',$p_fields->token);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$decrypt = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Update the User
		// -------------------------------------------------------------------------------------------------------------
		// create oject for the update
		$fields = new stdClass();
		$fields->id = $decrypt->id;
		$fields->fb_id = $p_fields->fb_id;
		// clear the user's token and its expire date
		$fields->token = null;
		$fields->token_expire = null;
		// Update the entry
		$return = $this->perform('table_workoutdb_user->update',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// Log the user in
		// -------------------------------------------------------------------------------------------------------------
		unset($fields);
		$fields = new stdClass();
		$fields->fb_id = $p_fields->fb_id;
		return $this->perform('action_login->loginUser',$fields);
	}

	// ==================================================================================================================
	// Update a user password for email and token
	// ==================================================================================================================

	public function updateGoogleLoginForToken($p_fields) {
		// echo "action_user->updatePasswordForEmailToken fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// Validate mandatory fields
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'token') || is_null($p_fields->token) || empty($p_fields->token) ) {
			return $this->return_handler->results(400,"Token must be provided",new stdClass());
		}
		if ( !property_exists($p_fields,'google_id') || is_null($p_fields->google_id) || empty($p_fields->google_id) ) {
			return $this->return_handler->results(400,"Facebook ID must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Validate email and token
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->decryptToken',$p_fields->token);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$decrypt = $return['response'];
		// -------------------------------------------------------------------------------------------------------------
		// Update the User
		// -------------------------------------------------------------------------------------------------------------
		// create oject for the update
		$fields = new stdClass();
		$fields->id = $decrypt->id;
		$fields->google_id = $p_fields->google_id;
		// clear the user's token and its expire date
		$fields->token = null;
		$fields->token_expire = null;
		// Update the entry
		$return = $this->perform('table_workoutdb_user->update',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -------------------------------------------------------------------------------------------------------------
		// Log the user in
		// -------------------------------------------------------------------------------------------------------------
		unset($fields);
		$fields = new stdClass();
		$fields->google_id = $p_fields->google_id;
		return $this->perform('action_login->loginUser',$fields);
	}
}