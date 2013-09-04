<?php

class action_workout_log extends action_generic {
	
	protected $uom = array();

	public function __construct() {
		parent::__construct();
	}
	
	// ==================================================================================================================
	// Get a list of workoutlogs for a user
	// ==================================================================================================================
	
	public function getForUser( $p_user_id ) {
		//
		// initialize the response data
		$count = 0;
		$entries = array();
		$response->count = $count;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$from_workout_log_library_exercise = "";
		$and_workout_log_library_exercise = "";
		$having_count_workout_log_library_exercise = "";
		$from_workout_log_library_equipment = "";
		$and_workout_log_library_equipment = "";
		$having_count_workout_log_library_equipment = "";
		if ( isset($_GET['q_ex']) && !empty($_GET['q_ex']) ) {
			// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// workout logs that used the list of exercises
			
			// make sure all exercises in the list are unique
			$ex = explode(",",$_GET['q_ex']);
			$ex_unique = array_unique($ex);
			$ex_list = implode(",", $ex_unique);
			
			$from_workout_log_library_exercise = "workout_log_library_exercise, ";
			
			$and_workout_log_library_exercise .= "AND workout_log_library_exercise.workout_log_id = workout_log.id ";
			$and_workout_log_library_exercise .= "AND workout_log_library_exercise.library_exercise_id in (" . $ex_list . ") ";
			
			$having_count_workout_log_library_exercise .= "GROUP BY workout_log.id ";
			$having_count_workout_log_library_exercise .= "HAVING count(workout_log_library_exercise.workout_log_id) = " . count($ex_unique) . " ";
		} else if ( isset($_GET['q_eq']) && !empty($_GET['q_eq']) ) {
			// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// workout logs that used the list of equipment
			
			// make sure all exercises in the list are unique
			$eq = explode(",",$_GET['q_eq']);
			$eq_unique = array_unique($eq);
			$eq_list = implode(",", $eq_unique);
			
			$from_workout_log_library_equipment = "workout_log_library_equipment, ";
			
			$and_workout_log_library_equipment .= "AND workout_log_library_equipment.workout_log_id = workout_log.id ";
			$and_workout_log_library_equipment .= "AND workout_log_library_equipment.library_equipment_id in (" . $eq_list . ") ";
			
			$having_count_workout_log_library_equipment .= "GROUP BY workout_log.id ";
			$having_count_workout_log_library_equipment .= "HAVING count(workout_log_library_equipment.workout_log_id) = " . count($eq_unique) . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// workout_recording_type_id
		$and_library_workout_recording_type_id = "";
		if ( isset($_GET['q_rt']) ) {
			if ( is_null($_GET['q_rt']) || $_GET['q_rt'] == '' ) {
				$and_library_workout_recording_type_id .= "AND workout_log.library_workout_recording_type_id IS NULL ";
			} else if ( is_numeric($_GET['q_rt']) ) {
				$and_library_workout_recording_type_id .= "AND workout_log.library_workout_recording_type_id = " . $_GET['q_rt'] . " ";
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// workout name
		$and_library_workout_name_like = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$and_library_workout_name_like .= "AND library_workout.name LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// benchmark workout
		$and_library_workout_benchmark_equal = "";
		if ( isset($_GET['q_bm']) && !is_null($_GET['q_bm']) && !empty($_GET['q_bm']) ) {
			if ( filter_var(strtolower($_GET['q_bm']), FILTER_VALIDATE_BOOLEAN) ) {
				$and_library_workout_benchmark_equal .= "AND library_workout.benchmark = 1 ";
			} else {
				$and_library_workout_benchmark_equal .= "AND library_workout.benchmark = 0 ";
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// logging of workout completed
		$and_workout_log_completed = "";
		if ( isset($_GET['q_cmp']) && !is_null($_GET['q_cmp']) && !empty($_GET['q_cmp']) ) {
			if ( filter_var(strtolower($_GET['q_cmp']), FILTER_VALIDATE_BOOLEAN) ) {
				$and_workout_log_completed .= "AND workout_log.workout_log_completed = 1 ";
			} else {
				$and_workout_log_completed .= "AND workout_log.workout_log_completed = 0 ";
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// workout performed as perscribed
		$and_workout_log_workout_modified = "";
		if ( isset($_GET['q_rx']) && !is_null($_GET['q_rx']) && !empty($_GET['q_rx']) ) {
			if ( filter_var(strtolower($_GET['q_rx']), FILTER_VALIDATE_BOOLEAN) ) {
				$where .= "AND workout_log.workout_modified = 0 ";
			} else {
				$where .= "AND workout_log.workout_modified = 1 ";
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// workout performed between two dates
		$and_calendar_event_start_between = "";
		if ( isset($_GET['q_evs_s']) && !empty($_GET['q_evs_s']) && is_numeric($_GET['q_evs_s']) &&
		     isset($_GET['q_evs_e']) && !empty($_GET['q_evs_e']) && is_numeric($_GET['q_evs_e']) ) {
			$and_calendar_event_start_between .= "AND calendar_event.start BETWEEN " . $_GET['q_evs_s'] . " AND " . $_GET['q_evs_e'] . " ";
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
		// Create the Selects
		// ---------------------------------------------------------------------------------------------------------
		
		$log_sql_select_count  = "";
		$log_sql_select_count .= "SELECT ";
		$log_sql_select_count .= "workout_log.id ";
		
		$log_sql_select_main  = "";
		$log_sql_select_main .= "SELECT ";
		$log_sql_select_main .= "workout_log.id 'workout_log.id', ";
		$log_sql_select_main .= "workout_log.user_id 'workout_log.user_id', ";
		$log_sql_select_main .= "workout_log.start 'workout_log.start', ";
		$log_sql_select_main .= "workout_log.workout_modified 'workout_log.workout_modified', ";
		$log_sql_select_main .= "workout_log.workout_log_completed 'workout_log.workout_log_completed', ";
		$log_sql_select_main .= "workout_log.auto_calculate_result 'workout_log.auto_calculate_result', ";
		$log_sql_select_main .= "workout_log.note 'workout_log.note', ";
		$log_sql_select_main .= "workout_log.result 'workout_log.result', ";
		$log_sql_select_main .= "workout_log.result_uom_id 'workout_log.result_uom_id', ";
		$log_sql_select_main .= "workout_log.time_limit 'workout_log.time_limit', ";
		$log_sql_select_main .= "workout_log.time_limit_uom_id 'workout_log.time_limit_uom_id', ";
		$log_sql_select_main .= "workout_log.time_limit_note 'workout_log.time_limit_note', ";
		$log_sql_select_main .= "workout_log.calendar_event_participation_id 'workout_log.calendar_event_participation_id', ";
		$log_sql_select_main .= "workout_log.library_workout_recording_type_id 'workout_log.library_workout_recording_type_id', ";
		$log_sql_select_main .= "workout_log.json_log_summary 'workout_log.json_log_summary.json', ";
		$log_sql_select_main .= "library_workout.id 'workout_log.workout_id.int', ";
		$log_sql_select_main .= "library_workout.name 'workout_log.workout_name', ";
		$log_sql_select_main .= "library_workout.benchmark 'workout_log.workout_benchmark.boolean', ";
		$log_sql_select_main .= "owner_client.id 'workout_owner.client_id.int', ";
		$log_sql_select_main .= "owner_client.name 'workout_owner.name', ";
		$log_sql_select_main .= "calendar_event.id 'calendar_event.id', ";
		$log_sql_select_main .= "calendar_event.name 'calendar_event.name', ";
		$log_sql_select_main .= "calendar_event.start 'calendar_event.start', ";
		$log_sql_select_main .= "calendar_event.duration 'calendar_event.duration', ";
		$log_sql_select_main .= "calendar_event.calendar_entry_id 'calendar_event.calendar_entry_id', ";
		$log_sql_select_main .= "calendar_event.calendar_entry_template_id 'calendar_event.calendar_entry_template_id', ";
		$log_sql_select_main .= "client.id 'client.id', ";
		$log_sql_select_main .= "client.name 'client.name', ";
		$log_sql_select_main .= "location.id 'location.id', ";
		$log_sql_select_main .= "location.name 'location.name', ";
		$log_sql_select_main .= "calendar.id 'calendar.id', ";
		$log_sql_select_main .= "calendar.name 'calendar.name', ";
		$log_sql_select_main .= "calendar.timezone 'calendar.timezone' ";
		
		$log_sql_from  = "";
		$log_sql_from .= "FROM ";
		$log_sql_from .= "client_user, ";
		$log_sql_from .= "calendar_event_participation, ";
		$log_sql_from .= "workout_log, ";
		$log_sql_from .= $from_workout_log_library_exercise;
		$log_sql_from .= $from_workout_log_library_equipment;
		$log_sql_from .= "library_workout ";
		$log_sql_from .= "LEFT OUTER JOIN client owner_client ";
		$log_sql_from .= "ON owner_client.id = library_workout.client_id, ";
		$log_sql_from .= "calendar_event, ";
		$log_sql_from .= "calendar ";
		$log_sql_from .= "LEFT OUTER JOIN location ";
		$log_sql_from .= "ON location.id = calendar.location_id ";
		$log_sql_from .= "LEFT OUTER JOIN client ";
		$log_sql_from .= "ON client.id = calendar.client_id ";
		
		$log_sql_where  = "";
		$log_sql_where .= "WHERE client_user.user_id = " . $p_user_id . " ";
		$log_sql_where .= "AND calendar_event_participation.client_user_id = client_user.id ";
		$log_sql_where .= "AND workout_log.calendar_event_participation_id = calendar_event_participation.id ";
		$log_sql_where .= $and_library_workout_recording_type_id;
		$log_sql_where .= $and_workout_log_completed;
		$log_sql_where .= $and_workout_log_workout_modified;
		$log_sql_where .= $and_workout_log_library_exercise;
		$log_sql_where .= $and_workout_log_library_equipment;
		$log_sql_where .= "AND library_workout.id = workout_log.library_workout_id ";
		$log_sql_where .= $and_library_workout_benchmark_equal;
		$log_sql_where .= $and_library_workout_name_like;
		$log_sql_where .= "AND calendar_event.id = calendar_event_participation.calendar_event_id ";
		$log_sql_where .= $and_calendar_event_start_between;
		$log_sql_where .= "AND calendar.id = calendar_event.calendar_id ";
		$log_sql_where .= $having_count_workout_log_library_exercise;
		$log_sql_where .= $having_count_workout_log_library_equipment;
		
		$log_sql_order  = "";
		$log_sql_order .= "ORDER BY ";
		$log_sql_order .= "calendar_event.start DESC, ";
		$log_sql_order .= "library_workout.name, ";
		$log_sql_order .= "owner_client.name ";
		
		// ---------------------------------------------------------------------------------------------------------
		// Create the Data select
		// ---------------------------------------------------------------------------------------------------------
		
		$log_sql_count  = "";
		$log_sql_count .= $log_sql_select_count;
		$log_sql_count .= $log_sql_from;
		$log_sql_count .= $log_sql_where;
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "COUNT(workout_log.id) cnt ";
		$sql .= "FROM ";
		$sql .= "(" . $log_sql_count . ") workout_log ";
		
		// echo "$sql<br />";
		
		$row = $this->db->query($sql)->row();
		if ( empty($row) || $row->cnt == 0 ) {
			$response->count = 0;
			$response->results = array();
			return $this->return_handler->results(200,"",$response);
		}
		$count = $row->cnt;
		
		// ---------------------------------------------------------------------------------------------------------
		// Create the Data select
		// ---------------------------------------------------------------------------------------------------------
		
		$sql  = $log_sql_select_main;
		$sql .= $log_sql_from;
		$sql .= $log_sql_where;
		$sql .= $log_sql_order;
		$sql .= $limit;
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			$response->count = 0;
			$response->results = array();
			return $this->return_handler->results(200,"",$response);
		}
		$rows = $query->result();
		
		$workout_logs = array();
		foreach ( $rows as $row ) {
			// echo json_encode($row) . "<br />\n";

			mysql_schema::cast_row('workoutdb',$row);
			// echo json_encode($row) . "<br />\n";
			
			$entry = mysql_schema::objectify_row($row);
			// echo json_encode($entry) . "<br /><br />\n\n";
			
			$workout_log = clone $entry->workout_log;
			$workout_log->workout_owner = clone $entry->workout_owner;
			$workout_log->event = clone $entry->calendar_event;
			$workout_log->event->client = clone $entry->client;
			$workout_log->event->location = clone $entry->location;
			$workout_log->event->calendar = clone $entry->calendar;
			$workout_log->event->calendar->timezone_offset = format_timezone_offset($entry->calendar->timezone);
			
			// alias work-around
			$workout_log->participation_id = $workout_log->calendar_event_participation_id;
			unset($workout_log->calendar_event_participation_id);
			$workout_log->workout_recording_type_id = $workout_log->library_workout_recording_type_id;
			unset($workout_log->library_workout_recording_type_id);
			$workout_log->summary = clone $workout_log->json_log_summary;
			unset($workout_log->json_log_summary);
			$workout_log->event->entry_id = $workout_log->event->calendar_entry_id;
			unset($workout_log->event->calendar_entry_id);
			$workout_log->event->template_id = $workout_log->event->calendar_entry_template_id;
			unset($workout_log->event->calendar_entry_template_id);
			
			array_push($workout_logs,clone $workout_log);
			unset($workout_log);
		}
				
		$response->count = $count;
		$response->results = $workout_logs;
		return $this->return_handler->results(200,"",$response);
	}

	// ==================================================================================================================
	// Get a list of workoutlogs for client on a given date (timezone from calendar)
	// ==================================================================================================================
	
	public function getForClientDate( $p_client_id, $p_date ) {
		// echo "getForClientDate $p_client_id $p_date<br />";
	    // Get a list of locations and thier calendars for a client
		$return = $this->perform('action_calendar->getLocationCalendarDateRangeForClientDate',$p_client_id,$p_date);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		$locations = $return['response'];
		// echo "---- Locatoins List ----<br />";
		// print_r($locations);

		$return = $this->perform('this->getForLocations',$locations);
		return $return;
	}
	
	public function getForlocations( $p_locations ) {
		// echo "getForlocations "; print_r($p_locations); echo "<br />";
		// ----------------------------------------------------------------------------------------------------------------------------------------------
		// get a list of completed and incompleted workout logs for all locations for a client
		// ----------------------------------------------------------------------------------------------------------------------------------------------
		$response = new stdClass();
		$response->count = 0;
		$response->results = array();
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$where = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$where .= "AND ";
			$where .= "concat(if(isnull(u.first_name),'',u.first_name), if(isnull(u.last_name),'',concat(' ',u.last_name))) ";
			$where .= "LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$limit = "LIMIT " . (($_GET['page'] - 1) * $_GET['page_length']) . ", " . $_GET['page_length'] . " ";
		}

		$sql_cnt = "";
		$sql = "";
		
		$sql_participants = "";
		foreach( $p_locations as $location ) {
			foreach ( $location->calendar as $calendar ) {
				// ------------------------------------------------------------------------------------------------------------------------------------------
				// Get count
				// ------------------------------------------------------------------------------------------------------------------------------------------
				//
				// Select the participants of a calendar's events on a given day
				$sql_cnt_temp  = "";
				$sql_cnt_temp .= "SELECT ";
				$sql_cnt_temp .= "count(p.id) cnt ";
				$sql_cnt_temp .= "FROM ";
				$sql_cnt_temp .= "calendar_event ev, ";
				$sql_cnt_temp .= "calendar_event_participation p, ";
				$sql_cnt_temp .= "client_user c, ";
				$sql_cnt_temp .= "user u ";
				$sql_cnt_temp .= "WHERE ev.calendar_id = " . $calendar->id . " ";
				$sql_cnt_temp .= "AND ev.start BETWEEN " . $calendar->start . " AND " . $calendar->end . " "; 
				$sql_cnt_temp .= "AND p.calendar_event_id = ev.id ";
				$sql_cnt_temp .= "AND c.id = p.client_user_id ";
				$sql_cnt_temp .= "AND u.id = c.user_id ";
				$sql_cnt_temp .= $where;
				
				if ( !empty($sql_cnt) ) {
					$sql_cnt .= " UNION ";
				}
				$sql_cnt .= "(" . $sql_cnt_temp . ")";
				
				// ------------------------------------------------------------------------------------------------------------------------------------------
				// Get data selection
				// ------------------------------------------------------------------------------------------------------------------------------------------
				//
				// Select the participants of a calendar's events on a given day
				$sql_temp  = "";
				$sql_temp .= "SELECT ";
				$sql_temp .= "ev.id calendar_event_id, ev.start calendar_event_start, ";
				$sql_temp .= "p.id calendar_event_participation_id, ";
				$sql_temp .= "c.id client_user_id, ";
				$sql_temp .= "u.first_name user_first_name, u.last_name user_last_name, ";
				$sql_temp .= "media.id media_id, media.media_url media_url ";
				$sql_temp .= "FROM ";
				$sql_temp .= "calendar_event ev, ";
				$sql_temp .= "calendar_event_participation p, ";
				$sql_temp .= "client_user c, ";
				$sql_temp .= "user u ";
				$sql_temp .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
				$sql_temp .= "ON media.user_id = u.id ";
				$sql_temp .= "WHERE ev.calendar_id = " . $calendar->id . " ";
				$sql_temp .= "AND ev.start BETWEEN " . $calendar->start . " AND " . $calendar->end . " "; 
				$sql_temp .= "AND p.calendar_event_id = ev.id ";
				$sql_temp .= "AND c.id = p.client_user_id ";
				$sql_temp .= "AND u.id = c.user_id ";
				$sql_temp .= $where;
				
				
				if ( !empty($sql_participants) ) {
					$sql_participants .= " UNION ";
				}
				$sql_participants .= ' (' . $sql_temp . ') ';
			}
		}
		//
		// Add Order By and Limit
		if ( !empty($sql_participants) ) {
			$sql_participants .= "ORDER BY user_first_name, user_last_name, calendar_event_start, calendar_event_id ";
			$sql_participants .= $limit;
		}
		//
		// Get the workout_logs and workout_logs_pending for the qualifying participants (name and paging filters)
		if ( !empty($sql_participants) ) {
			//
			// Select workout logs for the participants
			$sql_logs  = "";
			$sql_logs .= "SELECT ";
			$sql_logs .= "p.*, ";
			$sql_logs .= "if(l.workout_log_completed,'c','i') status, ";
			$sql_logs .= "l.id log_id ";
			$sql_logs .= "FROM ";
			$sql_logs .= "(" . $sql_participants . ") p ";
			$sql_logs .= "LEFT OUTER JOIN workout_log l ";
			$sql_logs .= "ON l.calendar_event_participation_id = p.calendar_event_participation_id ";
			
			// echo "$sql_logs\n\n";
			//
			// Select pending workout logs for the participants
			$sql_pending_logs  = "";
			$sql_pending_logs .= "SELECT ";
			$sql_pending_logs .= "p.*, ";
			$sql_pending_logs .= "'p' status, ";
			$sql_pending_logs .= "l.library_workout_id log_id ";
			$sql_pending_logs .= "FROM ";
			$sql_pending_logs .= "(" . $sql_participants . ") p ";
			$sql_pending_logs .= "LEFT OUTER JOIN workout_log_pending l ";
			$sql_pending_logs .= "ON l.calendar_event_participation_id = p.calendar_event_participation_id ";
			
			if ( !empty($sql) ) {
				$sql .= " UNION ";
			}
			$sql .= "(" . $sql_logs . ") UNION (" . $sql_pending_logs . ")";
			$sql .= "ORDER BY user_first_name, user_last_name, calendar_event_start, calendar_event_id, log_id ";
		}

		// echo "$sql<br />";
		
		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Run The Count Select
		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Complete the count select
		if ( !empty($sql_cnt) ) {
			$sql_cnt = "SELECT SUM(p.cnt) cnt FROM (" . $sql_cnt . ") p";
		}

		// echo "$sql_cnt<br />";
		
		$query = $this->db->query($sql_cnt);
		if ($query->num_rows() == 0 ) {
			return $this->return_handler->results(200,"",$response);
		}
		$row = $query->row();
		$response->count = (int) $row->cnt;
		
		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Run The data Select
		// ------------------------------------------------------------------------------------------------------------------------------------------
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0 ) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();
		
		$i = -1;
		$entries = array();
		foreach( $rows as $row ) {
			// echo "$i "; print_r($row); echo "<br />";
			// echo "$i " . $entries[$i]->participant_id . " : " . $row->calendar_event_participation_id . "<br />";
			
			if ( $i == -1 || $entries[$i]->participant_id != $row->calendar_event_participation_id ) {
				// a new Participant
				++$i;
				$entries[$i] = new stdClass();
				$entries[$i]->event_id = cast_int($row->calendar_event_id);
				$entries[$i]->participant_id = cast_int($row->calendar_event_participation_id);
				$entries[$i]->client_user = new stdClass();
				$entries[$i]->client_user->id = cast_int($row->client_user_id);
				$entries[$i]->client_user->first_name = $row->user_first_name;
				$entries[$i]->client_user->last_name = $row->user_last_name;
				$entries[$i]->client_user->media = format_media($row->media_id,$row->media_url);
				// initialize the 3 arrays
				$entries[$i]->completed = array();
				$entries[$i]->incompleted = array();
				$entries[$i]->pending = array();
			}
			if ( !is_null($row->log_id) ) {
				if ( $row->status == "c" ) {
					$entries[$i]->completed[] = (int) $row->log_id;
				} else if ( $row->status == "i" ) {
					$entries[$i]->incompleted[] = (int) $row->log_id;
				} else if ( $row->status == "p" ) {
					$entries[$i]->pending[] = (int) $row->log_id;
				}
			}
		}

		$response->results = $entries;
		return $this->return_handler->results(200,"",$response);
	}

	// ==================================================================================================================
	// Get a list workoutlogs for a client_user_id and workout_id
	// ==================================================================================================================
	
	public function getForClientUserWorkout( $p_client_user_id, $p_workout_id ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "workout_log.id 'workout_log.id', workout_log.user_id 'workout_log.user_id', workout_log.library_workout_id 'workout_log.library_workout_id', workout_log.calendar_event_participation_id 'workout_log.calendar_event_participation_id', ";
		$sql .= "workout_log.workout_modified 'workout_log.workout_modified', workout_log.workout_log_completed 'workout_log.workout_log_completed', workout_log.auto_calculate_result 'workout_log.auto_calculate_result', ";
		$sql .= "workout_log.result_uom_id 'workout_log.result_uom_id', workout_log.result 'workout_log.result', ";
		$sql .= "workout_log.time_limit 'workout_log.time_limit', workout_log.time_limit_uom_id 'workout_log.time_limit_uom_id', workout_log.time_limit_note 'workout_log.time_limit_note', ";
		$sql .= "workout_log.library_workout_recording_type_id 'workout_log.library_workout_recording_type_id', workout_log.start 'workout_log.start', ";
		$sql .= "workout_log.note 'workout_log.note', workout_log.json_log_summary 'workout_log.json_log_summary' ";
		$sql .= "FROM client_user, ";
		$sql .= "workout_log ";
		$sql .= "WHERE client_user.id = " . $p_client_user_id . " ";
		$sql .= "AND workout_log.user_id = client_user.user_id ";
		$sql .= "AND workout_log.library_workout_id = " . $p_workout_id . " ";
		$sql .= "ORDER BY workout_log.start ASC ";
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(200,"",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach ( $rows as $row ) {
			// echo json_encode($row) . "<br />\n";

			mysql_schema::cast_row('workoutdb',$row);
			// echo json_encode($row) . "<br />\n";
			
			$entry = mysql_schema::objectify_row($row);
			// echo json_encode($entry) . "<br /><br />\n\n";
			
			$entry = clone $entry->workout_log;
			
			// rename Alias's
			$entry->workout_id = $entry->library_workout_id;
			unset($entry->library_workout_id);
			$entry->participation_id = $entry->calendar_event_participation_id;
			unset($entry->calendar_event_participation_id);
			$entry->workout_recording_type_id = $entry->library_workout_recording_type_id;
			unset($entry->library_workout_recording_type_id);
			$entry->log_summary = json_decode($entry->json_log_summary);
			unset($entry->json_log_summary);
			
			array_push($entries,$entry);
			unset($entry);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// Get a workoutlog by its id ( user_id is used to get the stats about the workout for the user )
	// ==================================================================================================================
	
	public function getForId( $p_workout_log_id ) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get the workout log
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$sql  = "SELECT l.*, w.name, w.benchmark workout_benchmark ";
		$sql .= "FROM workout_log l ";
		$sql .= "LEFT OUTER JOIN library_workout w ";
		$sql .= "ON w.id = l.library_workout_id ";
		$sql .= "WHERE l.id = " . $p_workout_log_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();
		
		// print_r($row);
		
		$entry = new stdClass();
		$entry->id = cast_int($row->id);
		$entry->name = $row->name;
		$entry->participation_id = cast_int($row->calendar_event_participation_id);
		$entry->user_id = cast_int($row->user_id);
		$entry->workout_id = cast_int($row->library_workout_id);
		$entry->start = cast_int($row->start);
		$entry->workout_modified = cast_boolean($row->workout_modified);
		$entry->workout_log_completed = cast_boolean($row->workout_log_completed);
		$entry->auto_calculate_result = cast_boolean($row->auto_calculate_result);
		$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
		$entry->workout_benchmark = cast_boolean($row->workout_benchmark);
		$entry->note = $row->note;
		$entry->result_unit = format_result_unit($row->result, $row->result_uom_id);
		$entry->time_limit = format_time_limit($row->time_limit, $row->time_limit_uom_id, $row->time_limit_note);
		$entry->log_summary = json_decode($row->json_log_summary);
		
		$workout_log = json_decode($row->json_log);
		// Add the exercise names and media, add equipment deletable switch.
		$return = $this->getWorkoutLogDetailAdded($p_workout_log_id,$workout_log);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$entry->log = $return['response'];
		
		return $this->return_handler->results(200,"",$entry);
	}

	public function getWorkoutLogDetailAdded($p_workout_log_id,$workout_log) {
		// -----------------------------------------------------------------
		// Get the workout log's exercises
		// -----------------------------------------------------------------
		$this->exercise = array();
		
		$sql  = "SELECT ";
		$sql .= "ex.id id, ex.name name, ";
		$sql .= "media.id media_id, media.media_url media_url ";
		$sql .= "FROM workout_log_library_exercise xref, ";
		$sql .= "library_exercise ex ";
		$sql .= "LEFT OUTER JOIN library_exercise_media_last_entered media ";
		$sql .= "ON media.library_exercise_id = ex.id ";
		$sql .= "WHERE xref.workout_log_id = " . $p_workout_log_id . " ";
		$sql .= "AND ex.id = xref.library_exercise_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			foreach ( $rows as $row ) {
				$this->exercise[$row->id] = new stdClass();
				$this->exercise[$row->id]->name = $row->name;
				$this->exercise[$row->id]->media = format_media($row->media_id,$row->media_url);
			}
		}
		// -----------------------------------------------------------------
		// Get the workout log's equipment
		// -----------------------------------------------------------------
		$this->equipment = array();
		
		$sql  = "SELECT ";
		$sql .= "eq.id id, eq.name name, ";
		$sql .= "media.id media_id, media.media_url media_url ";
		$sql .= "FROM workout_log_library_equipment xref, ";
		$sql .= "library_equipment eq ";
		$sql .= "LEFT OUTER JOIN library_equipment_media_last_entered media ";
		$sql .= "ON media.library_equipment_id = eq.id ";
		$sql .= "WHERE xref.workout_log_id = " . $p_workout_log_id . " ";
		$sql .= "AND eq.id = xref.library_equipment_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			foreach ( $rows as $row ) {
				$this->equipment[$row->id] = new stdClass();
				$this->equipment[$row->id]->name = $row->name;
				$this->equipment[$row->id]->media = format_media($row->media_id,$row->media_url);
			}
		}
		
		// -----------------------------------------------------------------
		// Get the workout log's equipment deletable switch
		// ----------------------------------------------------------------- 
		$this->xref = array();
		
		$sql  = "SELECT ";
		$sql .= "ex_eq.library_exercise_id ex_id, ex_eq.library_equipment_id eq_id, ";
		$sql .= "IF(ex_eq.mandatory IS NULL,0,IF(ex_eq.mandatory = 1,0,1)) deletable ";
		$sql .= "FROM workout_log_library_equipment wk_eq ";
		$sql .= "LEFT OUTER JOIN library_exercise_equipment ex_eq ";
		$sql .= "ON ex_eq.library_equipment_id = wk_eq.library_equipment_id ";
		$sql .= "WHERE wk_eq.workout_log_id = " . $p_workout_log_id . " ";
		$sql .= "ORDER BY ex_eq.library_exercise_id, ex_eq.library_equipment_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			foreach ( $rows as $row ) {
				$this->xref[$row->ex_id][$row->eq_id] = cast_boolean($row->deletable);
			}
		}
		
		// echo "workout:$p_library_workout_id\n";
		$this->addWorkoutlogDetail($workout_log);
		return $this->return_handler->results(200,"",$workout_log);
	}

	public function addWorkoutlogDetail(&$data) {
		if ( is_object($data) || is_array($data) ) {
			foreach ( $data as $key => &$value ) {
				// echo " $key - ";
				switch ($key) {
					
					case "complete_round":
					case "incomplete_round":
						$this->addworkoutlogDetail($value);
						break;
						
					case "set":
						foreach ( $value as &$set ) {
							$this->addWorkoutlogDetail($set);
						}
						break;
						
					case "exercise_group":
						foreach( $value as &$exercise_group ) {
							$this->addWorkoutlogDetail($exercise_group);
						}
						break;
						
					case 'exercise':
						$value->name = $this->exercise[$value->id]->name;
						$value->media = clone $this->exercise[$value->id]->media;
						$this->curr_ex_id = $value->id;
						$this->addWorkoutlogDetail($value);
						unset($this->curr_ex_id);
						break;
						
					case 'equipment':
						foreach( $value as &$equipment ) {
							$equipment->name = $this->equipment[$equipment->id]->name;
							$equipment->media = clone $this->equipment[$equipment->id]->media;
							if ( !isset($this->xref[$this->curr_ex_id][$equipment->id]) ) {
								// echo "--- exercise_id:" . $this->curr_ex_id . " equipment:" . $equipment->id . " does not exist\n";
								$equipment->deletable = true;
							} else {
								$equipment->deletable = $this->xref[$this->curr_ex_id][$equipment->id];
							}
						}
						break;
					
					default:
						break;
				}
			}
		}
	}

	// ==================================================================================================================
	// Get a workout by its id ( user_id is used to get the stats about the workout for the user )
	// ==================================================================================================================
	
	public function getDetailForId( $p_workout_log_id ) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get the workout
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$sql  = "SELECT l.id id, l.json_log_summary json_log_summary, l.workout_modified, l.workout_log_completed, ";
		$sql .= "w.name workout_name ";
		$sql .= "FROM workout_log l ";
		$sql .= "LEFT OUTER JOIN library_workout w ";
		$sql .= "ON w.id = l.library_workout_id ";
		$sql .= "WHERE l.id = " . $p_workout_log_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			
			//print_r($row);
			
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->workout_modified = cast_boolean($row->workout_modified);
			$entry->workout_log_completed = cast_boolean($row->workout_log_completed);
			$entry->workout_name = $row->workout_name;
			$entry->log_summary = json_decode($row->json_log_summary);
			
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
	}

	// ==================================================================================================================
	// preview a workout log entry
	// ==================================================================================================================
	
	public function getPreview( $data ) {
		// echo "getPreview data:" . json_encode($data) . "<b />";
		// ---------------------------------------------------------------------------------
		// check mandatory fields
		// ---------------------------------------------------------------------------------
		if ( !property_exists($data,'user_id') || is_null($data->user_id) || empty($data->user_id) || !is_numeric($data->user_id) ) {
			return $this->return_handler->results(400,"User_id is mandatory",new stdClass);
		}
		if ( !property_exists($data,'log') || is_null($data->log) || empty($data->log) || !is_object($data->log) ) {
			return $this->return_handler->results(400,"log is mandatory",new stdClass);
		}
		// --------------------------------------------------------------------------------------------------------
		// get the user
		// --------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_user->getForId',$data->user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Invalid user_id",new stdClass());
		}
		$user = $return['response'];
		// ---------------------------------------------------------------------------------
		// set gender
		// ---------------------------------------------------------------------------------
		$gender = "both";
		if ( $user->gender == "M" ) {
			$gender = "male";
		} else if ( $user->gender == "F" ) {
			$gender = "female";
		}
		/*
		// ---------------------------------------------------------------------------------
		// get the total repeats
		// ---------------------------------------------------------------------------------
		$return = $this->perform('action_workout_log->getTotalRepeatsForWorkoutlog',$data->log);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$total_repeats = $return['response']->total_repeats;
		*/
		// --------------------------------------------------------------------------------------------------------
		// If workout log is based on a workout, set the time limit recording type based on the workout
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'workout_id') && !is_null($data->workout_id) ) {
			$return = $this->perform('table_workoutdb_library_workout->getForId',$data->workout_id);
			if ( $return['status'] > 200 ) {
				return $return;
			}
			$workout = $return['response'];
			
			// print_r($workout);
			if ( property_exists($workout,'time_limit') ) {
				$data->time_limit = clone $workout->time_limit;
			}
			$data->library_workout_recording_type_id = $workout->library_workout_recording_type_id;
		}
		
		// var_dump($data);
		// --------------------------------------------------------------------------------------------------------
		// Validate Result
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'workout_recording_type_id') ) {
			$data->library_workout_recording_type_id = $data->workout_recording_type_id;
		}
		if ( property_exists($data,'library_workout_recording_type_id') && !is_null($data->library_workout_recording_type_id) &&
		     ( $data->library_workout_recording_type_id == 0 ||
		       $data->library_workout_recording_type_id == 1 ||
		       $data->library_workout_recording_type_id == 2 ) ) {
			if ( !property_exists($data,'result_unit') || !property_exists($data->result_unit,'input') || !is_numeric($data->result_unit->input) || ((string) ((int) $data->result_unit->input)) != $data->result_unit->input ) {
				return $this->return_handler->results(400,"Result must be an Integer.",new stdClass());
			}
		}
		// ---------------------------------------------------------------------------------
		// load the lookup tables
		// ---------------------------------------------------------------------------------
		$return = $this->perform('action_workout->setupNeededLookupTables');
		if ( is_array($return) && array_key_exists('status',$return) && $return['status'] > 300 ) {
			return $return;
		}
		// ---------------------------------------------------------------------------------
		// Preview the workout_log
		// ---------------------------------------------------------------------------------
		// echo "complete_round:"; print_r($data->log->complete_round); echo "<b />";
		$return = $this->perform('action_workout->createWorkoutSummary',$data->log->complete_round);
		if ( is_array($return) && array_key_exists('status',$return) && $return['status'] > 300 ) {
			return $return;
		}
		$complete_round = $return->{$gender};
		
		// echo "incomplete_round:"; print_r($data->log->incomplete_round); echo "<b />";
		$return = $this->perform('action_workout->createWorkoutSummary',$data->log->incomplete_round);
		if ( is_array($return) && array_key_exists('status',$return) && $return['status'] > 300 ) {
			return $return;
		}
		$incomplete_round = $return->{$gender};
		
		$response = new stdClass();
		$response->complete_round = $complete_round;
		$response->incomplete_round = $incomplete_round;
		
		return $this->return_handler->results(200,"",$response);
	}

	// ==================================================================================================================
	// Create a workout log entry
	// ==================================================================================================================
	
	public function create_new( $data ) {
		// get the column names for the field alias names passed in in the data.
	}

	// ==================================================================================================================
	// Create a workout log entry
	// ==================================================================================================================
	
	public function create( $data ) {
		// echo json_encode($data) . " -- ";
		// --------------------------------------------------------------------------------------------------------
		// Insert the workout_log entry
		// --------------------------------------------------------------------------------------------------------
		// if the json_workout is set:
		//
		// 1) validate json_workout's structure
		// 2) store the original json_workout in the database as original_json_workout
		// 2) create a version of json_workout to store in the database
		// 3) create a list of exercises and equipment used in json_workout
		// --------------------------------------------------------------------------------------------------------
		// If the participation id and workout id already exist in workout_log, abort
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'participation_id') && !is_null($data->participation_id) && !empty($data->participation_id) && is_numeric($data->participation_id) &&
		     property_exists($data,'workout_id') && !is_null($data->workout_id) && !empty($data->workout_id) && is_numeric($data->workout_id) ) {
			$key['calendar_event_participation_id'] = $data->participation_id;
			$key['library_workout_id'] = $data->workout_id;
			$return = $this->perform('table_workoutdb_workout_log->getForAndKeys',$key,'id');
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( count($return['response']) > 0 ) {
				return $this->return_handler->results(400,"This workout has already been logged!",new stdClass);
			}
		}
		// --------------------------------------------------------------------------------------------------------
		// a valid participant_id must be passed in
		// --------------------------------------------------------------------------------------------------------
		if ( !property_exists($data,'participation_id') || is_null($data->participation_id) || empty($data->participation_id) || !is_numeric($data->participation_id) ) {
			return $this->return_handler->results(400,"a valid participation_id must be provided",new stdClass());
		}
		// --------------------------------------------------------------------------------------------------------
		// get the user
		// --------------------------------------------------------------------------------------------------------
		$return = $this->perform('action_user->getForParticipation',$data->participation_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Invalid user_id",new stdClass());
		}
		$user = $return['response'];
		//print_r($user);
		// --------------------------------------------------------------------------------------------------------
		// set the user_id, height and weight
		// --------------------------------------------------------------------------------------------------------
		$data->user_id = $user->id;
		$data->height = clone $user->height;
		$data->weight = clone $user->weight;
		// --------------------------------------------------------------------------------------------------------
		// if participant_id is passed in but start is not, use participant_id to lookup the start
		// --------------------------------------------------------------------------------------------------------
		if ( !property_exists($data,'start') || is_null($data->start) || empty($data->start) || !is_numeric($data->start) ) {
			$return = $this->perform('action_calendar_event->getForParticipation',$data->participation_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $return['status'] > 200 ) {
				return $this->return_handler->results(400,"Invalid participation_id",new stdClass());
			}
			$data->start = $return['response']->start;
		}
		// ---------------------------------------------------------------------------------
		// set gender
		// ---------------------------------------------------------------------------------
		$gender = "both";
		if ( $user->gender == "M" ) {
			$gender = "male";
		} else if ( $user->gender == "F" ) {
			$gender = "female";
		}
		// ========================================================================================================
		// Format and validate the workout log
		// ========================================================================================================
		// Deal with Aliases
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'workout_recording_type_id') ) {
			$data->library_workout_recording_type_id = $data->workout_recording_type_id;
		}
		// --------------------------------------------------------------------------------------------------------
		// If workout log is based on a workout, set the time limit recording type based on the workout
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'workout_id') && !is_null($data->workout_id) ) {
			$return = $this->perform('table_workoutdb_library_workout->getForId',$data->workout_id);
			if ( $return['status'] > 200 ) {
				return $return;
			}
			$workout = $return['response'];
			
			// print_r($workout);
			if ( property_exists($workout,'time_limit') ) {
				$data->time_limit = clone $workout->time_limit;
			}
			$data->library_workout_recording_type_id = $workout->library_workout_recording_type_id;
		}
		
		// var_dump($data);
		// --------------------------------------------------------------------------------------------------------
		// Validate Result
		// --------------------------------------------------------------------------------------------------------
		if ( !is_null($data->library_workout_recording_type_id) &&
		     ( $data->library_workout_recording_type_id == 0 ||
		       $data->library_workout_recording_type_id == 1 ||
		       $data->library_workout_recording_type_id == 2 ) ) {
			if ( !property_exists($data,'result_unit') || !property_exists($data->result_unit,'input') || !is_numeric($data->result_unit->input) || ((string) ((int) $data->result_unit->input)) != $data->result_unit->input ) {
				return $this->return_handler->results(400,"Result must be an Integer.",new stdClass());
			}
		}
		// --------------------------------------------------------------------------------------------------------
		// validate the workoutlog, create a storable json of the workoutlog,
		// create an exercise and equipment list for the workoutlog
		// --------------------------------------------------------------------------------------------------------
		$this->load->library('simplify');
		$return = $this->simplify->workout_log($data->log);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		$log = clone $return['response']->workout_log;
		
		$exercises = $return['response']->exercises;
		$equipment = $return['response']->equipment;
		// --------------------------------------------------------------------------------------------------------
		// if auto_calculate_result exists and is set to true, auto calculate the results
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'auto_calculate_result') && $data->auto_calculate_result &&
		     !is_null($data->library_workout_recording_type_id) && $data->library_workout_recording_type_id >= 2 && $data->library_workout_recording_type_id <= 5 ) {
			$return = $this->perform('this->autoCalculate',$data,$user->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			$auto = $return['response'];
			
			if ( !property_exists($data,'result_unit') ) {
				$data->result_unit = new stdClass();
			}
			
			switch ($data->library_workout_recording_type_id) {
				case 2:                                              // repeats
					$data->result_unit->id = $auto->repeats->id;
					$data->result_unit->input =  $auto->repeats->value;
					break;
				case 3:                                              // weight
					$data->result_unit->id = $auto->weight->id;
					$data->result_unit->input = $auto->weight->value;
					break;
				case 4:                                              // distance
					$data->result_unit->id = $auto->distance->id;
					$data->result_unit->input = $auto->distance->value;
					break;
				case 5:                                              // height
					$data->result_unit->id = $auto->height->id;
					$data->result_unit->input = $auto->height->value;
					break;
			}
		} else {
			$data->auto_calculate_result = false;
		}
		//---------------------------------------------------------------------------------------------------------
		// Load the needed lookup tables
		// --------------------------------------------------------------------------------------------------------
		$return = $this->perform('action_workout->setupNeededLookupTables');
		if ( $return['status'] > 300 ) {
			return $return;
		}
		// --------------------------------------------------------------------------------------------------------
		// Create the workoutlog summary
		// --------------------------------------------------------------------------------------------------------
		$return_summary_complete = $this->perform('action_workout->createWorkoutSummary',$log->complete_round);
		$return_summary_incomplete = $this->perform('action_workout->createWorkoutSummary',$log->incomplete_round);
		
		$summary = new stdClass();
		$summary->complete_round = clone $return_summary_complete->{$gender};
		$summary->incomplete_round = clone $return_summary_incomplete->{$gender};
		// --------------------------------------------------------------------------------------------------------
		// move the re-formatted log and new log summary into the data to be updated
		// --------------------------------------------------------------------------------------------------------
		$data->original_json_log = json_encode($data->log);
		unset($data->log);
		$data->json_log = json_encode($log);
		$data->json_log_summary = json_encode($summary);
		// print_r($data);
		// --------------------------------------------------------------------------------------------------------
		// insert the entry
		// --------------------------------------------------------------------------------------------------------
		$return_create = $this->perform('table_workoutdb_workout_log->insert',$data);
		if ( $return_create['status'] >= 300 ) {
			return $return_create;
		}
		
		$this->id = $return_create['response']->id;
		// --------------------------------------------------------------------------------------------------------
		// create the cross reference entries only
		// --------------------------------------------------------------------------------------------------------
		if ( count($exercises) > 0 ) {
			$this->id = $return_create['response']->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'workout_log';
			$this->xref_table_name = 'workout_log_library_exercise';
			$this->xrefed_table_name = 'library_exercise';
			$return = $this->perform('this->post_xref_list',$exercises);
			if ( $return['response'] >= 300 ) {
				return $return;
			}
		}
		// --------------------------------------------------------------------------------------------------------
		// create the cross reference entries only
		// --------------------------------------------------------------------------------------------------------
		if ( count($equipment) > 0 ) {
			$this->id = $return_create['response']->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'workout_log';
			$this->xref_table_name = 'workout_log_library_equipment';
			$this->xrefed_table_name = 'library_equipment';
			$return = $this->perform('this->post_xref_list',$equipment);
			if ( $return['response'] >= 300 ) {
				return $return;
			}
		}
		// --------------------------------------------------------------------------------------------------------
		// Now that everything worked, delete the workout_log_pending entry
		// --------------------------------------------------------------------------------------------------------
		$key['calendar_event_participation_id'] = $data->participation_id;
		$key['library_workout_id'] = $data->workout_id;
		$return = $this->perform('table_workoutdb_workout_log_pending->getForAndKeys',$key,'id');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		foreach( $return['response'] as $pending ) {
			$return = $this->perform('table_workoutdb_workout_log_pending->delete',$pending->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		
		return $return_create;
	}

	// ==================================================================================================================
	// Update a workout log entry
	// ==================================================================================================================
	
	public function update( $data ) {
		// --------------------------------------------------------------------------------------------------------
		// check for unchangable fields
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'participation_id') ) {
			unset($data->participation_id);
		}
		if ( property_exists($data,'user_id') ) {
			unset($data->user_id);
		}
		if ( property_exists($data,'workout_id') ) {
			unset($data->workout_id);
		}
		if ( property_exists($data,'start') ) {
			unset($data->start);
		}
		if ( property_exists($data,'workout_recording_type_id') ) {
			unset($data->workout_recording_type_id);
		}
		// --------------------------------------------------------------------------------------------------------
		// Deal with Aliases
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'workout_recording_type_id') ) {
			$data->library_workout_recording_type_id = $data->workout_recording_type_id;
		}
		// --------------------------------------------------------------------------------------------------------
		// Id must be vald
		// --------------------------------------------------------------------------------------------------------
		if ( !property_exists($data,'id') || is_null($data->id) || empty($data->id) || !is_numeric($data->id) ) {
			return $this->return_handler->results(400,"A valid ID must be used",null);
		}
		
		if ( property_exists($data,'log') ) {
			// --------------------------------------------------------------------------------------------------------
			// get the user
			// --------------------------------------------------------------------------------------------------------
			$return = $this->perform('action_user->getForWorkoutLog',$data->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $return['status'] > 200 ) {
				return $this->return_handler->results(400,"Invalid user_id",new stdClass());
			}
			$user = $return['response'];
			// ---------------------------------------------------------------------------------
			// set gender
			// ---------------------------------------------------------------------------------
			$gender = "both";
			if ( $user->gender == "M" ) {
				$gender = "male";
			} else if ( $user->gender == "F" ) {
				$gender = "female";
			}
			// ========================================================================================================
			// Format and validate the workout log
			// ========================================================================================================
			// Get the existing workout log values
			// --------------------------------------------------------------------------------------------------------
			$return = $this->perform('table_workoutdb_workout_log->getForId',$data->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $return['status'] > 200 ) {
				return $this->return_handler->results(400,"Invalid workout_log_id",new stdClass());
			}
			$workout_log = $return['response'];
			
			// --------------------------------------------------------------------------------------------------------
			// If workout log is based on a workout, set the time limit based on the workout
			// --------------------------------------------------------------------------------------------------------
			if ( !is_null($workout_log->library_workout_id) ) {
				$return = $this->perform('table_workoutdb_library_workout->getForId',$workout_log->library_workout_id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				if ( $return['status'] > 200 ) {
					return $this->return_handler->results(400,"Invalid workout_log_id",new stdClass());
				}
				$workout = $return['response'];
				// print_r($workout);
				
				// set the time_limit to the workout's time_limit
				if ( property_exists($workout,'time_limit') ) {
					$data->time_limit = clone $workout->time_limit;
				}
				$data->library_workout_recording_type_id = $workout->library_workout_recording_type_id;
			}
			
			// echo json_encode($data);
			// --------------------------------------------------------------------------------------------------------
			// Validate Result
			// --------------------------------------------------------------------------------------------------------
			if ( !is_null($data->library_workout_recording_type_id) &&
			     ( $data->library_workout_recording_type_id == 0 ||
			       $data->library_workout_recording_type_id == 1 ||
			       $data->library_workout_recording_type_id == 2 ) ) {
				if ( !property_exists($data,'result_unit') || !property_exists($data->result_unit,'input') || !is_numeric($data->result_unit->input) || ((string) ((int) $data->result_unit->input)) != $data->result_unit->input ) {
					return $this->return_handler->results(400,"Result must be an Integer.",new stdClass());
				}
			}
			// --------------------------------------------------------------------------------------------------------
			// validate the workoutlog, create a storable json of the workoutlog,
			// create an exercise and equipment list for the workoutlog
			// --------------------------------------------------------------------------------------------------------
			$this->load->library('simplify');
			$return = $this->simplify->workout_log($data->log);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
	
			$log = clone $return['response']->workout_log;
			
			$exercises = $return['response']->exercises;
			$equipment = $return['response']->equipment;
			// --------------------------------------------------------------------------------------------------------
			// if auto_calculate_result exists and is set to true, auto calculate the results
			// --------------------------------------------------------------------------------------------------------
			if ( property_exists($data,'auto_calculate_result') && $data->auto_calculate_result &&
			     !is_null($data->library_workout_recording_type_id) && $data->library_workout_recording_type_id >= 2 && $data->library_workout_recording_type_id <= 5 ) {
				$return = $this->perform('this->autoCalculate',$data,$user->id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				$auto = $return['response'];
				
				if ( !property_exists($data,'result_unit') ) {
					$data->result_unit = new stdClass();
				}
				
				switch ($data->library_workout_recording_type_id) {
					case 2:                                              // repeats
						$data->result_unit->id = $auto->repeats->id;
						$data->result_unit->input = $auto->repeats->value;
						break;
					case 3:                                              // weight
						$data->result_unit->id = $auto->weight->id;
						$data->result_unit->input = $auto->weight->value;
						break;
					case 4:                                              // distance
						$data->result_unit->id = $auto->distance->id;
						$data->result_unit->input = $auto->distance->value;
						break;
					case 5:                                              // height
						$data->result_unit->id = $auto->height->id;
						$data->result_unit->input = $auto->height->value;
						break;
				}
			} else {
				$data->auto_calculate_result = false;
			}
			// --------------------------------------------------------------------------------------------------------
			// Load the needed lookup tables
			// --------------------------------------------------------------------------------------------------------
			$return = $this->perform('action_workout->setupNeededLookupTables');
			if ( $return['status'] > 300 ) {
				return $return;
			}
			// --------------------------------------------------------------------------------------------------------
			// Create the workoutlog summary
			// --------------------------------------------------------------------------------------------------------
			$return_summary_complete = $this->perform('action_workout->createWorkoutSummary',$log->complete_round);
			$return_summary_incomplete = $this->perform('action_workout->createWorkoutSummary',$log->incomplete_round);
			
			$summary = new stdClass();
			$summary->complete_round = clone $return_summary_complete->{$gender};
			$summary->incomplete_round = clone $return_summary_incomplete->{$gender};
			// --------------------------------------------------------------------------------------------------------
			// move the original log, re-formatted log and new log summary into the data to be updated
			// --------------------------------------------------------------------------------------------------------
			$data->original_json_log = json_encode($data->log);
			unset($data->log);
			$data->json_log = json_encode($log);
			$data->json_log_summary = json_encode($summary);
		} else {
			// --------------------------------------------------------------------------------------------------------
			// If the log was not changed, do not change the log summary
			// --------------------------------------------------------------------------------------------------------
			if ( property_exists($data,'json_log_summary') ) {
				unset($data->json_log_summary);
			}
		}
		// --------------------------------------------------------------------------------------------------------
		// update the entry
		// --------------------------------------------------------------------------------------------------------
		$return_update = $this->perform('table_workoutdb_workout_log->update',$data);
		if ( $return_update['status'] >= 300 ) {
			return $return_update;
		}
		
		// --------------------------------------------------------------------------------------------------------
		// Only update the workoutlog exercise and equipment lists if the log was in the data
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'original_json_log') ) {
			// --------------------------------------------------------------------------------------------------------
			// create the cross reference entries only
			// --------------------------------------------------------------------------------------------------------
			$this->id = $data->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'workout_log';
			$this->xref_table_name = 'workout_log_library_exercise';
			$this->xrefed_table_name = 'library_exercise';
			$return = $this->perform('this->put_xref_list',$exercises);
			if ( $return['response'] >= 300 ) {
				return $return;
			}
			// --------------------------------------------------------------------------------------------------------
			// create the cross reference entries only
			// --------------------------------------------------------------------------------------------------------
			$this->id = $data->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'workout_log';
			$this->xref_table_name = 'workout_log_library_equipment';
			$this->xrefed_table_name = 'library_equipment';
			$return = $this->perform('this->put_xref_list',$equipment);
			if ( $return['response'] >= 300 ) {
				return $return;
			}
		}
		return $return_update;
	}

	// ==================================================================================================================
	// Delete a workout log entry
	// ==================================================================================================================
	
	public function delete( $p_id = null ) {
		// --------------------------------------------------------------------------------------------------------
		// Id must be vald
		// --------------------------------------------------------------------------------------------------------
		if ( is_null($p_id) || empty($p_id) ) {
			return $this->return_handler->results(400,"A valid ID must be used",null);
		}
		
		$this->id = $p_id;
		// --------------------------------------------------------------------------------------------------------
		//  delete the workout_log_library_exercise entries
		// --------------------------------------------------------------------------------------------------------
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'workout_log';
		$this->linked_table_name = 'workout_log_library_exercise';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// --------------------------------------------------------------------------------------------------------
		//  delete the workout_log_library_equipment entries
		// --------------------------------------------------------------------------------------------------------
		$this->id = $p_id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'workout_log';
		$this->linked_table_name = 'workout_log_library_equipment';
		$return = $this->perform('this->delete_linked_entries');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// --------------------------------------------------------------------------------------------------------
		// delete the workout_log
		// --------------------------------------------------------------------------------------------------------
		return $this->perform('table_workoutdb_workout_log->delete',$p_id);
	}

	// =========================================================================================================================================
	// Auto Calculate
	// =========================================================================================================================================
	
	public function autoCalculate($data,$p_user_id) {
		// echo "AutoCalculate type:$library_workout_recording_type_id<br />\n";
		// echo json_encode($data) . "<br />\n";
		if ( !property_exists($data,'log') ) {
			return $this->return_handler->results(400,"log is mandatory.",new stdClass());
		}
		$workout_log = $data->log;
		// -------------------------------------------------------------------------------------------------------------------------------------
		// Get the User that is logged in
		// -------------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_user->getForId',$p_user_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		// store off the user obje`ct
		$this->user = clone $return['response'];
		
		// print_r($this->user);
		// -------------------------------------------------------------------------------------------------------------------------------------
		// Get a lookup table for the Unit Of Measure
		// -------------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getUOMLookup');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// store off the uom lookup table
		$this->uom = $return['response'];
		$this->system = 'english';
		
		// print_r($this->uom);
		// echo json_encode($this->uom);
		// -------------------------------------------------------------------------------------------------------------------------------------
		// Tally the Workout log
		// -------------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->tallyLevel','workout_log',$workout_log,'workout_log');
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		// echo "total repeats:" . $return['response'] . ""<br />\n";
		// -------------------------------------------------------------------------------------------------------------------------------------
		return $this->perform('this->formatAutoCalculateResponse',$return['response']);
	}

	public function formatAutoCalculateResponse($p_totals) {
		// -------------------------------------------------------------------------------------------------------------------------------------
		// Get a lookup table for the Standard Unit Of Measure For the Measurement Types (calories, distance, height, time, weight)
		// -------------------------------------------------------------------------------------------------------------------------------------
		$standard_uom = array();
		foreach($this->uom as $uom) {
			if ( ($uom->library_measurement_system_name = 'all' && $uom->english_conversion == 1) ||
			     ($this->system == 'english' && $uom->library_measurement_system_name = 'english' && $uom->english_conversion == 1) ||
			     ($this->system == 'metric' && $uom->library_measurement_system_name = 'metric' && $uom->metric_conversion == 1) ) {
				$standard_uom[strtolower($uom->library_measurement_name)] = $uom->id;
			} 
		}
		// echo json_encode($standard_uom);
		// -------------------------------------------------------------------------------------------------------------------------------------
		// create an object for each total ( value, uom_id )
		// -------------------------------------------------------------------------------------------------------------------------------------
		$response = new stdClass();
		foreach( $p_totals as $key => $value ) {
			$response->{$key} = new stdClass();
			$response->{$key}->value = $value;
			if ( array_key_exists($key, $standard_uom) ) {
				$response->{$key}->id = $standard_uom[$key];
			} else {
				$response->{$key}->id = null;
			}
		}
		
		return $this->return_handler->results(200,"",$response);
	}

	// ===================================================================================================================================================
	// Get the Units of Measure with their Measurement ( calories, distance, height, time, weight ) and System of Measurement (english, metric, russian, all)
	// ===================================================================================================================================================

	public function getUOMLookup() {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "library_measurement_system_unit.id 'library_measurement_system_unit.id', ";
		$sql .= "library_measurement_system_unit.abbr 'library_measurement_system_unit.abbr', ";
		$sql .= "library_measurement_system_unit.name 'library_measurement_system_unit.name', ";
		$sql .= "library_measurement_system_unit.metric_conversion 'library_measurement_system_unit.metric_conversion', ";
		$sql .= "library_measurement_system_unit.english_conversion 'library_measurement_system_unit.english_conversion', ";
		$sql .= "library_measurement_system_unit.library_measurement_id 'library_measurement_system_unit.library_measurement_id', ";
		$sql .= "library_measurement_system_unit.library_measurement_system_id 'library_measurement_system_unit.library_measurement_system_id', ";
		$sql .= "library_measurement.name 'library_measurement.name', ";
		$sql .= "library_measurement_system.name 'library_measurement_system.name' ";
		$sql .= "FROM ";
		$sql .= "library_measurement_system_unit ";
		$sql .= "LEFT OUTER JOIN library_measurement ";
		$sql .= "ON library_measurement.id = library_measurement_system_unit.library_measurement_id ";
		$sql .= "LEFT OUTER JOIN library_measurement_system ";
		$sql .= "ON library_measurement_system.id = library_measurement_system_unit.library_measurement_system_id ";
		$sql .= "ORDER BY ";
		$sql .= "library_measurement_system_unit.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach( $rows as $row ) {
			
			// echo json_encode($row) . "<br />\n<br />\n";

			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// echo json_encode($row) . "<br />\n<br />\n";
			
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// echo json_encode($row) . "<br />\n<br />\n";
			
			// put entry into entries
			$entries[$row->library_measurement_system_unit->id] = clone $row->library_measurement_system_unit;
			$entries[$row->library_measurement_system_unit->id]->library_measurement_name = $row->library_measurement->name;
			$entries[$row->library_measurement_system_unit->id]->library_measurement_system_name = $row->library_measurement_system->name;
			
			// echo json_encode($entries[$row->library_measurement_system_unit->id]) . "<br />\n<br />\n";
		}
		
		// echo json_encode($entries) . "<br />\n<br />\n";
		
		return $this->return_handler->results(200,"",$entries);
	}

	// ===================================================================================================================================================
	// Tally
	// ===================================================================================================================================================

	public function tallyLevel($level_name,$level,$breadcrumb) {
		// echo "$breadcrumb<br />\n";
		
		 if ( is_array($level) ) {
			$return = $this->perform('this->tallyArray',$level_name,$level,$breadcrumb);
			return $return;
		} else if ( is_object($level) ) {
			$return = $this->perform('this->tallyObject',$level_name,$level,$breadcrumb);
			return $return;
		}
		
		return $this->return_handler->results(400,$breadcrumb . " node not an object or array.",null);
	}
	
	public function tallyArray($level_name,$level,$breadcrumb) {
		// echo "array $breadcrumb<br />\n";
		
		$total_repeats = 0;
		$total_distance = 0;
		$total_height = 0;
		$total_weight = 0;
		
		$i = 0;
		foreach( $level as $node ) {
			$return = $this->perform('this->tallyLevel',$level_name,$node,$breadcrumb . '[' . $i . ']');
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			$total_repeats += $return['response']->repeats;
			$total_distance += $return['response']->distance;
			$total_height += $return['response']->height;
			$total_weight += $return['response']->weight;
			
			++$i;
		}
		
		$response = new stdClass();
		$response->repeats = $total_repeats;
		$response->distance = $total_distance;
		$response->height = $total_height;
		$response->weight = $total_weight;
		return $this->return_handler->results(200,'',$response);
	}
	
	public function tallyObject($level_name,$level,$breadcrumb) {
		// echo "object $breadcrumb<br />\n";
		
		$total_repeats = 0;
		$total_distance = 0;
		$total_height = 0;
		$total_weight = 0;
		
		switch ($level_name) {
			case 'workout_log':
				if ( property_exists($level,'complete_round') ) {
					$return = $this->perform('this->tallyLevel','complete_round',$level->complete_round,$breadcrumb . '.complete_workout');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// print_r($return); echo "<br />\n";
					$total_repeats += $return['response']->repeats;
					$total_distance += $return['response']->distance;
					$total_height += $return['response']->height;
					$total_weight += $return['response']->weight;
				}
				if ( property_exists($level,'incomplete_round') ) {
					$return = $this->perform('this->tallyLevel','incomplete_round',$level->incomplete_round,$breadcrumb . '.incomplete_workout');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$total_repeats += $return['response']->repeats;
					$total_distance += $return['response']->distance;
					$total_height += $return['response']->height;
					$total_weight += $return['response']->weight;
				}
				if ( property_exists($level,'repeats') ) {
					$return = $this->perform('this->processRepeats','repeats',$level->repeats,$breadcrumb . '.repeats');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$total_repeats *= $return['response']->repeats;
					$total_distance *= $return['response']->repeats;
					$total_height *= $return['response']->repeats;
					$total_weight *= $return['response']->repeats;
				}
				break;
			
			case 'complete_round':
			case 'incomplete_round':
				if ( property_exists($level,'set') ) {
					$return = $this->perform('this->tallyLevel','set',$level->set,$breadcrumb . '.set');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// print_r($return); echo "<br />\n";
					$total_repeats += $return['response']->repeats;
					$total_distance += $return['response']->distance;
					$total_height += $return['response']->height;
					$total_weight += $return['response']->weight;
				}
				if ( property_exists($level,'repeats') ) {
					$return = $this->perform('this->processRepeats','repeats',$level->repeats,$breadcrumb . '.repeats');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$total_repeats *= $return['response']->repeats;
					$total_distance *= $return['response']->repeats;
					$total_height *= $return['response']->repeats;
					$total_weight *= $return['response']->repeats;
				}
				break;
				
			case 'set':
				if ( property_exists($level,'exercise_group') ) {
					$return = $this->perform('this->tallyLevel','exercise_group',$level->exercise_group,$breadcrumb . '.exercise_group');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// print_r($return); echo "<br />\n";
					$total_repeats += $return['response']->repeats;
					$total_distance += $return['response']->distance;
					$total_height += $return['response']->height;
					$total_weight += $return['response']->weight;
				}
				if ( property_exists($level,'repeats') ) {
					$return = $this->perform('this->processRepeats','repeats',$level->repeats,$breadcrumb . '.repeats');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$total_repeats *= $return['response']->repeats;
					$total_distance *= $return['response']->repeats;
					$total_height *= $return['response']->repeats;
					$total_weight *= $return['response']->repeats;
				}
				break;
				
			case 'exercise_group':
				if ( property_exists($level,'exercise') ) {
					$return = $this->perform('this->tallyLevel','exercise',$level->exercise,$breadcrumb . '.exercise');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// print_r($return); echo "<br />\n";
					$total_repeats += $return['response']->repeats;
					$total_distance += $return['response']->distance;
					$total_height += $return['response']->height;
					$total_weight += $return['response']->weight;
				}
				if ( property_exists($level,'repeats') ) {
					$return = $this->perform('this->processRepeats','repeats',$level->repeats,$breadcrumb . '.repeats');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$total_repeats *= $return['response']->repeats;
					$total_distance *= $return['response']->repeats;
					$total_height *= $return['response']->repeats;
					$total_weight *= $return['response']->repeats;
					// echo "distance:$total_distance repeats:$total_repeats<br />\n";
				}
				break;
			
			case 'exercise':
				if ( property_exists($level,'distance_measurement') ) {
					$return = $this->perform('this->processDistanceMeasurement','distance_measurement',$level->distance_measurement,$breadcrumb . '.distance_measurement');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// print_r($return); echo "<br />\n";
					$total_distance += $return['response']->distance;
					$total_repeats = 1;
					// echo $return['response']->distance . "<br />\n";
				}
				if ( property_exists($level,'equipment') ) {
					$return = $this->perform('this->tallyLevel','equipment',$level->equipment,$breadcrumb . '.equipment');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// print_r($return); echo "<br />\n";
					$total_distance += $return['response']->distance;
					$total_height += $return['response']->height;
					$total_weight += $return['response']->weight;
				}
				break;
			
			case 'equipment':
				if ( property_exists($level,'unit') ) {
					$return = $this->perform('this->processUnit','unit',$level->unit,$breadcrumb . '.unit');
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// print_r($return); echo "<br />\n";
					$total_distance += $return['response']->distance;
					$total_height += $return['response']->height;
					$total_weight += $return['response']->weight;
				}
				break;
			
			default:
				break;
		}
		
		$response = new stdClass();
		$response->repeats = $total_repeats;
		$response->distance = $total_distance;
		$response->height = $total_height;
		$response->weight = $total_weight;
		return $this->return_handler->results(200,'',$response);
	}

	public function processRepeats($level_name,$level,$breadcrumb) {
		// echo "$breadcrumb<br />\n";
		// echo json_encode($level) . "<br />\n";
		
		$repeats = 0;
		
		if ( !is_object($level) ) {
			return $this->return_handler->results(400,$breadcrumb . " is not an object.",$this->formatRepeatsResponse($repeats));
		}
		if ( count((array) $level) == 0 ) {
			return $this->return_handler->results(200,$breadcrumb . " is empty. AutoCalc Requires Completed workout log.",$this->formatRepeatsResponse($repeats));
		}
		if ( !property_exists($level,'input') ) {
			return $this->return_handler->results(200,$breadcrumb . " id is missing. AutoCalc Requires Completed workout log.",$this->formatRepeatsResponse($repeats));
		}
		if ( !is_string($level->input) ) {
			return $this->return_handler->results(200,$breadcrumb . ".input is not a String.",$this->formatRepeatsResponse($repeats));
		}
		
		$temp = explode(':', $level->input);
		if ( count($temp) == 1 ) {
			// echo "temp[0]:" . $temp[0] . "<br />\n"; 
			if ( is_null($temp[0]) || empty($temp[0]) || !is_numeric($temp[0]) ) {
				return $this->return_handler->results(400,$breadcrumb . ".input is empty!",$this->formatRepeatsResponse($repeats));
			}
			$value = (float) $temp[0];
		} else if ( count($temp) == 2 ) {
			// echo "temp[1]:" . $temp[1] . "<br />\n"; 
			if ( is_null($temp[1]) || empty($temp[1]) || !is_numeric($temp[1]) ) {
				return $this->return_handler->results(400,$breadcrumb . ".input does not have a value! AutoCalc Requires Completed workout log.",$this->formatRepeatsResponse($repeats));
			}
			$value = (float) $temp[1];
		}
		
		$repeats += $value;
		
		return $this->return_handler->results(200,"",$this->formatRepeatsResponse($repeats));
	}
	
	private function formatRepeatsResponse($repeats) {
		$response = new stdClass();
		$response->repeats = $repeats;
		
		return $response;
	}

	public function processDistanceMeasurement($level_name,$level,$breadcrumb) {
		// echo "$breadcrumb<br />\n";
		// echo json_encode($level) . "<br />\n";
		
		$distance = 0;
		
		if ( !is_object($level) ) {
			return $this->return_handler->results(400,$breadcrumb . " is not an object.",$this->formatDistanceResponse($distance));
		}
		if ( count((array) $level) == 0 ) {
			return $this->return_handler->results(200,$breadcrumb . " is empty. AutoCalc Requires Completed workout log.",$this->formatDistanceResponse($distance));
		}
		if ( !property_exists($level,'id') ) {
			return $this->return_handler->results(200,$breadcrumb . " id is missing. AutoCalc Requires Completed workout log.",$this->formatDistanceResponse($distance));
		}
		if ( !is_int($level->id) ) {
			return $this->return_handler->results(400,$breadcrumb . ".id is not an Integer.",$this->formatDistanceResponse($distance));
		}
		if ( !array_key_exists($level->id,$this->uom) ) {
			return $this->return_handler->results(400,$breadcrumb . ".id is not valid!",null);
		}
		if ( $this->uom[$level->id]->library_measurement_name != "Distance" ) {
			return $this->return_handler->results(400,$breadcrumb . ".id is not a Distance measurement!",null);
		}
		if ( !property_exists($level,'value') ) {
			return $this->return_handler->results(200,$breadcrumb . " value is missing. AutoCalc Requires Completed workout log.",$this->formatDistanceResponse($distance));
		}
		if ( !property_exists($level->value,'input') ) {
			return $this->return_handler->results(200,$breadcrumb . ".value input is missing. AutoCalc Requires Completed workout log.",$this->formatDistanceResponse($distance));
		}
		if ( !is_string($level->value->input) ) {
			return $this->return_handler->results(200,$breadcrumb . ".value.input is not a String.",$this->formatDistanceResponse($distance));
		}
		
		$temp = explode(':', $level->value->input);
		if ( count($temp) == 1 ) {
			// echo "temp[0]:" . $temp[0] . "<br />\n"; 
			if ( is_null($temp[0]) || empty($temp[0]) || !is_numeric($temp[0]) ) {
				return $this->return_handler->results(400,$breadcrumb . ".value.input is empty! AutoCalc Requires Completed workout log.",$this->formatDistanceResponse($distance));
			}
			$value = (float) $temp[0];
		} else if ( count($temp) == 2 ) {
			// echo "temp[1]:" . $temp[1] . "<br />\n"; 
			if ( is_null($temp[1]) || empty($temp[1]) || !is_numeric($temp[1]) ) {
				return $this->return_handler->results(400,$breadcrumb . ".value.input does not have a value! AutoCalc Requires Completed workout log.",$this->formatDistanceResponse($distance));
			}
			$value = (float) $temp[1];
		}
		
		if ( $this->system = "english" ) {
			$distance += $value * $this->uom[$level->id]->english_conversion;
		} else {
			$distance += $value * $this->uom[$level->id]->metric_conversion;
		}
		
		return $this->return_handler->results(200,"",$this->formatDistanceResponse($distance));
	}
	
	private function formatDistanceResponse($distance) {
		$response = new stdClass();
		$response->distance = $distance;
		
		return $response;
	}

	public function processUnit($level_name,$level,$breadcrumb) {
		// echo "$breadcrumb<br />\n";
		// echo json_encode($level) . "<br />\n";
		
		$distance = 0;
		$height = 0;
		$weight = 0;
		
		if ( !is_object($level) ) {
			return $this->return_handler->results(400,$breadcrumb . " is not an object.",$this->formatUnitResponse($distance,$height,$weight));
		}
		if ( count((array) $level) == 0 ) {
			return $this->return_handler->results(200,$breadcrumb . " is empty. AutoCalc Requires Completed workout log.",$this->formatUnitResponse($distance,$height,$weight));
		}
		if ( !property_exists($level,'id') ) {
			return $this->return_handler->results(200,$breadcrumb . " id is missing. AutoCalc Requires Completed workout log.",$this->formatUnitResponse($distance,$height,$weight));
		}
		if ( !is_int($level->id) ) {
			return $this->return_handler->results(400,$breadcrumb . ".id is not an Integer.",$this->formatUnitResponse($distance,$height,$weight));
		}
		if ( !array_key_exists($level->id,$this->uom) ) {
			return $this->return_handler->results(400,$breadcrumb . ".id is not valid!",null);
		}
		if ( !(property_exists($level,'man') && property_exists($level->man,'input') && is_string($level->man->input)) &&
		     !(property_exists($level,'woman') && property_exists($level->woman,'input') && is_string($level->woman->input)) ) {
			return $this->return_handler->results(400,$breadcrumb . " man and women are both is not valid!",null);
		}
		
		// get the correct input
		if ( $this->user->gender == 'F' && property_exists($level,'woman') && property_exists($level->woman,'input') && is_string($level->woman->input) ) {
			$input = $level->woman->input;
			$breadcrumb_gender = $breadcrumb . ".woman";
		} else if ( property_exists($level,'man') && property_exists($level->man,'input') && is_string($level->man->input) ) {
			$input = $level->man->input;
			$breadcrumb_gender = $breadcrumb . ".man";
		} else if ( property_exists($level,'woman') && property_exists($level->woman,'input') && is_string($level->woman->input) ) {
			$input = $level->woman->input;
			$breadcrumb_gender = $breadcrumb . ".woman";
		}
		// echo "input:|$input|<br />\n";
		
		$value = 0;
		$temp = explode(':', $input);
		if ( count($temp) == 1 ) {
			// echo "temp[0]:" . $temp[0] . "<br />\n"; 
			if ( is_null($temp[0]) || empty($temp[0]) || !is_numeric($temp[0]) ) {
				return $this->return_handler->results(400,$breadcrumb_gender . ".input is empty! AutoCalc Requires Completed workout log.",$this->formatUnitResponse($distance,$height,$weight));
			}
			$value = (float) $temp[0];
		} else if ( count($temp) == 2 ) {
			// echo "temp[1]:" . $temp[1] . "<br />\n"; 
			if ( is_null($temp[1]) || empty($temp[1]) || !is_numeric($temp[1]) ) {
				return $this->return_handler->results(400,$breadcrumb_gender . ".input does not have a value! AutoCalc Requires Completed workout log.",$this->formatUnitResponse($distance,$height,$weight));
			}
			$value = (float) $temp[1];
		}
		// echo "value=$value<br />\n";
		
		// echo "name:" . $this->uom[$level->id]->library_measurement_name . " conversion:" . $this->uom[$level->id]->english_conversion . "<br />\n";
		
		if ( $this->system == 'english' ) {
			switch ( $this->uom[$level->id]->library_measurement_name ) {
				case 'Distance':
					$distance += $value * $this->uom[$level->id]->english_conversion;
					break;
				case 'Weight':
					$weight += $value * $this->uom[$level->id]->english_conversion;
					break;
				case 'Height':
					$height += $value * $this->uom[$level->id]->english_conversion;
					break;
			}
		} else {
			switch ( $this->uom[$level->id]->library_measurement_name ) {
				case 'Distance':
					$distance += $value * $this->uom[$level->id]->metric_conversion;
					break;
				case 'Weight':
					$weight += $value * $this->uom[$level->id]->metric_conversion;
					break;
				case 'Height':
					$height += $value * $this->uom[$level->id]->metric_conversion;
					break;
			}
		}
		
		return $this->return_handler->results(200,"",$this->formatUnitResponse($distance,$height,$weight));
	}
	
	private function formatUnitResponse($distance,$height,$weight) {
		$response = new stdClass();
		$response->distance = $distance;
		$response->height = $height;
		$response->weight = $weight;
		
		return $response;
	}
}