<?php

class action_calendar_event extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// =======================================================================================================================================
	// Get the calendar event and its timezone for a class ( entry_id and utc_start_date/time )
	// =======================================================================================================================================
	
	public function getForEntryStart( $p_calendar_entry_id, $p_utc_start, $p_use_alias=true ) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "calendar_event.id 'calendar_event.id', calendar_event.name 'calendar_event.name', calendar_event.start 'calendar_event.start', ";
		$sql .= "calendar_event.location 'calendar_event.location', calendar_event.description 'calendar_event.description', ";
		$sql .= "calendar.id 'calendar.id', calendar.name 'calendar.name', calendar.timezone 'calendar.timezone' ";
		$sql .= "FROM calendar_event, ";
		$sql .= "calendar ";
		$sql .= "WHERE calendar_event.calendar_entry_id = " . $p_calendar_entry_id . " AND calendar_event.start = " . $p_utc_start . " ";
		$sql .= "AND calendar.id = calendar_event.calendar_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0 ) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$row = $query->row();
		
		// get node names
		$table = new stdClass();
		$table->calendar = mysql_schema::getTableAlias('workoutdb','calendar',$p_use_alias);
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
		// echo json_encode($row) . "<br /><br />\n\n";
		
		$entry = clone $row->calendar_event;
		$entry->{$table->calendar} = clone $row->calendar;
		
		return $this->return_handler->results(200,"",$entry);
	}

	// =======================================================================================================================================
	// Get the calendar entry and its event for a given UTC start date/time
	// =======================================================================================================================================
	
	public function getCalendarEntryEventForEntryStart( $p_calendar_entry_id, $p_start ) {
		//
		// initialize the response data
		$entry = new stdClass();
		// ------------------------------------------------------------------------------------------------------------
		// Get the calendar entry
		// ------------------------------------------------------------------------------------------------------------
		$sql  = "SELECT en.id, en.calendar_id, en.all_day, en.duration, en.name, en.description, en.location, ";
		$sql .= "en.calendar_entry_template_id, en.calendar_entry_type_id, en.rsvp, en.log_participant, wod, en.log_result, en.payment, en.waiver, ";
		$sql .= "en.calendar_entry_repeat_type_id, en.start, en.end, en.removed_dates, ";
		$sql .= "cal.timezone ";
		$sql .= "FROM calendar_entry en, ";
		$sql .= "calendar cal ";
		$sql .= "WHERE en.id = " . $p_calendar_entry_id . " ";
		$sql .= "AND cal.id = en.calendar_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			
			// setup the new entry
			$entry = new stdClass();
			$entry->id = (int) $row->id;
			$entry->calendar_id = (int) $row->calendar_id;
			$entry->calendar_timezone = $row->timezone;
			if ( $row->all_day ) {
				$entry->all_day = true;
			} else {
				$entry->all_day = false;
			}
			$entry->duration = (int) $row->duration;
			$entry->name = $row->name;
			$entry->description = $row->description;
			$entry->location = $row->location;
			$entry->template_id = (int) $row->calendar_entry_template_id;
			$entry->entry_type_id = (int) $row->calendar_entry_type_id;
			$entry->rsvp = (boolean) $row->rsvp;
			$entry->log_participant = (boolean) $row->log_participant;
			$entry->wod = (boolean) $row->wod;
			$entry->log_result = (boolean) $row->log_result;
			$entry->waiver = (boolean) $row->waiver;
			$entry->payment = (boolean) $row->payment;
			$entry->repeat_type_id = (int) $row->calendar_entry_repeat_type_id;
			$entry->start = (int) $row->start;
			if ( is_null($row->end) ) {
				$entry->end = null;
			} else {
				$entry->end = (int) $row->end;
			}
			$entry->removed = json_decode($row->removed_dates);
			
			// print_r($entry);
		
			// get the blocked dates
			$return = $this->perform('action_calendar_entry->getBlockedDates',$p_calendar_entry_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			// print_r($return);
			$entry->blocked = clone $return['response'];
			
			// can the start and repeat type be changed?
			$return = $this->perform('action_calendar_entry->getHasEvent',$p_calendar_entry_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $return['response']->hasEvent ) {
				$entry->blocked->start = true;
				$entry->blocked->repeat_type = true;
				$entry->blocked->template = true;
				$entry->blocked->calendar = true;
			} else {
				$entry->blocked->start = false;
				$entry->blocked->repeat_type = false;
				$entry->blocked->template = false;
				$entry->blocked->calendar = false;
			}
				
			// ------------------------------------------------------------------------------------------------------------
			// if the entry is a WOD, get the scheduled workouts for the calendar entry/date
			// ------------------------------------------------------------------------------------------------------------
			$wod = array();
			
			if ( $entry->wod && !is_null($entry->template_id) ) {
				// set the date for the entry
				date_default_timezone_set($entry->calendar_timezone);
				$date = date("Ymd",$p_start);
				// echo "date:$date<br />";
				
				$sql  = "SELECT wk.id, wk.name ";
				$sql .= "FROM calendar_entry_template_wod wod, ";
				$sql .= "calendar_entry_template_wod_library_workout x, ";
				$sql .= "library_workout wk ";
				$sql .= "WHERE wod.calendar_entry_template_id = " . $entry->template_id . " ";
				$sql .= "AND wod.yyyymmdd = '" . $date . "' ";
				$sql .= "AND x.calendar_entry_template_wod_id = wod.id ";
				$sql .= "AND wk.id = x.library_workout_id ";
		
				// echo "$sql<br />";
				
				$query = $this->db->query($sql);
				if ($query->num_rows() > 0) {
					$rows = $query->result(); 
					
					foreach ( $rows as $row ) {
						$workout = new stdClass();
						$workout->id = (int) $row->id;
						$workout->name = $row->name;
						array_push($wod, $workout);
						unset($workout);
					}
				}
			}
		
			// ------------------------------------------------------------------------------------------------------------
			// Get the calendar event for the entry/start
			// ------------------------------------------------------------------------------------------------------------
			$sql  = "SELECT * ";
			$sql .= "FROM calendar_event e ";
			$sql .= "WHERE e.calendar_entry_id = " .  $p_calendar_entry_id . " ";
			$sql .= "AND e.start = " . $p_start . " ";
	
			// echo "$sql<br />";
			
			$query = $this->db->query($sql);
			if ($query->num_rows() == 1) {
				$row = $query->row();
				
				// setup the new event
				
				$event->id = (int) $row->id;
				$event->calendar_id = (int) $row->calendar_id;
				if ( $row->all_day ) {
					$event->all_day = true;
				} else {
					$event->all_day = false;
				}
				$event->duration = (int) $row->duration;
				$event->name = $row->name;
				$event->description = $row->description;
				$event->location = $row->location;
				$event->template_id = (int) $row->calendar_entry_template_id;
				$event->rsvp = (boolean) $row->rsvp;
				$event->log_participant = (boolean) $row->log_participant;
				$entry->wod = (boolean) $row->wod;
				$event->log_result = (boolean) $row->log_result;
				$event->waiver = (boolean) $row->waiver;
				$event->payment = (boolean) $row->payment;
				$event->start = (int) $row->start;
				$event->note = $row->note;
		
				// ------------------------------------------------------------------------------------------------------------
				// Get the participants for the calendar event
				// ------------------------------------------------------------------------------------------------------------
				$event->participant = array();
				$sql  = "SELECT cu.id, u.first_name, u.last_name, u.email ";
				$sql .= "FROM calendar_event_participation p, ";
				$sql .= "client_user cu, ";
				$sql .= "user u ";
				$sql .= "WHERE p.calendar_event_id = " .  $event->id . " ";
				$sql .= "AND cu.id = p.client_user_id ";
				$sql .= "AND u.id = cu.user_id ";
		
				// echo "$sql<br />";
				
				$query = $this->db->query($sql);
				if ($query->num_rows() > 0) {
					$rows = $query->result(); 
					
					foreach ( $rows as $row ) {
						$participant = new stdClass();
						$participant->id = (int) $row->id;
						$participant->first_name = $row->first_name;
						$participant->last_name = $row->last_name;
						$participant->email = $row->email;
						array_push($event->participant, $participant);
						unset($participant);
					}
				}	
				
				// ------------------------------------------------------------------------------------------------------------
				// Get the scheduled workouts for the calendar event
				// ------------------------------------------------------------------------------------------------------------
				$event->workout = array();
				$sql  = "SELECT w.id, w.name ";
				$sql .= "FROM calendar_event_library_workout xref, ";
				$sql .= "library_workout w ";
				$sql .= "WHERE xref.calendar_event_id = " .  $event->id . " ";
				$sql .= "AND w.id = xref.library_workout_id ";
		
				// echo "$sql<br />";
				
				$query = $this->db->query($sql);
				if ($query->num_rows() > 0) {
					$rows = $query->result(); 
					
					foreach ( $rows as $row ) {
						$workout = new stdClass();
						$workout->id = (int) $row->id;
						$workout->name = $row->name;
						array_push($event->workout, $workout);
						unset($workout);
					}
				}
			} else {
				$event = new stdClass();
			}
			$response = new stdClass();
			$response->entry = clone $entry;
			$response->event = clone $event;
			$response->wod = $wod;
			return $this->return_handler->results(200,"",$response);
		} else {
			return $this->return_handler->results(204,"No Entry Found",$entry);
		}
	}

	// =======================================================================================================================================
	// Get the list of events to display on the calendar for a date
	// =======================================================================================================================================
	
	public function getForCalendarStartByDay( $p_calendar_id, $p_start, $p_count_deleted = false ) {
		// echo "getForCalendarStartByDay calendar_id:$p_calendar_id start:$p_start count_deleted:$p_count_deleted<br />";
		// --------------------------------------------------------------------------------------------------------------------
		//  Get the calendar entry
		// --------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_calendar->getForId',$p_calendar_id);
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Invalid calendar",array());
		}
		$calendar = $return['response'];
		// --------------------------------------------------------------------------------------------------------------------
		//  Set the timezone to the calendar's timezone
		// --------------------------------------------------------------------------------------------------------------------
		// Set the server's default timezone
		date_default_timezone_set($calendar->timezone);
		// --------------------------------------------------------------------------------------------------------------------
		//  Get wods with workouts ( return array uses calendar_entry_template_id as its key )
		// --------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getWODHasWorkout',$calendar->client_id,$p_start);
		if ( $return['status'] >= 300 ) {
			return $this->return_handler->results(400,"Invalid calendar",array());
		}
		$wod_has_workout = $return['response'];
		// --------------------------------------------------------------------------------------------------------------------
		//  Get the entries for the calendar for the date starting
		// --------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getCalendarEntriesForCalendarStart',$p_calendar_id,$p_start);
		if ( $return['status'] >= 300 ) {
			return  $return;
		}
		$entry_list = $return['response'];
		// echo json_encode($entry_list) . '<br />\n';
		// --------------------------------------------------------------------------------------------------------------------
		//  translate the calendar entries into calendar events for the date
		// --------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getEventsForEntriesStart',$p_start,$entry_list);
		if ( $return['status'] >= 300 ) {
			return  $return;
		}
		$event_list = $return['response'];
		//
		// initialize response
		$media = array();
		$events = array();
		if ( count($event_list) > 0 ) {
			// --------------------------------------------------------------------------------------------------------------------
			// get a list of existing events and a count of their participants for that calendar and date
			//
			// this list is keyed by calendar_entry_id + "_" + start
			// --------------------------------------------------------------------------------------------------------------------
			$return = $this->perform('this->getParticipationForCalendarStartByEvent',$p_calendar_id,$p_start,$p_count_deleted);
			if ( $return['status'] >= 300 ) {
				return  $return;
			}
			$participation = $return['response'];
			// --------------------------------------------------------------------------------------------------------------------
			// get a list of existing events that have workouts assigned to them
			//
			// this list is keyed by calendar_entry_id + "_" + start
			// --------------------------------------------------------------------------------------------------------------------
			$return = $this->perform('this->getHasWorkoutForCalendarStartByEvent',$p_calendar_id,$p_start);
			if ( $return['status'] >= 300 ) {
				return  $return;
			}
			$has_workout = $return['response'];
			// --------------------------------------------------------------------------------------------------------------------
			//  Merge the event list with the entry list
			// --------------------------------------------------------------------------------------------------------------------
			$return = $this->perform('this->mergeEventsWithParticipationAndHasWorkout',$event_list,$participation,$has_workout,$wod_has_workout);
			if ( $return['status'] >= 300 ) {
				return  $return;
			}
			$events = $return['response'];
		}
		// ----------------------------------------------------------------------------------------------------------------------------------
		// Get a list of all the images for the calendar for the day
		// ----------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getCalendarMediaForCalendarStart',$p_calendar_id,$p_start);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$media = $return['response'];
		
		
		/*
		// The competition project has been put on hold.  It has not been moved to production!
		// 
		// ----------------------------------------------------------------------------------------------------------------------------------
		// Get a list of all conpetitions where $p_start is between the registration start and end
		// ----------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('action_competition->getForClientStart',$calendar->client_id,$p_start);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$competition = $return['response'];
		*/

		$response = new stdClass();
		$response->media = $media;
		$response->events = $events;
		// $response->competition = $competition;
		return $this->return_handler->results(200,"",$response);
	}

	public function getCalendarEntriesForCalendarStart( $p_calendar_id, $p_start ) {
		// store the date range we are searching between
		$between_from = mktime(0,0,0,date("m",$p_start),date("d",$p_start),date("Y",$p_start));
		$between_thru = mktime(0,0,-1,date("m",$p_start),date("d",$p_start) + 1,date("Y",$p_start));
		// echo "start:" . date('Y/m/d H:i:s',$p_start) . " from:" . date('Y/m/d H:i:s',$between_from) . " thru:" . date('Y/m/d H:i:s',$between_thru) . "<br />";
		// --------------------------------------------------------------------------------------------------------------------
		//  get the a list of the calendar entrys at the calendar and date
		// --------------------------------------------------------------------------------------------------------------------
		$sql  = "SELECT e.*, ";
		$sql .= "c.timezone, ";
		$sql .= "r.name repeat_type ";
		$sql .= "FROM calendar c, ";
		$sql .= "calendar_entry e, ";
		$sql .= "calendar_entry_repeat_type r ";
		$sql .= "WHERE c.id = " . $p_calendar_id . " ";
		$sql .= "AND e.calendar_id = c.id ";
		$sql .= "AND (e.start <= " . $between_thru . " AND (e.end IS NULL OR e.end >= " . $between_from . "))";
		$sql .= "AND r.id = e.calendar_entry_repeat_type_id ";
		$sql .= "ORDER BY e.start, e.name ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entries Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$entry = clone $row;
			$entry->removed_dates = json_decode($row->removed_dates);
			array_push($entries,clone $entry);
			unset($entry);
		}

		return $this->return_handler->results(200,"",$entries);
	}

	public function getEventsForEntriesStart( $p_start, $p_entry_list ) {
		// store the date range we are searching between
		$between_from = mktime(0,0,0,date("m",$p_start),date("d",$p_start),date("Y",$p_start));
		$between_thru = mktime(0,0,-1,date("m",$p_start),date("d",$p_start) + 1,date("Y",$p_start));
		// echo "start:" . date('Y/m/d H:i:s',$p_start) . " from:" . date('Y/m/d H:i:s',$between_from) . " thru:" . date('Y/m/d H:i:s',$between_thru) . "<br />";
		//
		// Initialize the response
		$event_list = array();
		//
		// Create the events for each entry in the list
		foreach( $p_entry_list as $entry ) {
			// print_r($entry); echo "<br />";
			if ( $entry->repeat_type == "once" ) {
				if ( $entry->start >= $between_from && $entry->start <= $between_thru ) {
					$event = clone $entry;
					// copy the id to the calendar_entry_id
					$event->calendar_entry_id = $event->id;
					// unset the entry fields not found in the event table
					unset($event->id);
					unset($event->calendar_entry_type_id);
					unset($event->calendar_entry_repeat_type_id);
					unset($event->end);
					unset($event->removed_dates);
					unset($event->repeat_type);
					// put the one time only event to the event list
					array_push($event_list,$event);
					// clear the event from memory
					unset($event);
				}
			} else {
				if ( $entry->repeat_type == "weekly" ) {
					// Set the server's default timezone
					date_default_timezone_set($entry->timezone);

					$start = $entry->start;

					while ( $start < $between_from ) {
						$temp = mktime(date("H",$start),date("i",$start),date("s",$start),date("m",$start),date("d",$start)+7,date("Y",$start));
						$start = $temp;
					}
				}
				/*
				$status = "";
				if ( $start >= $between_from && $start <= $between_thru ) {
					$status = "between";
				}
				// echo "from:$between_from thru:$between_thru start:$start entry->start:" . $entry->start . " $status<br />";
				*/
				while ( $start >= $between_from && $start <= $between_thru ) {
					if ( !in_array($start,$entry->removed_dates) ) {
						//
						// create a new entry
						$event = clone $entry;
						// reset the start date
						$event->start = $start;
						// copy the id to the calendar_entry_id
						$event->calendar_entry_id = $event->id;
						// unset the entry fields not found in the event table
						unset($event->id);
						unset($event->calendar_entry_type_id);
						unset($event->calendar_entry_repeat_type_id);
						unset($event->end);
						unset($event->removed_dates);
						unset($event->repeat_type);
						// put the one time only event to the event list
						array_push($event_list,$event);
						// clear the event from memory
						unset($event);
					}
					//
					// increment the start date
					if ( $entry->repeat_type == "weekly" ) {
						// Set the server's default timezone
						date_default_timezone_set($entry->timezone);

						$temp = mktime(date("H",$start),date("i",$start),date("s",$start),date("m",$start),date("d",$start)+7,date("Y",$start));
						$start = $temp;
					}
				}
			}
		}
		// sort the event list by start date
		$this->load->helper('compare_helper');
		usort($event_list,'calendar_event_compare');

		return $this->return_handler->results(200,"",$event_list);
	}

	public function getParticipationForCalendarStartByEvent( $p_calendar_id, $p_start, $p_count_deleted = false ) {
		// echo "getParticipationForCalendarStartByEvent count_Delete:$p_count_deleted<br >";
		// store the date range we are searching between
		$between_from = mktime(0,0,0,date("m",$p_start),date("d",$p_start),date("Y",$p_start));
		$between_thru = mktime(0,0,-1,date("m",$p_start),date("d",$p_start) + 1,date("Y",$p_start));
		// echo "start:" . date('Y/m/d H:i:s',$p_start) . " from:" . date('Y/m/d H:i:s',$between_from) . " thru:" . date('Y/m/d H:i:s',$between_thru) . "<br />";
		// --------------------------------------------------------------------------------------------------------------------------------------
		// Create a table that can be used to lookup participation for a given event key {calendar_entry_id}_{event_start}
		// --------------------------------------------------------------------------------------------------------------------------------------

		$sql  = "SELECT e.*, ";
		if ( $p_count_deleted ) {
			$sql .= "count(p.id) participant_count, ";
		} else {
			$sql .= "sum(if(m.id IS NOT NULL AND m.deleted IS NULL,1,0)) participant_count, ";
		}
		$sql .= "sum(if(m.client_user_role_id=(SELECT id FROM client_user_role_review),if(m.deleted IS NULL,1,0),0)) review_count, ";
		$sql .= "sum(if(m.client_user_role_id=(SELECT id FROM client_user_role_trial),if(m.deleted IS NULL,1,0),0)) trial_count ";
		$sql .= "FROM calendar_event e ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation p ";
		$sql .= "LEFT OUTER JOIN client_user m ";
		$sql .= "ON m.id = p.client_user_id ";
		if ( !$p_count_deleted ) {
			$sql .= "AND m.deleted IS NULL ";
		}
		$sql .= "ON p.calendar_event_id = e.id ";
		$sql .= "WHERE e.calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND e.start BETWEEN " . $between_from . " AND " . $between_thru . " ";
		$sql .= "GROUP BY e.start, e.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entries Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$event_key = $row->calendar_entry_id . "_" . $row->start;
			$entries[$event_key] = clone $row;
		}
		return $this->return_handler->results(200,"",$entries);
	}

	public function getHasWorkoutForCalendarStartByEvent( $p_calendar_id, $p_start ) {
		// store the date range we are searching between
		$between_from = mktime(0,0,0,date("m",$p_start),date("d",$p_start),date("Y",$p_start));
		$between_thru = mktime(0,0,-1,date("m",$p_start),date("d",$p_start) + 1,date("Y",$p_start));
		// echo "start:" . date('Y/m/d H:i:s',$p_start) . " from:" . date('Y/m/d H:i:s',$between_from) . " thru:" . date('Y/m/d H:i:s',$between_thru) . "<br />";
		// --------------------------------------------------------------------------------------------------------------------------------------
		// Create a table that can be used to lookup if a event key {calendar_entry_id}_{event_start} has workouts assigned
		// --------------------------------------------------------------------------------------------------------------------------------------

		$sql  = "SELECT e.*, ";
		$sql .= "count(w.id) workout_count ";
		$sql .= "FROM calendar_event e, ";
		$sql .= "calendar_event_library_workout w ";
		$sql .= "WHERE e.calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND e.start >= " . $between_from . " AND e.start <= " . $between_thru . " ";
		$sql .= "AND w.calendar_event_id = e.id ";
		$sql .= "GROUP BY e.start, e.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entries Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$event_key = $row->calendar_entry_id . "_" . $row->start;
			$entries[$event_key] = true;
		}

		return $this->return_handler->results(200,"",$entries);
	}

	public function getWODHasWorkout( $p_client_id, $p_start ) {
		$yyyymmdd = date("Ymd",$p_start);
		$sql  = "SELECT wod.calendar_entry_template_id template_id, ";
		$sql .= "count(xref.library_workout_id) workout_count ";
		$sql .= "FROM calendar_entry_template_wod wod, ";
		$sql .= "calendar_entry_template_wod_library_workout xref ";
		$sql .= "WHERE wod.client_id = " . $p_client_id . " ";
		$sql .= "AND wod.yyyymmdd = " . $yyyymmdd . " ";
		$sql .= "AND xref.calendar_entry_template_wod_id = wod.id ";
		$sql .= "GROUP BY wod.calendar_entry_template_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entries Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			// echo "row:"; print_r($row); echo "<br /><br />";
			$entries[$row->template_id] = true;
		}

		return $this->return_handler->results(200,"",$entries);
	}

	public function mergeEventsWithParticipationAndHasWorkout( $event_list, $participation = array(), $has_workout = array(), $wod_has_workout = array() ) {
		// --------------------------------------------------------------------------------------------------------------------
		//  Merge the event list with the entry list
		// --------------------------------------------------------------------------------------------------------------------
		$events = array();
		foreach( $event_list as $event ) {
			// Create the event key
			$event_key = $event->calendar_entry_id . "_" . $event->start;
			// initialize the new entry
			$new_entry = new stdClass();
			$new_entry->key = $event_key;
			// Does the event exist for the entry?
			if ( array_key_exists($event_key, $participation) ) {
				// create the new entry using the event
				$new_entry->start = $participation[$event_key]->start;
				$new_entry->duration = $participation[$event_key]->duration;
				$new_entry->name = $participation[$event_key]->name;
				$new_entry->participant_count = $participation[$event_key]->participant_count;
				$new_entry->alert_count = $participation[$event_key]->review_count + $participation[$event_key]->trial_count;
			} else {
				// create the new entry using the old entry
				$new_entry->start = $event->start;
				$new_entry->duration = $event->duration;
				$new_entry->name = $event->name;
				$new_entry->participant_count = 0;
				$new_entry->alert_count = 0;
			}
			if ( array_key_exists($event->calendar_entry_template_id, $wod_has_workout) ) {
				$new_entry->has_workout = true;
			} else if ( array_key_exists($event_key, $has_workout) ) {
				$new_entry->has_workout = true;
			} else {
				$new_entry->has_workout = false;
			}
			// put the new entry to the new entry array
			array_push($events,$new_entry);
			// clear the new entry from memory
			unset($new_entry);
		}

		return $this->return_handler->results(200,"",$events);
	}

	public function getCalendarMediaForCalendarStart( $p_calendar_id, $p_start ) {
		// store the date range we are searching between
		$between_from = mktime(0,0,0,date("m",$p_start),date("d",$p_start),date("Y",$p_start));
		$between_thru = mktime(0,0,-1,date("m",$p_start),date("d",$p_start) + 1,date("Y",$p_start));
		// echo "start:" . date('Y/m/d H:i:s',$p_start) . " from:" . date('Y/m/d H:i:s',$between_from) . " thru:" . date('Y/m/d H:i:s',$between_thru) . "<br />";
		// --------------------------------------------------------------------------------------------------------------------------------------
		// Get the Calendar Media for a Calandar for a Date starting
		// --------------------------------------------------------------------------------------------------------------------------------------
		$sql  = "SELECT id, media_url ";
		$sql .= "FROM calendar_media ";
		$sql .= "WHERE calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND date BETWEEN " . $between_from . " AND " . $between_thru . " ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entries Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$entry = new stdClass();
			$entry->id = $row->id;
			$entry->url = $row->media_url;
			array_push($entries,$entry);
			unset($entry);
		}

		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// Get the calendar_entry and calendar_event for a calendar_entry id and calendar_event UTC start date/time
	// ==================================================================================================================

	public function getEntryEventForEntryStart( $p_calendar_entry_id, $p_start ) {
		//
		// initialize the response data
		$entry = new stdClass();
		// ------------------------------------------------------------------------------------------------------------
		// Get the calendar entry
		// ------------------------------------------------------------------------------------------------------------
		$sql  = "SELECT en.id, en.calendar_id, en.all_day, en.duration, en.name, en.description, en.location, ";
		$sql .= "en.calendar_entry_template_id, en.calendar_entry_type_id, en.rsvp, en.log_participant, wod, en.log_result, en.payment, en.waiver, ";
		$sql .= "en.calendar_entry_repeat_type_id, en.start, en.end, en.removed_dates, ";
		$sql .= "cal.timezone ";
		$sql .= "FROM calendar_entry en, ";
		$sql .= "calendar cal ";
		$sql .= "WHERE en.id = " . $p_calendar_entry_id . " ";
		$sql .= "AND cal.id = en.calendar_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 1) {
			$row = $query->row();

			// setup the new entry
			$entry = new stdClass();
			$entry->id = (int) $row->id;
			$entry->calendar_id = (int) $row->calendar_id;
			$entry->calendar_timezone = $row->timezone;
			if ( $row->all_day ) {
				$entry->all_day = true;
			} else {
				$entry->all_day = false;
			}
			$entry->duration = (int) $row->duration;
			$entry->name = $row->name;
			$entry->description = $row->description;
			$entry->location = $row->location;
			$entry->template_id = (int) $row->calendar_entry_template_id;
			$entry->entry_type_id = (int) $row->calendar_entry_type_id;
			$entry->rsvp = (boolean) $row->rsvp;
			$entry->log_participant = (boolean) $row->log_participant;
			$entry->wod = (boolean) $row->wod;
			$entry->log_result = (boolean) $row->log_result;
			$entry->waiver = (boolean) $row->waiver;
			$entry->payment = (boolean) $row->payment;
			$entry->repeat_type_id = (int) $row->calendar_entry_repeat_type_id;
			$entry->start = (int) $row->start;
			if ( is_null($row->end) ) {
				$entry->end = null;
			} else {
				$entry->end = (int) $row->end;
			}
			$entry->removed = json_decode($row->removed_dates);

			// print_r($entry);

			// get the blocked dates
			$return = $this->perform('action_calendar_entry->getBlockedDates',$p_calendar_entry_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			
			// print_r($return);
			$entry->blocked = clone $return['response'];

			// can the start and repeat type be changed?
			$return = $this->perform('action_calendar_entry->getHasEvent',$p_calendar_entry_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $return['response']->hasEvent ) {
				$entry->blocked->start = true;
				$entry->blocked->repeat_type = true;
				$entry->blocked->template = true;
				$entry->blocked->calendar = true;
			} else {
				$entry->blocked->start = false;
				$entry->blocked->repeat_type = false;
				$entry->blocked->template = false;
				$entry->blocked->calendar = false;
			}

			// ------------------------------------------------------------------------------------------------------------
			// if the entry is a WOD, get the scheduled workouts for the calendar entry/date
			// ------------------------------------------------------------------------------------------------------------
			$wod = array();

			if ( $entry->wod && !is_null($entry->template_id) ) {
				// set the date for the entry
				date_default_timezone_set($entry->calendar_timezone);
				$date = date("Ymd",$p_start);
				// echo "date:$date<br />";

				$sql  = "SELECT wk.id, wk.name ";
				$sql .= "FROM calendar_entry_template_wod wod, ";
				$sql .= "calendar_entry_template_wod_library_workout x, ";
				$sql .= "library_workout wk ";
				$sql .= "WHERE wod.calendar_entry_template_id = " . $entry->template_id . " ";
				$sql .= "AND wod.yyyymmdd = '" . $date . "' ";
				$sql .= "AND x.calendar_entry_template_wod_id = wod.id ";
				$sql .= "AND wk.id = x.library_workout_id ";

				// echo "$sql<br />";

				$query = $this->db->query($sql);
				if ($query->num_rows() > 0) {
					$rows = $query->result();

					foreach ( $rows as $row ) {
						$workout = new stdClass();
						$workout->id = (int) $row->id;
						$workout->name = $row->name;
						array_push($wod, $workout);
						unset($workout);
					}
				}
			}

			// ------------------------------------------------------------------------------------------------------------
			// Get the calendar event for the entry/start
			// ------------------------------------------------------------------------------------------------------------
			$sql  = "SELECT * ";
			$sql .= "FROM calendar_event e ";
			$sql .= "WHERE e.calendar_entry_id = " .  $p_calendar_entry_id . " ";
			$sql .= "AND e.start = " . $p_start . " ";

			// echo "$sql<br />";

			$query = $this->db->query($sql);
			if ($query->num_rows() == 1) {
				$row = $query->row();

				// setup the new event

				$event->id = (int) $row->id;
				$event->calendar_id = (int) $row->calendar_id;
				if ( $row->all_day ) {
					$event->all_day = true;
				} else {
					$event->all_day = false;
				}
				$event->duration = (int) $row->duration;
				$event->name = $row->name;
				$event->description = $row->description;
				$event->location = $row->location;
				$event->template_id = (int) $row->calendar_entry_template_id;
				$event->rsvp = (boolean) $row->rsvp;
				$event->log_participant = (boolean) $row->log_participant;
				$entry->wod = (boolean) $row->wod;
				$event->log_result = (boolean) $row->log_result;
				$event->waiver = (boolean) $row->waiver;
				$event->payment = (boolean) $row->payment;
				$event->start = (int) $row->start;
				$event->note = $row->note;

				// ------------------------------------------------------------------------------------------------------------
				// Get the participants for the calendar event
				// ------------------------------------------------------------------------------------------------------------
				$event->participant = array();
				$sql  = "SELECT cu.id, u.first_name, u.last_name, u.email ";
				$sql .= "FROM calendar_event_participation p, ";
				$sql .= "client_user cu, ";
				$sql .= "user u ";
				$sql .= "WHERE p.calendar_event_id = " .  $event->id . " ";
				$sql .= "AND cu.id = p.client_user_id ";
				$sql .= "AND u.id = cu.user_id ";

				// echo "$sql<br />";

				$query = $this->db->query($sql);
				if ($query->num_rows() > 0) {
					$rows = $query->result();

					foreach ( $rows as $row ) {
						$participant = new stdClass();
						$participant->id = (int) $row->id;
						$participant->first_name = $row->first_name;
						$participant->last_name = $row->last_name;
						$participant->email = $row->email;
						array_push($event->participant, $participant);
						unset($participant);
					}
				}

				// ------------------------------------------------------------------------------------------------------------
				// Get the scheduled workouts for the calendar event
				// ------------------------------------------------------------------------------------------------------------
				$event->workout = array();
				$sql  = "SELECT w.id, w.name ";
				$sql .= "FROM calendar_event_library_workout xref, ";
				$sql .= "library_workout w ";
				$sql .= "WHERE xref.calendar_event_id = " .  $event->id . " ";
				$sql .= "AND w.id = xref.library_workout_id ";

				// echo "$sql<br />";

				$query = $this->db->query($sql);
				if ($query->num_rows() > 0) {
					$rows = $query->result();

					foreach ( $rows as $row ) {
						$workout = new stdClass();
						$workout->id = (int) $row->id;
						$workout->name = $row->name;
						array_push($event->workout, $workout);
						unset($workout);
					}
				}
			} else {
				$event = new stdClass();
			}
			$response = new stdClass();
			$response->entry = clone $entry;
			$response->event = clone $event;
			$response->wod = $wod;
			return $this->return_handler->results(200,"",$response);
		} else {
			return $this->return_handler->results(204,"No Entry Found",$entry);
		}
	}

	// ==================================================================================================================
	// get the event for a participation_id
	// ==================================================================================================================
	
	public function getForParticipation( $p_calendar_event_participation_id ) {
		$sql  = "";
		$sql .= "SELECT ev.* ";
		$sql .= "FROM calendar_event_participation p, ";
		$sql .= "calendar_event ev ";
		$sql .= "WHERE p.id = " . $p_calendar_event_participation_id . " ";
		$sql .= "AND ev.id = p.calendar_event_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		$row = $query->row();;
		
		return $this->return_handler->results(200,"",$row);
	}

	// ==================================================================================================================
	// Create Calendar event
	// ==================================================================================================================

	public function create( $data ) {
		// print_r($data); echo "<br />";
		$key = explode('_',$data->key);
		$calendar_entry_id = $key[0];
		$start = $key[1];
		//
		// make sure the entry_id and start in the key and in the data match.
		if ( isset($data->start) && $data->start != $start ) {
			return $this->return_handler->results(400,"Key and start do not match",new stdClass);
		}
		if ( isset($data->entry_id) && $data->entry_id != $calendar_entry_id ) {
			return $this->return_handler->results(400,"Key and Entry Id do not match",new stdClass);
		}
		// does the event already exist
		$key = array();
		$key['calendar_entry_id'] = $calendar_entry_id;
		$key['start'] = $start;
		$return = $this->perform('table_workoutdb_calendar_event->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			// update the event
			$event = clone $return['response'][0];
			$return = $this->perform('this->updateCalendarEvent',$event,$data);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			$response = new stdClass();
			$response->id = $event->id;
			return $this->return_handler->results(201,"Event Created",$response);
		} else {
			// create the event
			return $this->perform('this->createCalendarEvent',$data);
		}
	}
	
	public function updateCalendarEvent( $p_event, $data ) {
		// do not allow the start date/time to change
		if ( property_exists($data,'start') && $p_event->start != $data->start ) {
			return $this->return_handler->results(400,"start can not be changed",new stdClass);
		}
		$key = explode('_',$data->key);
		$data->calendar_entry_id = $key[0];
		$data->start = $key[1];
		$data->id = $p_event->id;
		
		if ( isset($data->template_id) && !is_null($data->template_id) && !empty($data->template_id) && is_numeric($data->template_id) ) {
			// if the template is not changing
			$temp = (array) $data;
			if ( array_key_exists('log_participant', $temp) ) {
				unset($data->log_participant);
			}
			if ( array_key_exists('wod', $temp) ) {
				unset($data->wod);
			}
			if ( array_key_exists('log_result', $temp) ) {
				unset($data->log_result);
			}
			if ( array_key_exists('rsvp', $temp) ) {
				unset($data->rsvp);
			}
			if ( array_key_exists('waiver', $temp) ) {
				unset($data->waiver);
			}
			if ( array_key_exists('paymnent', $temp) ) {
				unset($data->paymnent);
			}
			if ( array_key_exists('all_day', $temp) ) {
				unset($data->all_day);
			}
			if ( array_key_exists('duration', $temp) ) {
				unset($data->duration);
			}
			if ( array_key_exists('name', $temp) ) {
				unset($data->name);
			}
		}
		// update the calendar event.
		return $this->perform('table_workoutdb_calendar_event->update',$data);
	}
	
	public function createCalendarEvent( $p_data ) {
		// base the calendar_event on the calendar_entry
		$key = explode('_',$p_data->key);
		$calendar_entry_id = $key[0];
		$start = $key[1];
		//
		// initialize the response
		$response = new stdClass();
		$response->id = null;
		//
		// get the calendar_entry
		$return = $this->perform('table_workoutdb_calendar_entry->getForId',$calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"Invalid Entry Id",$response);
		}
		$entry = $return['response'];
		
		// create the default event using the entry
		$event = clone $entry;
		$event->calendar_entry_id = $entry->id;
		$event->start = $start;
		unset($event->id);
		unset($event->calendar_entry_repeat_type_id);
		unset($event->removed_dates);
		// overwrite the template with data for fields other than 
		if ( property_exists($p_data,'location') ) {
			$event->location = $p_data->location;
		}
		if ( property_exists($p_data,'description') ) {
			$event->description = $p_data->description;
		}
		//
		// create the calendar event.
		$return = $this->perform('table_workoutdb_calendar_event->insert',$event);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$event_return = $return;
		$event->id = $return['response']->id;
		//
		// if a WOD event, link the WOD workouts to the new event
		// echo "Event->wod:" . $event->wod . " event->template:" . $event->calendar_entry_template_id . '<br />';
		if ( $event->wod && !is_null($event->calendar_entry_template_id) ) {
			// get the calendar
			$return = $this->perform('table_workoutdb_calendar->getForId',$event->calendar_id);
			// echo "calendar return:"; print_r($return); echo "<br />";
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( $return['status'] == 200 ) {
				$calendar = $return['response'];
				// Set the server's default timezone
				date_default_timezone_set($calendar->timezone);
				// get the date range based on the calendar
				$date = date('Ymd',$start);
				// get a list of the workout of the day workouts for the calendar template for a date
				$return = $this->perform('this->getWODWorkoutIdsByTemplateDate',$event->calendar_entry_template_id, $date);
				// echo "wod return:"; print_r($return); echo "<br />";
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				if ( $return['status'] == 200 ) {
					// ------------------------------------------------------------------
					// Add the list of Workouts to the new Event
					// ------------------------------------------------------------------
					foreach ( $return['response'] as $library_workout_id ) {
						$fields = new stdClass();
						$fields->calendar_event_id = $event->id;
						$fields->library_workout_id = $library_workout_id;
						$return = $this->perform('table_workoutdb_calendar_event_library_workout->insert',$fields);
						unset($fields);
						if ( $return['status'] >= 300 ) {
							return $return;
						}
					}
				}
			}
		}

		return $event_return;
	}

	public function getWODWorkoutIdsByTemplateDate( $p_calendar_entry_template_id,$p_date ) {
		$entries = array();
		
		$sql  = "SELECT w.library_workout_id id ";
		$sql .= "FROM calendar_entry_template_wod wod, ";
		$sql .= "calendar_entry_template_wod_library_workout w ";
		$sql .= "WHERE wod.calendar_entry_template_id = " . $p_calendar_entry_template_id . " ";
		$sql .= "AND wod.yyyymmdd = " . $p_date . " ";
		$sql .= "AND w.calendar_entry_template_wod_id = wod.id ";

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

	// ==================================================================================================================
	// Schedule a list of workouts to a calendar_event
	// ==================================================================================================================
	
	public function scheduleEventWorkouts($p_data) {
		// parse the key in $data
		$key = explode('_',$p_data->key);
		$calendar_entry_id = $key[0];
		$start = $key[1];
		// does the event already exist
		$key = array();
		$key['calendar_entry_id'] = $calendar_entry_id;
		$key['start'] = $start;
		$return = $this->perform('table_workoutdb_calendar_event->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			$event = $return['response'][0];
			// link the list of workout ids to the existing event
			$this->id = $event->id;
			$this->table_name = "calendar_event";
			$this->xref_table_name = "calendar_event_library_workout";
			$this->xrefed_table_name = "library_workout";
			$return = $this->perform('this->put_xref_list',$p_data->workout);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		} else {
			// create the event using the calendar_entry entry
			$return = $this->perform('this->createCalendarEvent',$p_data);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			// link the list of workout ids to the new event
			$this->id = $return['response']->id;
			$this->table_name = "calendar_event";
			$this->xref_table_name = "calendar_event_library_workout";
			$this->xrefed_table_name = "library_workout";
			$return = $this->perform('this->post_xref_list',$data->workout);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}

		return $return;
	}

	// =========================================================================================================================================
	// Schedule a singe Workout to a non WOD Event
	// =========================================================================================================================================
	
	public function scheduleEventWorkout($data) {
		// parse the key in $data
		$key = explode('_',$data->key);
		$calendar_entry_id = $key[0];
		$start = $key[1];
		// does the event already exist
		$key = array();
		$key['calendar_entry_id'] = $calendar_entry_id;
		$key['start'] = $start;
		$return = $this->perform('table_workoutdb_calendar_event->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			// create the event using the calendar_entry entry
			$return = $this->perform('this->createCalendarEvent',$data);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// save the Event ID
		$calendar_event_id = $return['response']->id;
		// does the link to the workout already exist for the event
		$key = array();
		$key['calendar_event_id'] = $calendar_event_id;
		$key['library_workout_id'] = $data->workout;
		$return = $this->perform('table_workoutdb_calendar_event_library_workout->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			// link the workout to the event
			$xref = new stdClass();
			$xref->calendar_event_id = $calendar_event_id;
			$xref->library_workout_id = $data->workout;
			//
			$return = $this->perform('table_workoutdb_calendar_event_library_workout->insert',$xref);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			//
			unset($xref);
		}

		return $return;
	}

	// ---------------------------------------------------------------------------------------------------------------------------------------
	// Unschedule a single workout to a calendar_event
	// ---------------------------------------------------------------------------------------------------------------------------------------
	
	public function removeEventWorkout($data) {
		// parse the key in $data
		$key = explode('_',$data->key);
		$calendar_entry_id = $key[0];
		$start = $key[1];
		// does the event already exist
		$key = array();
		$key['calendar_entry_id'] = $calendar_entry_id;
		$key['start'] = $start;
		$return = $this->perform('table_workoutdb_calendar_event->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] == 200 ) {
			// Save the Event ID
			$calendar_event_id = $return['response'][0]->id;
			// does the event have participation?
			$key = array();
			$key['calendar_event_id'] = $calendar_event_id;
			$return = $this->perform('table_workoutdb_calendar_event_participation->getForAndKeys');
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			if ( count($return['response'] == 0) ) {
				// does the link to the workout already exist for the event
				$key = array();
				$key['calendar_event_id'] = $calendar_event_id;
				$key['library_workout_id'] = $data->workout;
				$return = $this->perform('table_workoutdb_calendar_event_library_workout->getForAndKeys',$key);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				if ( $return['status'] == 200 ) {
					// Delete the link to the Workout
					$return = $this->perform('table_workoutdb_calendar_event_library_workout->delete',$return['response'][0]->id);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
				}
			} else {
				$return = $this->return_handler->results(400,"Has participation",new stdClass());
			}
		}

		return $return;
	}

}