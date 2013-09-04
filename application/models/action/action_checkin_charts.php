<?php

class action_checkin_charts extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get the Number of checkins for the charts for a client and date ( the date is yymmdd )
	//
	// 1) checkins by class start time for the date passed in
	// 2) checkins by day for the last 30 days
	// 3) checkins by week for the last 7 weeks
	// 4) checkins by month for the last 7 months
	//
	// ==================================================================================================================


	public function getChartNumbersForClientDate( $p_client_id, $p_ccyymmdd ) {
		// =============================================================================================
		// Assumptions : 
		// 1) the timezone for a location will be stored in the location
		// 1) all calendars for a location will have the same timezone as the location.
		// 2) all calendars for all classrooms under a location will have the timezone of the location
		//
		// Basically the timezone is a function of the location
		// =============================================================================================
		// Initialize the response
		// ---------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientLocationForClient',$p_client_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}

		$response = $return['response'];
		unset($return);
		// ---------------------------------------------------------------------------------------------
		// get Days participant counts for a client's locations
		// ---------------------------------------------------------------------------------------------
		$return = $this->perform('this->getParticipantCountsForClientDate',$p_client_id,$p_ccyymmdd);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		foreach( $response->location as &$location ) {
			if ( array_key_exists($location->id, $return['response']) ) {
				// echo "location:" . $location->id . "<br />\n";
				$location->participant = $return['response'][$location->id];
			}
		}
		unset($return);
		// ---------------------------------------------------------------------------------------------
		// get Daily participant counts for a client's locations for the last 30 days
		// ---------------------------------------------------------------------------------------------
		$return = $this->perform('this->getDailyCountForLast30Days',$p_client_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		foreach( $response->location as &$location ) {
			if ( array_key_exists($location->id, $return['response']) ) {
				foreach( $location->last_30_days as &$entry ) {
					if ( array_key_exists($entry->start,$return['response'][$location->id]) ) {
						$entry->count = $return['response'][$location->id][$entry->start];
					}
				}
			}
		}
		// ---------------------------------------------------------------------------------------------
		// get Weekly participant counts for a client's locations for the last 7 weeks
		// ---------------------------------------------------------------------------------------------
		$return = $this->perform('this->getDailyCountForLast7Weeks',$p_client_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		foreach( $response->location as &$location ) {
			if ( array_key_exists($location->id, $return['response']) ) {
				foreach( $location->last_7_weeks as &$entry ) {
					if ( array_key_exists($entry->start,$return['response'][$location->id]) ) {
						$entry->count = $return['response'][$location->id][$entry->start];
					}
				}
			}
		}
		// ---------------------------------------------------------------------------------------------
		// get Monthly participant counts for a client's locations for the last 7 months
		// ---------------------------------------------------------------------------------------------
		$return = $this->perform('this->getDailyCountForLast7Months',$p_client_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		
		foreach( $response->location as &$location ) {
			if ( array_key_exists($location->id, $return['response']) ) {
				foreach( $location->last_7_months as &$entry ) {
					if ( array_key_exists($entry->start,$return['response'][$location->id]) ) {
						$entry->count = $return['response'][$location->id][$entry->start];
					}
				}
			}
		}
		
		return $this->return_handler->results(200,"",$response);
	}
	
	public function getClientLocationForClient( $p_client_id, $p_use_alias=true ) {
		$sql  = "SELECT ";
		$sql .= "client.id 'client.id', client.name 'client.name', ";
		$sql .= "location.id 'location.id', location.name 'location.name', location.timezone 'location.timezone', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW(),@server_timezone,location.timezone),'%Y%m%d') 'now.ccyymmdd', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW(),@server_timezone,location.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'week.week_7', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 1 WEEK,@server_timezone,location.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'week.week_6', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 2 WEEK,@server_timezone,location.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'week.week_5', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 3 WEEK,@server_timezone,location.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'week.week_4', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 4 WEEK,@server_timezone,location.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'week.week_3', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 5 WEEK,@server_timezone,location.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'week.week_2', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 6 WEEK,@server_timezone,location.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'week.week_1', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW(),@server_timezone,location.timezone),'%b') 'month.month_7', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 1 MONTH,@server_timezone,location.timezone),'%b') 'month.month_6', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 2 MONTH,@server_timezone,location.timezone),'%b') 'month.month_5', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 3 MONTH,@server_timezone,location.timezone),'%b') 'month.month_4', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 4 MONTH,@server_timezone,location.timezone),'%b') 'month.month_3', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 5 MONTH,@server_timezone,location.timezone),'%b') 'month.month_2', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL 6 MONTH,@server_timezone,location.timezone),'%b') 'month.month_1' ";
		$sql .= "FROM ";
		$sql .= "(SELECT @server_timezone := server.timezone FROM server) server, ";
		$sql .= "client, ";
		$sql .= "location ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND location.client_id = client.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$clients = array();
		$c = -1;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row,$p_use_alias);
			// echo json_encode($row) . "<br />\n";
			
			if ( $c < 0 || $clients[$c]->client->id != $row->client->id ) {
				++$c;
				$clients[$c]->client = clone $row->client;
				// initialize the class array
				$clients[$c]->location = array();
				$location = &$clients[$c]->location;
				$l = -1;
			}
			if ( $l < 0 || $location[$l]->id != $row->location->id ) {
				++$l;
				$location[$l] = clone $row->location;
				// initialize loction chart arrays
				$location[$l]->participant = array();
				$location[$l]->last_30_days = array();
				$i = -1;
				for ( $x = 29; $x >= 0; --$x ) {
					++$i;
					$location[$l]->last_30_days[$i] = new stdClass();
					$location[$l]->last_30_days[$i]->start = date('n/j',mktime(0,0,0,substr($row->now->ccyymmdd,4,2),substr($row->now->ccyymmdd,6,2) - $x,substr($row->now->ccyymmdd,0,4)));
					$location[$l]->last_30_days[$i]->count = 0;
				}
				$location[$l]->last_7_weeks = array();
				$location[$l]->last_7_months = array();
				$i = -1;
				for ( $x = 1; $x <= 7; ++$x ) {
					++$i;
					$location[$l]->last_7_weeks[$i] = new stdClass();
					$location[$l]->last_7_weeks[$i]->start = $row->week->{'week_' . $x};
					$location[$l]->last_7_weeks[$i]->count = 0;
					$location[$l]->last_7_months[$i] = new stdClass();
					$location[$l]->last_7_months[$i]->start = $row->month->{'month_' . $x};
					$location[$l]->last_7_months[$i]->count = 0;
				}
			}
		}
			
		return $this->return_handler->results(200,"",$clients[0]);
	}
	
	public function getParticipantCountsForClientDate( $p_client_id, $p_ccyymmdd, $p_use_alias=true ) {
		$sql  = "SELECT ";
		$sql .= "location.id 'location.id', ";
		$sql .= "calendar_event.start 'calendar_event.start', ";
		$sql .= "count(calendar_event_participation.id) 'calendar_event.checkins.int' ";
		$sql .= "FROM ";
		$sql .= "(SELECT @server_timezone := server.timezone FROM server) server, ";
		$sql .= "client, ";
		$sql .= "location ";
		$sql .= "LEFT OUTER JOIN calendar ";
		$sql .= "LEFT OUTER JOIN calendar_event ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation ";
		$sql .= "ON calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "ON calendar_event.calendar_id = calendar.id ";
		$sql .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%Y%m%d') = '" . $p_ccyymmdd . "' ";
		$sql .= "ON calendar.location_id = location.id ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND location.client_id = client.id ";
		$sql .= "GROUP BY location.id, calendar_event.start ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$location = array();
		$l = null;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row,$p_use_alias);
			// echo json_encode($row) . "<br />\n";
			
			if ( is_null($l) || $l != $row->location->id ) {
				$l = $row->location->id;
				// initialize the class array
				$location[$l] = array();
				$participant = &$location[$l];
				$p = -1;
			}
			if ( !is_null($row->calendar_event->start) ) {
				++$p;
				$participant[$p] = new stdClass();
				$participant[$p]->start = $row->calendar_event->start;
				$participant[$p]->count = $row->calendar_event->checkins;
				$participant[$p]->key = "L" . $row->location->id . "_" . $row->calendar_event->start;
			}
		}
			
		return $this->return_handler->results(200,"",$location);
	}
	
	public function getDailyCountForLast30Days( $p_client_id ) {
		$sql  = "SELECT ";
		$sql .= "location.id 'location.id', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%c/%e') 'location.label', ";
		$sql .= "count(calendar_event_participation.id) 'location.checkins.int' ";
		$sql .= "FROM ";
		$sql .= "(SELECT @server_timezone := server.timezone FROM server) server, ";
		$sql .= "client, ";
		$sql .= "location ";
		$sql .= "LEFT OUTER JOIN calendar ";
		$sql .= "LEFT OUTER JOIN calendar_event ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation ";
		$sql .= "ON calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "ON calendar_event.calendar_id = calendar.id ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= "BETWEEN ";
		$sql .= "DATE_FORMAT(CONVERT_TZ((NOW() - INTERVAL 29 DAY),@server_timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW(),@server_timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= "ON calendar.location_id = location.id ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND location.client_id = client.id ";
		$sql .= "GROUP BY location.id, DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%Y%m%d') ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$location = array();
		$l = null;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// echo json_encode($row) . "<br />\n";
			
			if ( is_null($l) || $l != $row->location->id ) {
				$l = $row->location->id;
				// initialize the class array
				$location[$l] = array();
			}
			if ( !is_null($row->location->label) & !empty($row->location->label) ) {
				$location[$l][$row->location->label] = $row->location->checkins;
			}
		}
			
		return $this->return_handler->results(200,"",$location);
	}
	
	public function getDailyCountForLast7Weeks( $p_client_id ) {
		$sql  = "SELECT ";
		$sql .= "location.id 'location.id', ";
		$sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%X%V Sunday'),'%X%V %W'),'%c/%e') 'location.label', ";
		$sql .= "count(calendar_event_participation.id) 'location.checkins.int' ";
		$sql .= "FROM ";
		$sql .= "(SELECT @server_timezone := server.timezone FROM server) server, ";
		$sql .= "client, ";
		$sql .= "location ";
		$sql .= "LEFT OUTER JOIN calendar ";
		$sql .= "LEFT OUTER JOIN calendar_event ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation ";
		$sql .= "ON calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "ON calendar_event.calendar_id = calendar.id ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%X%V') ";
		$sql .= "BETWEEN ";
		$sql .= "DATE_FORMAT(CONVERT_TZ((NOW() - INTERVAL 6 WEEK),@server_timezone,calendar.timezone),'%X%V') ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW(),@server_timezone,calendar.timezone),'%X%V') ";
		$sql .= "ON calendar.location_id = location.id ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND location.client_id = client.id ";
		$sql .= "GROUP BY location.id, DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%X%V') ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$location = array();
		$l = null;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// echo json_encode($row) . "<br />\n";
			
			if ( is_null($l) || $l != $row->location->id ) {
				$l = $row->location->id;
				// initialize the class array
				$location[$l] = array();
			}
			if ( !is_null($row->location->label) & !empty($row->location->label) ) {
				$location[$l][$row->location->label] = $row->location->checkins;
			}
		}
			
		return $this->return_handler->results(200,"",$location);
	}
	
	public function getDailyCountForLast7Months( $p_client_id ) {
		$sql  = "SELECT ";
		$sql .= "location.id 'location.id', ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%b') 'location.label', ";
		$sql .= "count(calendar_event_participation.id) 'location.checkins.int' ";
		$sql .= "FROM ";
		$sql .= "(SELECT @server_timezone := server.timezone FROM server) server, ";
		$sql .= "client, ";
		$sql .= "location ";
		$sql .= "LEFT OUTER JOIN calendar ";
		$sql .= "LEFT OUTER JOIN calendar_event ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation ";
		$sql .= "ON calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "ON calendar_event.calendar_id = calendar.id ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%Y%m') ";
		$sql .= "BETWEEN ";
		$sql .= "DATE_FORMAT(CONVERT_TZ((NOW() - INTERVAL 6 MONTH),@server_timezone,calendar.timezone),'%Y%m') ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW(),@server_timezone,calendar.timezone),'%Y%m') ";
		$sql .= "ON calendar.location_id = location.id ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND location.client_id = client.id ";
		$sql .= "GROUP BY location.id, DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),@server_timezone,calendar.timezone),'%Y%m') ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$location = array();
		$l = null;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// echo json_encode($row) . "<br />\n";
			
			if ( is_null($l) || $l != $row->location->id ) {
				$l = $row->location->id;
				// initialize the class array
				$location[$l] = array();
			}
			if ( !is_null($row->location->label) & !empty($row->location->label) ) {
				$location[$l][$row->location->label] = $row->location->checkins;
			}
		}
			
		return $this->return_handler->results(200,"",$location);
	}
}