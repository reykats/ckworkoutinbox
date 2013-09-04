<?php

// ==================================================================================================================
//
//   The actions against the client_user and its associated user
//
// ==================================================================================================================

class action_member extends action_generic {

	protected $user;
	protected $client_user;

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get a list of all client_users for a client
	// ==================================================================================================================

	public function getForClientFormatWeb( $p_client_id ) {
		//
		// initialize the response data
		$count = 0;
		$entries = array();
		$response->count = $count;
		$response->results = $entries;
		// ---------------------------------------------------------------------------------------------------------
		//
		// Prepair the select options
		//
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for role_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$role_id_check = "";
		if ( isset($_GET['q_r']) && !empty($_GET['q_r']) && is_numeric($_GET['q_r']) ) {
			$role_id_check = "AND m.client_user_role_id = " . $_GET['q_r'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for location_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$location_id_check = "";
		if ( isset($_GET['q_loc']) && !empty($_GET['q_loc']) && is_numeric($_GET['q_loc']) ) {
			$location_id_check = "AND m.location_id = " . $_GET['q_loc'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "AND concat(";
			$search_check .= "if(isnull(u.first_name),'',concat(' ',u.first_name)),";
			$search_check .= "if(isnull(u.last_name),'',concat(' ',u.last_name)),";
			$search_check .= "if(isnull(u.email),'',concat(' ',u.email)),";
			$search_check .= "if(isnull(u.phone),'',concat(' ',u.phone))";
			$search_check .= ") LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$limit = "LIMIT " . (($_GET['page'] - 1) * $_GET['page_length']) . ", " . $_GET['page_length'] . " ";
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the total record count without paging limits
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT count(m.id) cnt ";
		$sql .= "FROM client_user m, ";
		$sql .= "user u ";
		$sql .= "WHERE m.client_id = " . $p_client_id . " ";
		$sql .= "AND m.deleted IS NULL ";
		$sql .= $role_id_check;
		$sql .= $location_id_check;
		$sql .= "AND u.id = m.user_id ";
		$sql .= $search_check;

		// echo "$sql<br />";

		$row = $this->db->query($sql)->row();
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the record entries
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT m.id, ";
		$sql .= "u.id user_id, u.first_name, u.last_name, u.phone, u.email, u.last_login, ";
		$sql .= "media.id media_id, media.media_url media_url, ";
		$sql .= "MAX(calendar_event.start) last_checkin, ";
		$sql .= "r.name role, ";
		$sql .= "l.name location ";
		$sql .= "FROM client_user m ";
		$sql .= "LEFT OUTER JOIN user u ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$sql .= "ON media.user_id = u.id ";
		$sql .= "ON u.id = m.user_id ";
		$sql .= "LEFT OUTER JOIN client_user_role r ";
		$sql .= "ON r.id = m.client_user_role_id ";
		$sql .= "LEFT OUTER JOIN location l ";
		$sql .= "ON l.id = m.location_id ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation ";
		$sql .= "LEFT OUTER JOIN calendar_event ";
		$sql .= "ON calendar_event.id = calendar_event_participation.calendar_event_id ";
		$sql .= "ON calendar_event_participation.client_user_id = m.id ";
		$sql .= "WHERE m.client_id = " . $p_client_id . " ";
		$sql .= "AND m.deleted IS NULL ";
		$sql .= $role_id_check;
		$sql .= $location_id_check;
		$sql .= $search_check;
		$sql .= "GROUP BY m.id ";
		$sql .= "ORDER BY u.first_name, u.last_name, u.email ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();

		foreach ( $rows as $row ) {
			//
			// store the results in return format
			$enyty = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->user_id = cast_int($row->user_id);
			$entry->role = $row->role;
			$entry->first_name = $row->first_name;
			$entry->last_name = $row->last_name;
			$entry->phone = $row->phone;
			$entry->email = $row->email;
			$entry->location = $row->location;
			$entry->media = format_media($row->media_id,$row->media_url);
			$entry->last_login = cast_int($row->last_login);
			$entry->last_checkin = cast_int($row->last_checkin);
			array_push($entries,clone $entry);
			unset($entry);
		}

		$response->count = $count;
		$response->results = $entries;
		return $this->return_handler->results(200,"",$response);
	}

	// ==================================================================================================================
	// Get a list of all client_users for a client
	// ==================================================================================================================

	public function getForClientFormatMobile( $p_client_id ) {
		//
		// initialize the response data
		$count = 0;
		$entries = array();
		$response->count = $count;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for role_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$role_id_check = "";
		if ( isset($_GET['q_r']) && !empty($_GET['q_r']) && is_numeric($_GET['q_r']) ) {
			$role_id_check = "AND m.client_user_role_id = " . $_GET['q_r'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) &&
		     !isset($_GET['page']) ) {
			$limit = "LIMIT 0, " . $_GET['page_length'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$limit = "LIMIT " . (($_GET['page'] - 1) * $_GET['page_length']) . ", " . $_GET['page_length'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "AND concat(";
			$search_check .= "if(isnull(u.first_name),'',concat(' ',u.first_name)),";
			$search_check .= "if(isnull(u.last_name),'',concat(' ',u.last_name))";
			$search_check .= ") LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the total record count without paging limits
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT count(m.id) cnt ";
		$sql .= "FROM client_user m, ";
		$sql .= "user u ";
		$sql .= "WHERE m.client_id = " . $p_client_id . " ";
		$sql .= "AND m.deleted IS NULL ";
		$sql .= $role_id_check;
		$sql .= "AND u.id = m.user_id ";
		$sql .= $search_check;

		// echo "$sql<br />";

		$row = $this->db->query($sql)->row();
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the record entries
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT m.id id, m.client_user_role_id role_id, ";
		$sql .= "u.id user_id, u.first_name, u.last_name, u.email, ";
		$sql .= "media.media_url, ";
		$sql .= "r.name role_name ";
		$sql .= "FROM client_user m ";
		$sql .= "LEFT OUTER JOIN client_user_role r ";
		$sql .= "ON r.id = m.client_user_role_id, ";
		$sql .= "user u ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$sql .= "ON media.user_id = u.id ";
		$sql .= "WHERE m.client_id = " . $p_client_id . " ";
		$sql .= "AND m.deleted IS NULL ";
		$sql .= $role_id_check;
		$sql .= "AND u.id = m.user_id ";
		$sql .= $search_check;
		$sql .= "ORDER BY u.first_name, u.last_name, u.email ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"",$response);
		}
		$rows = $query->result();

		$entries = array();

		foreach ( $rows as $row ) {
			// load the client_user entry
			$entry->id = cast_int($row->id);
			$entry->first_name = $row->first_name;
			$entry->last_name = $row->last_name;
			$entry->email = $row->email;
			$entry->media = $row->media_url;
			$entry->role = $row->role_name;
			// push the client_user entry to the entries
			array_push($entries,clone $entry);
			// clear the client_user_entry
			unset($entry);
		}

		$response->count = $count;
		$response->results = $entries;
		return $this->return_handler->results(200,"",$response);
	}

	// ==================================================================================================================
	// Get a single client_user for an client_user_id
	// ==================================================================================================================

	public function getForId( $p_client_user_id ) {
		// echo "getForId id:$p_client_user_id<br />";

		$sql  = "SELECT m.id id, m.client_user_role_id role_id, m.location_id, m.note note, ";
		$sql .= "u.id user_id, u.first_name, u.last_name, u.phone, u.email, u.gender, u.birthday birthday, u.address, ";
		$sql .= "u.height height, u.height_uom_id height_uom_id, u.weight weight, u.weight_uom_id weight_uom_id, u.about_me, ";
		$sql .= "media.id media_id, media.media_url ";
		$sql .= "FROM client_user m, ";
		$sql .= "user u ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$sql .= "ON media.user_id = u.id ";
		$sql .= "WHERE m.id = " . $p_client_user_id . " ";
		$sql .= "AND m.deleted IS NULL ";
		$sql .= "AND u.id = m.user_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();

		$entry = null;
		// load the equipment entry
		$entry->id = cast_int($row->id);
		$entry->role_id = cast_int($row->role_id);
		$entry->location_id = cast_int($row->location_id);
		$entry->first_name = $row->first_name;
		$entry->last_name = $row->last_name;
		$entry->phone = $row->phone;
		$entry->email = $row->email;
		$entry->gender = $row->gender;
		$entry->birthday = cast_int($row->birthday);
		$entry->note = $row->note;
		$entry->address = $row->address;
		$entry->height = format_height($row->height,$row->height_uom_id);
		$entry->weight = format_weight($row->weight,$row->weight_uom_id);
		$entry->media = format_media($row->media_id,$row->media_url);
		$entry->about_me = $row->about_me;

		return $this->return_handler->results(200,"",$entry);
	}

	// ==================================================================================================================
	// Create a member (create user if needed and create/reactivate client_user if needed)
	// ==================================================================================================================

	public function create($p_fields,$p_notify_user=true) {
		// echo "action_member fields:" . json_encode($p_fields) . "<br />";
		$p_fields = (object) $p_fields;
		// --------------------------------------------------------------------------------------------------------------
		// initialize the response
		// --------------------------------------------------------------------------------------------------------------
		$response = new stdClass();
		$response->id = null;
		// -------------------------------------------------------------------------------------------------------------
		// Are all the Mandatory fields present
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'client_id') || is_null($p_fields->client_id) || empty($p_fields->client_id) || !is_numeric($p_fields->client_id) ) {
			return $this->return_handler->results(400,"client_id is missing or invalid type.",$response);
		}
		if ( !property_exists($p_fields,'role_id') || is_null($p_fields->role_id) || empty($p_fields->role_id) || !is_numeric($p_fields->role_id) ) {
			return $this->return_handler->results(400,"role_id is missing or invalid type.",$response);
		}
		if ( !property_exists($p_fields,'first_name') || is_null($p_fields->first_name) || empty($p_fields->first_name) || !is_string($p_fields->first_name) ) {
			return $this->return_handler->results(400,"first_name is missing or invalid type.",$response);
		}
		if ( !property_exists($p_fields,'last_name') || is_null($p_fields->last_name) || empty($p_fields->last_name) || !is_string($p_fields->last_name) ) {
			return $this->return_handler->results(400,"last_name is missing or invalid type.",$response);
		}
		if ( !property_exists($p_fields,'email') || is_null($p_fields->email) || empty($p_fields->email) || !is_string($p_fields->email) ) {
			return $this->return_handler->results(400,"email is missing or invalid type.",$response);
		}

		// -------------------------------------------------------------------------------------------------------------
		// Get the User ID.
		// Create the User if it does not exist.
		// -------------------------------------------------------------------------------------------------------------
		$fields = clone $p_fields;
		$return = $this->perform('table_workoutdb_user->insert',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$user = clone $return['response'];

		unset($fields);
		// --------------------------------------------------------------------------------------------------------------
		// Create a New User Profile Media
		// --------------------------------------------------------------------------------------------------------------
		if ( property_exists($p_fields,'media') && is_object($p_fields->media) &&
		     property_exists($p_fields->media,'url') && !is_null($p_fields->media->url) && !empty($p_fields->media->url) &&
		     (!property_exists($p_fields->media,'id') || is_null($p_fields->media->id) || empty($p_fields->media->id)) ) {
		    // create the fields to post
			$fields = new stdClass();
			$fields->url = $p_fields->media->url;
			$fields->user_id = $user->id;
			// POST the fields
			$return = $this->perform('table_workoutdb_user_profile_media->insert',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}

			unset($fields);
		}

		// -------------------------------------------------------------------------------------------------------------
		// Create the Client User if it does not exist and return the New Client User's ID
		// Return the Existing Client User's ID if one exists already
		// -------------------------------------------------------------------------------------------------------------
		$fields = clone $p_fields;
		$fields->user_id = $user->id;
		$return = $this->perform('table_workoutdb_client_user->insert',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$client_user = clone $return['response'];
		unset($fields);
		// -------------------------------------------------------------------------------------------------------------
		// Set the user's token and token expire date.  Send a welcome email to the user with a link to set thier password
		// ( user and client_user are objects that contain id and action )
		// -------------------------------------------------------------------------------------------------------------
		if ( $user->action != 'update' || $client_user->action != 'update' ) {
			// -----------------------------------------------------------------------------------------------
			// set the user's token and token expire date
			// -----------------------------------------------------------------------------------------------
			$this->load->helper('access');
			$fields = new stdClass();
			$fields->id = $user->id;
			$fields->token = generate_token($length=10);  // generate_token is function in helper access
			$fields->token_expire = null;
			$return = $this->perform('table_workoutdb_user->update',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $p_notify_user ) {
				// -----------------------------------------------------------------------------------------------
				// Send the Welcome user email
				// -----------------------------------------------------------------------------------------------
				$return = $this->perform('email_account_status_change->sendEmail',$user,$client_user);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}

		$response->id = $client_user->id;
		return $this->return_handler->results(201,"Entry Created",$response);
	}

	// ==================================================================================================================
	// Update a member (update the user if needed and update the client_user if needed)
	// ==================================================================================================================

	public function update($p_fields,$p_notify=true) {
		// echo "action_member->update fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// Client User Id is mandatory
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'id') || is_null($p_fields->id) || empty($p_fields->id) || !is_numeric($p_fields->id) ) {
			return $this->return_handler->results(400,"Id must be provided",new stdClass());
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the User Id from the Client User entry
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_client_user->getForId',$p_fields->id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$client_user = $return['response'];
		// echo "client_user current:"; print_r($client_user); echo "<br />";

		unset($fields);
		// -------------------------------------------------------------------------------------------------------------
		// Update the User
		// -------------------------------------------------------------------------------------------------------------
		// Create the entry object
		$fields = clone $p_fields;
		$fields->id = $client_user->user_id;
		// echo "user:"; print_r($fields); echo "<br />";
		// Update the entry
		$return = $this->perform('table_workoutdb_user->update',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		unset($fields);
		// --------------------------------------------------------------------------------------------------------------
		// Create a New User Profile Media (if media->id is null or empty, create a new entry)
		// --------------------------------------------------------------------------------------------------------------
		if ( property_exists($p_fields,'media') && is_object($p_fields->media) &&
		     property_exists($p_fields->media,'url') && !is_null($p_fields->media->url) && !empty($p_fields->media->url) &&
		     (!property_exists($p_fields->media,'id') || is_null($p_fields->media->id) || empty($p_fields->media->id)) ) {
		    // create the fields to post
			$fields = new stdClass();
			$fields->url = $p_fields->media->url;
			$fields->user_id = $client_user->user_id;
			// POST the fields
			$return = $this->perform('table_workoutdb_user_profile_media->insert',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			// echo "inserted user_profile_media:"; print_r($return);

			unset($fields);
		}
		// -------------------------------------------------------------------------------------------------------------
		// Update the Client User
		// -------------------------------------------------------------------------------------------------------------
		// Create the entry object
		$fields = clone $p_fields;
		// echo "client_user:"; print_r($fields); echo "<br />";
		// Update the entry
		$return = $this->perform('table_workoutdb_client_user->update',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		unset($fields);

		return $return;
	}

	// ==================================================================================================================
	// deactivate a client_user (set the client_user's deleted column to the current UTC date/time)
	// ==================================================================================================================

	public function deactivate( $p_id = null ) {
		// echo "action_member->deactivate id:$p_id<br />";
		// deactivate the client user
		return $this->perform('table_workoutdb_client_user->delete',$p_id);
	}
}