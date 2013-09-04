<?php

class widget_schedule_wod extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// =======================================================================================================================================
	// Get the list of WOD or log results classes and thier scheduled times for a client on a given date (p_date is UTC date/time)
	// =======================================================================================================================================
	
	public function getScheduleWODForClient( $p_client ) {
		if ( !array_key_exists('date', $_GET) || (!isset($_GET['date']) && !is_numeric($_GET['date'])) ) {
			date_default_timezone_set($p_client->timezone);
			$date = date('Ymd');
		} else {
			$date = $_GET['date'];
		}
		// echo "date:$date<br />";
		// ---------------------------------------------------------------------------------------------------------------------
		// Get the schedule
		// ---------------------------------------------------------------------------------------------------------------------
		// get a list of events for a client between two dates
		$return = $this->perform('action_calendar->getEventsByClientDate',$p_client->id,$date);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		
		// Sort the resultset into template/location/date/time order
		$return = $this->perform('action_calendar->sortByLocationTemplateDateTime',$return['response']);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		
		// Convert the list of events into a schedule format
		$return = $this->perform('this->formatSchedule',$return['response']);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		
		$schedule_list = $return['response'];
		// echo "schedule_list:"; print_r($schedule_list); echo "<br />";
		// ---------------------------------------------------------------------------------------------------------------------
		// Get the WODs
		// ---------------------------------------------------------------------------------------------------------------------
		
		// get a list wod workouts for location/template for a given client and date
		$return = $this->perform('this->getWODsByClientDate',$p_client->id,$date);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$wod_list = $return['response'];
		// echo "wod_list:"; print_r($wod_list); echo "<br />";
		
		$response = new stdClass();
		$response->name = $p_client->name;
		$response->schedule = $schedule_list;
		$response->wod = $wod_list;
		return $this->return_handler->results(200,"",$response);
	}

	public function formatSchedule( $p_event_list ) {
		$location = array();
		$l = -1;
		foreach ( $p_event_list as $entry ) {
			// echo json_encode($entry);
			if ( $l < 0 || $location[$l]->name != $entry->location->name) {
				++$l;
				$location[$l]->name = $entry->location->name;
				$location[$l]->timezone_offset = format_timezone_offset($entry->event->timezone);
				// initialize the class array
				$location[$l]->class = array();
				$class = &$location[$l]->class;
				$c = -1;
			}
			if ( $c < 0 || $class[$c]->name != $entry->event->name ) {
				++$c;
				$class[$c]->name = $entry->event->name;
				$class[$c]->note = $entry->event->description;
				// initialize the class_time array
				$class[$c]->class_time = array();
				$class_time = &$class[$c]->class_time;
				$t = -1;
			}
			if ( $t < 0 || $class_time[$t] != $entry->event->start ) {
				++$t;
				$class_time[$t] = $entry->event->start;
			}
		}
		
		return $this->return_handler->results(200,"",$location);
	}
	
	public function getWODsByClientDate( $p_client_id, $p_date ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "calendar_entry_template.name 'calendar_entry_template.name', ";
		$sql .= "calendar_entry_template_wod.note 'calendar_entry_template_wod.note', ";
		$sql .= "library_workout.name 'library_workout.name', ";
		$sql .= "library_workout.benchmark 'library_workout.benchmark', ";
		$sql .= "library_workout.note 'library_workout.note', ";
		$sql .= "library_workout.json_workout_summary 'library_workout.json_workout_summary', ";
		$sql .= "library_workout_recording_type.name 'library_workout_recording_type.name' ";
		$sql .= "FROM ";
		$sql .= "calendar_entry_template_wod ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template ";
		$sql .= "ON calendar_entry_template.id = calendar_entry_template_wod.calendar_entry_template_id ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template_wod_library_workout ";
		$sql .= "LEFT OUTER JOIN library_workout ";
		$sql .= "LEFT OUTER JOIN library_workout_recording_type ";
		$sql .= "ON library_workout_recording_type.id = library_workout.library_workout_recording_type_id ";
		$sql .= "ON library_workout.id = calendar_entry_template_wod_library_workout.library_workout_id ";
		$sql .= "ON calendar_entry_template_wod_library_workout.calendar_entry_template_wod_id = calendar_entry_template_wod.id ";
		$sql .= "WHERE calendar_entry_template_wod.client_id = " . $p_client_id . " ";
		$sql .= "AND calendar_entry_template_wod.yyyymmdd = '" . $p_date . "' ";
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(200,"",array());
		}
		$rows = $query->result();
		
		$class = array();
		$c = -1;
		foreach ( $rows as $row ) {
			// echo json_encode($row) . "<br />\n";

			mysql_schema::cast_row('workoutdb',$row);
			// echo json_encode($row) . "<br />\n";
			
			$entry = mysql_schema::objectify_row($row);
			// echo json_encode($entry) . "<br /><br />\n\n";
			
			if ( $c < 0 || $class[$c]->name != $entry->calendar_entry_template->name ) {
				++$c;
				$class[$c]->name = $entry->calendar_entry_template->name;
				$class[$c]->note = $entry->calendar_entry_template_wod->note;
				// initialize the class array
				$class[$c]->workout = array();
				$workout = &$class[$c]->workout;
				$w = -1;
			}
			if ( !is_null($entry->library_workout->name) ) {
				if ( $w < 0 || $workout[$w]->name != $entry->library_workout->name ) {
					++$w;
					$workout[$w] = new stdClass();
					$workout[$w]->name = $entry->library_workout->name;
					$workout[$w]->benchmark = $entry->library_workout->benchmark;
					$workout[$w]->note = $entry->library_workout->note;
					$workout[$w]->summary = json_decode($entry->library_workout->json_workout_summary);
					$workout[$w]->workout_recording_type = $entry->library_workout_recording_type->name;
				}
			}
		}
		
		return $this->return_handler->results(200,"",$class);
	}
}