<?php

class action_has_activity extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// =======================================================================================================================================
	// Get the list of dates during the week that have activity for a Client
	// =======================================================================================================================================

	public function getDaysWithActivityForClientDate($p_client_id,$p_date) {
		$ccyymmdd_start = $p_date;
		$ccyymmdd_end = date('Ymd',mktime(0,0,0,substr($p_date,4,2),substr($p_date,6,2) + 6,substr($p_date,0,4)));
		
		$sql  = "( ";
		$sql .= "SELECT ";
		$sql .= "calendar_entry_template_wod.yyyymmdd ccyymmdd ";
		$sql .= "FROM ";
		$sql .= "calendar_entry_template_wod_library_workout, ";
		$sql .= "calendar_entry_template_wod ";
		$sql .= "WHERE calendar_entry_template_wod.client_id = " . $p_client_id . " ";
		$sql .= "AND calendar_entry_template_wod.yyyymmdd BETWEEN '" . $ccyymmdd_start . "' AND '" . $ccyymmdd_end . "' ";
		$sql .= "AND calendar_entry_template_wod_library_workout.calendar_entry_template_wod_id = calendar_entry_template_wod.id ";
		$sql .= ") ";
		$sql .= "UNION ";
		$sql .= "( ";
		$sql .= "SELECT ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ccyymmdd ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_library_workout ";
		$sql .= "WHERE calendar.client_id = " . $p_client_id . " ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') BETWEEN '" . $ccyymmdd_start . "' AND '" . $ccyymmdd_end . "' ";
		$sql .= "AND calendar_event_library_workout.calendar_event_id = calendar_event.id ";
		$sql .= "GROUP BY DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= ") ";
		$sql .= "UNION ";
		$sql .= "( ";
		$sql .= "SELECT ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ccyymmdd ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation ";
		$sql .= "WHERE calendar.client_id = " . $p_client_id . " ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') BETWEEN '" . $ccyymmdd_start . "' AND '" . $ccyymmdd_end . "' ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "GROUP BY DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= ") ";
		$sql .= "ORDER BY ccyymmdd ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(200,"",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach( $rows as $row ) {
			$entries[] = $row->ccyymmdd;
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// =======================================================================================================================================
	// Get the list of dates during the week that have activity for a User
	// =======================================================================================================================================

	public function getDaysWithActivityForUserDate($p_user_id,$p_date) {
		$ccyymmdd_start = $p_date;
		$ccyymmdd_end = date('Ymd',mktime(0,0,0,substr($p_date,4,2),substr($p_date,6,2) + 6,substr($p_date,0,4)));
		
		$sql  = "SELECT ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ccyymmdd ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "client_user, ";
		$sql .= "calendar_event_participation, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " AND client_user.deleted IS NULL ";
		$sql .= "AND calendar_event_participation.client_user_id = client_user.id ";
		$sql .= "AND calendar_event.id = calendar_event_participation.calendar_event_id ";
		$sql .= "AND calendar.id = calendar_event.calendar_id ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= "BETWEEN '" . $ccyymmdd_start . "' AND '" . $ccyymmdd_end . "' ";
		$sql .= "GROUP BY DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(200,"",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach( $rows as $row ) {
			$entries[] = $row->ccyymmdd;
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// =======================================================================================================================================
	// Get the list of dates during the week that have activity for a User
	// =======================================================================================================================================
	
	public function getDaysWithLeaderboardActivityForUserDate($p_user_id,$p_date) {
		$ccyymmdd_start = $p_date;
		$ccyymmdd_end = date('Ymd',mktime(0,0,0,substr($p_date,4,2),substr($p_date,6,2) + 6,substr($p_date,0,4)));
		
		$sql  = "SELECT ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ccyymmdd ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "client_user, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation, ";
		$sql .= "workout_log ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " AND client_user.deleted IS NULL ";
		$sql .= "AND calendar.client_id = client_user.client_id ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= "BETWEEN '" . $ccyymmdd_start . "' AND '" . $ccyymmdd_end . "' ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "AND workout_log.calendar_event_participation_id = calendar_event_participation.id ";
		$sql .= "AND workout_log.library_workout_recording_type_id IS NOT NULL ";
		$sql .= "GROUP BY DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(200,"",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach( $rows as $row ) {
			$entries[] = $row->ccyymmdd;
		}
		
		return $this->return_handler->results(200,"",$entries);
	}
}