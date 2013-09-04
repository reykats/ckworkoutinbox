<?php

class action_wod extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// =========================================================================================================================================
	// Schedule a singe Workout to a WOD
	// =========================================================================================================================================
	
	public function scheduleWODWorkout($data) {
		// parse the key in $data
		$key = explode('_',$data->key);
		$calendar_entry_template_id = str_replace("W","",$key[0]);
		$date = $key[1];
		// does the calendar_entry_template_wod already exist
		// echo "entry:$calendar_entry_template_id date:$date <br />";
		// ---------------------------------------------------------------------------------------------------------
		// Does the WOD entry already exist
		// ---------------------------------------------------------------------------------------------------------
		$key = array();
		$key['calendar_entry_template_id'] = $calendar_entry_template_id;
		$key['yyyymmdd'] = $date;
		$return = $this->perform('table_workoutdb_calendar_entry_template_wod->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// print_r($return);
		if ( $return['status'] == 200 ) {
			$wod = $return['response'][0];
			// ---------------------------------------------------------------------------------------------------------
			// Add the Workout to an already existing WOD
			// ---------------------------------------------------------------------------------------------------------
			$return = $this->perform('this->addWorkoutToWOD',$wod->id,$data->workout);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		} else {
			// ---------------------------------------------------------------------------------------------------------
			// Ceate a new WOD with the Workout
			// ---------------------------------------------------------------------------------------------------------
			$wod_data = new stdClass();
			$wod_data->calendar_entry_template_id = $calendar_entry_template_id;
			$wod_data->yyyymmdd = $date;
			$wod_data->note = '';
			$wod_data->workout = (array) $data->workout;
			$return = $this->perform('this->createWOD',$wod_data);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		
		return $return;
	}
	
	public function addWorkoutToWOD( $p_calendar_entry_template_wod_id, $p_workout ) {
		// do not allow a workout to be scheduled to a template more than once a day.
		$key = array();
		$key['calendar_entry_template_wod_id'] = $p_calendar_entry_template_wod_id;
		$key['library_workout_id'] = $p_workout;
		$return = $this->perform('table_workoutdb_calendar_entry_template_wod_library_workout->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// print_r($return);
		if ( $return['status'] == 200 ) {
			return $this->return_handler->results(200,"Workout Already Scheduled",new stdClass());
		}
		// -------------------------------------------------------------
		// Link the Workout to the WOD
		// -------------------------------------------------------------
		$xref = new stdClass();
		$xref->calendar_entry_template_wod_id = $p_calendar_entry_template_wod_id;
		$xref->library_workout_id = $p_workout;
		//
		$return = $this->perform('table_workoutdb_calendar_entry_template_wod_library_workout->insert',$xref);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		// -------------------------------------------------------------------------
		// Update the list of Workouts linked to all existing Events for the WOD
		// -------------------------------------------------------------------------
		$return = $this->perform('this->getCalendarEventsByWOD',$p_calendar_entry_template_wod_id);
		// echo "getCalendarEventsByWOD:"; print_r($return); echo "<br /><br />";
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// print_r($return_events);
		if ( $return['status'] == 200 ) {
			$events = $return['response'];
			foreach ( $events as $calendar_event_id ) {
				// link the workout to the event
				$xref = new stdClass();
				$xref->calendar_event_id = $calendar_event_id;
				$xref->library_workout_id = $p_workout;
				//
				$return = $this->perform('table_workoutdb_calendar_event_library_workout->insert',$xref);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				// print_r($return_post);
				//
				unset($xref);
			}
		}
		
		return $return;
	}

	// =========================================================================================================================================
	// Unschedule a singe Workout to a WOD
	// =========================================================================================================================================
	
	public function removeWODWorkout($data) {
		// parse the key in $data
		$key = explode('_',$data->key);
		$calendar_entry_template_id = str_replace("W","",$key[0]);
		$date = $key[1];
		// store off the workout ID
		$workout_id = $data->workout;
		// ---------------------------------------------------------------------------------------------------------
		// does the calendar_entry_template_wod already exist
		// ---------------------------------------------------------------------------------------------------------
		$key = array();
		$key['calendar_entry_template_id'] = $calendar_entry_template_id;
		$key['yyyymmdd'] = $date;
		$return = $this->perform('table_workoutdb_calendar_entry_template_wod->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			return $this->return_handler->results(400,"Invalid Key",new stdClass());
		}
		// Store off the WOD id
		$calendar_entry_template_wod_id = $return['response'][0]->id;
		// ---------------------------------------------------------------------------------------------------------
		// get the link to the WOD Workout to delete
		// ---------------------------------------------------------------------------------------------------------
		$key = array();
		$key['calendar_entry_template_wod_id'] = $calendar_entry_template_wod_id;
		$key['library_workout_id'] = $data->workout;
		$return = $this->perform('table_workoutdb_calendar_entry_template_wod_library_workout->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// print_r($return);
		if ( $return['status'] == 200 ) {
			// ---------------------------------------------------------------------------------------------------------
			// delete the link to the WOD Workout
			// ---------------------------------------------------------------------------------------------------------
			// Store off the WOD/workout ID
			$calendar_entry_template_wod_library_workout_id = $return['response'][0]->id;
			// Delete the link to the Workout
			$return = $this->perform('table_workoutdb_calendar_entry_template_wod_library_workout->delete',$calendar_entry_template_wod_library_workout_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// ---------------------------------------------------------------------------------------------------------
		// get the event/workout to delete
		// ---------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getCalendarEventLibraryWorkoutByWODWorkout',$calendar_entry_template_wod_id,$workout_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// print_r($return);
		if ( $return['status'] == 200 ) {
			foreach( $return['response'] as $id ) {
				// ---------------------------------------------------------------------------------------------------------
				// Delete the link to the Workout
				// ---------------------------------------------------------------------------------------------------------
				$return = $this->perform('table_workoutdb_calendar_event_library_workout->delete',$id);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}
		
		return $this->return_handler->results(202,"Entry removed",new stdClass());
	}
		
	// =========================================================================================================================================
	// Update/Create the calendar_entry_template_wod without workouts
	// =========================================================================================================================================
	
	public function changeWODNote($data) {
		if ( !isset($data->key) || !isset($data->note) ) {
			return $this->return_handler->results(400,"Key or Note not provided",array());
		}
		// parse the key in $data
		$key = explode('_',$data->key);
		$calendar_entry_template_id = str_replace("W","",$key[0]);
		$date = $key[1];
		// does the calendar_entry_template_wod already exist
		$key = array();
		$key['calendar_entry_template_id'] = $calendar_entry_template_id;
		$key['yyyymmdd'] = $date;
		$return = $this->perform('table_workoutdb_calendar_entry_template_wod->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			// -----------------------------------------------------------------------
			// Update WOD
			//------------------------------------------------------------------------
			// prepare update data
			$entry->id = $return['response'][0]->id;
			$entry->note = $data->note;
			// update entry
			return $this->perform('table_workoutdb_calendar_entry_template_wod->update',$entry);
		} else {
			// -----------------------------------------------------------------------
			// create a WOD without any workout
			//------------------------------------------------------------------------
			$wod_data = new stdClass();
			$wod_data->calendar_entry_template_id = $calendar_entry_template_id;
			$wod_data->yyyymmdd = $date;
			$wod_data->note = $data->note;
			$wod_data->workout = array();
			// post entry
			$return = $this->perform('this->createWOD',$wod_data);
		}
	}
	
	public function createWOD( $data ) {
		// Get the calendar_entry_template entry
		$return = $this->perform('table_workoutdb_calendar_entry_template->getForId',$data->calendar_entry_template_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			$return = $this->return_handler->results(400,"Not a valid Template",new stdClass());
		}
		// strore off the template
		$template = $return['response'];
		// -------------------------------------------------------------
		// create the WOD
		// -------------------------------------------------------------
		$wod = new stdClass();
		$wod->calendar_entry_template_id = $data->calendar_entry_template_id;
		$wod->yyyymmdd = $data->yyyymmdd;
		$wod->client_id = $template->client_id;
		$wod->note = $data->note;
		// create the entry
		$return = $this->perform('table_workoutdb_calendar_entry_template_wod->insert',$wod);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$calendar_entry_template_wod_id = $return['response']->id;
		if ( count($data->workout) > 0 ) {
			// ------------------------------------------------------------------
			// Add the list of Workouts to the new WOD
			// ------------------------------------------------------------------
			$this->id = $calendar_entry_template_wod_id;
			$this->table_name = "calendar_entry_template_wod";
			$this->xref_table_name = "calendar_entry_template_wod_library_workout";
			$this->xrefed_table_name = "library_workout";
			$return = $this->perform('this->post_xref_list',$data->workout);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			// -------------------------------------------------------------------------
			// Add the list of Workouts to all existing Events for the WOD
			// -------------------------------------------------------------------------
			$return = $this->perform('this->getCalendarEventsByWOD',$calendar_entry_template_wod_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			$events = $return['response'];
			foreach ( $events as $calendar_event_id ) {
				$this->id = $calendar_event_id;
				$this->table_name = "calendar_event";
				$this->xref_table_name = "calendar_event_library_workout";
				$this->xrefed_table_name = "library_workout";
				$return = $this->perform('this->post_xref_list',$data->workout);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
			}
		}		
		return $return;
	}
		
	// =========================================================================================================================================
	// Supporting Get methods
	// =========================================================================================================================================

	public function getCalendarEventsByWOD( $p_calendar_entry_template_wod_id ) {
		// get the date and calendars for the wod
		$return = $this->perform('this->getCalendarByWOD',$p_calendar_entry_template_wod_id);
		// echo "getCalendarByWOD:"; print_r($return); echo "<br /><br />";
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			return $this->return_handler->results(202,"No Entries found.",array());
		}
		$calendars = $return['response'];
		
		$entries = array();
		foreach ( $calendars as $calendar ) {
			// Set the server's default timezone
			date_default_timezone_set($calendar->timezone);
			// get the date range based on the calendar
			$start = mktime(0,0,0,substr($calendar->yyyymmdd,4,2),substr($calendar->yyyymmdd,6,2),substr($calendar->yyyymmdd,0,4));
			$end = mktime(0,0,-1,substr($calendar->yyyymmdd,4,2),substr($calendar->yyyymmdd,6,2) + 1,substr($calendar->yyyymmdd,0,4));
			
			$return = $this->perform('this->getCalendarEventsByCalendarWODDateRange',$calendar->id,$p_calendar_entry_template_wod_id,$start,$end);
			// echo "getCalendarEventsByCalendarWODDateRange:"; print_r($return); echo "<br /><br />";
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			$entries = array_merge($entries,$return['response']);
			// echo "entries:"; print_r($entries); echo "<br /><br />";
			
		}
		return $this->return_handler->results(200,"",$entries);
	}
	
	public function getCalendarByWOD($p_calendar_entry_template_wod_id) {
		$sql  = "SELECT wod.yyyymmdd, c.id, c.timezone ";
		$sql .= "FROM calendar_entry_template_wod wod, ";
		$sql .= "calendar c ";
		$sql .= "WHERE wod.id = " . $p_calendar_entry_template_wod_id . " ";
		$sql .= "AND c.client_id = wod.client_id ";
		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result(); 
			
			$entries = array();
			foreach ( $rows as $row ) {
				array_push($entries,$row);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
	}
	
	public function getCalendarEventsByCalendarWODDateRange($p_calendar_id,$p_calendar_entry_template_wod_id,$p_start,$p_end) {
		$sql  = "SELECT ev.* ";
		$sql .= "FROM calendar_entry_template_wod wod, ";
		$sql .= "calendar_event ev ";
		$sql .= "WHERE wod.id = " . $p_calendar_entry_template_wod_id . " ";
		$sql .= "AND ev.calendar_entry_template_id = wod.calendar_entry_template_id ";
		$sql .= "AND ev.calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND ev.start BETWEEN " . $p_start . " AND " . $p_end . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result(); 
			
			$entries = array();
			foreach ( $rows as $row ) {
				array_push($entries,(int) $row->id);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
	}

	public function getCalendarEventLibraryWorkoutByWODWorkout( $p_calendar_entry_template_wod_id,$p_library_workout_id ) {
		$entries = array();
		
		// get the date and calendars for the wod
		$return = $this->perform('this->getCalendarByWOD',$p_calendar_entry_template_wod_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			return $this->return_handler->results(202,"No Entries found.",array());
		}
		$calendars = $return['response'];
		foreach ( $calendars as $calendar ) {
			// Set the server's default timezone
			date_default_timezone_set($calendar->timezone);
			// get the date range based on the calendar
			$start = mktime(0,0,0,substr($calendar->yyyymmdd,4,2),substr($calendar->yyyymmdd,6,2),substr($calendar->yyyymmdd,0,4));
			$end = mktime(0,0,-1,substr($calendar->yyyymmdd,4,2),substr($calendar->yyyymmdd,6,2) + 1,substr($calendar->yyyymmdd,0,4));
			
			$return = $this->perform('this->getCalendarEventLibraryWorkoutsByCalendarWODWorkoutDateRange',$calendar->id,$p_calendar_entry_template_wod_id,$p_library_workout_id,$start,$end);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			$entries = array_merge($entries,$return['response']);
		}
		return $this->return_handler->results(200,"",$entries);
	}

	public function getCalendarEventLibraryWorkoutsByCalendarWODWorkoutDateRange($p_calendar_id,$p_calendar_entry_template_wod_id,$p_library_workout_id,$p_start,$p_end) {
		$entries = array();
		
		$sql  = "SELECT w.id ";
		$sql .= "FROM calendar_entry_template_wod wod, ";
		$sql .= "calendar_event ev, ";
		$sql .= "calendar_event_library_workout w ";
		$sql .= "WHERE wod.id = " . $p_calendar_entry_template_wod_id . " ";
		$sql .= "AND ev.calendar_entry_template_id = wod.calendar_entry_template_id ";
		$sql .= "AND ev.calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND ev.start BETWEEN " . $p_start . " AND " . $p_end . " ";
		$sql .= "AND w.calendar_event_id = ev.id ";
		$sql .= "AND w.library_workout_id = " . $p_library_workout_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result(); 
			
			foreach ( $rows as $row ) {
				array_push($entries,(int) $row->id);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
	}
	
}