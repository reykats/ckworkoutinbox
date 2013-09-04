<?php

class action_leaderboard extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// =======================================================================================================================================
	// Get the list of client's and their workouts that have logs for a user and date
	// =======================================================================================================================================
	
	public function getClientWorkoutsWithLogsForUserDate($p_user_id,$p_start) {
		
		$sql  = "SELECT ";
		$sql .= "client.id 'client.id', client.name 'client.name', ";
		$sql .= "library_workout.id 'library_workout.id', library_workout.name 'library_workout.name', ";
		$sql .= "library_workout.library_workout_recording_type_id `library_workout.library_workout_recording_type_id` ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "client_user, ";
		$sql .= "client, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation, ";
		$sql .= "workout_log, ";
		$sql .= "library_workout ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " AND client_user.deleted IS NULL ";
		$sql .= "AND client.id = client_user.client_id ";
		$sql .= "AND calendar.client_id = client_user.client_id ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') = '" . $p_start . "' ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "AND workout_log.calendar_event_participation_id = calendar_event_participation.id ";
		$sql .= "AND workout_log.library_workout_recording_type_id IS NOT NULL ";
		$sql .= "AND library_workout.id = workout_log.library_workout_id ";
		$sql .= "GROUP BY client.id, library_workout.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$client = array();
		$c = -1;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// print_r($row); echo "<br />";
			
			if ( $c < 0 || $client[$c]->id != $row->client->id ) {
				++$c;
				$client[$c]->id = $row->client->id;
				$client[$c]->name = $row->client->name;
				// initialize the class array
				$client[$c]->workout = array();
				$workout = &$client[$c]->workout;
				$w = -1;
			}
			if ( $w < 0 || $workout[$w]->id != $row->library_workout->id ) {
				++$w;
				$workout[$w]->id = $row->library_workout->id;
				$workout[$w]->name = $row->library_workout->name;
				$workout[$w]->workout_recording_type_id = $row->library_workout->library_workout_recording_type_id;
			}
		}
		
		return $this->return_handler->results(200,"",$client);
	}

	// ===================================================================================================================================================
	// Get a list of Workouts assigned to log_result Events for a Client on a Date
	// ===================================================================================================================================================

	public function getWorkoutsForClientDate( $p_client_id, $p_ccyymmdd ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "library_workout.name 'library_workout.name', library_workout.id 'library_workout.id', ";
		$sql .= "library_workout.library_workout_recording_type_id `library_workout.library_workout_recording_type_id` ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "client, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation, ";
		$sql .= "workout_log, ";
		$sql .= "library_workout ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND calendar.client_id = client.id ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') = '" . $p_ccyymmdd . "' ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "AND workout_log.calendar_event_participation_id = calendar_event_participation.id ";
		$sql .= "AND library_workout.id = workout_log.library_workout_id ";
		$sql .= "AND library_workout.library_workout_recording_type_id IS NOT NULL ";
		$sql .= "GROUP BY library_workout.name, library_workout.id";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach( $rows as $row ) {
			
			// print_r($row); echo "<br />";
			
			// store the entry info
			$entry = new stdClass();
			$entry->id = cast_int($row->{'library_workout.id'});
			$entry->name = $row->{'library_workout.name'};
			$entry->workout_recording_type_id = cast_int($row->{'library_workout.library_workout_recording_type_id'});
			// put entry into entries
			array_push($entries,$entry);
			// clear the entry from memory
			unset($entry);
		}
			
		return $this->return_handler->results(200,"",$entries);
	}

	// ===================================================================================================================================================
	// Get a list of locations and their classes for a client on a given day for a workout scheduled
	// ===================================================================================================================================================

	public function getLocationScheduleForClientDateWorkout( $p_client_id, $p_ccyymmdd, $p_library_workout_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "location.id `location.id`, location.name `location.name`, ";
		$sql .= "location.timezone `location.timezone`, ";
		$sql .= "calendar_event.id `calendar_event.id`, calendar_event.name `calendar_event.name`, calendar_event.start `calendar_event.start` ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "client, ";
		$sql .= "calendar ";
		$sql .= "LEFT OUTER JOIN location ";
		$sql .= "ON location.id = calendar.location_id, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation, ";
		$sql .= "workout_log ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND calendar.client_id = client.id ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') = '" . $p_ccyymmdd . "' ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "AND workout_log.calendar_event_participation_id = calendar_event_participation.id ";
		$sql .= "AND workout_log.library_workout_id = " . $p_library_workout_id . " ";
		$sql .= "GROUP BY location.name, calendar_event.name, calendar_event.start, calendar_event.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$location = array();
		$l = -1;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// print_r($row); echo "<br />";
			
			if ( $l < 0 || $location[$l]->id != $row->location->id ) {
				++$l;
				$location[$l]->id = $row->location->id;
				$location[$l]->name = $row->location->name;
				$location[$l]->timezone = $row->location->timezone;
				// get the timezone_offset for the calendar's timezone
				$location[$l]->timezone_offset = format_timezone_offset($row->location->timezone);
				// initialize the class array
				$location[$l]->class = array();
				$class = &$location[$l]->class;
				$c = -1;
			}
			if ( $c < 0 || $class[$c]->id != $row->calendar_event->id ) {
				++$c;
				$class[$c]->id = $row->calendar_event->id;
				$class[$c]->name = $row->calendar_event->name;
				$class[$c]->start = $row->calendar_event->start;
			}
		}
			
		return $this->return_handler->results(200,"",$location);
	}

	// ===================================================================================================================================================
	// Get the Ranking for Workout for a client on a date
	// ===================================================================================================================================================
	
	public function getRankingForClientDateWorkout( $p_client_id, $p_ccyymmdd, $p_workout_id ) {
		// ---------------------------------------------------------------------------------------------------------
		// get the workout
		// ---------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_library_workout->getForId',$p_workout_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$workout = clone $return['response'];
		// ---------------------------------------------------------------------------------------------------------
		// get the logged in user's client_user_id for the client
		// ---------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientUserForClientSession',$p_client_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"This user does not have access to this Client's data.",new stdClass());
		}
		$current_client_user_id = $return['response']->client_user_id;
		// ---------------------------------------------------------------------------------------------------------
		// Get the leader board list
		// ---------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getResultsForClientDateWorkout',$p_client_id, $p_ccyymmdd, $p_workout_id,$workout->library_workout_recording_type_id,$current_client_user_id);
		return $return;
	}

	public function getClientUserForClientSession( $p_client_id ) {
		// get the user's session data
		$user = $this->session->userdata('user');
		
		// get the client_user for the client
		$client_user_id = null;
		foreach ( $user->client as $client ) {
			if ( $client->id == $p_client_id ) {
				$client_user_id = $client->client_user_id;
			}
		}

		if ( !is_null($client_user_id) ) {		
			$response = new stdClass();
			$response->client_user_id = $client_user_id;
			return $this->return_handler->results(200,"",$response);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
	}
	
	public function getResultsForClientDateWorkout( $p_client_id, $p_ccyymmdd, $p_workout_id, $p_library_workout_recording_type_id, $p_client_user_id ) {
		// ---------------------------------------------------------------------------------------------------------
		// Filter the result set to be ranked
		// ---------------------------------------------------------------------------------------------------------
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the calendar table's location_id = $_GET['q_loc']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_calendar_location_id = "";
		if ( isset($_GET['q_loc']) && !empty($_GET['q_loc']) && is_numeric($_GET['q_loc']) ) {
			$and_calendar_location_id = "AND calendar.location_id = " . $_GET['q_loc'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the calendar_event table's id = $_GET['q_ev']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_calendar_event_id = "";
		if ( isset($_GET['q_ev']) && !empty($_GET['q_ev']) && is_numeric($_GET['q_ev']) ) {
			$and_calendar_event_id = "AND calendar_event.id = " . $_GET['q_ev'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the user table's anonymous_on_leaderboard = FALSE
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_user_not_anonymous = "";
		if ( isset($_GET['q_a']) && !empty($_GET['q_a']) && !filter_var(strtolower($_GET['q_a']), FILTER_VALIDATE_BOOLEAN) ) {
			$and_user_not_anonymous = "AND !user.anonymous_on_leaderboard ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the user table's gender = $_GET['q_gen']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_user_gender = "";
		if ( isset($_GET['q_gen']) && !empty($_GET['q_gen']) && ($_GET['q_gen'] == "M" || $_GET['q_gen'] == "F") ) {
			$and_user_gender = "AND user.gender = '" . $_GET['q_gen'] . "' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the user table's birthday BETWEEN $_GET['q_b_s'] AND $_GET['q_b_e']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_user_birthday = "";
		$and_user_age = "";
		if ( isset($_GET['q_b_s']) && !empty($_GET['q_b_s']) && is_numeric($_GET['q_b_s']) &&
		     isset($_GET['q_b_e']) && !empty($_GET['q_b_e']) && is_numeric($_GET['q_b_e']) ) {
			$and_user_birthday = "AND user.birthday BETWEEN " . $_GET['q_b_s'] . " AND " . $_GET['q_b_e'] . " ";
		} else if ( isset($_GET['q_a_s']) && !empty($_GET['q_a_s']) && is_numeric($_GET['q_a_s']) &&
		            isset($_GET['q_a_e']) && !empty($_GET['q_a_e']) && is_numeric($_GET['q_a_e']) ) {
		    // You can only create UTC Date/Time from 1900/1/1 through 2037/12/31
		    if ( $_GET['q_a_s'] > 110 ) {
		    	$start_date = strtotime("-110 year",time());
		    } else {
		    	if ( $_GET['q_a_s'] == 0 ) {
		    		$start_date = time();
		    	} else {
		    		$start_date = strtotime("-" . $_GET['q_a_s'] . " year",time());
				}
		    }
		    // You can only create UTC Date/Time from 1900/1/1 through 2037/12/31
		    if ( $_GET['q_a_e'] > 110 ) {
		    	$end_date = strtotime("-110 year",time());
		    } else {
		    	if ( $_GET['q_a_e'] == 0 ) {
		    		$end_date = time();
		    	} else {
		    		$end_date = strtotime("-" . $_GET['q_a_e'] . " year",time());
				}
		    }
			
			// echo "start:$start_date end:$end_date start:" . $_GET['q_a_s'] . " end:" . $_GET['q_a_e'] . "<br />\n";
			// echo "start:" . date('Ymd',$start_date) . " end:" . date('Ymd',$end_date) . "<br />\n";
			
		    $and_user_age  = "AND user.birthday BETWEEN " . $end_date . " AND " . $start_date . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the workout_log table's height BETWEEN $_GET['q_h_s'] AND $_GET['q_h_e']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_workout_log_height = "";
		if ( isset($_GET['q_h_s']) && !is_null($_GET['q_h_s']) && (!empty($_GET['q_h_s']) || $_GET['q_h_s'] == "0") && is_numeric($_GET['q_h_s']) &&
		     isset($_GET['q_h_e']) && !is_null($_GET['q_h_e']) && (!empty($_GET['q_h_e']) || $_GET['q_h_e'] == "0") && is_numeric($_GET['q_h_e']) ) {
			$and_workout_log_height .= "AND ";
			$and_workout_log_height .= "(height_uom.english_conversion * workout_log.height) ";
			$and_workout_log_height .= "BETWEEN " . $_GET['q_h_s'] . " AND " . $_GET['q_h_e'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the workout_log table's height BETWEEN $_GET['q_w_s'] AND $_GET['q_w_e']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_workout_log_weight = "";
		if ( isset($_GET['q_w_s']) && !is_null($_GET['q_w_s']) && (!empty($_GET['q_w_s']) || $_GET['q_w_s'] == "0") && is_numeric($_GET['q_w_s']) &&
		     isset($_GET['q_w_e']) && !is_null($_GET['q_w_e']) && (!empty($_GET['q_w_e']) || $_GET['q_w_e'] == "0") && is_numeric($_GET['q_w_e']) ) {
			$and_workout_log_weight .= "AND ";
			$and_workout_log_weight .= "(weight_uom.english_conversion * workout_log.weight) "; // in pounds
			$and_workout_log_weight .= "BETWEEN " . $_GET['q_w_s'] . " AND " . $_GET['q_w_e'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the workout_log table's workout_modified = !$_GET['q_rx']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_workout_log_workout_modified = "";
		if ( isset($_GET['q_rx']) && !is_null($_GET['q_rx']) && !empty($_GET['q_rx']) ) {
			if ( filter_var(strtolower($_GET['q_rx']), FILTER_VALIDATE_BOOLEAN) ) {
				$and_workout_log_workout_modified .= "AND NOT workout_log.workout_modified ";
			} else {
				$and_workout_log_workout_modified .= "AND workout_log.workout_modified ";
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the workout_log table's workout_log_completed = $_GET['q_cmp']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_workout_log_workout_log_completed = "";
		if ( isset($_GET['q_cmp']) && !is_null($_GET['q_cmp']) && !empty($_GET['q_cmp']) ) {
			if ( filter_var(strtolower($_GET['q_cmp']), FILTER_VALIDATE_BOOLEAN) ) {
				$and_workout_log_workout_log_completed .= "AND workout_log.workout_log_completed ";
			} else {
				$and_workout_log_workout_log_completed .= "AND NOT workout_log.workout_log_completed ";
			}
		}
		
		// ---------------------------------------------------------------------------------------------------------
		// filter the Ranked result set
		// ---------------------------------------------------------------------------------------------------------
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// where the user table's name LIKE !$_GET['q_n']
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$where_user_name = "";
		$and_user_name = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			// concatinate varchar field values
			//
			// for list, user table is in subquery named result
			$concat  = "concat(";
			$concat .= "if(isnull(result.`user.first_name`),'',concat(' ',result.`user.first_name`))";
			$concat .= ",";
			$concat .= "if(isnull(result.`user.last_name`),'',concat(' ',result.`user.last_name`))";
			$concat .= ")";
			$where_user_name  = "WHERE " . $concat . " LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
			// for count, user table is in main query
			$concat  = "concat(";
			$concat .= "if(isnull(user.first_name),'',concat(' ',user.first_name))";
			$concat .= ",";
			$concat .= "if(isnull(user.last_name),'',concat(' ',user.last_name))";
			$concat .= ")";
			$and_user_name  = "AND " . $concat . " LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Max Length - starting at 1st entry, what is the max length of the result set
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) &&
		     !isset($_GET['page']) ) {
			$limit = "LIMIT 0, " . $_GET['page_length'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Paging
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$limit = "LIMIT " . (($_GET['page'] - 1) * $_GET['page_length']) . ", " . $_GET['page_length'] . " ";
		}
		
		// ---------------------------------------------------------------------------------------------------------
		// Create a list SELECT and a count SELECT
		// ---------------------------------------------------------------------------------------------------------
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Select the workout log results for a client/ccyymmdd/workout
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// SELECT count the list of entries
		$sql_results_count  = "";
		$sql_results_count .= "COUNT(workout_log.id) cnt ";
		// SELECT list of entries
		$sql_results_list  = "";
		$sql_results_list .= "calendar_event.id 'calendar_event.id', calendar_event.name 'calendar_event.name', calendar_event.start 'calendar_event.start', ";
		$sql_results_list .= "location.id 'location.id', location.name 'location.name', ";
		$sql_results_list .= "user.id 'user.id', ";
		$sql_results_list .= "if (user.anonymous_on_leaderboard AND client_user.id <> " . $p_client_user_id . ",'',user.first_name) 'user.first_name', ";
		$sql_results_list .= "if (user.anonymous_on_leaderboard AND client_user.id <> " . $p_client_user_id . ",'Anonymous',user.last_name) 'user.last_name', ";
		$sql_results_list .= "user.birthday 'user.birthday', user.gender 'user.gender', ";
		$sql_results_list .= "workout_log.height 'user.height', workout_log.height_uom_id 'user.height_uom_id', ";  // store wortkout_log height a the user's height
		$sql_results_list .= "workout_log.weight 'user.weight', workout_log.weight_uom_id 'user.weight_uom_id', ";  // store wortkout_log weight a the user's weight
		$sql_results_list .= "if (user.anonymous_on_leaderboard AND client_user.id <> " . $p_client_user_id . ",null,user_profile_media.media_url) 'user.media', ";  // store the media url with the user
		$sql_results_list .= "workout_log.id 'workout_log.id', workout_log.workout_modified 'workout_log.workout_modified', workout_log.workout_log_completed 'workout_log.workout_log_completed', ";
		$sql_results_list .= "workout_log.result 'workout_log.result', workout_log.result_uom_id 'workout_log.result_uom_id', ";
		$sql_results_list .= "workout_log.library_workout_recording_type_id 'workout_log.library_workout_recording_type_id', ";
		$sql_results_list .= "workout_log.time_limit 'workout_log.time_limit', workout_log.time_limit_uom_id 'workout_log.time_limit_uom_id', workout_log.time_limit_note 'workout_log.time_limit_note', ";
		$sql_results_list .= "if(result_uom.metric_conversion IS NULL,workout_log.result,result_uom.metric_conversion * workout_log.result) 'workout_log.std_result', ";
		$sql_results_list .= "if(workout_log.result IS NULL OR workout_log.result = 0,1,0) 'workout_log.empty' ";
		// FROM
		$sql_results_from  = "";
		$sql_results_from .= "server server, ";
		$sql_results_from .= "client client, ";
		$sql_results_from .= "calendar calendar ";
		$sql_results_from .= "LEFT OUTER JOIN location ";
		$sql_results_from .= "ON location.id = calendar.location_id, ";
		$sql_results_from .= "calendar_event, ";
		$sql_results_from .= "calendar_event_participation, ";
		$sql_results_from .= "workout_log ";
		$sql_results_from .= "LEFT OUTER JOIN library_measurement_system_unit result_uom ";
		$sql_results_from .= "ON result_uom.id = workout_log.result_uom_id ";
		$sql_results_from .= "LEFT OUTER JOIN library_measurement_system_unit height_uom ";
		$sql_results_from .= "ON height_uom.id = workout_log.height_uom_id ";
		$sql_results_from .= "LEFT OUTER JOIN library_measurement_system_unit weight_uom ";
		$sql_results_from .= "ON weight_uom.id = workout_log.weight_uom_id, ";
		$sql_results_from .= "client_user, ";
		$sql_results_from .= "user ";
		$sql_results_from .= "LEFT OUTER JOIN user_profile_media_last_entered user_profile_media ";
		$sql_results_from .= "ON user_profile_media.user_id = user.id ";
		// WHERE
		$sql_results_where  = "";
		$sql_results_where .= "client.id = " . $p_client_id . " ";
		$sql_results_where .= "AND calendar.client_id = client.id ";
		$sql_results_where .= $and_calendar_location_id;
		$sql_results_where .= "AND calendar_event.calendar_id = calendar.id ";
		$sql_results_where .= $and_calendar_event_id;
		$sql_results_where .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') = '" . $p_ccyymmdd . "' ";
		$sql_results_where .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql_results_where .= "AND workout_log.calendar_event_participation_id = calendar_event_participation.id ";
		$sql_results_where .= "AND workout_log.library_workout_id = " . $p_workout_id . " ";
		$sql_results_where .= $and_workout_log_workout_log_completed;
		$sql_results_where .= $and_workout_log_workout_modified;
		$sql_results_where .= $and_workout_log_height;
		$sql_results_where .= $and_workout_log_weight;
		$sql_results_where .= "AND client_user.id = calendar_event_participation.client_user_id ";
		$sql_results_where .= "AND user.id = client_user.user_id ";
		$sql_results_where .= $and_user_not_anonymous;
		$sql_results_where .= $and_user_gender;
		$sql_results_where .= $and_user_birthday;
		$sql_results_where .= $and_user_age;
		// ORDER BY
		$result_order = "DESC";
		if ( $p_library_workout_recording_type_id == 0 ) {     // if record by time
			$result_order = "ASC";
		}
		$sql_results_order  = "";
		$sql_results_order .= "if(workout_log.result IS NULL OR workout_log.result = 0,1,0), ";
		$sql_results_order .= "workout_log.result " . $result_order . ", ";
		$sql_results_order .= "user.first_name, ";
		$sql_results_order .= "user.last_name ";
		// -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -
		// create the count SELECT
		// -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -
		$sql_results_count_entries  = "";
		$sql_results_count_entries .= "SELECT ";
		$sql_results_count_entries .= $sql_results_count;
		$sql_results_count_entries .= "FROM ";
		$sql_results_count_entries .= $sql_results_from;
		$sql_results_count_entries .= "WHERE ";
		$sql_results_count_entries .= $sql_results_where;
		// -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -
		// create the list SELECT
		// -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -   -
		$sql_results_list_entries  = "";
		$sql_results_list_entries .= "SELECT ";
		$sql_results_list_entries .= $sql_results_list;
		$sql_results_list_entries .= "FROM ";
		$sql_results_list_entries .= $sql_results_from;
		$sql_results_list_entries .= "WHERE ";
		$sql_results_list_entries .= $sql_results_where;
		$sql_results_list_entries .= "ORDER BY ";
		$sql_results_list_entries .= $sql_results_order;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Rank the workout log results
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$sql_ranking  = "";
		$sql_ranking .= "SELECT ";
		$sql_ranking .= "(@entry := @entry + 1) 'ranking.entry.int', ";                                                     // always increment the entry number
		$sql_ranking .= "(if(@prev = result.`workout_log.std_result`,@ranking,(@ranking := @entry))) 'ranking.number.int', ";   // set the ranking to the entry number when the workout_log.result changes
		$sql_ranking .= "(@prev) 'ranking.prev_result.decimal', ";                                                          // the previous entry's workout_log.result
		$sql_ranking .= "(@prev := result.`workout_log.std_result`) 'ranking.curr_result.decimal', ";                           // this entry's result
		$sql_ranking .= "result.* ";
		$sql_ranking .= "FROM ";
		$sql_ranking .= "(SELECT @entry := 0) entry, ";        // initialize mysql variable
		$sql_ranking .= "(SELECT @ranking := 1) ranking, ";    // initialize mysql variable
		$sql_ranking .= "(SELECT @prev := 0) prev, ";          // initialize mysql variable
		$sql_ranking .= "( " . $sql_results_list_entries . " ) result ";
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Filter the the Ranked results if needed (paging / name search)
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if ( !empty($limit) || !empty($and_user_name) ) {
			$sql_main_list  = "";
			$sql_main_list .= "SELECT result.* ";
			$sql_main_list .= "FROM ";
			$sql_main_list .= " ( " . $sql_ranking . " ) result ";
			$sql_main_list .= $where_user_name;
			$sql_main_list .= $limit;
		} else {
			$sql_main_list  = $sql_ranking;
		}
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Count the workout log results
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$sql_main_count = $sql_results_count_entries;
		if ( !empty($and_user_name) ) {
			$sql_main_count .= $and_user_name;
		}
		
		// ---------------------------------------------------------------------------------------------------------
		// Run the count SELECT
		// ---------------------------------------------------------------------------------------------------------
		$sql = $sql_main_count;
		
		// echo "$sql<br />";
		
		$row = $this->db->query($sql)->row();
		if ( empty($row) || $row->cnt == 0 ) {
			$response->count = 0;
			$response->results = array();
			return $this->return_handler->results(200,"",$response);
		}
		$count = $row->cnt;
		
		// ---------------------------------------------------------------------------------------------------------
		// Run the list SELECT
		// ---------------------------------------------------------------------------------------------------------
		$sql = $sql_main_list;
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			$response->count = 0;
			$response->results = array();
			return $this->return_handler->results(200,"",$response);
		}
		$rows = $query->result();
		
		$entries = array();
		foreach ( $rows as $row ) {
			// print_r($row);
			// cast the column values to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// remove any elements you want
			unset($row->workout_log->std_result);
			
			// rename elements to their aliases
			$row->workout_log->workout_recording_type_id = $row->workout_log->library_workout_recording_type_id;
			unset($row->workout_log->library_workout_recording_type_id);
			
			// Add any calculated elements you want
			// echo json_encode($row) . "<br />\n";
			$row->user->age = date('Y',time()) - date('Y',$row->user->birthday) - (date('md',time()) < date('md',$row->user->birthday) ? 1 : 0);
			
			array_push($entries,$row);
		}
				
		$response->count = $count;
		$response->results = $entries;
		return $this->return_handler->results(200,"",$response);
	}
}