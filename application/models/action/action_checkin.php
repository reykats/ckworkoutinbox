<?php

class action_checkin extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get a single checkin for an calendar_event_participation_id
	// ==================================================================================================================
	
	public function getForId( $p_calendar_event_participation_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "u.id user_id, send_log_notification, ";
		$sql .= "ev.id event_id, ev.start event_start, ev.duration event_duration, ev.log_result event_log_result ";
		$sql .= "FROM ";
		$sql .= "calendar_event_participation p, ";
		$sql .= "client_user cu, ";
		$sql .= "user u, ";
		$sql .= "calendar_event ev ";
		$sql .= "WHERE p.id = " . $p_calendar_event_participation_id . " ";
		$sql .= "AND cu.id = p.client_user_id ";
		$sql .= "AND u.id = cu.user_id ";
		$sql .= "AND ev.id = p.calendar_event_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$row = $query->row(); 
		
		// print_r($row);
		
		$entry = new stdClass();
		$entry->user = new stdClass();
		$entry->user->id = cast_int($row->user_id);
		$entry->user->send_log_notification = cast_boolean($row->send_log_notification);
		$entry->event = new stdClass();
		$entry->event->id = cast_int($row->event_id);
		$entry->event->start = cast_int($row->event_start);
		$entry->event->duration = cast_int($row->event_duration);
		$entry->event->log_result = cast_boolean($row->event_log_result);
		
		return $this->return_handler->results(200,"",$entry);
	}

	// ==================================================================================================================
	// Get a list of checked in members for a class
	// ==================================================================================================================

	public function getForEntryStartFormatWeb($p_calendar_entry_id,$p_start){
		// ------------------------------------------------------------------------------------------------------------
		//
		// Get the participants for the calendar event
		//
		// ------------------------------------------------------------------------------------------------------------
		// initialize the response data
		$count = 0;
		$name = null;
		$start = null;
		$entries = array();
		$response->count = $count;
		$response->name = $name;
		$response->start = $start;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for role_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$role_id_check = "";
		if ( isset($_GET['q_r']) && !empty($_GET['q_r']) && is_numeric($_GET['q_r']) ) {
			$role_id_check = "AND cu.client_user_role_id = " . $_GET['q_r'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for location_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$location_id_check = "";
		if ( isset($_GET['q_loc']) && !empty($_GET['q_loc']) && is_numeric($_GET['q_loc']) ) {
			$location_id_check = "AND cu.location_id = " . $_GET['q_loc'] . " ";
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
		// Get the total record count without paging limits
		// ---------------------------------------------------------------------------------------------------------

		$sql  = "SELECT count(cu.id) cnt ";
		$sql .= "FROM calendar_event ev, ";
		$sql .= "calendar_event_participation p, ";
		$sql .= "client_user cu, ";
		$sql .= "user u ";
		$sql .= "WHERE ev.calendar_entry_id = " .  $p_calendar_entry_id . " ";
		$sql .= "AND ev.start = " . $p_start . " ";
		$sql .= "AND p.calendar_event_id = ev.id ";
		$sql .= "AND cu.id = p.client_user_id ";
		$sql .= $role_id_check;
		$sql .= $location_id_check;
		$sql .= "AND u.id = cu.user_id ";
		$sql .= $search_check;

		// echo "$sql<br />";

		$row = $this->db->query($sql)->row();
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
		if ( $count > 0 ) {
			// ---------------------------------------------------------------------------------------------------------
			// Get the record entries
			// ---------------------------------------------------------------------------------------------------------
			$sql  = "SELECT ev.name event_name, ev.start event_start, cu.id, u.first_name, u.last_name, u.email, u.phone, ";
			$sql .= "MAX(ev2.start) last_checkin, ";
			$sql .= "r.name role, ";
			$sql .= "l.name location, ";
			$sql .= "media.id media_id, media.media_url media_url ";
			$sql .= "FROM calendar_event ev, ";
			$sql .= "calendar_event_participation p, ";
			$sql .= "client_user cu ";
			$sql .= "LEFT OUTER JOIN client_user_role r ";
			$sql .= "ON r.id = cu.client_user_role_id ";
			$sql .= "LEFT OUTER JOIN location l ";
			$sql .= "ON l.id = cu.location_id ";
			$sql .= "LEFT OUTER JOIN calendar_event_participation p2 ";
			$sql .= "LEFT OUTER JOIN calendar_event ev2 ";
			$sql .= "ON ev2.id = p2.calendar_event_id ";
			$sql .= "ON p2.client_user_id = cu.id, ";
			$sql .= "user u ";
			$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
			$sql .= "ON media.user_id = u.id ";
			$sql .= "WHERE ev.calendar_entry_id = " .  $p_calendar_entry_id . " ";
			$sql .= "AND ev.start = " . $p_start . " ";
			$sql .= "AND p.calendar_event_id = ev.id ";
			$sql .= "AND cu.id = p.client_user_id ";
			$sql .= $role_id_check;
			$sql .= $location_id_check;
			$sql .= "AND u.id = cu.user_id ";
			$sql .= $search_check;
			$sql .= "GROUP BY cu.id ";
			$sql .= "ORDER BY u.first_name, u.last_name, u.email ";
			$sql .= $limit;

			// echo "$sql<br />";

			$query = $this->db->query($sql);
			if ($query->num_rows() > 0) {
				$rows = $query->result();

				foreach ( $rows as $row ) {
					if ( is_null($name) ) {
						$name = $row->event_name;
						$start = (int) $p_start;
					}
					$entry = new stdClass();
					$entry->id = cast_int($row->id);
					$entry->first_name = $row->first_name;
					$entry->last_name = $row->last_name;
					$entry->email = $row->email;
					$entry->phone = $row->phone;
					$entry->role = $row->role;
					$entry->location = $row->location;
					$entry->media = new stdClass();
					$entry->media = format_media($row->media_id,$row->media_url);
					$entry->last_checkin = cast_int($row->last_checkin);
					array_push($entries, $entry);
					unset($entry);
				}

				$response->count = $count;
				$response->name = $name;
				$response->start = $start;
				$response->results = $entries;
				return $this->return_handler->results(200,"",$response);
			}
		}
		return $this->return_handler->results(204,"No Entry Found",$response);
	}
	
	// ==================================================================================================================
	// Get a list of checked in members for a class (Mobile Format)
	// ==================================================================================================================

	public function getForEntryStartFormatMobile( $p_entry_id, $p_start, $p_show_deleted = false ) {
		// get a list of checkins for an event
		$sql_checkins  = "SELECT u.id user_id, u.first_name first_name, u.last_name last_name, u.email email, ";
		$sql_checkins .= "m.id client_user_id, r.name role, m.note note, p.id participation_id, ";
		if ( $p_show_deleted ) {
			$sql_checkins .= "if(m.deleted IS NULL,0,1) deleted, ";
		}
		$sql_checkins .= " media.media_url media ";
		$sql_checkins .= "FROM calendar_event e, ";
		$sql_checkins .= "calendar_event_participation p, ";
		$sql_checkins .= "client_user m, ";
		$sql_checkins .= "client_user_role r, ";
		$sql_checkins .= "user u ";
		$sql_checkins .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$sql_checkins .= "ON media.user_id = u.id ";
		$sql_checkins .= "WHERE e.start = " . $p_start . " ";
		$sql_checkins .= "AND e.calendar_entry_id = " . $p_entry_id . " ";
		$sql_checkins .= "AND p.calendar_event_id = e.id ";
		$sql_checkins .= "AND m.id = p.client_user_id ";
		if ( !$p_show_deleted ) {
			$sql_checkins .= "AND m.deleted IS NULL ";
		}
		$sql_checkins .= "AND r.id = m.client_user_role_id ";
		$sql_checkins .= "AND u.id = m.user_id ";
		
		$sql  = "";
		// get the checkins with thier pending workout logs
		$sql .= "( ";
		$sql .= "SELECT c.*, ";
		$sql .= "p.id pending_id, p.library_workout_id pending_workout_id, ";
		$sql .= "null log_id, null log_workout_id ";
		$sql .= "FROM ";
		$sql .= "( " . $sql_checkins . ") c ";
		$sql .= "LEFT OUTER JOIN workout_log_pending p ";
		$sql .= "ON p.calendar_event_participation_id = c.participation_id";
		$sql .= ") ";
		// union
		$sql .= "UNION ";
		// get the checkins with thier workout logs
		$sql .= "( ";
		$sql .= "SELECT c.*, ";
		$sql .= "null pending_id, null pending_workout_id, ";
		$sql .= "l.id log_id, l.library_workout_id log_workout_id ";
		$sql .= "FROM ";
		$sql .= "( " . $sql_checkins . ") c, ";
		$sql .= "workout_log l ";
		$sql .= "WHERE l.calendar_event_participation_id = c.participation_id ";
		$sql .= ") ";
		// order by
		$sql .= "ORDER BY LOWER(first_name), LOWER(last_name), email ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(200,"No Entries Found",array());
		}
		$rows = $query->result();
		
		$i = -1;
		$entries = array();
		foreach ( $rows as $row ) {
			 //echo json_encode($row) . "<br /><br />\n\n";
			if ( $i < 0 || $entries[$i]->id != $row->client_user_id ) {
				++$i;	
				$entries[$i] = new stdClass();
				$entries[$i]->id = cast_int($row->client_user_id);
				$entries[$i]->participation_id = cast_int($row->participation_id);
				$entries[$i]->first_name = $row->first_name;
				$entries[$i]->last_name = $row->last_name;
				$entries[$i]->role = $row->role;
				$entries[$i]->media = $row->media;
				if ( empty($row->note) ) {
					$entries[$i]->note = false;
				} else {
					$entries[$i]->note = true;
				}
				
				// array to hold pending ids for user
				$entries[$i]->workout_log_pending = array();
                $workout_log_pending = &$entries[$i]->workout_log_pending;
                $p = -1;
                // array to hold work out log ids
                $entries[$i]->workout_log = array();
                $workout_log = &$entries[$i]->workout_log;
                $l = -1;
			}
			if ( $p < 0 || $workout_log_pending[$p]->id != $row->pending_id ) {
				if ($row->pending_id != null) {
				++$p;
				$workout_log_pending[$p] = new stdClass();
				$workout_log_pending[$p]->id = cast_int($row->pending_id);
				$workout_log_pending[$p]->workout_id = cast_int($row->pending_workout_id);
				}
			}
			if ( $l < 0 || $workout_log[$l]->id != $row->log_id ) {
				if ($row->log_id != null) {
				++$l;
				$workout_log[$l] = new stdClass();
				$workout_log[$l]->id = cast_int($row->log_id);
				$workout_log[$l]->workout_id = cast_int($row->log_workout_id);
				}
			}
		}

		$response = $entries;
		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// get a list of a member's how have checked into an event at a location at a given UTC date/time
	// ==================================================================================================================

	public function getForLocationStart($p_location_id,$p_start){
		// ------------------------------------------------------------------------------------------------------------
		//
		// Get the participants for the calendar event
		//
		// ------------------------------------------------------------------------------------------------------------
		// initialize the response data
		$count = 0;
		$name = null;
		$start = null;
		$entries = array();
		$response->count = $count;
		$response->name = $name;
		$response->start = $start;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for role_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$role_id_check = "";
		if ( isset($_GET['q_r']) && !empty($_GET['q_r']) && is_numeric($_GET['q_r']) ) {
			$role_id_check = "AND cu.client_user_role_id = " . $_GET['q_r'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for location_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$location_id_check = "";
		if ( isset($_GET['q_loc']) && !empty($_GET['q_loc']) && is_numeric($_GET['q_loc']) ) {
			$location_id_check = "AND cu.location_id = " . $_GET['q_loc'] . " ";
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
			$search_check .= ") LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
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
		// Get the total record count without paging limits
		// ---------------------------------------------------------------------------------------------------------
		
		$sql  = "SELECT count(cu.id) cnt ";
		$sql .= "FROM calendar cal, ";
		$sql .= "calendar_event ev, ";
		$sql .= "calendar_event_participation p, ";
		$sql .= "client_user cu, ";
		$sql .= "user u ";
		$sql .= "WHERE cal.location_id = " . $p_location_id . " AND cal.classroom_id IS NULL ";
		$sql .= "AND ev.calendar_id = cal.id ";
		$sql .= "AND ev.start = " . $p_start . " ";
		$sql .= "AND p.calendar_event_id = ev.id ";
		$sql .= "AND cu.id = p.client_user_id ";
		$sql .= $role_id_check;
		$sql .= $location_id_check;
		$sql .= "AND u.id = cu.user_id ";
		$sql .= $search_check;

		// echo "$sql<br />";
		
		$row = $this->db->query($sql)->row();
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
		if ( $count > 0 ) {
			// ---------------------------------------------------------------------------------------------------------
			// Get the record entries
			// ---------------------------------------------------------------------------------------------------------
			$sql  = "SELECT loc.name location_name, ";
			$sql .= "ev.name event_name, ev.start event_start, cu.id, u.first_name, u.last_name, u.email, u.phone, ";
			$sql .= "r.name role, ";
			$sql .= "l.name location, ";
			$sql .= "media.id media_id, media.media_url media_url ";
			$sql .= "FROM location loc, ";
			$sql .= "calendar cal, ";
			$sql .= "calendar_event ev, ";
			$sql .= "calendar_event_participation p, ";
			$sql .= "client_user cu ";
			$sql .= "LEFT OUTER JOIN client_user_role r ";
			$sql .= "ON r.id = cu.client_user_role_id ";
			$sql .= "LEFT OUTER JOIN location l ";
			$sql .= "ON l.id = cu.location_id, ";
			$sql .= "user u ";
			$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
			$sql .= "ON media.user_id = u.id ";
			$sql .= "WHERE loc.id = " . $p_location_id . " ";
			$sql .= "AND cal.location_id = loc.id AND cal.classroom_id IS NULL ";
			$sql .= "AND ev.calendar_id = cal.id ";
			$sql .= "AND ev.start = " . $p_start . " ";
			$sql .= "AND p.calendar_event_id = ev.id ";
			$sql .= "AND cu.id = p.client_user_id ";
			$sql .= $role_id_check;
			$sql .= $location_id_check;
			$sql .= "AND u.id = cu.user_id ";
			$sql .= $search_check;
			$sql .= "ORDER BY u.first_name, u.last_name, u.email ";
			$sql .= $limit;
	
			// echo "$sql<br />";
			
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0) {
				$rows = $query->result(); 
				
				foreach ( $rows as $row ) {
					if ( is_null($name) ) {
						$name = $row->location_name;
						$start = cast_int($p_start);
					}
					$entry = new stdClass();
					$entry->id = cast_int($row->id);
					$entry->first_name = $row->first_name;
					$entry->last_name = $row->last_name;
					$entry->email = $row->email;
					$entry->phone = $row->phone;
					$entry->role = $row->role;
					$entry->location = $row->location;
					$entry->media = format_media($row->media_id,$row->media_url);
					array_push($entries, $entry);
					unset($entry);
				}
				
				$response->count = $count;
				$response->name = $name;
				$response->start = $start;
				$response->results = $entries;	
				return $this->return_handler->results(200,"",$response);
			}
		}
		return $this->return_handler->results(204,"No Entry Found",$response);
	}

	// ==================================================================================================================
	// get a list of a member's checkins
	// ==================================================================================================================

	public function getForClientUser( $p_client_user_id ) {
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
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "AND concat(";
			$search_check .= "if(isnull(e.name),'',concat(' ',e.name)),";
			$search_check .= "if(isnull(l.name),'',concat(' ',l.name))";
			$search_check .= ") LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
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
		$sql  = "SELECT count(p.id) cnt ";
		$sql .= "FROM calendar_event_participation p, ";
		$sql .= "calendar_event e, ";
		$sql .= "calendar cal, ";
		$sql .= "location l ";
		$sql .= "WHERE p.client_user_id = " . $p_client_user_id . " ";
		$sql .= "AND e.id = p.calendar_event_id ";
		$sql .= "AND cal.id = e.calendar_id ";
		$sql .= "AND l.id = cal.location_id ";
		$sql .= $search_check;

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ( $query->num_rows() > 0 ) {
			$row = $query->row();
			$count = $row->cnt;
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the record entries
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT p.id calendar_event_participation_id, ";
		$sql .= "e.id calendar_event_id, e.name calendar_event_name, e.start calendar_event_start, e.duration calendar_event_duration, ";
		$sql .= "l.id location_id, l.name location_name ";
		$sql .= "FROM calendar_event_participation p, ";
		$sql .= "calendar_event e, ";
		$sql .= "calendar cal, ";
		$sql .= "location l ";
		$sql .= "WHERE p.client_user_id = " . $p_client_user_id . " ";
		$sql .= "AND e.id = p.calendar_event_id ";
		$sql .= "AND cal.id = e.calendar_id ";
		$sql .= "AND l.id = cal.location_id ";
		$sql .= $search_check;
		$sql .= "ORDER BY e.start DESC ";
		$sql .= $limit;

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		
		if ( $query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();
		
		foreach ( $rows as $row ) {
			
			// echo "row : "; print_r($row); echo "<br />";
			
			// initialize the participation entry's data
			$entry = new stdClass();
			$entry->id = cast_int($row->calendar_event_participation_id);
			$entry->event = new stdClass();
			$entry->event->id = cast_int($row->calendar_event_id);
			$entry->event->name = $row->calendar_event_name;
			$entry->event->start = cast_int($row->calendar_event_start);
			$entry->event->duration = cast_int($row->calendar_event_duration);
			$entry->event->workout = array();
			$entry->event->location = new stdClass();
			$entry->event->location->id = cast_int($row->location_id);
			$entry->event->location->name = $row->location_name;
			array_push($entries,$entry);
			unset($entry);
		}

		// echo "entries: "; print_r($entries); echo "<br />";
		
		$response->count = $count;
		$response->results = $entries;
		return $this->return_handler->results(200,"",$response);
		
	}

	// ==================================================================================================================
	// Get a list of checked checkins for a user for a day
	// ==================================================================================================================

	public function getForUserDate($p_user_id,$p_date) {
		$entries = array();
		// get the calendars (and client_user IDs at the calendar) for the user
		$return = $this->perform('action_calendar->getForUser',$p_user_id,$use_alias=false);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			$calendars = $return['response'];
			// echo "action_calendar->getForUser:"; print_r($calendars);
			foreach( $calendars as $calendar ) {
				// echo "calendar:"; print_r($calendar); echo "<br /><br />";
				// Set the server's default timezone
				date_default_timezone_set($calendar->timezone);
				// get the UTC date range for the date based on the calendar
				$start = mktime(0,0,0,substr($p_date,4,2),substr($p_date,6,2),substr($p_date,0,4));
				$end = mktime(0,0,-1,substr($p_date,4,2),substr($p_date,6,2) + 1,substr($p_date,0,4));
				$date = date('Ymd',$start);
				// echo "calendar:" . $calendar->id . " date:$date start:$start end:$end<br/>\n";
				// does the calendar have any participaiton on the date
				$return = $this->perform('this->getForCalendarClientUserDateRange',$calendar->id,$calendar->client_user_id,$p_date,$start,$end);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				// echo "getForCalendarClientUserDateRange:"; print_r($return); echo "<br /><br />";
				
				$entries = array_merge($entries,$return['response']);
			}
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	public function getForCalendarClientUserDateRange($p_calendar_id,$p_client_user_id,$p_date,$p_start,$p_end) {
		// echo "start:$p_start end:$p_end<br />\n";
		//
		// get the base query that returns a client user's participation in events with log_result for a given date
		//
		$sql_p  = "SELECT client.id client_id, client.name client_name, ";
		$sql_p .= "location.id location_id, location.name location_name, location.timezone location_timezone, ";
		$sql_p .= "event.id event_id, event.name event_name, event.calendar_entry_id entry_id, event.start event_start, ";
		$sql_p .= "p.id participation_id, p.client_user_id client_user_id, p.start_emotional_level_id start_emotional_id, p.end_emotional_level_id end_emotional_id, p.note note, ";
		$sql_p .= "p.created, p.created_by_app, p.created_by_user_id, ";
		$sql_p .= "user.first_name, user.last_name ";
		$sql_p .= "FROM ";
		$sql_p .= "server, ";
		$sql_p .= "calendar_event_participation p ";
		$sql_p .= "LEFT OUTER JOIN user ";
		$sql_p .= "ON user.id = p.created_by_user_id, ";
		$sql_p .= "calendar_event event, ";
		$sql_p .= "calendar calendar, ";
		$sql_p .= "location location, ";
		$sql_p .= "client client ";
		$sql_p .= "WHERE p.client_user_id = " . $p_client_user_id . " ";
		$sql_p .= "AND event.id = p.calendar_event_id ";
		$sql_p .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(event.start),server.timezone,calendar.timezone),'%Y%m%d') = '" . $p_date . "' ";
		$sql_p .= "AND event.calendar_id = " . $p_calendar_id . " ";
		$sql_p .= "AND event.log_participant ";
		$sql_p .= "AND calendar.id = event.calendar_id ";
		$sql_p .= "AND calendar.classroom_id IS NULL ";
		$sql_p .= "AND calendar.location_id IS NOT NULL AND location.id = calendar.location_id ";
		$sql_p .= "AND calendar.client_id IS NOT NULL AND client.id = calendar.client_id ";
		
		// echo "sql_p: $sql_p <br /><br />";
		
		// the main select
		$sql  = "";
		//
		// Get the participation and all of its pending workout logs
		$sql .= "( ";
		$sql .= "SELECT p.*, ";
		$sql .= "pending.id pending_id, pending.library_workout_id pending_workout_id, ";
		$sql .= "null log_id, null log_workout_id, ";
		$sql .= "w.name workout_name ";
		$sql .= "FROM ";
		$sql .=  "(" . $sql_p . ") p ";
		$sql .= "LEFT OUTER JOIN workout_log_pending pending ";
		$sql .= "LEFT OUTER JOIN library_workout w ";
		$sql .= "ON w.id = pending.library_workout_id ";
		$sql .= "ON pending.calendar_event_participation_id = p.participation_id ";
		$sql .= ") ";
		//
		// UNION
		$sql .= "UNION ";
		//
		// get the participation and all of its workout logs
		$sql .= "( ";
		$sql .= "SELECT p.*, ";
		$sql .= "null pending_id, null pending_workout_id, ";
		$sql .= "log.id log_id, log.library_workout_id log_workout_id, ";
		$sql .= "w.name workout_name ";
		$sql .= "FROM ";
		$sql .=  "(" . $sql_p . ") p ";
		$sql .= "LEFT OUTER JOIN workout_log log ";
		$sql .= "LEFT OUTER JOIN library_workout w ";
		$sql .= "ON w.id = log.library_workout_id ";
		$sql .= "ON log.calendar_event_participation_id = p.participation_id ";
		$sql .= ") ";
		//
		// order the complete results
		$sql .= "ORDER BY event_start, participation_id, pending_id, log_id ";
		

		// echo "$sql<br /><br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$entries = array();
		
		$participation = new stdClass();
		$participation->id = null;
		foreach( $rows as $row ) {
			// echo "row:"; print_r($row); echo "<br /><br />\n\n";
			
			if ( $row->participation_id != $participation->id ) {
				if ( !is_null($participation->id) ) {
					array_push($entries,$participation);
					unset($participation);
					$participation = new stdClass();
				}
				$participation->id = cast_int($row->participation_id);
				$participation->client_user_id = cast_int($row->client_user_id);
				$participation->start_emotional_id = cast_int($row->start_emotional_id);
				$participation->end_emotional_id = cast_int($row->end_emotional_id);
				$participation->note = $row->note;
				
				$participation->event = new stdClass();
				$participation->event->id = cast_int($row->event_id);
				$participation->event->participation_id = cast_int($row->participation_id);
				$participation->event->name = $row->event_name;
				$participation->event->entry_id = $row->entry_id;
				$participation->event->start = cast_int($row->event_start);
				$participation->event->workout_log_pending = array();
				$participation->event->workout_log = array();
				$participation->client = new stdClass();
				$participation->location = new stdClass();
				$participation->created = format_created($row->created,$row->created_by_app,$row->created_by_user_id,$row->first_name,$row->last_name);
			}
			if ( !is_null($row->client_id) ) {
				$participation->client->id = cast_int($row->client_id);
				$participation->client->name = $row->client_name;
			}
			if ( !is_null($row->location_id) ) {
				$participation->location->id = cast_int($row->location_id);
				$participation->location->name = $row->location_name;
				$participation->location->timezone = $row->location_timezone;
				$participation->location->timezone_offset = format_timezone_offset($row->location_timezone);
			}
			if ( !is_null($row->pending_id) ) {
				$pendeing = new stdClass();
				$pending->id = cast_int($row->pending_id);
				$pending->workout_id = cast_int($row->pending_workout_id);
				array_push($participation->event->workout_log_pending,$pending);
				unset($pending);
			}
			if ( !is_null($row->log_id) ) {
				$log = new stdClass();
				$log->id = cast_int($row->log_id);
				$log->workout_id = cast_int($row->log_workout_id);
				array_push($participation->event->workout_log,$log);
				unset($log);
			}
		}
		
		if ( !is_null($participation->id) ) {
			array_push($entries,$participation);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// get the emotional levels for a participation id
	// ==================================================================================================================

	public function getEmotionalForID( $p_calendar_event_participation_id ) {
		
		$sql  = "SELECT p.start_emotional_level_id, p.end_emotional_level_id ";
		$sql .= "FROM calendar_event_participation p ";
		$sql .= "WHERE p.id = " . $p_calendar_event_participation_id . " ";
		
		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(202,"No Entries found.",new stdClass());
		}
		$row = $query->row();

		$entry = new stdClass();
		$entry->start_emotional_id = cast_int($row->start_emotional_level_id);
		$entry->end_emotional_id = cast_int($row->end_emotional_level_id);
		
		return $this->return_handler->results(200,"",$entry);
	}

	// ==================================================================================================================
	// Checkin an Existing Member
	// ==================================================================================================================

	public function checkinExistingMember($p_calendar_entry_id,$p_start,$p_client_user_id) {
		// echo "entry: $p_calendar_entry_id start: $p_start user: $p_client_user_id<br />";
		// --------------------------------------------------------------------------------------------------------------
		// initialize the response
		// --------------------------------------------------------------------------------------------------------------
		$response = new stdClass();
		$response->id = null;
		// -------------------------------------------------------------------------------------------------------------
		// Make sure $p_client_user_id is a single Client User Id
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_client_user->getForId',$p_client_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"No a valid Client User",$response);
		}
		$client_user = $return['response'];
		if ( !is_null($client_user->deleted) ) {
			return $this->return_handler->results(400,"The Client User was inactivated",$response);
		}
		// -------------------------------------------------------------------------------------------------------------
		// Get the Calendar Event ID.
		// Create the Calendar Event if it does not exist.
		// -------------------------------------------------------------------------------------------------------------
		$fields = new stdClass();
		$fields->key = $p_calendar_entry_id . "_" . $p_start;
		$return = $this->perform('action_calendar_event->create',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$calendar_event_id = $return['response']->id;
		
		unset($fields);
		// -------------------------------------------------------------------------------------------------------------
		// Create the checkin
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->create',$calendar_event_id,$p_client_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$calendar_event_participant_id = $return['response']->id;
		
		// -------------------------------------------------------------------------------------------------------------
		// Put the To the User Notification queue
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('cli_user_notification->queueCheckin',$calendar_event_participant_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$response->id = $calendar_event_participant_id;
		return $this->return_handler->results(201,"Checked In",$response);
	}

	public function create($p_calendar_event_id,$p_client_user_id) {
		// -------------------------------------------------------------------------------------------------------------
		// Get the Calendar Event Participation entry if it exists
		// -------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['calendar_event_id'] = $p_calendar_event_id;
		$key['client_user_id'] = $p_client_user_id;
		$return = $this->perform('table_workoutdb_calendar_event_participation->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			$response = new stdClass();
			$response->id = $return['response'][0]->id;
			return $this->return_handler->results(201,"Checked In",$response);
		}
		// -------------------------------------------------------------------------------------------------------------
		// Create the Calendar Event Participation.
		// -------------------------------------------------------------------------------------------------------------
		$fields = new stdClass();
		$fields->calendar_event_id = $p_calendar_event_id;
		$fields->client_user_id = $p_client_user_id;
		$return = $this->perform('table_workoutdb_calendar_event_participation->insert',$fields);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$calendar_event_participant_id = $return['response']->id;
		
		$return_create = $return;
		unset($fields);
		// -------------------------------------------------------------------------------------------------------------
		// If any workouts are linked to the event, link them to the participation as pending workout logs
		// -------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['calendar_event_id'] = $p_calendar_event_id;
		$return = $this->perform('table_workoutdb_calendar_event_library_workout->getForAndKeys',$key);
		$xrefs = $return['response'];
		foreach( $xrefs as $xref ) {
			$fields = new stdClass;
			$fields->calendar_event_participation_id = $calendar_event_participant_id;
			$fields->library_workout_id = $xref->library_workout_id;
			$return = $this->perform('table_workoutdb_workout_log_pending->insert',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}

		return $return_create;
	}

	// ==================================================================================================================
	// Checkin a New Member
	// ==================================================================================================================

	public function checkinNewMember($p_fields) {
		$fields = (object) $p_fields;
		// --------------------------------------------------------------------------------------------------------------
		// initialize the response
		// --------------------------------------------------------------------------------------------------------------
		$response = new stdClass();
		$response->id = null;
		// -------------------------------------------------------------------------------------------------------------
		// Are all the Mandatory fields present
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($fields,'key') || is_null($fields->key) || empty($fields->key) || !is_string($fields->key) ) {
			return $this->return_handler->results(400,"key is missing or invalid type.",$response);
		}
		if ( !property_exists($fields,'role_id') || is_null($fields->role_id) || empty($fields->role_id) || !is_numeric($fields->role_id) ) {
			return $this->return_handler->results(400,"role_id is missing or invalid type.",$response);
		}
		if ( !property_exists($fields,'first_name') || is_null($fields->first_name) || empty($fields->first_name) || !is_string($fields->first_name) ) {
			return $this->return_handler->results(400,"first_name is missing or invalid type.",$response);
		}
		if ( !property_exists($fields,'last_name') || is_null($fields->last_name) || empty($fields->last_name) || !is_string($fields->last_name) ) {
			return $this->return_handler->results(400,"last_name is missing or invalid type.",$response);
		}
		if ( !property_exists($fields,'email') || is_null($fields->email) || empty($fields->email) || !is_string($fields->email) ) {
			return $this->return_handler->results(400,"email is missing or invalid type.",$response);
		}
		// -------------------------------------------------------------------------------------------------------------
		// Brake the key up into calendar_entry_id and event start utc date/time
		// -------------------------------------------------------------------------------------------------------------
		$key = explode('_',$fields->key);
		$calendar_entry_id = $key[0];
		$start = $key[1];
		// -------------------------------------------------------------------------------------------------------------
		// Get the Client Id
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientForEntry',$calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			return $this->return_handler->results(400,"Invalid Client",$response);
		}
		$client_id = $return['response']->id;
		// -------------------------------------------------------------------------------------------------------------
		// Get the User ID.
		// Create the User if it does not exist.
		// -------------------------------------------------------------------------------------------------------------
		$member = clone $fields;
		$member->client_id = $client_id;
		$return = $this->perform('action_member->create',$member);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$client_user_id = $return['response']->id;

		unset($member);
		// -------------------------------------------------------------------------------------------------------------
		// Create the Participant if it does not exist and return the New Participant's ID
		// Return the Existing Participant's ID if one exists already
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->checkinExistingMember',$calendar_entry_id,$start,$client_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$participant_id = $return['response']->id;

		$response->id = $client_user_id;
		$response->participation_id = $participant_id;
		return $this->return_handler->results(201,"Entry Created",$response);
	}

	public function getClientForEntry($p_calendar_entry_id) {
		//
		// initialize returned response
		$response = new stdClass();
		$response->id = null;
		//
		// Get the client_id of the event_id
		$sql  = "SELECT c.client_id id ";
		$sql .= "FROM calendar_entry e, ";
		$sql .= "calendar c ";
		$sql .= "WHERE e.id = " . $p_calendar_entry_id . " ";
		$sql .= "AND c.id = e.calendar_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$row = $query->row();

			$response->id = $row->id;
			return $this->return_handler->results(200,"",$response);
		} else {
			return $this->return_handler->results(204,"",$response);
		}
	}

	// ==================================================================================================================
	// Update the emotional level (before, after, or both)
	// ==================================================================================================================

	public function updateCheckinEmotionalLevel( $data ) {
		// ------------------------------------------------------------------------------------------------------------
		// Was ID passed in
		// ------------------------------------------------------------------------------------------------------------
		if ( !property_exists($data,'id') || is_null($data->id) || empty($data->id) || !is_numeric($data->id) ) {
			return $this->return_handler->results(400,"ID is mandatory",new stdClass());
		}
		// ------------------------------------------------------------------------------------------------------------
		// Was start and/or end emotional level id passed in
		// ------------------------------------------------------------------------------------------------------------
		if ( !property_exists($data,'start_emotional_id') && !property_exists($data,'end_emotional_id') ) {
			return $this->return_handler->results(400,"start and/or end emotional level must be provided.",new stdClass());
		}
		// ------------------------------------------------------------------------------------------------------------
		// Create the update object
		// ------------------------------------------------------------------------------------------------------------
		$update = new stdClass();
		$update->id = $data->id;
		if ( property_exists($data,'start_emotional_id') ) {
			$update->start_emotional_id = $data->start_emotional_id;
		}
		if ( property_exists($data,'end_emotional_id') ) {
			$update->end_emotional_id = $data->end_emotional_id;
		}
		// ------------------------------------------------------------------------------------------------------------
		// update the Checkin
		// ------------------------------------------------------------------------------------------------------------
		return $this->perform('table_workoutdb_calendar_event_participation->update',$data);
	}

	// ==================================================================================================================
	// Update the emotional level (before, after, or both)
	// ==================================================================================================================

	public function updateCheckinNote( $data ) {
		// ------------------------------------------------------------------------------------------------------------
		// Was ID passed in
		// ------------------------------------------------------------------------------------------------------------
		if ( !property_exists($data,'id') || is_null($data->id) || empty($data->id) || !is_numeric($data->id) ) {
			return $this->return_handler->results(400,"ID is mandatory",new stdClass());
		}
		if ( !property_exists($data,'note') || is_null($data->note) ) {
			return $this->return_handler->results(400,"Note is mandatory",new stdClass());
		}
		// ------------------------------------------------------------------------------------------------------------
		// Create the update object
		// ------------------------------------------------------------------------------------------------------------
		$update = new stdClass();
		$update->id = $data->id;
		$update->note = $data->note;
		// ------------------------------------------------------------------------------------------------------------
		// update the Checkin
		// ------------------------------------------------------------------------------------------------------------
		return $this->perform('table_workoutdb_calendar_event_participation->update',$data);
	}

	// ==================================================================================================================
	// Delete a Check-in
	// ==================================================================================================================

	public function deleteCheckins($p_calendar_entry_id,$p_start,$p_client_users = array() ) {
		// -------------------------------------------------------------------------------------------------------------
		// Get the event
		// ------------------------------------------------------------------------------------------------------------
		$key = array();
		$key['calendar_entry_id'] = $p_calendar_entry_id;
		$key['start'] = $p_start;
		$return = $this->perform('table_workoutdb_calendar_event->getForAndKeys',$key);
		$calendar_event_id = $return['response'][0]->id;
		// -------------------------------------------------------------------------------------------------------------
		// Delete a list of client_users from an event
		// -------------------------------------------------------------------------------------------------------------
		if ( count($p_client_users) == 0 || is_null($p_client_users[0]) || empty($p_client_users[0]) || !is_numeric($p_client_users[0]) ) {
			return $this->return_handler->results(400,"Invalid Client User list provided",new stdClass());
		}
		foreach ( $p_client_users as $client_user_id ) {
			$return = $this->perform('this->deleteClientUserFromEvent',$calendar_event_id,$client_user_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}

		return $this->return_handler->results(202,"Members removed from class",new stdClass());
	}

	public function deleteClientUserFromEvent( $p_calendar_event_id,$p_client_user_id ) {
		// -------------------------------------------------------------------------------------------------------------
		// If the Client User's Participation id for the Event
		// -------------------------------------------------------------------------------------------------------------
		$return = $this->perform("this->getDeletableForClientUserEvent",$p_calendar_event_id,$p_client_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// no participation
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(203,"No participation found",new stdClass());
		}
		
		$checkin = $return['response'];
		foreach ( $checkin as $participation ) {
			if ( $participation->deletable ) {
				// get the list of workout_log_pending entries for the calendar_event_participation_id
				$key = array();
				$key['calendar_event_participation_id'] = $participation->id;
				$return = $this->perform('table_workoutdb_workout_log_pending->getForAndKeys',$key);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				$workout_log_pending = $return['response'];
				// delete all workout_log_pending entries for the calendar_event_participation
				foreach( $workout_log_pending as $pending ) {
					$return = $this->perform('table_workoutdb_workout_log_pending->delete',$pending->id);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
				}
				// delete the calendar_event_participation entry
				$return = $this->perform('table_workoutdb_calendar_event_participation->delete',$participation->id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}

		return $this->return_handler->results(203,"Event participant deleted",new stdClass());
	}
	
	public function getDeletableForClientUserEvent($p_calendar_event_id,$p_client_user_id) {
		// get the client_user's participation id for the event if it exists.
		// if it exsts, does the participation have workout logging against it?
		$sql  = "";
		$sql .= "SELECT p.id, if(l.id IS NULL,1,0) deletable ";
		$sql .= "FROM calendar_event_participation p ";
		$sql .= "LEFT OUTER JOIN workout_log l ";
		$sql .= "ON l.calendar_event_participation_id = p.id ";
		$sql .= "WHERE p.calendar_event_id = " . $p_calendar_event_id . " ";
		$sql .= "AND p.client_user_id = ". $p_client_user_id . " ";
		$sql .= "limit 1 ";
		
		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach ( $rows as $row ) {
			$entry = new stdClass();
			$entry->id = (int) $row->id;
			$entry->deletable = (boolean) $row->deletable;
			array_push($entries,$entry);
			unset($entry);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}
}