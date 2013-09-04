<?php

class cli_del_pending_logs extends action_generic {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function delete( $p_months ) {
		// get a list of Clients and Calendars
		$return = $this->perform('this->getClientCalendars');
		// Delete the Pending Workout Logs from the returned Calendar Events.
		$deleted = 0;
		foreach ( $return['response'] as $client ) {
			foreach ( $client->calendar as $calendar_id ) {
				// get a list of Events and their Workout Log Pending
				$return = $this->perform('this->getEventPendingForCalendarMonths',$calendar_id,$p_months);
				foreach ( $return['response'] as $calendar_event ) {
					foreach ( $calendar_event->workout_log_pending as $workout_log_pending_id ) {
						$return = $this->perform('table_workoutdb_workout_log_pending->delete',$workout_log_pending_id);
						if ( $return['status'] >= 300 ) {
							return $return;
						}
						++$deleted;
					}
				}
			}
		}
		
		return $this->return_handler->results(200,"",$deleted);
	}
	
	// ===================================================================================================================================================
	// Get a list of All Clients and thier Calendars
	// ===================================================================================================================================================

	public function getClientCalendars( $p_use_alias = false ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "client.id 'client.id', ";
		$sql .= "calendar.id 'calendar.id' ";
		$sql .= "FROM ";
		$sql .= "client, ";
		$sql .= "calendar ";
		$sql .= "WHERE calendar.client_id = client.id ";
		$sql .= "GROUP BY client.name, client.id, calendar.name, calendar.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->calendar = mysql_schema::getTableAlias('workoutdb','calendar',$p_use_alias);
		
		$client = array();
		$c = -1;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table and use column aliases
			$row = mysql_schema::objectify_row($row,$p_use_alias);
			// print_r($row); echo "<br />";
			
			if ( $c < 0 || $client[$c]->id != $row->client->id ) {
				++$c;
				$client[$c] = clone $row->client;
				// initialize the calendar array
				$client[$c]->{$table->calendar} = array();
				$calendar = &$client[$c]->{$table->calendar};
				$cal = -1;
			}
			if ( $cal < 0 || $calendar[$cal] != $row->calendar->id ) {
				++$cal;
				$calendar[$cal] = $row->calendar->id;
			}
		}
			
		return $this->return_handler->results(200,"",$client);
	}
	
	// ===================================================================================================================================================
	// Get a list of calendar events with pending workout log older than 1 month
	// ===================================================================================================================================================

	public function getEventPendingForCalendarMonths( $p_calendar_id, $p_months, $p_use_alias = false ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "calendar_event.id 'calendar_event.id', ";
		$sql .= "workout_log_pending.id 'workout_log_pending.id' ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation, ";
		$sql .= "workout_log_pending ";
		$sql .= "WHERE calendar.id = " . $p_calendar_id . " ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') < DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL " . $p_months . " MONTH,server.timezone,calendar.timezone),'%Y%m%d') ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "AND workout_log_pending.calendar_event_participation_id = calendar_event_participation.id ";
		$sql .= "ORDER BY calendar_event.name, calendar_event.start, calendar_event.id, workout_log_pending.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->calendar_event = mysql_schema::getTableAlias('workoutdb','calendar_event',$p_use_alias);
		$table->workout_log_pending = mysql_schema::getTableAlias('workoutdb','workout_log_pending',$p_use_alias);
		$table->library_workout = mysql_schema::getTableAlias('workoutdb','library_workout',$p_use_alias);
		
		$calendar_event = array();
		$e = -1;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table and use column aliases
			$row = mysql_schema::objectify_row($row,$p_use_alias);
			// print_r($row); echo "<br />";
			
			if ( $e < 0 || $calendar_event[$e]->id != $row->calendar_event->id ) {
				++$e;
				$calendar_event[$e] = clone $row->calendar_event;
				// initialize the workout log pending array
				$calendar_event[$e]->{$table->workout_log_pending} = array();
				$workout_log_pending = &$calendar_event[$e]->{$table->workout_log_pending};
				$p = -1;
			}
			// echo json_encode($workout_log_pending) . "<br />\n";
			if ( $p < 0 || $workout_log_pending[$p] != $row->workout_log_pending->id ) {
				++$p;
				$workout_log_pending[$p] = $row->workout_log_pending->id;
			}
		}
			
		return $this->return_handler->results(200,"",$calendar_event);
	}
}