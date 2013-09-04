<?php

class action_workout extends action_generic {
	
	// lookup table variables
	protected $db_database_name;
	protected $db_table_name;
	protected $db_field_name;
	protected $lookup_table_name;
	
	// lookup tables
	protected $recording_type = array();
	protected $measurement = array();
	protected $unit = array();
	protected $equipment = array();
	protected $exercise = array();
	protected $exercise_deletable = array();
	
	protected $exerciseList = array();
	protected $equipmentList = array();
	protected $mandatoryList = array();
	
	protected $expression_variables = array();

	public function __construct() {
		parent::__construct();
		
		$this->expression_variables = array(
			'{#maxe}' => 'Max',
			'{#brn}' => 'Number of Round',
			'{#bbw}' => 'BodyWeight',
			'{#mom}' => 'Minute on Minute',
			'{#1RM}' => '1RM'
		);
	}
	
	// ==================================================================================================================
	// Get a list of the workouts scheduled for an event (entry/UTCDateTime)
	// ==================================================================================================================
	
	public function getForEntryStart ($p_entry_id,$p_utc_start) {
		// get the calendar entry name and the workouts for that calendar entry for a given date/time
		$sql  = "SELECT en.name, w.library_workout_id id ";
		$sql .= "FROM calendar_entry en ";
		$sql .= "LEFT OUTER JOIN calendar_event ev ";
		$sql .= "LEFT OUTER JOIN calendar_event_library_workout w ";
		$sql .= "ON w.calendar_event_id = ev.id ";
		$sql .= "ON ev.calendar_entry_id = en.id AND ev.start = " . $p_utc_start . " ";
		$sql .= "WHERE en.id = " . $p_entry_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result(); 
			
			$entry = new stdClass();
			$entry->key = null;
			foreach ( $rows as $row ) {
				if ( is_null($entry->key) ) {
					$entry->key = $p_entry_id . "_" . $p_utc_start;
					$entry->name = $row->name;
					$entry->workout = array();
				}
				if ( !is_null($row->id) ) {
					array_push($entry->workout,(int) $row->id);
				}
			}
			
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
	}

	// ==================================================================================================================
	// Get a list of the workouts scheduled for an event (entry/UTCDateTime) and client_user
	// ==================================================================================================================
	
	public function getForEntryStartClientUser ($p_entry_id,$p_utc_start,$p_client_user_id) {
		// -----------------------------------------------------------------------------------
		// get the user entry
		// -----------------------------------------------------------------------------------
		$return = $this->perform('action_user->getForClientUser',$p_client_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid client_user_id",new stdClass());
		}
		$user = $return['response'];
		// -----------------------------------------------------------------------------------
		// get the calendar_entry entry
		// -----------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_calendar_entry->getForId',$p_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$entry = clone $return['response'];
		// -----------------------------------------------------------------------------------
		// get the workouts
		// -----------------------------------------------------------------------------------
		if ( $entry->wod ) {
			return $this->perform('this->getForEntryStartClientUserByWOD',$entry,$p_utc_start,$user->id,$user->gender);
		} else {
			return $this->perform('this->getForEntryStartClientUserByEvent',$p_entry_id,$p_utc_start,$user->id,$user->gender);
		}
	}
		
	public function getForEntryStartClientUserByWOD($p_entry,$p_utc_start,$p_user_id,$p_gender) {
		// -----------------------------------------------------------------------------------
		// get the entry's calendar
		// -----------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_calendar->getForId',$p_entry->calendar_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Not a valid calendar_id",new stdClass());
		}
		$calendar = $return['response'];
		// --------------------------------------------------------------------------------------------------------------------
		//  Set the timezone to the calendar's timezone
		// --------------------------------------------------------------------------------------------------------------------
		// Set the server's default timezone
		date_default_timezone_set($calendar->timezone);
		// -----------------------------------------------------------------------------------
		// get the workouts for a WOD
		// -----------------------------------------------------------------------------------
		$yyyymmdd = date("Ymd",$p_utc_start);
		
		$sql  = "";
		$sql .= "SELECT w.*, ";
		$sql .= "media.id media_id, media.media_url media_url, ";
		$sql .= "l.count, ";
		$sql .= "l.start ";
		$sql .= "FROM calendar_entry_template_wod wod, ";
		$sql .= "calendar_entry_template_wod_library_workout xref, ";
		$sql .= "library_workout w ";
		$sql .= "LEFT OUTER JOIN library_workout_media_last_entered media ";
		$sql .= "ON media.library_workout_id = w.id ";
		$sql .= "LEFT OUTER JOIN workout_logged_by_user l ";
		$sql .= "ON l.library_workout_id = w.id AND l.user_id = " . $p_user_id . " ";
		$sql .= "WHERE wod.calendar_entry_template_id = " . $p_entry->calendar_entry_template_id . " AND wod.yyyymmdd = " . $yyyymmdd . " ";
		$sql .= "AND xref.calendar_entry_template_wod_id = wod.id ";
		$sql .= "AND w.id = xref.library_workout_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result(); 
		
		$entries = array();
		foreach ( $rows as $row ) {
			// print_r($row); echo "<br /><br />";
			// load the workout entry
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->name = $row->name;
			$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
			$entry->media = format_media($row->media_id,$row->media_url);
			$entry->last_logged = cast_int($row->start);
			$entry->number_of_completed_log = cast_int($row->count);
			$entry->benchmark = cast_boolean($row->benchmark);
			$entry->note = $row->note;
			
			if ( $p_gender == "M" ) {
				$entry->workout_summary = json_decode($row->json_workout_summary_male);
			} else if ( $p_gender == "F" ) {
				$entry->workout_summary = json_decode($row->json_workout_summary_female);
			} else {
				$entry->workout_summary = json_decode($row->json_workout_summary);
			}
			
			array_push($entries,$entry);
			unset($entry);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}
		
	public function getForEntryStartClientUserByEvent($p_entry_id,$p_utc_start,$p_user_id,$p_gender) {
		// -----------------------------------------------------------------------------------
		// get the workouts for that calendar entry for a given date/time
		// -----------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT w.*, ";
		$sql .= "media.id media_id, media.media_url media_url, ";
		$sql .= "u.number_of_users, ";
		$sql .= "p.last_participation ";
		$sql .= "FROM calendar_event ev ";
		$sql .= "calendar_event_library_workout xref, ";
		$sql .= "library_workout w ";
		$sql .= "LEFT OUTER JOIN library_workout_media_last_entered media ";
		$sql .= "ON media.library_workout_id = w.id ";
		$sql .= "LEFT OUTER JOIN library_workout_number_of_users u ";
		$sql .= "ON u.library_workout_id = w.id ";
		$sql .= "LEFT OUTER JOIN library_workout_last_participation_by_user p ";
		$sql .= "ON p.library_workout_id = w.id AND p.user_id = " . $p_user_id . " ";
		$sql .= "WHERE ev.calendar_entry_id = " . $p_entry_id . " AND ev.start = " . $p_utc_start . " ";
		$sql .= "AND xref.calendar_event_id = ev.id ";
		$sql .= "AND w.id = xref.library_workout_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result(); 
		
		$entries = array();
		foreach ( $rows as $row ) {
			// print_r($row); echo "<br /><br />";
			// load the workout entry
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->name = $row->name;
			$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
			$entry->media = format_media($row->media_id,$row->media_url);
			$entry->last_performed = cast_int($row->last_participation);
			$entry->number_of_user = cast_int($row->number_of_users);
			$entry->benchmark = cast_boolean($row->benchmark);
			$entry->note = $row->note;
			
			if ( $p_gender == "M" ) {
				$entry->workout_summary = json_decode($row->json_workout_summary_male);
			} else if ( $p_gender == "F" ) {
				$entry->workout_summary = json_decode($row->json_workout_summary_female);
			} else {
				$entry->workout_summary = json_decode($row->json_workout_summary);
			}
			
			array_push($entries,$entry);
			unset($entry);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// Get a list of the workouts scheduled for workout-of-the-day (template/yyyymmdd)
	// ==================================================================================================================
	
	public function getForTemplateDate ($p_template_id,$p_date) {
		// get the calendar entry name and the workouts for that calendar entry for a given date/time
		$sql  = "SELECT t.name, w.id ";
		$sql .= "FROM calendar_entry_template t ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template_wod wod ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template_wod_library_workout w ";
		$sql .= "ON w.calendar_entry_template_wod_id = wod.id ";
		$sql .= "ON wod.calendar_entry_template_id = t.id AND wod.yyyymmdd = " . $p_date . " ";
		$sql .= "WHERE t.id = " . $p_template_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result(); 
			
			$entry = new stdClass();
			$entry->key = null;
			foreach ( $rows as $row ) {
				if ( is_null($entry->key) ) {
					$entry->key = "W" . $p_template_id . "_" . $p_date;
					$entry->name = $row->name;
					$entry->workout = array();
				}
				if ( !is_null($row->id) ) {
					array_push($entry->workout,(int) $row->id);
				}
			}
			
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
	}

	// ==================================================================================================================
	// Get all workouts ( client_id is used to get participation numbers)
	// ==================================================================================================================
	
	public function getForClient($p_client_id) {
		//
		// initialize the response data
		$count = 0;
		$entries = array();
		$response->count = $count;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$exercisefrom = "";
		$equipmentfrom = "";
		$equipmentleftouterjoin = "";
		$participation_between = "";
		$where = "";
		$exercisegroupby = "";
		$equipmentgroupby = "";
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if ( isset($_GET['q_ex']) && !empty($_GET['q_ex']) ) {
			// make sure all exercises in the list are unique
			$ex = explode(",",$_GET['q_ex']);
			$ex_unique = array_unique($ex);
			$ex_list = implode(",", $ex_unique);
			
			$exercisefrom = "library_workout_library_exercise ex, ";
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "ex.library_exercise_id in (" . $ex_list . ") ";
			$where .= "AND w.id = ex.library_workout_id ";
			$exercisegroupby .= "GROUP BY ex.library_workout_id ";
			$exercisegroupby .= "HAVING count(ex.library_workout_id) = " . count($ex_unique) . " ";
		} else if ( isset($_GET['q_bw']) && !empty($_GET['q_bw']) && $_GET['q_bw'] ) {
			$equipmentleftouterjoin .= "LEFT OUTER JOIN library_workout_library_exercise ex ";
			$equipmentleftouterjoin .= "LEFT OUTER JOIN library_exercise_equipment eq ";
			$equipmentleftouterjoin .= "ON eq.library_exercise_id = ex.library_exercise_id AND eq.mandatory ";
			$equipmentleftouterjoin .= "ON ex.library_workout_id = w.id ";
			$equipmentgroupby .= "GROUP BY w.id ";
			$equipmentgroupby .= "HAVING count(eq.library_equipment_id) = 0 ";
		} else if ( isset($_GET['q_eq']) && !empty($_GET['q_eq']) ) {
			// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// make sure all exercises in the list are unique
			$eq = explode(",",$_GET['q_eq']);
			$eq_unique = array_unique($eq);
			$eq_list = implode(",", $eq_unique);
		
			$equipmentfrom = "library_workout_library_equipment eq, ";
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "eq.library_equipment_id in (" . $eq_list . ") ";
			$where .= "AND w.id = eq.library_workout_id ";
			$equipmentgroupby .= "GROUP BY eq.library_workout_id ";
			$equipmentgroupby .= "HAVING count(eq.library_workout_id) = " . count($eq_unique) . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if ( isset($_GET['q_rt']) ) {
			if ( empty($_GET['q_rt']) && $_GET['q_rt'] != '0' ) {
				if ( empty($where) ) {
					$where .= "WHERE ";
				} else {
					$where .= "AND ";
				}
				$where .= "w.library_workout_recording_type_id IS NULL ";
			} else if ( is_numeric($_GET['q_rt']) ) {
				if ( empty($where) ) {
					$where .= "WHERE ";
				} else {
					$where .= "AND ";
				}
				$where .= "w.library_workout_recording_type_id = " . $_GET['q_rt'] . " ";
			}
		}
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "w.name LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		if ( isset($_GET['q_bm']) && !empty($_GET['q_bm']) && $_GET['q_bm'] ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "w.benchmark = 1 ";
		}
		if ( isset($_GET['q_c']) && !empty($_GET['q_c']) && is_numeric($_GET['q_c']) ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "w.client_id = " . $_GET['q_c'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$check_participation_date = false;
		if ( isset($_GET['q_sp']) && !empty($_GET['q_sp']) && is_numeric($_GET['q_sp']) &&
		     isset($_GET['q_ep']) && !empty($_GET['q_ep']) && is_numeric($_GET['q_ep']) ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(p.last_participation),server.timezone,p.timezone),'%Y%m%d') BETWEEN " . $_GET['q_sp'] . " AND " . $_GET['q_ep'] . " ";
			$check_participation_date = true;
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
		$sql  = "SELECT count(w.id) cnt ";
		$sql .= "FROM ( ";
		$sql .= "SELECT w.id id ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= $exercisefrom;
		$sql .= $equipmentfrom;
		$sql .= "library_workout w ";
		$sql .= $equipmentleftouterjoin;
		if ( $check_participation_date ) {
			$sql .= "LEFT OUTER JOIN library_workout_last_participation p ";
			$sql .= "ON p.library_workout_id = w.id AND p.client_id = " . $p_client_id . " ";
		}
		$sql .= $where;
		$sql .= $exercisegroupby;
		$sql .= $equipmentgroupby;
		$sql .= ") w ";
		
		// echo "$sql<br />";
		
		$row = $this->db->query($sql)->row();
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
		
		if ( $count > 0 ) {
			// ---------------------------------------------------------------------------------------------------------
			//
			// Get the record entries
			//
			// ---------------------------------------------------------------------------------------------------------
			
			$sql  = "SELECT w.*, ";
			$sql .= "c.name client_name, ";
			$sql .= "media.id media_id, media.media_url media_url, ";
			$sql .= "p.last_participation, ";
			$sql .= "( ";
			$sql .= "SELECT COUNT(DISTINCT l.user_id) number_of_users ";
			$sql .= "FROM workout_log l ";
			$sql .= "WHERE l.library_workout_id = w.id ";
			$sql .= "AND l.workout_log_completed ";
			$sql .= ") number_of_users, ";
			$sql .= "( ";
			$sql .= "SELECT IF(ev.id IS NULL AND wod.id IS NULL,true,false) deletable ";
			$sql .= "FROM library_workout wk ";
			$sql .= "LEFT OUTER JOIN calendar_event_library_workout ev ";
			$sql .= "ON ev.library_workout_id = wk.id ";
			$sql .= "LEFT OUTER JOIN calendar_entry_template_wod_library_workout wod ";
			$sql .= "ON wod.library_workout_id = wk.id ";
			$sql .= "WHERE wk.id = w.id ";
			$sql .= "GROUP BY wk.id ";
			$sql .= ") deletable ";
			$sql .= "FROM ";
			$sql .= "server server, ";
			$sql .= $exercisefrom;
			$sql .= $equipmentfrom;
			$sql .= "library_workout w ";
			$sql .= $equipmentleftouterjoin;
			$sql .= "LEFT OUTER JOIN client c ";
			$sql .= "ON c.id = w.client_id ";
			$sql .= "LEFT OUTER JOIN library_workout_media_last_entered media ";
			$sql .= "ON media.library_workout_id = w.id ";
			$sql .= "LEFT OUTER JOIN library_workout_last_participation p ";
			$sql .= "ON p.library_workout_id = w.id AND p.client_id = " . $p_client_id . " ";
			$sql .= $where;
			$sql .= $exercisegroupby;
			$sql .= $equipmentgroupby;
			$sql .= "ORDER BY w.name ";
			$sql .= $limit;
	
			// echo "$sql<br />";
			
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0) {
				$rows = $query->result();
	
				$entries = array();
				
				foreach ( $rows as $row ) {
					
					// echo "row : "; print_r($row); echo "<br />";
					
					$entry = new stdClass();
					$entry->id = cast_int($row->id);
					$entry->name = $row->name;
					$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
					$entry->media = format_media($row->media_id,$row->media_url);
					$entry->last_performed = cast_int($row->last_participation);
					$entry->number_of_user = cast_int($row->number_of_users);
					$entry->deletable = cast_boolean($row->deletable);
					$entry->benchmark = cast_boolean($row->benchmark);
					$entry->note = $row->note;
					$entry->client = new stdClass();
					$entry->client->id = cast_int($row->client_id);
					$entry->client->name = $row->client_name;
					$entry->summary = json_decode($row->json_workout_summary);

					array_push($entries,$entry);
					unset($entry);
				}
				
				$response->count = $count;
				$response->results = $entries;
				return $this->return_handler->results(200,"",$response);
			}
		}

		$response->count = 0;
		$response->results = array();
		return $this->return_handler->results(200,"",$response);
	}

	// ==================================================================================================================
	// Get all workouts ( user_id is used to get participation numbers)
	// ==================================================================================================================
	
	public function getForUser($p_user_id) {
		//
		// initialize the response data
		$count = 0;
		$entries = array();
		$response->count = $count;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$exercisefrom = "";
		$equipmentfrom = "";
		$equipmentleftouterjoin = "";
		$participation_between = "";
		$where = "";
		$exercisegroupby = "";
		$equipmentgroupby = "";
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if ( isset($_GET['q_ex']) && !empty($_GET['q_ex']) ) {
			// make sure all exercises in the list are unique
			$ex = explode(",",$_GET['q_ex']);
			$ex_unique = array_unique($ex);
			$ex_list = implode(",", $ex_unique);
			
			$exercisefrom = "library_workout_library_exercise ex, ";
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "ex.library_exercise_id in (" . $ex_list . ") ";
			$where .= "AND w.id = ex.library_workout_id ";
			$exercisegroupby .= "GROUP BY ex.library_workout_id ";
			$exercisegroupby .= "HAVING count(ex.library_workout_id) = " . count($ex_unique) . " ";
		} else if ( isset($_GET['q_bw']) && !empty($_GET['q_bw']) && $_GET['q_bw'] ) {
			$equipmentleftouterjoin .= "LEFT OUTER JOIN library_workout_library_exercise ex ";
			$equipmentleftouterjoin .= "LEFT OUTER JOIN library_exercise_equipment eq ";
			$equipmentleftouterjoin .= "ON eq.library_exercise_id = ex.library_exercise_id AND eq.mandatory ";
			$equipmentleftouterjoin .= "ON ex.library_workout_id = w.id ";
			$equipmentgroupby .= "GROUP BY w.id ";
			$equipmentgroupby .= "HAVING count(eq.library_equipment_id) = 0 ";
		} else if ( isset($_GET['q_eq']) && !empty($_GET['q_eq']) ) {
			// make sure all exercises in the list are unique
			$eq = explode(",",$_GET['q_eq']);
			$eq_unique = array_unique($eq);
			$eq_list = implode(",", $eq_unique);
		
			$equipmentfrom = "library_workout_library_equipment eq, ";
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "eq.library_equipment_id in (" . $eq_list . ") ";
			$where .= "AND w.id = eq.library_workout_id ";
			$equipmentgroupby .= "GROUP BY eq.library_workout_id ";
			$equipmentgroupby .= "HAVING count(eq.library_workout_id) = " . count($eq_unique) . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if ( isset($_GET['q_rt']) ) {
			if ( empty($_GET['q_rt']) && $_GET['q_rt'] != '0' ) {
				if ( empty($where) ) {
					$where .= "WHERE ";
				} else {
					$where .= "AND ";
				}
				$where .= "w.library_workout_recording_type_id IS NULL ";
			} else if ( is_numeric($_GET['q_rt']) ) {
				if ( empty($where) ) {
					$where .= "WHERE ";
				} else {
					$where .= "AND ";
				}
				$where .= "w.library_workout_recording_type_id = " . $_GET['q_rt'] . " ";
			}
		}
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "w.name LIKE '%" . mysql_escape_string($_GET['q_n']) . "%' ";
		}
		if ( isset($_GET['q_bm']) && !empty($_GET['q_bm']) && $_GET['q_bm'] ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "w.benchmark = 1 ";
		}
		if ( isset($_GET['q_c']) && !empty($_GET['q_c']) && $_GET['q_c'] ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "w.client_id = " . $_GET['q_c'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$check_participation_date = false;
		if ( isset($_GET['q_sp']) && !empty($_GET['q_sp']) && is_numeric($_GET['q_sp']) &&
		     isset($_GET['q_ep']) && !empty($_GET['q_ep']) && is_numeric($_GET['q_ep']) ) {
			if ( empty($where) ) {
				$where .= "WHERE ";
			} else {
				$where .= "AND ";
			}
			$where .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(p.last_participation),server.timezone,p.timezone),'%Y%m%d') BETWEEN " . $_GET['q_sp'] . " AND " . $_GET['q_ep'] . " ";
			$check_participation_date = true;
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
		$sql  = "SELECT count(w.id) cnt ";
		$sql .= "FROM ( ";
		$sql .= "SELECT w.id id ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= $exercisefrom;
		$sql .= $equipmentfrom;
		$sql .= "library_workout w ";
		$sql .= $equipmentleftouterjoin;
		if ( $check_participation_date ) {
			$sql .= "LEFT OUTER JOIN ";
			$sql .= "( ";
			$sql .= "SELECT c.user_id, w.library_workout_id, max(e.start) last_participation, cal.timezone ";
			$sql .= "FROM  ";
			$sql .= "calendar_event_library_workout w, ";
			$sql .= "calendar_event e, ";
			$sql .= "calendar cal, ";
			$sql .= "calendar_event_participation p, ";
			$sql .= "client_user c ";
			$sql .= "WHERE e.id = w.calendar_event_id ";
			$sql .= "AND cal.id = e.calendar_id ";
			$sql .= "AND p.calendar_event_id = e.id ";
			$sql .= "AND c.id = p.client_user_id ";
			$sql .= "AND c.user_id = " . $p_user_id . " ";
			$sql .= "GROUP BY c.user_id, w.library_workout_id ";
			$sql .= ") p ";
			$sql .= "ON p.library_workout_id = w.id ";
		}
		$sql .= $where;
		$sql .= $exercisegroupby;
		$sql .= $equipmentgroupby;
		$sql .= ") w ";
		
		// echo "$sql<br />";
		
		$row = $this->db->query($sql)->row();
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
		
		if ( $count > 0 ) {
			// ---------------------------------------------------------------------------------------------------------
			//
			// Get the record entries
			//
			// ---------------------------------------------------------------------------------------------------------
			$sql  = "SELECT w.*, ";
			$sql .= "c.name client_name, ";
			$sql .= "media.id media_id, media.media_url media_url, ";
			$sql .= "p.last_participation, ";
			$sql .= "( ";
			$sql .= "SELECT COUNT(DISTINCT cu.user_id) number_of_users ";
			$sql .= "FROM ";
			$sql .= "calendar_event_library_workout cw, ";
			$sql .= "calendar_event_participation cp, ";
			$sql .= "client_user cu ";
			$sql .= "WHERE cw.library_workout_id = w.id ";
			$sql .= "AND cp.calendar_event_id = cw.calendar_event_id ";
			$sql .= "AND cu.id = cp.client_user_id ";
			$sql .= "GROUP BY cw.library_workout_id ";
			$sql .= ") number_of_users, ";
			$sql .= "( ";
			$sql .= "SELECT IF(ev.id IS NULL AND wod.id IS NULL,true,false) deletable ";
			$sql .= "FROM library_workout wk ";
			$sql .= "LEFT OUTER JOIN calendar_event_library_workout ev ";
			$sql .= "ON ev.library_workout_id = wk.id ";
			$sql .= "LEFT OUTER JOIN calendar_entry_template_wod_library_workout wod ";
			$sql .= "ON wod.library_workout_id = wk.id ";
			$sql .= "WHERE wk.id = w.id ";
			$sql .= "GROUP BY wk.id ";
			$sql .= ") deletable ";
			$sql .= "FROM ";
			$sql .= "server, ";
			$sql .= $exercisefrom;
			$sql .= $equipmentfrom;
			$sql .= "library_workout w ";
			$sql .= $equipmentleftouterjoin;
			$sql .= "LEFT OUTER JOIN client c ";
			$sql .= "ON c.id = w.client_id ";
			$sql .= "LEFT OUTER JOIN library_workout_media_last_entered media ";
			$sql .= "ON media.library_workout_id = w.id ";
			$sql .= "LEFT OUTER JOIN ";
			$sql .= "( ";
			$sql .= "SELECT c.user_id, w.library_workout_id, max(e.start) last_participation, cal.timezone ";
			$sql .= "FROM  ";
			$sql .= "calendar_event_library_workout w, ";
			$sql .= "calendar_event e, ";
			$sql .= "calendar cal, ";
			$sql .= "calendar_event_participation p, ";
			$sql .= "client_user c ";
			$sql .= "WHERE e.id = w.calendar_event_id ";
			$sql .= "AND cal.id = e.calendar_id ";
			$sql .= "AND p.calendar_event_id = e.id ";
			$sql .= "AND c.id = p.client_user_id ";
			$sql .= "AND c.user_id = " . $p_user_id . " ";
			$sql .= "GROUP BY c.user_id, w.library_workout_id ";
			$sql .= ") p ";
			$sql .= "ON p.library_workout_id = w.id ";
			$sql .= $where;
			$sql .= $exercisegroupby;
			$sql .= $equipmentgroupby;
			$sql .= "ORDER BY w.name ";
			$sql .= $limit;
	
			// echo "$sql<br />";
			
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0) {
				$rows = $query->result();
	
				$entries = array();
				
				foreach ( $rows as $row ) {
					
					// echo "row : "; print_r($row); echo "<br />";
					
					$entry = new stdClass();
					$entry->id = cast_int($row->id);
					$entry->name = $row->name;
					$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
					$entry->media = format_media($row->media_id,$row->media_url);
					$entry->last_performed = cast_int($row->last_participation);
					$entry->number_of_user = cast_int($row->number_of_users);
					$entry->deletable = cast_boolean($row->deletable);
					$entry->benchmark = cast_boolean($row->benchmark);
					$entry->note = $row->note;
					$entry->client = new stdClass();
					$entry->client->id = cast_int($row->client_id);
					$entry->client->name = $row->client_name;
					$entry->summary = json_decode($row->json_workout_summary);

					array_push($entries,$entry);
					unset($entry);
				}
				
				$response->count = $count;
				$response->results = $entries;
				return $this->return_handler->results(200,"",$response);
			}
		}

		$response->count = 0;
		$response->results = array();
		return $this->return_handler->results(200,"",$response);
	}

	// ==================================================================================================================
	// Get a workout by its id
	// ==================================================================================================================
	
	public function getForId( $p_library_workout_id ) {
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		// Is the workout used by a log
		$sql_log  = "";
		$sql_log .= "SELECT ";
		$sql_log .= "count(workout_log.id) cnt ";
		$sql_log .= "FROM ";
		$sql_log .= "workout_log ";
		$sql_log .= "WHERE workout_log.library_workout_id = " . $p_library_workout_id . " ";
		$sql_log .= "LIMIT 1 ";
		// Is the workout used by a Pending log
		$sql_pen  = "";
		$sql_pen .= "SELECT ";
		$sql_pen .= "count(workout_log_pending.id) cnt ";
		$sql_pen .= "FROM ";
		$sql_pen .= "workout_log_pending ";
		$sql_pen .= "WHERE workout_log_pending.library_workout_id = " . $p_library_workout_id . " ";
		$sql_pen .= "LIMIT 1 ";
		// Is the workout used by a calendar_event
		$sql_cev  = "";
		$sql_cev .= "SELECT ";
		$sql_cev .= "count(calendar_event_library_workout.id) cnt ";
		$sql_cev .= "FROM ";
		$sql_cev .= "calendar_event_library_workout ";
		$sql_cev .= "WHERE calendar_event_library_workout.library_workout_id = " . $p_library_workout_id . " ";
		$sql_cev .= "LIMIT 1 ";
		// Is the workout used by a Workout Of the Day
		$sql_wod  = "";
		$sql_wod .= "SELECT ";
		$sql_wod .= "count(calendar_entry_template_wod_library_workout.id) cnt ";
		$sql_wod .= "FROM ";
		$sql_wod .= "calendar_entry_template_wod_library_workout ";
		$sql_wod .= "WHERE calendar_entry_template_wod_library_workout.library_workout_id = " . $p_library_workout_id . " ";
		$sql_wod .= "LIMIT 1 ";
		// Is the workout deletable?
		$sql_del  = "";
		$sql_del .= "SELECT ";
		$sql_del .= "if(sum(used.cnt) = 0,1,0) deletable ";
		$sql_del .= "FROM ";
		$sql_del .= "(";
		$sql_del .= "(" . $sql_log . ") ";
		$sql_del .= "UNION ALL ";
		$sql_del .= "(" . $sql_pen . ") ";
		$sql_del .= "UNION ALL ";
		$sql_del .= "(" . $sql_cev . ") ";
		$sql_del .= "UNION ALL ";
		$sql_del .= "(" . $sql_wod . ") ";
		$sql_del .= ") used ";
		//      Get the workout and all its media
		$sql  = "SELECT wk.*, ";
		$sql .= "c.name client_name, ";
		$sql .= "media.id media_id, media.media_url media_url, ";
		$sql .= "(" . $sql_del . ") deletable ";
		$sql .= "FROM library_workout wk ";
		$sql .= "LEFT OUTER JOIN client c ";
		$sql .= "ON c.id = wk.client_id ";
		$sql .= "LEFT OUTER JOIN library_workout_media media ";
		$sql .= "ON media.library_workout_id = wk.id ";
		$sql .= "WHERE wk.id = " . $p_library_workout_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$rows = $query->result();
		
		$entry = null;
		foreach( $rows as $row ) {
			if ( is_null($entry) ) {
				// load the workout entry
				$entry->id = cast_int($row->id);
				$entry->name = $row->name;
				$entry->deletable = cast_boolean($row->deletable);
				$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
				$entry->benchmark = cast_boolean($row->benchmark);
				$entry->note = $row->note;
				$entry->client = new stdClass();
				$entry->client->id = cast_int($row->client_id);
				$entry->client->name = $row->client_name;
				$entry->workout_summary = json_decode($row->json_workout_summary);
				$entry->media = array();
				$workout = json_decode($row->json_workout);
			}
			if ( !is_null($row->media_id) ) {
				// load media
				$media = new stdClass();
				$media->id = cast_int($row->media_id);
				$media->url = $row->media_url;
				array_push($entry->media,$media);
				unset($media);
			}
		}

		// Add the names to the exercises, equipment, and units. Add measurement to equipment.
		$return = $this->getWorkoutDetailAdded($p_library_workout_id,$workout);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$entry->workout = $return['response'];
		
		return $this->return_handler->results(200,"",$entry);
	}

	public function getWorkoutDetailAdded($p_library_workout_id,$workout) {
		// -----------------------------------------------------------------
		// Get a list of all exercises and their media
		// -----------------------------------------------------------------
		$return = $this->perform('this->getExerciseList',$p_use_alias=true);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -----------------------------------------------------------------
		// Get a list of all equipment and their media
		// -----------------------------------------------------------------
		$return = $this->perform('this->getEquipmentList',$p_use_alias=true);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -----------------------------------------------------------------
		// Get a list of all exercises with/and their mandatory equipment
		// -----------------------------------------------------------------
		$return = $this->perform('this->getMandatoryList',$p_use_alias=true);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// echo "workout:$p_library_workout_id\n";
		// print_r($this->xref);
		$this->addWorkoutDetail($workout);
		return $this->return_handler->results(200,"",$workout);
	}

	public function getExerciseList($p_use_alias=true) {
        $this->exerciseList = array();
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "library_exercise.id 'library_exercise.id', library_exercise.name 'library_exercise.name', ";
		$sql .= "library_exercise_media_last_entered.id 'library_exercise_media_last_entered.id', library_exercise_media_last_entered.media_url 'library_exercise_media_last_entered.media_url' ";
		$sql .= "FROM library_exercise ";
		$sql .= "LEFT OUTER JOIN library_exercise_media_last_entered ";
		$sql .= "ON library_exercise_media_last_entered.library_exercise_id = library_exercise.id ";
        // echo "$sql<br />";
        
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return $this->return_handler->results(204,"No Entry Found",null);
        }
        $rows = $query->result();
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
         	
         	$this->exerciseList[$row->library_exercise->id] = clone $row->library_exercise;
         	$this->exerciseList[$row->library_exercise->id]->media = format_object_with_id($row->library_exercise_media_last_entered);
        }
		
		return $this->return_handler->results(200,"",null);
	}

	public function getEquipmentList($p_use_alias=true) {
        $this->equipmentList = array();
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "library_equipment.id 'library_equipment.id', library_equipment.name 'library_equipment.name', ";
		$sql .= "library_equipment_media_last_entered.id 'library_equipment_media_last_entered.id', library_equipment_media_last_entered.media_url 'library_equipment_media_last_entered.media_url' ";
		$sql .= "FROM library_equipment ";
		$sql .= "LEFT OUTER JOIN library_equipment_media_last_entered ";
		$sql .= "ON library_equipment_media_last_entered.library_equipment_id = library_equipment.id ";
        // echo "$sql<br />";
        
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return $this->return_handler->results(204,"No Entry Found",null);
        }
        $rows = $query->result();
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row,$p_use_alias);
            // objectify the row by table and use column aliases if needed
         	
         	$this->equipmentList[$row->library_equipment->id] = clone $row->library_equipment;
         	$this->equipmentList[$row->library_equipment->id]->media = format_object_with_id($row->library_equipment_media_last_entered);
        }
        
		return $this->return_handler->results(200,"",null);
	}

	public function getMandatoryList($p_use_alias=true) {
        $this->mandatoryList = array();
        
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "library_exercise_equipment.library_exercise_id 'library_exercise_equipment.library_exercise_id', ";
		$sql .= "library_exercise_equipment.library_equipment_id 'library_exercise_equipment.library_equipment_id' ";
		$sql .= "FROM library_exercise_equipment ";
		$sql .= "WHERE library_exercise_equipment.mandatory ";
        // echo "$sql<br />";
        
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return $this->return_handler->results(204,"No Entry Found",nul);
        }
        $rows = $query->result();
		
		// column aliases if they are needed
		$column = new stdClass();
		$column->library_equipment_id = mysql_schema::getColumnAlias('workoutdb','library_exercise_equipment','library_equipment_id',$p_use_alias);
		$column->library_exercise_id = mysql_schema::getColumnAlias('workoutdb','library_exercise_equipment','library_exercise_id',$p_use_alias);
		
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
         	
         	$this->mandatoryList[$row->library_exercise_equipment->{$column->library_exercise_id}][$row->library_exercise_equipment->{$column->library_equipment_id}] = false;
        }
		
		return $this->return_handler->results(200,"",null);
	}

	public function addWorkoutDetail(&$data) { 	
		if ( is_object($data) || is_array($data) ) {
			foreach ( $data as $key => &$value ) {
				// echo " $key - ";
				switch ($key) {
						
					case "set":
						foreach ( $value as &$set ) {
							$this->addWorkoutDetail($set);
						}
						break;
						
					case "exercise_group":
						foreach( $value as &$exercise_group ) {
							$this->addWorkoutDetail($exercise_group);
						}
						break;
						
					case 'exercise':
						if ( array_key_exists($value->id,$this->exerciseList) ) {
							$value->name = $this->exerciseList[$value->id]->name;
							$value->media = clone $this->exerciseList[$value->id]->media;
						} else {
							echo "exercise_id:" . $value->id . " invalid<br />\n";
						}
						// initialize the mandatory equipment array for the exercise
						if ( isset($this->mandatoryList[$value->id]) ) {
							// var_dump($this->mandatoryList[$value->id]);
							$this->mandatoryEquipment = $this->mandatoryList[$value->id];
						} else {
							$this->mandatoryEquipment = array();
						}
						// add name, media and deletable to the exercise's equipment
						$this->addWorkoutDetail($value);
						// add any missing mandatory equipment to the exercise
						foreach ( $this->mandatoryEquipment as $equipment_id => $found ) {
							if ( !$found ) {
								$equipment = clone $this->equipmentList[$equipment_id];
								$equipment->unit = new stdClass();
								$equipment->deletable = FALSE;
								$value->equipment[] = clone $equipment;
								unset($equipment);
							}
						}
						break;
						
					case 'equipment':
						foreach( $value as &$equipment ) {
							if ( array_key_exists($equipment->id,$this->equipmentList) ) {
								$equipment->name = $this->equipmentList[$equipment->id]->name;
								$equipment->media = clone $this->equipmentList[$equipment->id]->media;
							} else {
								echo "equipment_id:" . $equipment->id . " invalid<br />\n";
							}
							if ( isset($this->mandatoryEquipment[$equipment->id]) ) {
								// echo "--- exercise_id:" . $this->curr_ex_id . " equipment:" . $equipment->id . " does not exist\n";
								$equipment->deletable = FALSE;
								// tag the mandatory equipment as found
								$this->mandatoryEquipment[$equipment->id] = TRUE;
							} else {
								$equipment->deletable = TRUE;
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
	// Get a workout by its id
	// ==================================================================================================================
	
	public function getDetailForId( $p_workout_id ) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get the workout
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$sql  = "SELECT w.*, ";
		$sql .= "c.name client_name ";
		$sql .= "FROM library_workout w ";
		$sql .= "LEFT OUTER JOIN client c ";
		$sql .= "ON c.id = w.client_id ";
		$sql .= "WHERE w.id = " . $p_workout_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			
			//print_r($row);
			
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->name = $row->name;
			$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
			$entry->benchmark = cast_boolean($row->benchmark);
			$entry->note = $row->note;
			$entry->client = new stdClass();
			$entry->client->id = cast_int($row->client_id);
			$entry->client->name = $row->client_name;
			$entry->summary = json_decode($row->json_workout_summary);
			
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
	}

	// ==================================================================================================================
	// Get a workout by its id ( client_id is used to get the stats about the workout for the client )
	// ==================================================================================================================
	
	public function getDetailForIdClient( $p_workout_id, $p_client_id ) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get the workout
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$sql  = "SELECT w.*, ";
		$sql .= "c.name client_name, ";
		$sql .= "media.id media_id, media.media_url media_url, ";
		$sql .= "p.last_participation, ";
		$sql .= "u.number_of_users ";
		$sql .= "FROM library_workout w ";
		$sql .= "LEFT OUTER JOIN client c ";
		$sql .= "ON c.id = w.client_id ";
		$sql .= "LEFT OUTER JOIN library_workout_media_last_entered media ";
		$sql .= "ON media.library_workout_id = w.id ";
		$sql .= "LEFT OUTER JOIN library_workout_last_participation p ";
		$sql .= "ON p.library_workout_id = w.id AND p.client_id = " . $p_client_id . " ";
		$sql .= "LEFT OUTER JOIN num_users_logged_workout u ";
		$sql .= "ON u.library_workout_id = w.id ";
		$sql .= "WHERE w.id = " . $p_workout_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			
			//print_r($row);
			
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->name = $row->name;
			$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
			$entry->media = format_media($row->media_id,$row->media_url);
			$entry->last_performed = cast_int($row->last_participation);
			$entry->number_of_user = cast_int($row->number_of_users);
			$entry->benchmark = cast_boolean($row->benchmark);
			$entry->note = $row->note;
			$entry->client = new stdClass();
			$entry->client->id = cast_int($row->client_id);
			$entry->client->name = $row->client_name;
			$entry->summary = json_decode($row->json_workout_summary);
			
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
	}

	// ==================================================================================================================
	// Get a workout by its id ( user_id is used to get the stats about the workout for the user )
	// ==================================================================================================================
	
	public function getDetailForIdUser( $p_workout_id, $p_user_id ) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get the workout
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$sql  = "SELECT w.*, ";
		$sql .= "c.name client_name, ";
		$sql .= "media.id media_id, media.media_url media_url, ";
		$sql .= "p.last_participation, ";
		$sql .= "u.number_of_users ";
		$sql .= "FROM library_workout w ";
		$sql .= "LEFT OUTER JOIN client c ";
		$sql .= "ON c.id = w.client_id ";
		$sql .= "LEFT OUTER JOIN library_workout_media_last_entered media ";
		$sql .= "ON media.library_workout_id = w.id ";
		$sql .= "LEFT OUTER JOIN ";
		$sql .= "( ";
		$sql .= "SELECT c.user_id, w.library_workout_id, max(e.start) last_participation, cal.timezone ";
		$sql .= "FROM  ";
		$sql .= "calendar_event_library_workout w, ";
		$sql .= "calendar_event e, ";
		$sql .= "calendar cal, ";
		$sql .= "calendar_event_participation p, ";
		$sql .= "client_user c ";
		$sql .= "WHERE w.library_workout_id = " . $p_workout_id . " AND e.id = w.calendar_event_id ";
		$sql .= "AND cal.id = e.calendar_id ";
		$sql .= "AND p.calendar_event_id = e.id ";
		$sql .= "AND c.id = p.client_user_id ";
		$sql .= "AND c.user_id = " . $p_user_id . " ";
		$sql .= "GROUP BY c.user_id, w.library_workout_id ";
		$sql .= ") p ";
		$sql .= "ON p.library_workout_id = w.id ";
		// $sql .= "LEFT OUTER JOIN library_workout_last_participation_by_user p ";
		// $sql .= "ON p.library_workout_id = w.id AND p.user_id = " . $p_user_id . " ";
		$sql .= "LEFT OUTER JOIN num_users_logged_workout u ";
		$sql .= "ON u.library_workout_id = w.id ";
		$sql .= "WHERE w.id = " . $p_workout_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			
			//print_r($row);
			
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->name = $row->name;
			$entry->workout_recording_type_id = cast_int($row->library_workout_recording_type_id);
			$entry->media = format_media($row->media_id,$row->media_url);
			$entry->last_performed = cast_int($row->last_participation);
			$entry->number_of_user = cast_int($row->number_of_users);
			$entry->benchmark = cast_boolean($row->benchmark);
			$entry->note = $row->note;
			$entry->client = new stdClass();
			$entry->client->id = cast_int($row->client_id);
			$entry->client->name = $row->client_name;
			$entry->summary = json_decode($row->json_workout_summary);
			
			return $this->return_handler->results(200,"",$entry);
		} else {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
	}

	// =========================================================================================================================================
	// Create a workout and all its components
	// =========================================================================================================================================
	
	public function create($data) {
		// echo "action_workout->create<br />\n";
		// Are all the mandatory fields present?
		if ( !isset($data->name) || !isset($data->benchmark) || !isset($data->workout) ) {
			return $this->return_handler->results(400,"A mandatory field is missing",new stdClass());
		}
		// --------------------------------------------------------------------------------------------------------
		// do not allow duplicate names
		// --------------------------------------------------------------------------------------------------------
		$key = array();
		$key['name'] = $data->name;
		$return = $this->perform('table_workoutdb_library_workout->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			return $this->return_handler->results(400,"Workout: '" . $data->name . "' already exists.",new stdClass());
		}
		// --------------------------------------------------------------------------------------------------------
		// if the json_workout is set:
		//
		// 1) validate json_workout's structure
		// 2) store the original json_workout in the database as original_json_workout
		// 2) create a version of json_workout to store in the database
		// 3) create a list of exercises and equipment used in json_workout
		// --------------------------------------------------------------------------------------------------------
		// validate the workout, create a storable json of the workout, create an exercise and equipment list for the workout
		$this->load->library('simplify');
		$return = $this->simplify->workout($data->workout);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$workout = $return['response']->workout;
		$exercises = $return['response']->exercises;
		$equipment = $return['response']->equipment;
		// get the needed lookup tables
		$return = $this->perform('this->setupNeededLookupTables');
		if ( $return['status'] > 300 ) {
			return $return;
		}
		// create the workout summary
		$return_summary = $this->createWorkoutSummary($workout);
		
		$data->original_json_workout = json_encode($data->workout);
		$data->json_workout = json_encode($workout);
		$data->json_workout_summary = json_encode($return_summary->both);
		$data->json_workout_summary_male = json_encode($return_summary->male);
		$data->json_workout_summary_female = json_encode($return_summary->female);
		// post the entry
		$return_create = $this->perform('table_workoutdb_library_workout->insert',$data);
		if ( $return_create['status'] >= 300 ) {
			return $return_create;
		}
		
		$this->id = $return_create['response']->id;
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries and entries for media
		if ( isset($data->media) && count((array) $data->media) > 0 ) {
			$this->id = $return_create['response']->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_workout';
			$this->linked_table_name = 'library_workout_media';
			$return = $this->perform('this->post_linked_entry_list',$data->media);
			if ( $return['response'] >= 300 ) {
				return $return;
			}
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries only
		$this->id = $return_create['response']->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_workout';
		$this->xref_table_name = 'library_workout_library_exercise';
		$this->xrefed_table_name = 'library_exercise';
		$return = $this->perform('this->post_xref_list',$exercises);
		if ( $return['response'] >= 300 ) {
			return $return;
		}
		// - - - - - - - - - - - - - - - - - - - - - - - -
		// create the cross reference entries only
		$this->id = $return_create['response']->id;
		$this->database_name = 'workoutdb';
		$this->table_name = 'library_workout';
		$this->xref_table_name = 'library_workout_library_equipment';
		$this->xrefed_table_name = 'library_equipment';
		$return = $this->perform('this->post_xref_list',$equipment);
		if ( $return['response'] >= 300 ) {
			return $return;
		}
		
		return $return_create;
	}
	
	// =========================================================================================================================================
	// Create a displayable version of the workout
	// =========================================================================================================================================
	
	public function getPreviewWorkoutSummary($data) {
		// echo "getPreviewWorkoutSummary data:"; print_r($data); echo "<b />";
		
		$return = $this->perform('this->setupNeededLookupTables');
		if ( $return['status'] > 300 ) {
			return $return;
		}
		
		$summary = $this->createWorkoutSummary($data);
		// print_r($summary);
		
		return $this->return_handler->results(200,"",$summary->both);
	}

	// =========================================================================================================================================
	// load the needed lookup tables
	// =========================================================================================================================================
		
	public function setupNeededLookupTables() {
		// setup the recording_type Lookup Table
		$this->db_database_name = "workoutdb";
		$this->db_table_name = "library_workout_recording_type";
		$this->db_field_name = "name";
		$this->lookup_table_name = "recording_type";
		$return = $this->setupLookup();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// setup the exercise Lookup Table
		$this->db_database_name = "workoutdb";
		$this->db_table_name = "library_exercise";
		$this->db_field_name = "name";
		$this->lookup_table_name = "exercise";
		$return = $this->setupLookup();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// setup the equipment Lookup Table
		$this->db_database_name = "workoutdb";
		$this->db_table_name = "library_equipment";
		$this->db_field_name = "name";
		$this->lookup_table_name = "equipment";
		$return = $this->setupLookup();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// setup the unit Lookup Table
		$this->db_database_name = "workoutdb";
		$this->db_table_name = "library_measurement_system_unit";
		$this->db_field_name = "abbr";
		$this->lookup_table_name = "unit";
		$return = $this->setupLookup();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		// setup the measurement Lookup Table
		$this->db_database_name = "workoutdb";
		$this->db_table_name = "library_measurement";
		$this->db_field_name = "name";
		$this->lookup_table_name = "measurement";
		$return = $this->setupLookup();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
	}

	// =========================================================================================================================================
    // Lookup Tables
    //
    // A Lookup Table is indexed by the id and returns the value you are looking up for that id.
    //
    // To setup/create a Lookup Table you must set the following before calling setupLookup() :
    //
    //     $this->db_database_name : The database that will be used
    //     $this->db_table_name : The database table that will be used to load the Lookup Table
    //     $this->db_field_name : the field the value is to come from
    //     $this->lookup_table_name : The Lookup Table you are creating
	// =========================================================================================================================================
	
	public function setupLookup() {
		$return = $this->perform('table_' . $this->db_database_name . '_' . $this->db_table_name . '->getAll');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			$this->{$this->lookup_table_name} = array();
			foreach( $return['response']->results as $entry ) {
				$this->{$this->lookup_table_name}[$entry->id] = $entry->{$this->db_field_name};
			}
		}
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	public function getLookupValue($p_id) {
		$id = (int) $p_id;
		if ( array_key_exists($id, $this->{$this->lookup_table_name}) ) {
			return $this->{$this->lookup_table_name}[$id];
		} else {
			return "";
		}
	}

	public function validLookupId($p_id) {
		if ( $this->getLookupValue($p_id) == '' ) {
			return false;
		} else {
			return true;
		}
	}

	// =========================================================================================================================================
	// Create a summary of the workout used for display
	// =========================================================================================================================================

	public function createWorkoutSummary($p_workout) {
		// echo "<br />WORKOUT<br />";
		$time_limit = "";
		$repeats = "";
		$sets_summary = new stdClass();
		$sets_summary->both = array();
		$sets_summary->male = array();
		$sets_summary->female = array();
		$finish = "";
		foreach( $p_workout as $key => $value ) {
			switch ($key) {
				case 'time_limit':
					$time_limit = $this->format_time_limit($value);
					break;
					
				case 'repeats':
					$repeats = $this->format_repeats($value);
					$finish = $this->format_finish($value);
					break;
					
				case 'set':
					// where $sets_summary is an object {"sets_summary":{ "male":["male summary","break summary"],"female":["female summary","break summary"],"both":["both male and female summary","break summary"]}}
					$sets_summary = $this->summarySets($value);
					// print_r($sets_summary);
					break;
					
				default:
					break;
			}
		}
		
		$workout_finish = $this->format_workout_finish($time_limit,$finish);
		
		$summary = new stdClass();
		$summary->both = new stdClass();
		$summary->male = new stdClass();
		$summary->female = new stdClass();
		
		foreach( $summary as $key => &$value ) {
			$value->set = array();
			if ( count($sets_summary) > 0 ) {
				$value->set = $sets_summary->{$key};
			}
			$value->finish = $workout_finish;
		}
		
		return $summary;
	}
	
	public function summarySets($p_sets) {
		// echo "<br />SETS<br />";
		$summary = new stdClass();
		$summary->both = array();
		$summary->male = array();
		$summary->female = array();
		foreach( $p_sets as $set ) {
			$time_limit = "";
			$repeats = "";
			$exercise_group_summary = new stdClass();
			$exercise_group_summary->both = array();
			$exercise_group_summary->male = array();
			$exercise_group_summary->female = array();
			$break = "";
			foreach( $set as $key => $value ) {
				switch ($key) {
					case 'time_limit':
						$time_limit = $this->format_time_limit($value);
						break;
						
					case 'repeats':
						$repeats = $this->format_sets($value);
						break;
						
					case 'break_time':
						$break = $this->format_break($value);
						break;
						
					case 'exercise_group':
						// where $exercise_group_summary is an object {"exercise_group_summary":{ "male":["male summary","break summary"],"female":["female summary","break summary"],"both":["both male and female summary","break summary"]}}
						$exercise_group_summary = $this->summaryExerciseGroups($value);
						// print_r($exercise_group_summary);
						break;
						
					default:
						break;
				}
			}
			
			$set_header = $this->format_set_header($time_limit,$repeats);
			
			foreach ( $summary as $key => &$value_array ) {
				$set = new stdClass();
				$set->repeat = "";
				$set->exercise = array();
				$set->break = "";
				if ( !empty($set_header) ) {
					$set->repeat = $set_header;
				}
				if ( count($exercise_group_summary->{$key}) > 0 ) {
					$set->exercise = $exercise_group_summary->{$key};
				}
				if ( !empty($break) ) {
					$set->break = $break;
				}
				$value_array[] = $set;
			}
		}
		
		return $summary;
	}
	
	public function summaryExerciseGroups($p_exercise_groups) {
		// echo "<br />EXERCISE GROUPS<br />";
		$summary = new stdClass();
		$summary->both = array();
		$summary->male = array();
		$summary->female = array();
		foreach( $p_exercise_groups as $exercise_group ) {
			$time_limit = "";
			$repeats = "";
			$exercise = new stdClass();
			$exercise->both = "";
			$exercise->male = "";
			$exercise->female = "";
			$break = "";
			foreach( $exercise_group as $key => $value ) {
				switch ($key) {
					case 'time_limit':
						$time_limit = $this->format_time_limit($value);
						break;
						
					case 'repeats':
						$repeats = $this->format_exercise_repeats($value);
						break;
						
					case 'break_time':
						$break = $this->format_break($value);
						break;
						
					case 'exercise':
						// where $equipment is an object {"equipment":{ "male":"male summary","female":"female summary","both":"both male and female summary"}}
						$exercise = $this->summaryExercise($value);
						break;
						
					default:
						break;
				}
			}
			foreach( $exercise as $key => $value ) {
				$exercise_group = $this->format_exercise_group($value,$time_limit,$repeats);
				if ( !empty($exercise_group) ) {
					$summary->{$key}[] = $exercise_group;
				}
				if ( !empty($break) ) {
					$summary->{$key}[] = $break;
				}
			}
		}

		return $summary;
	}
	
	public function summaryExercise($p_exercise) {
		// echo "<br />EXERCISE<br />";
		$name = "";
		$distance_measurement = "";
		$equipment = new stdClass();
		$equipment->both = "";
		$equipment->male = "";
		$equipment->female = "";
		foreach( $p_exercise as $key => $value ) {
			switch ($key) {
				case 'id':
					$this->lookup_table_name = "exercise";
					$exercise_name = $this->getLookupValue($value);
					$name = $this->format_name($exercise_name);
					break;
					
				case 'distance_measurement':
					$distance_measurement = $this->format_distance_measurement($value);
					break;
					
				case 'equipment':
					// where $equipment is an object {"equipment":{ "male":"male summary","female":"female summary","both":"both male and female summary"}}
					$equipment = $this->summaryEquipment($value);
					// print_r($equipment);
					break;
					
				default:
					break;
			}
		}
		
		foreach ( $equipment as $key => $value ) {
			$exercise->{$key} = $this->format_exercise($name, $distance_measurement, $value);
		}
		
		return $exercise;
	}
	
	public function summaryEquipment($p_equipment) {
		// echo "<br />EQUIPMENT<br />";
		$equipment_list = new stdClass();
		$equipment_list->both = array();
		$equipment_list->male = array();
		$equipment_list->female = array();
		
		foreach( $p_equipment as $equipment ) {
			$equipment_name = "";
			$name = "";
			$measurement = "";
			$unit = "";
			foreach( $equipment as $key => $value ) {
				switch ($key) {
					case 'id':
						$this->lookup_table_name = "equipment";
						$equipment_name = $this->getLookupValue($value);
						$name = $this->format_name($equipment_name);
						break;
						
					case 'measurement':
						$measurement = $this->format_measurement($value);
						// echo "measurements: " . $measurement . "<br />";
						break;
						
					case 'unit':
						// where $unit is an object {"unit":{ "male":"male summary","female":"female summary","both":"both male and female summary"}}
						$unit = $this->format_unit($value);
						// print_r($unit);
						break;
						
					default:
						break;
				}
			}
			foreach( $equipment_list as $key => &$value_array ) {
				if ( !empty($measurement) || !empty($unit->{$key}) ) {
					$equipment = $this->format_equipment($name,$unit->{$key});
					if ( !empty($equipment) ) {
						$value_array[] = $equipment;
					}
				}
			}
			// print_r($equipment_list);
		}
		
		// print_r($equipment_list);
		$equipment = new stdClass();
		$equipment->both = $this->format_equipment_list($equipment_list->both);
		$equipment->male = $this->format_equipment_list($equipment_list->male);
		$equipment->female = $this->format_equipment_list($equipment_list->female);
		// print_r($equipment);
		
		return $equipment;
	}

	public function format_time_limit($p_object) {
		$str = '';
		// -----------------------------------------------------------------
		// validate the object's structure
		// -----------------------------------------------------------------
		if ( !is_object($p_object) ||
		     !property_exists($p_object,'id') ||
		     !property_exists($p_object,'value') ||
		     !property_exists($p_object->value,'input') || is_null($p_object->value->input) || empty($p_object->value->input) ) {
			return $str;
		}
		// -----------------------------------------------------------------
		// get the unit of measure if needed
		// -----------------------------------------------------------------
		$unit_abbr = '';
		$this->lookup_table_name = "unit";
		if ( $this->validLookupId($p_object->id) ) {
			$unit_abbr = ' ' . $this->getLookupValue($p_object->id);
		}
		// -----------------------------------------------------------------
		// format the input
		// -----------------------------------------------------------------
		$value = $this->format_input($p_object->value->input);
		
		return 'for ' . $value . $unit_abbr;
	}
	
	public function format_input($p_input) {
		// echo "action_workout->format_input " . $p_input . " -- ";
		// -----------------------------------------------------------------
		// input contains a numeric value
		// -----------------------------------------------------------------
		if ( is_numeric($p_input) ) {
			// echo "numeric : $p_input --- ";
			return $p_input;
		}
		// -----------------------------------------------------------------
		// split the input at the : if it exists
		// -----------------------------------------------------------------
		$part = explode(':',$p_input);
		// the 1st part is the formula
		$formula = $part[0];
		// if it exists, the 2nd part is the value
		$value = null;
		if ( count($part) > 1 ) {
			$value = $part[1];
		}
		// -----------------------------------------------------------------
		// format the formula
		// -----------------------------------------------------------------
		$formatted_formula = $this->format_formula($formula);
		// echo "formatted_formula:$formatted_formula -- ";
		
		if ( is_null($value) ) {
			return $formatted_formula;
		} else {
			return $value . '(' . $formatted_formula . ')';
		}
	}
	
	public function format_formula($p_formula) {
		// echo "formula:$p_formula -- ";
		// -----------------------------------------------------------------
		// split the formula at the *
		// -----------------------------------------------------------------
		$part = explode('*',$p_formula);
		// the 1st part is the variable
		$variable = $part[0];
		// if it exists, the 2nd part is the number
		$number = null;
		if ( count($part) > 1 ) {
			$number = $part[1];
		}
		// -----------------------------------------------------------------
		// If the variable is invalid
		// -----------------------------------------------------------------
		if ( !array_key_exists($variable,$this->expression_variables) ) {
			if ( is_null($number) || $number == 1 ) {
				return $variable . ' (unknown variable)';
			} else {
				return $number . ' X ' . $variable . ' (unknown variable)';
			}
		}
		// -----------------------------------------------------------------
		// if there is no number or it is 1, just return the variable name
		// -----------------------------------------------------------------
		if ( is_null($number) || $number == 1 ) {
			return $this->expression_variables[$variable];
		}
		// -----------------------------------------------------------------
		// if both variable and number exist, format them
		// -----------------------------------------------------------------
		switch ($variable) {
			case '{#brn}' :
			case '{#mom}' :
				return $number . ' X ' . $this->expression_variables[$variable];
			case '{#maxe}':
			case '{#bbw}' :
			case '{#1RM}' :
				return ($number * 100) . '% of ' . $this->expression_variables[$variable];
		}
		
		return '';
	}
	
	public function format_repeats($repeats) {
		$str = "";
		if ( count((array) $repeats) > 0 ) {
			if ( isset($repeats->input) && !is_null($repeats->input) && !empty($repeats->input) ) {
				if ( is_numeric($repeats->input) ) {
					if ( $repeats->input == 1 ) {
						$str .= $repeats->input . " round";
					} else {
						$str .= $repeats->input . " rounds";
					}
				} else {
					$str .= $this->format_input($repeats->input) . " rounds";
				}
			}
		}
		
		return $str;
	}
	
	public function format_finish($repeats) {
		$str = "";
		
		if ( count((array) $repeats) > 0 ) {
			if ( isset($repeats->input) && !is_null($repeats->input) && !empty($repeats->input) ) {
				if ( is_numeric($repeats->input) ) {
					if ( $repeats->input == 1 ) {
						$str .= $repeats->input . " time";
					} else {
						$str .= $repeats->input . " times";
					}
				} else {
					$str .= $this->format_input($repeats->input) . " times";
				}
			}
		}
		
		return $str;
	}
	
	public function format_sets($repeats) {
		$str = "";
		
		if ( count((array) $repeats) > 0 ) {
			if ( isset($repeats->input) && !is_null($repeats->input) && !empty($repeats->input) ) {
				if ( is_numeric($repeats->input) ) {
					if ( $repeats->input == 1 ) {
						$str .= "X " . $repeats->input . " set";
					} else {
						$str .= "X " . $repeats->input . " sets";
					}
				} else {
					$str .= "X " . $this->format_input($repeats->input) . " sets";
					
				}
			}
		}
		
		return $str;
	}
	
	public function format_exercise_repeats($repeats) {
		$str = "";
		
		if ( count((array) $repeats) > 0 ) {
			if ( isset($repeats->input) ) {
				if ( (!is_null($repeats->input) && !empty($repeats->input)) || $repeats->input == '0' ) {
					$str .= $this->format_input($repeats->input);
				}
			}
		}
		
		return $str;
	}
	
	public function format_break($break) {
		$str = "";
		
		if ( count((array) $break) > 0 ) {
			
			$this->lookup_table_name = "unit";
			$unit_abbr = $this->getLookupValue($break->id);
			if ( !empty($unit_abbr) ) {
				$unit_abbr = " " . $unit_abbr;
			}
			
			if ( count($break->value) > 0 & isset($break->value->input) && !is_null($break->value->input) && !empty($break->value->input) ) {
				$str .= "Rest for " . $this->format_input($break->value->input) . $unit_abbr;
			}
		}
		
		return $str;
	}
	
	public function format_distance_measurement($distance_measurement) {
		$str = "";
		
		if ( count((array) $distance_measurement) > 0 ) {
			
			$this->lookup_table_name = "unit";
			$unit_abbr = $this->getLookupValue($distance_measurement->id);
			if ( !empty($unit_abbr) ) {
				$unit_abbr = " " . $unit_abbr;
			}
			
			if ( count($distance_measurement->value) > 0 & isset($distance_measurement->value->input) && !is_null($distance_measurement->value->input) && !empty($distance_measurement->value->input) ) {
				$str .= $this->format_input($distance_measurement->value->input) . $unit_abbr;
			}
		}
		
		return $str;
	}
	
	public function format_measurement($measurements) {
		$str = "";
		
		if ( count($measurements) > 0 ) {
			foreach($measurements as $id) {
				$this->lookup_table_name = "measurement";
				$measurement_name = $this->getLookupValue($id);
				if ( !empty($measurement_name) ) {
					if ( !empty($str) ) {
						$measurement_name = ", " . $measurement_name;
					}
				}
				
				$str .= $measurement_name;
			}
		}
		
		return $str;
	}
	
	public function format_unit($unit) {
		$return = new stdClass();
		$return->both = "";
		$return->male = "";
		$return->female = "";
		
		if ( count((array) $unit) > 0 ) {
			
			$this->lookup_table_name = "unit";
			$unit_abbr = $this->getLookupValue($unit->id);
			if ( !empty($unit_abbr) ) {
				$unit_abbr = " " . $unit_abbr;
			}
			
			$use_man = false;
			if ( property_exists($unit,'man') && count((array) $unit->man) > 0 & isset($unit->man->input) && !is_null($unit->man->input) && !empty($unit->man->input) ) {
				$use_man = true;
			}
			
			$use_woman = false;
			if ( property_exists($unit,'woman') && count((array) $unit->woman) > 0 & isset($unit->woman->input) && !is_null($unit->woman->input) && !empty($unit->woman->input) ) {
				$use_woman = true;
			}
			
			if ( $use_man && $use_woman && $unit->man->input != $unit->woman->input ) {
				$return->both .= $this->format_input($unit->man->input) . "/" . $this->format_input($unit->woman->input) . $unit_abbr;
				$return->male .= $this->format_input($unit->man->input) . $unit_abbr;
				$return->female .= $this->format_input($unit->woman->input) . $unit_abbr;
			} else if ( $use_man ) {
				$return->both .= $this->format_input($unit->man->input) . $unit_abbr;
				$return->male .= $return->both;
				$return->female .= $return->both;
			} else if ( $use_woman ) {
				$return->both .= $this->format_input($unit->woman->input) . $unit_abbr;
				$return->male .= $return->both;
				$return->female .= $return->both;
			}
		}
		
		return $return;
	}
	
	public function format_name($name) {
		$str = trim($name);
		
		return $str;
	}

	public function format_workout_header($time_limit,$repeats) {
		$str = "";
		
		if ( !empty($time_limit) && !empty($repeats) ) {
			$str .= $repeats . " " . $time_limit . " :";
		} else if ( !empty($time_limit) ) {
			$str .= $time_limit . " :";
		} else if ( !empty($repeats) ) {
			$str .= $repeats . " :";
		}
		
		return $str;
	}

	public function format_workout_finish($time_limit,$repeats) {
		$str = "";
		
		if ( !empty($time_limit) && !empty($repeats) ) {
			$str .= "Repeat " . $repeats . " " . $time_limit;
		} else if ( !empty($time_limit) ) {
			$str .= "Repeat " . $time_limit;
		} else if ( !empty($repeats) ) {
			if ( $repeats == "1 time" ) {
				$str .= "Finished";
			} else {
				$str .= "Repeat " . $repeats;
			}
		}
		
		return $str;
	}
	
	public function format_set_header($time_limit,$repeats) {
		$str = "";
		
		if ( !empty($time_limit) && !empty($repeats) ) {
			$str .= $time_limit . " " . $repeats;
		} else if ( !empty($time_limit) ) {
			$str .= $time_limit;
		} else if ( !empty($repeats) ) {
			$str .= $repeats;
		}
		
		return $str;
	}
	
	public function format_exercise_group($exercise,$time_limit,$repeats) {
		$str = "";
				
		if ( !empty($time_limit) && !empty($repeats) ) {
			$str .= $time_limit . " " . $repeats;
		} else if ( !empty($time_limit) ) {
			$str .= $time_limit . " of";
		} else if ( (!is_null($repeats) && !empty($repeats)) || $repeats == '0' ) {
			$str .= $repeats;
		}
		
		if ( !empty($exercise) ) {
			$str .= " " . $exercise;
		}
		
		return $str;
	}
	
	public function format_exercise($name, $distance_measurement, $equipment) {
		$str = "";
		
		if ( !empty($name) ) {
			$str .= $name;
		}
		if ( !empty($distance_measurement) ) {
			$str .= " " . $distance_measurement;
		}
		
		if ( !empty($equipment) ) {
			$str .= ", " . $equipment;
		}
		
		return $str;
	}
	
	public function format_equipment($name,$unit) {
		$str = "";
		
		if ( !empty($name) ) {
			$str .= $name;
		}
		if ( !empty($unit) ) {
			$str .= " " . $unit;
		}
		
		return $str;
	}
	
	public function format_equipment_list($equipment_list) {
		$str = "";
		foreach( $equipment_list as $equipment ) {
			if ( empty($str) ) {
				$and = "";
			} else {
				$and = " | ";
			}
			$str .= $and . $equipment;
		}
		
		return $str;
	}

	// =========================================================================================================================================
	// Update a workout and al its components
	// =========================================================================================================================================
	
	public function update($data) {
		// --------------------------------------------------------------------------------------------------------
		// Id must be vald
		// --------------------------------------------------------------------------------------------------------
		if ( !property_exists($data,'id') || is_null($data->id) || empty($data->id) || !is_numeric($data->id) ) {
			return $this->return_handler->results(400,"A valid ID must be used",null);
		}
		// --------------------------------------------------------------------------------------------------------
		// do not allow duplicate names
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'name') ) {
			$key = array();
			$key['name'] = $data->name;
			$return = $this->perform('table_workoutdb_library_workout->getForAndKeys',$key);
			if ( $return['status'] == 200 && $data->id != $return['response'][0]->id ) {
				return $this->return_handler->results(400,"Workout: '" . $data->name . "' already exists.",new stdClass());
			}
		}
		if ( property_exists($data,'workout') ) {
			// ========================================================================================================
			// Format and validate the workout log
			// ========================================================================================================
			// validate the workout, create a storable json, create an exercise and equipment list for the workout
			// --------------------------------------------------------------------------------------------------------
			$this->load->library('simplify');
			$return = $this->simplify->workout($data->workout);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			$workout = $return['response']->workout;
			$exercises = $return['response']->exercises;
			$equipment = $return['response']->equipment;
			// --------------------------------------------------------------------------------------------------------
			// Load the needed lookup tables
			// --------------------------------------------------------------------------------------------------------
			$return = $this->perform('this->setupNeededLookupTables');
			if ( $return['status'] > 300 ) {
				return $return;
			}
			// --------------------------------------------------------------------------------------------------------
			// create the workout summary
			// --------------------------------------------------------------------------------------------------------
			$return_summary = $this->perform('this->createWorkoutSummary',$workout);
			// --------------------------------------------------------------------------------------------------------
			// move the original workout, re-formatted workout and new workout summary into the data to be updated
			// --------------------------------------------------------------------------------------------------------
			$data->original_json_workout = json_encode($data->workout);
			unset($data->workout);
			$data->json_workout = json_encode($workout);
			$data->json_workout_summary = json_encode($return_summary->both);
			$data->json_workout_summary_male = json_encode($return_summary->male);
			$data->json_workout_summary_female = json_encode($return_summary->female);
		} else {
			// --------------------------------------------------------------------------------------------------------
			// If the workout was not changed, do not change the workout summary
			// --------------------------------------------------------------------------------------------------------
			if ( property_exists($data,"original_json_workout") ) {
				unset($data->original_json_workout);
			}
			if ( property_exists($data,"json_workout_summary") ) {
				unset($data->json_summary);
			}
			if ( property_exists($data,"json_workout_summary_male") ) {
				unset($data->json_summary_male);
			}
			if ( property_exists($data,"json_workout_summary_female") ) {
				unset($data->json_summary_female);
			}
		}

		// put the entry
		$return_update = $this->perform('table_workoutdb_library_workout->update',$data);
		if ( $return_update['status'] >= 300 ) {
			return $return_update;
		}
		
		$this->id = $data->id;
		// --------------------------------------------------------------------------------------------------------
		// create the linked entries for media
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'media') && count((array) $data->media) > 0 && (!isset($data->media->id) || is_null($data->media->id) || empty($data->media->id)) ) {
			$this->id = $data->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_workout';
			$this->linked_table_name = 'library_workout_media';
			$return = $this->perform('this->post_linked_entry_list',$data->media);
			if ( $return['response'] >= 300 ) {
				return $return;
			}
		}
		// --------------------------------------------------------------------------------------------------------
		// Only update the workout exercise and equipment lists if the log was in the data
		// --------------------------------------------------------------------------------------------------------
		if ( property_exists($data,'original_json_workout') ) {
			// --------------------------------------------------------------------------------------------------------
			// create the cross reference entries only
			// --------------------------------------------------------------------------------------------------------
			$this->id = $data->id;
			$this->database_name = 'workoutdb';
			$this->table_name = 'library_workout';
			$this->xref_table_name = 'library_workout_library_exercise';
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
			$this->table_name = 'library_workout';
			$this->xref_table_name = 'library_workout_library_equipment';
			$this->xrefed_table_name = 'library_equipment';
			$return = $this->perform('this->put_xref_list',$equipment);
			if ( $return['response'] >= 300 ) {
				return $return;
			}
		}
		return $return_update;
	}

	// =========================================================================================================================================
	// Delete a workout and all its components
	// =========================================================================================================================================
	
	public function delete( $p_id ) {
		// the 1st entry in params is the id
		$id = $p_id;
		
		// delete uses the $id parameter for input
		if ( $id ) {
			
			$this->id = $id;
			$this->database_name = "workoutdb";
			$this->table_name = "library_workout";
			
			if ( $this->isDeletableWorkout($this->id) ) {
				// - - - - - - - - - - - - - - - - - - - - - - - -
				//  delete the workout's media
				$this->database_name = 'workoutdb';
				$this->table_name = 'library_workout';
				$this->linked_table_name = 'library_workout_media';
				$this->delete_linked_entries();
				// - - - - - - - - - - - - - - - - - - - - - - - -
				//  delete the library_workout_library_exercise entries
				$this->database_name = 'workoutdb';
				$this->table_name = 'library_workout';
				$this->linked_table_name = 'library_workout_library_exercise';
				$this->delete_linked_entries();
				// - - - - - - - - - - - - - - - - - - - - - - - -
				//  delete the library_workout_library_equipment entries
				$this->database_name = 'workoutdb';
				$this->table_name = 'library_workout';
				$this->linked_table_name = 'library_workout_library_equipment';
				$this->delete_linked_entries();
				// - - - - - - - - - - - - - - - - - - - - - - - -
				// delete the workout
				$return = $this->perform('table_workoutdb_library_workout->delete',$this->id);
				
				return $return;
			} else {
				return $this->return_handler->results(400,"Not Deletable",new stdClass());
			}
		} else {
			return $this->return_handler->results(400,"ID not provided",new stdClass());
		}
	}
	
	public function isDeletableWorkout($p_library_workout_id) {
		// Is the workout used by a log
		$sql_log  = "";
		$sql_log .= "SELECT ";
		$sql_log .= "count(workout_log.id) cnt ";
		$sql_log .= "FROM ";
		$sql_log .= "workout_log ";
		$sql_log .= "WHERE workout_log.library_workout_id = " . $p_library_workout_id . " ";
		$sql_log .= "LIMIT 1 ";
		// Is the workout used by a Pending log
		$sql_pen  = "";
		$sql_pen .= "SELECT ";
		$sql_pen .= "count(workout_log_pending.id) cnt ";
		$sql_pen .= "FROM ";
		$sql_pen .= "workout_log_pending ";
		$sql_pen .= "WHERE workout_log_pending.library_workout_id = " . $p_library_workout_id . " ";
		$sql_pen .= "LIMIT 1 ";
		// Is the workout used by a calendar_event
		$sql_cev  = "";
		$sql_cev .= "SELECT ";
		$sql_cev .= "count(calendar_event_library_workout.id) cnt ";
		$sql_cev .= "FROM ";
		$sql_cev .= "calendar_event_library_workout ";
		$sql_cev .= "WHERE calendar_event_library_workout.library_workout_id = " . $p_library_workout_id . " ";
		$sql_cev .= "LIMIT 1 ";
		// Is the workout used by a Workout Of the Day
		$sql_wod  = "";
		$sql_wod .= "SELECT ";
		$sql_wod .= "count(calendar_entry_template_wod_library_workout.id) cnt ";
		$sql_wod .= "FROM ";
		$sql_wod .= "calendar_entry_template_wod_library_workout ";
		$sql_wod .= "WHERE calendar_entry_template_wod_library_workout.library_workout_id = " . $p_library_workout_id . " ";
		$sql_wod .= "LIMIT 1 ";
		// Is the workout deletable?
		$sql_del  = "";
		$sql_del .= "SELECT ";
		$sql_del .= "if(sum(used.cnt) = 0,1,0) deletable ";
		$sql_del .= "FROM ";
		$sql_del .= "(";
		$sql_del .= "(" . $sql_log . ") ";
		$sql_del .= "UNION ALL ";
		$sql_del .= "(" . $sql_pen . ") ";
		$sql_del .= "UNION ALL ";
		$sql_del .= "(" . $sql_cev . ") ";
		$sql_del .= "UNION ALL ";
		$sql_del .= "(" . $sql_wod . ") ";
		$sql_del .= ") used ";

		// echo "$sql_del<br />";
		
		$query = $this->db->query($sql_del);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			
			return (boolean) $row->deletable;
		} else {
			return true;
		}
	}
	
}