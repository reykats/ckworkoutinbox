<?php

class action_calendar extends action_generic {

	public function __construct() {
		parent::__construct();
	}
	
	// ==================================================================================================================
	// Get the calendar for a calendar_entry
	// ==================================================================================================================

	public function getForCalendarEntry($p_calendar_entry_id,$p_use_alias=true) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "calendar.id 'calendar.id', calendar.name 'calendar.name', calendar.timezone 'calendar.timezone', ";
		$sql .= "calendar.user_id 'calendar.user_id', calendar.client_id 'calendar.client_id', calendar.location_id 'calendar.location_id', calendar.classroom_id 'calendar.classroom_id' ";
		$sql .= "FROM ";
		$sql .= "calendar_entry ";
		$sql .= "LEFT OUTER JOIN calendar ";
		$sql .= "ON calendar.id = calendar_entry.calendar_id ";
		$sql .= "WHERE calendar_entry.id = " . $p_calendar_entry_id . " ";
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass);
		}
		
		$row = $query->row();
		
		// cast the column values in the row to their column type
		mysql_schema::cast_row('workoutdb',$row);
        // objectify the row by table and use column aliases if needed
		$row = mysql_schema::objectify_row($row,$p_use_alias);
     	// echo json_encode($row) . "<br /><br />\n\n";

		return $this->return_handler->results(200,"",$row->calendar);
	}
	
	// ==================================================================================================================
	// Get the calendars for a client, location, or classroom
	// ==================================================================================================================

	public function getForClientLocationOrClassroom($p_client_id,$p_location_id=null,$p_classroom_id=null,$p_use_alias=true) {
		// echo "action_calendar->getForClientLocationOrClassroom client:$p_client_id location:$p_location_id classroom:$p_classroom_id<br />\n"
		//
		// initialize the response data
		$count = 0;
		$entries = array();
		$response->count = $count;
		$response->results = $entries;
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair the main WHERE statement
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$where = "";
		if ( !is_null($p_classroom_id) && !empty($p_classroom_id) ) {
			$where  = "WHERE calendar.classroom_id = " . $p_classroom_id . " ";
		} else if ( !is_null($p_location_id) && !empty($p_location_id) ) {
			$where  = "WHERE calendar.location_id = " . $p_location_id . " ";
		} else if ( !is_null($p_client_id) && !empty($p_client_id) ) {
			$where  = "WHERE calendar.client_id = " . $p_client_id . " ";
		}
		// echo "where:$where<br />";
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$search_check = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$search_check  = "AND ";
			$search_check .= "concat(";
			$search_check .= "if(isnull(calendar.name),'',concat(' ',calendar.name))";
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
		//
		// Get the total record count without paging limits
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT count(calendar.id) cnt ";
		$sql .= "FROM calendar ";
		$sql .= $where;
		$sql .= $search_check;

		// echo "$sql<br />";

		$row = $this->db->query($sql)->row();
		if ( !empty($row) ) {
			$count = $row->cnt;
		}
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Get a list of all Calendars for a Client
		//
	    // ---------------------------------------------------------------------------------------------------------
		//
		// initialize the response data
		$entries = array();
		//
		// select
		$sql  = "SELECT calendar.id 'calendar.id', calendar.name 'calendar.name', calendar.timezone 'calendar.timezone', ";
		$sql .= "client.id 'client.id', client.name 'client.name', ";
		$sql .= "location.id 'location.id', location.name 'location.name', location.address 'location.address', ";
		$sql .= "classroom.id 'classroom.id', classroom.name 'classroom.name' ";
		$sql .= "FROM calendar ";
		$sql .= "LEFT OUTER JOIN client ";
		$sql .= "ON client.id = calendar.client_id ";
		$sql .= "LEFT OUTER JOIN location ";
		$sql .= "ON location.id = calendar.location_id ";
		$sql .= "LEFT OUTER JOIN classroom ";
		$sql .= "ON classroom.id = calendar.classroom_id ";
		$sql .= $where;
		$sql .= $search_check;
		$sql .= "ORDER BY client.name, location.name, classroom.name ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->client = mysql_schema::getTableAlias('workoutdb','client',$p_use_alias);
		$table->location = mysql_schema::getTableAlias('workoutdb','location',$p_use_alias);
		$table->classroom = mysql_schema::getTableAlias('workoutdb','classroom',$p_use_alias);
		
        $calendar = array();
        $c = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			$c++;
			$calendar[$c] = clone $row->calendar;
			$calendar[$c]->{$table->client} = format_object_with_id($row->client);
			$calendar[$c]->{$table->location} = format_object_with_id($row->location);
			$calendar[$c]->{$table->classroom} = format_object_with_id($row->classroom);
			$calendar[$c]->owner = new stdClass();
		}

		$response->count = $count;
		$response->results = $calendar;
		return $this->return_handler->results(200,"",$response);
	}
	
	// =======================================================================================================================================
	// Get the calendars for a user
	// =======================================================================================================================================
	
	public function getForUser($p_user_id,$p_use_alias=true) {
		$sql  = "";
		$sql .= "SELECT calendar.id 'calendar.id', calendar.name 'calendar.name', calendar.timezone 'calendar.timezone', ";
		$sql .= "calendar.client_id 'calendar.client_id', calendar.location_id 'calendar.location_id', calendar.classroom_id 'calendar.classroom_id', ";
		$sql .= "client_user.id 'calendar.client_user_id.int' ";
		$sql .= "FROM client_user, ";
		$sql .= "calendar, ";
		$sql .= "client ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " ";
		$sql .= "AND client_user.deleted IS NULL ";
		$sql .= "AND calendar.client_id = client_user.client_id ";
		$sql .= "AND client.id = client_user.client_id ";
		$sql .= "ORDER BY client.name, client.id, calendar.name, calendar.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
        $calendar = array();
        $c = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
			
			$c++;
			$calendar[$c] = clone $row->calendar;
		}
		
		return $this->return_handler->results(200,"",$calendar);
	}
	
	// =======================================================================================================================================
	// Get the list of WOD or logged result classes and thier scheduled times for a client on a given date (p_date is UTC date/time)
	// =======================================================================================================================================
	
	public function getClassesForClientDate($p_client_id,$p_date) {
		// get a list of events for a client between two dates
		$return = $this->perform('this->getEventsByClientDate',$p_client_id,$p_date);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		
		// Sort the resultset into template/location/date/time order
		$return = $this->perform('this->sortByTemplateLocationDateTime',$return['response']);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		
		$event_list = $return['response'];
		// echo "---- Event List ----<br />";
		// echo json_encode($event_list);
		
		// get a list of the workouts for wod events
		$return = $this->perform('this->getWODWorkoutsByClientDate',$p_client_id,$p_date);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$wod_list = $return['response'];
		// echo "---- WOD List ----<br />";
		// print_r($wod_list);
		
		// get a list of the workouts for non-wod events
		$return = $this->perform('this->getNonWODWorkoutsByClientDate',$p_client_id,$p_date);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$non_wod_list = $return['response'];
		// echo "---- Non-WOD List ----<br />";
		// print_r($non_wod_list);
		
		// get the repeat types by entry
		$return = $this->perform('this->getEntryRepeatTypeByClientDate',$p_client_id,$p_date);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$repeat_type_list = $return['response'];
		// echo "---- Repeat Type List ----<br />";
		// print_r($repeat_type_list);
		 
		// format the list of events
		return $this->perform('this->formatClassesByClientDay',$event_list,$wod_list,$non_wod_list,$repeat_type_list,$p_date);
	}
	
	public function getEventsByClientDate($p_client_id,$p_date) {
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Get the events ( both projected from entries and actual events ) For a client On a given day
		//
	    // ---------------------------------------------------------------------------------------------------------
	    // Get a list of locations and thier calendars for a client
		$return = $this->perform('this->getLocationCalendarDateRangeForClientDate',$p_client_id,$p_date);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		$locations = $return['response'];
		// echo "---- Locatoins List ----<br />";
		// print_r($locations);

		$entries = array();
		foreach( $locations as $loc ) {
			$location = new stdClass();
			$location->id = $loc->id;
			$location->name = $loc->name;
			foreach ( $loc->calendar as $calendar ) {
				
				// echo "calendar:"; print_r($calendar); echo "<br />";
		
				// Get a list of all entries for the calendar on the date
				$return = $this->perform('this->getEntriesForLocationCalendar',$location,$calendar);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				if ( $return['status'] > 200 ) {
					continue;
				}
				// echo "---- Entries:"; print_r($return['response']); echo "<br /><br /><br />";
				
				// Convert the list of entries into a list of events
				$return = $this->perform('this->translateEntriesToEventsForDateRange',$return['response'],$calendar->start, $calendar->end);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				if ( $return['status'] > 200 ) {
					continue;
				}
				// store the translated event list
				$trans_event_list = $return['response'];
				// echo "---- Tranlated Events:"; print_r($trans_event_list); echo "<br /><br /><br />";
				
				// Get a list of all events that the client has during the time period with class and location
				$return = $this->perform('this->getEventsForCalendarDateRange',$calendar);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				
				// Merge the event list with the tranlated event list
				$return = $this->perform('this->mergeEvents',$trans_event_list, $return['response']);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				//echo "Meged Events:"; print_r($return['response']); echo "<br /><br /><br />";
				
				// Merge the Merged Events to the Entries array
				$entries = array_merge($entries,$return['response']);
			}
			unset($location);
		}

		// echo "---- Entries List ---- ";
		// echo json_encode($entries);
		
		if ( count($entries) > 0 ) {
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(202,"No Entries found.",array());
		}
	}

	public function sortByTemplateLocationDateTime($entries) {
		// sort the event list by template/location/date/time
		$this->load->helper('compare_helper');
		
		usort($entries,'client_event_compare');
		
		return $this->return_handler->results(200,"",$entries);
	}

	public function sortByLocationTemplateDateTime($entries) {
		// sort the event list by location/template/date/time
		$this->load->helper('compare_helper');
		
		usort($entries,'client_event_compare2');
		
		return $this->return_handler->results(200,"",$entries);
	}
	
	public function getLocationCalendarDateRangeForClientDate( $p_client_id, $p_date ) {
		// Format the start and end date/time
		$start = substr($p_date,0,4) . "-" . substr($p_date,4,2) . "-" . substr($p_date,6,2) . " 00:00:00";
		$end = substr($start,0,10) . " 23:59:59";
					
		// get the location calendars for a client
		$sql  = "SELECT ";
		$sql .= "location.id location_id, location.name location_name, ";
		$sql .= "calendar.id calendar_id, calendar.name calendar_name, calendar.timezone calendar_timezone ";
		$sql .= "FROM client, ";
		$sql .= "location, ";
		$sql .= "calendar ";
		$sql .= "WHERE client.id = " . $p_client_id . " ";
		$sql .= "AND location.client_id = client.id ";
		$sql .= "AND calendar.location_id = location.id AND calendar.classroom_id IS NULL ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$location = array();
		$l = -1;
		foreach( $rows as $row ) {
			if ( $l < 0 || $location[$l]->id != $row->location_id ) {
				++$l;
				$location[$l]->id = $row->location_id;
				$location[$l]->name = $row->location_name;
				// initialize the calendar array
				$location[$l]->calendar = array();
				$calendar = &$location[$l]->calendar;
				$cal = -1;
			}
			if ( $cal < 0 || $calendar[$cal]->id != $row->calendar_id ) {
				++$cal;
				$calendar[$cal]->id = $row->calendar_id;
				$calendar[$cal]->name = $row->calendar_name;
				$calendar[$cal]->timezone = $row->calendar_timezone;
				// get the timezone_offset for the calendar's timezone
				$calendar[$cal]->timezone_offset = format_timezone_offset($row->calendar_timezone);
				// Set the server's default timezone
				date_default_timezone_set($row->calendar_timezone);
				// Set the UTC start and end date/time of the date for the calendar
				$calendar[$cal]->start = strtotime($start);
				$calendar[$cal]->end = strtotime($end);
			}
		}
			
		return $this->return_handler->results(200,"",$location);
	}

	public function getEntriesForLocationCalendar( $p_location, $p_calendar ) {
		// --------------------------------------------------------------------------------------------------------------------
		//  get the a list of the calendar entrys at the calendar and date
		// --------------------------------------------------------------------------------------------------------------------
		$event_list = array();
		
		$sql  = "SELECT calendar_entry.*, ";
		$sql .= "calendar_entry_repeat_type.name calendar_entry_repeat_type, ";
		$sql .= "calendar_entry_template.name template_name, calendar_entry_template.id template_id ";
		$sql .= "FROM calendar_entry ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template ";
		$sql .= "ON calendar_entry_template.id = calendar_entry.calendar_entry_template_id, ";
		$sql .= "calendar_entry_repeat_type ";
		$sql .= "WHERE calendar_entry.calendar_id = " . $p_calendar->id . " ";
		$sql .= "AND (calendar_entry.start <= " . $p_calendar->end . " AND (calendar_entry.end IS NULL OR calendar_entry.end >= " . $p_calendar->start . ")) ";
		$sql .= "AND calendar_entry_repeat_type.id = calendar_entry.calendar_entry_repeat_type_id ";
		$sql .= "ORDER BY calendar_entry_template.name, calendar_entry_template.id, calendar_entry.name, calendar_entry.id, calendar_entry.start ";
		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			$entries = array();
			foreach ( $rows as $row ) {
				// print_r($row); echo "<br />";
				
				$entry = new stdClass();
				// store the class info in a class object
				$entry->wod = new stdClass();
				if ( is_null($row->template_id) ) {
					$entry->template->name = null;
					$entry->template->id = null;
				} else {
					$entry->template->name = $row->template_name;
					$entry->template->id = (int) $row->template_id;
				}
				// store the location info in a location object
				$entry->location = clone $p_location;
				// store the entry info
				$entry->entry->id = cast_int($row->id);
				$entry->entry->calendar_id = cast_int($row->calendar_id);
				$entry->entry->timezone = $p_calendar->timezone;
				$entry->entry->calendar_entry_type_id = cast_int($row->calendar_entry_type_id);
				$entry->entry->calendar_entry_repeat_type_id = cast_int($row->calendar_entry_repeat_type_id);
				$entry->entry->calendar_entry_repeat_type = $row->calendar_entry_repeat_type;
				$entry->entry->calendar_entry_template_id = cast_int($row->calendar_entry_template_id);
				$entry->entry->log_participant = cast_boolean($row->log_participant);
				$entry->entry->wod = cast_boolean($row->wod);
				$entry->entry->log_result = cast_boolean($row->log_result);
				$entry->entry->rsvp = cast_boolean($row->rsvp);
				$entry->entry->waiver = cast_boolean($row->waiver);
				$entry->entry->payment = cast_boolean($row->payment);
				$entry->entry->all_day = cast_boolean($row->all_day);
				$entry->entry->duration = cast_int($row->duration);
				$entry->entry->all_day = cast_boolean($row->all_day);
				$entry->entry->start = cast_int($row->start);
				$entry->entry->end = cast_int($row->end);
				$entry->entry->name = $row->name;
				$entry->entry->description = $row->description;
				$entry->entry->location = $row->location;
				$entry->entry->removed_dates = json_decode($row->removed_dates);
				// put the entry into the array of entries
				array_push($entries,clone $entry);
				// clear the entry from memory
				unset($entry);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
	}

	public function translateEntriesToEventsForDateRange( $p_entry_list, $p_from, $p_thru ) {
		//
		// Initialize the response
		$event_list = array();
		//
		// Create the events for each entry in the list
		foreach( $p_entry_list as $entry ) {
			// print_r($entry); echo "<br />";
			// echo "repeat type: " . $entry->calendar_entry_repeat_type . " start: " . $entry->start . " from: " . $p_from . " thru: " . $p_thru . "<br />";
			if ( $entry->entry->calendar_entry_repeat_type == "once" ) {
				if ( $entry->entry->start >= $p_from && $entry->entry->start <= $p_thru) {
					// clone entry to create the event
					$event = clone $entry;
					// rename the event's entry to event
					unset($event->entry);
					$event->event = clone $entry->entry;
					// copy the id to the calendar_entry_id
					$event->event->calendar_entry_id = $event->event->id;
					// create a note field
					$event->event->note = null;
					// unset the entry fields not found in the event table
					unset($event->event->id);
					unset($event->event->calendar_entry_repeat_type_id);
					unset($event->event->calendar_entry_repeat_type);
					unset($event->event->end);
					unset($event->event->removed_dates);
					// put the one time only event to the event list
					array_push($event_list,$event);
					// clear the event from memory
					unset($event);
				}
			} else {
				if ( $entry->entry->calendar_entry_repeat_type == "weekly" ) {
					// Set the server's default timezone
					date_default_timezone_set($entry->entry->timezone);
					
					$start = $entry->entry->start;
					
					while ( $start < $p_from ) {
						$temp = mktime(date("H",$start),date("i",$start),date("s",$start),date("m",$start),date("d",$start)+7,date("Y",$start));
						$start = $temp;
					}
				}
				/*
				$status = "";
				if ( $start >= $p_from && $start <= $p_thru ) {
					$status = "between";
				}
				echo "from:$p_from thru:$p_thru start:$start entry->start:" . $entry->start . " $status<br />";
				*/
				while ( $start >= $p_from && $start <= $p_thru ) {
					if ( !in_array($start,$entry->entry->removed_dates) ) {
						//
						// create a new entry
						$event = clone $entry;
						// rename the event's entry to event
						unset($event->entry);
						$event->event = clone $entry->entry;
						// reset the start date
						$event->event->start = $start;
						// copy the id to the calendar_entry_id
						$event->event->calendar_entry_id = $event->event->id;
						// create a note field
						$event->event->note = null;
						// unset the entry fields not found in the event table
						unset($event->event->id);
						unset($event->event->calendar_entry_repeat_type_id);
						unset($event->event->calendar_entry_repeat_type);
						unset($event->event->end);
						unset($event->event->removed_dates);
						// put the one time only event to the event list
						array_push($event_list,$event);
						// clear the event from memory
						unset($event);
					}
					//
					// increment the start date
					if ( $entry->entry->calendar_entry_repeat_type == "weekly" ) {
						// Set the server's default timezone
						date_default_timezone_set($entry->entry->timezone);
						
						$temp = mktime(date("H",$start),date("i",$start),date("s",$start),date("m",$start),date("d",$start)+7,date("Y",$start));
						$start = $temp;
					}
				}
			}
		}
		// sort the event list by start date
		$this->load->helper('compare_helper');
		usort($event_list,'client_event_compare');
		
		return $this->return_handler->results(200,"",$event_list);
	}
	
	public function getEventsForCalendarDateRange(  $p_calendar ) {
		// --------------------------------------------------------------------------------------------------------------------
		// get a list of all existing events and a count of thier participants for the client and date
		//
		// this list is keyed by calendar_entry_id + "_" + start
		// --------------------------------------------------------------------------------------------------------------------
		$entries = array();
		
		$sql  = "SELECT calendar_event.* ";
		$sql .= "FROM calendar_event ";
		$sql .= "WHERE calendar_event.calendar_id = " . $p_calendar->id . " ";
		$sql .= "AND calendar_event.start BETWEEN " . $p_calendar->start . " AND " . $p_calendar->end . " ";
		$sql .= "GROUP BY calendar_event.start ";

		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			foreach ( $rows as $row ) {
				// store the entry info
				$entry->calendar_id = cast_int($row->calendar_id);
				$entry->timezone = $p_calendar->timezone;
				$entry->calendar_entry_template_id = cast_int($row->calendar_entry_template_id);
				$entry->calendar_entry_id = cast_int($row->calendar_entry_id);
				$entry->start = cast_int($row->start);
				$entry->log_participant = cast_boolean($row->log_participant);
				$entry->wod = cast_boolean($row->wod);
				$entry->log_result = cast_boolean($row->log_result);
				$entry->rsvp = cast_boolean($row->rsvp);
				$entry->waiver = cast_boolean($row->waiver);
				$entry->payment = cast_boolean($row->payment);
				$entry->duration = cast_int($row->duration);
				$entry->all_day = cast_boolean($row->all_day);
				$entry->name = $row->name;
				$entry->description = $row->description;
				$entry->location = $row->location;
				$entry->note = $row->note;
				// create the event_key ( entry_id . "_" . start )
				$event_key = $row->calendar_entry_id . "_" . $row->start;
				// put the entry in the array of entries under that event_key
				$entries[$event_key] = clone $entry;
				// clear the entry from memory
				unset($entry);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",$entries);
		}
	}

	public function mergeEvents( $trans_list, $event_list ) {
		// --------------------------------------------------------------------------------------------------------------------
		//  Merge the event list with the translated entry list
		// --------------------------------------------------------------------------------------------------------------------
		foreach( $trans_list as &$event ) {
			// Create the event key
			$event_key = $event->event->calendar_entry_id . "_" . $event->event->start;
			// Does the event exist for the entry?
			if ( array_key_exists($event_key, $event_list) ) {
				$event->event = $event_list[$event_key];
			}
			$event->event->key = $event_key;
		}
		return $this->return_handler->results(200,"",$trans_list);
	}
	
	public function getWODWorkoutsByClientDate(  $p_client_id, $p_date ) {
		// --------------------------------------------------------------------------------------------------------------------
		// get a list of all template wod's and their workouts for the client and date
		//
		// this list is keyed by template wod id
		// --------------------------------------------------------------------------------------------------------------------
		$entries = array();
		
		$sql  = "SELECT calendar_entry_template_wod.*, calendar_entry_template_wod_library_workout.library_workout_id ";
		$sql .= "FROM calendar_entry_template_wod ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template_wod_library_workout ";
		$sql .= "LEFT OUTER JOIN library_workout ";
		$sql .= "ON library_workout.id = calendar_entry_template_wod_library_workout.library_workout_id ";
		$sql .= "ON calendar_entry_template_wod_library_workout.calendar_entry_template_wod_id = calendar_entry_template_wod.id ";
		$sql .= "WHERE calendar_entry_template_wod.client_id = " . $p_client_id . " ";
		$sql .= "AND calendar_entry_template_wod.yyyymmdd = '" . $p_date . "' ";
		$sql .= "ORDER BY calendar_entry_template_wod.id, library_workout.name, library_workout.id ";

		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			$wod = new stdClass();
			$wod->id = null;
			
			foreach ( $rows as $row ) {
				if ( $wod->id != $row->id ) {
					if ( !is_null($wod->id) ) {
						$entries[$wod->calendar_entry_template_id] = clone $wod;
						unset($wod);
						$wod = new stdClass();
					}
					$wod->id = cast_int($row->id);
					$wod->calendar_entry_template_id = cast_int($row->calendar_entry_template_id);
					$wod->date = cast_int($row->yyyymmdd);
					$wod->client_id = cast_int($row->client_id);
					$wod->note = $row->note;
					$wod->workout = array();
				}
				if ( !is_null($row->library_workout_id) ) {
					array_push($wod->workout,cast_int($row->library_workout_id));
				}
			}
			if ( !is_null($wod->id) ) {
				$entries[$wod->calendar_entry_template_id] = clone $wod;
				unset($wod);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",$entries);
		}
	}
	
	public function getNonWODWorkoutsByClientDate(  $p_client_id, $p_date ) {
	    // Get a list of locations and thier calendars for a client
	    $return = $this->perform('this->getLocationCalendarDateRangeForClientDate',$p_client_id,$p_date);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		$locations = $return['response'];

		$entries = array();
		foreach( $locations as $loc ) {
			foreach ( $loc->calendar as $calendar ) {
				// print_r($calendar); echo "<br />";
				$return = $this->perform('this->getNonWODWorkoutsByCalendarDateRange',$calendar);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				// echo "---------<br />"; print_r($return); echo "---------<br />";
				// Merge the New Entries to the Entries array
				$entries = array_merge($entries,$return['response']);
			}
		}
		
		if ( count($entries) > 0 ) {
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(202,"No Entries found.",array());
		}
	}
	
	public function getNonWODWorkoutsByCalendarDateRange($p_calendar) {
		// --------------------------------------------------------------------------------------------------------------------
		// get a list of all existing non-wod events and thier workouts for the client and date
		//
		// this list is keyed by calendar_entry_id + "_" + start
		// --------------------------------------------------------------------------------------------------------------------
		$entries = array();
		
		$sql  = "SELECT calendar_event.*, calendar_event_library_workout.library_workout_id workout_id ";
		$sql .= "FROM calendar_event, ";
		$sql .= "calendar_event_library_workout, ";
		$sql .= "library_workout ";
		$sql .= "WHERE calendar_event.calendar_id = " . $p_calendar->id . " ";
		$sql .= "AND NOT calendar_event.wod ";
		$sql .= "AND calendar_event.start BETWEEN " . $p_calendar->start . " AND " . $p_calendar->end . " ";
		$sql .= "AND calendar_event_library_workout.calendar_event_id = calendar_event.id ";
		$sql .= "AND library_workout.id = calendar_event_library_workout.library_workout_id ";
		$sql .= "GROUP BY calendar_event.start, library_workout.name, calendar_event_library_workout.library_workout_id ";

		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			$save_event_key = null;
			
			foreach ( $rows as $row ) {
				// create the event_key ( entry_id . "_" . start )
				$event_key = $row->calendar_entry_id . "_" . $row->start;
				if ( $event_key != $save_event_key ) {
					if ( !is_null($save_event_key) ) {
						// put the entry in the array of entries under that event_key
						$entries[$save_event_key] = clone $entry;
						// clear the entry from memory
						unset($entry);
					}
					// store the entry info
					$entry = new stdClass();
					$entry->calendar_id = cast_int($row->calendar_id);
					$entry->calendar_entry_template_id = cast_int($row->calendar_entry_template_id);
					$entry->calendar_entry_id = cast_int($row->calendar_entry_id);
					$entry->start = cast_int($row->start);
					$entry->log_participant = cast_boolean($row->log_participant);
					$entry->wod = cast_boolean($row->wod);
					$entry->log_result = cast_boolean($row->log_result);
					$entry->rsvp = cast_boolean($row->rsvp);
					$entry->waiver = cast_boolean($row->waiver);
					$entry->payment = cast_boolean($row->payment);
					$entry->duration = cast_int($row->duration);
					$entry->all_day = cast_boolean($row->all_day);
					$entry->name = $row->name;
					$entry->description = $row->description;
					$entry->location = $row->location;
					$entry->note = $row->note;
					$entry->workout = array();
				}
				if ( !is_null($row->workout_id) ) {
					array_push($entry->workout,$row->workout_id);
				}
			}
			// save the last event
			if ( !is_null($save_event_key) ) {
				// put the entry in the array of entries under that event_key
				$entries[$save_event_key] = clone $entry;
				// clear the entry from memory
				unset($entry);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",$entries);
		}
	}

	public function getEntryRepeatTypeByClientDate( $p_client_id, $p_date ) {
	    // Get a list of locations and thier calendars for a client
	    $return = $this->getLocationCalendarDateRangeForClientDate($p_client_id,$p_date);
		if ( $return['status'] != 200 ) {
			return $return;
		}
		$locations = $return['response'];

		$entries = array();
		foreach( $locations as $loc ) {
			foreach ( $loc->calendar as $calendar ) {
				// print_r($calendar); echo "<br />";
				$return = $this->getEntryRepeatTypeByCalendarDateRange($calendar,$entries);
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				$entries = $return['response'];
			}
		}
		
		if ( count($entries) > 0 ) {
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(202,"No Entries found.",array());
		}
	}
	
	public function getEntryRepeatTypeByCalendarDateRange($p_calendar,$p_entries) {
		// --------------------------------------------------------------------------------------------------------------------
		//  get the a list of the calendar entry repeat types for entrys at the calendar and date
		// --------------------------------------------------------------------------------------------------------------------
		
		$sql  = "SELECT calendar_entry.*, calendar_entry_repeat_type.name calendar_entry_repeat_type ";
		$sql .= "FROM calendar_entry, ";
		$sql .= "calendar_entry_repeat_type ";
		$sql .= "WHERE calendar_entry.calendar_id = " . $p_calendar->id . " ";
		$sql .= "AND (calendar_entry.start <= " . $p_calendar->end . " AND (calendar_entry.end IS NULL OR calendar_entry.end >= " . $p_calendar->start . ")) ";
		$sql .= "AND calendar_entry_repeat_type.id = calendar_entry.calendar_entry_repeat_type_id ";
		
		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();
			
			foreach ( $rows as $row ) {
				// print_r($row); echo "<br />";
				
				$p_entries[$row->id] = $row->calendar_entry_repeat_type;
			}
			
			return $this->return_handler->results(200,"",$p_entries);
		} else {
			return $this->return_handler->results(204,"No Entry Found",$p_entries);
		}
	}
	
	public function formatClassesByClientDay($p_event_list,$p_wod_list,$p_non_wod_list,$p_repeat_type_list,$p_date) {
		// echo "event list : <br />"; print_r($p_event_list); echo "<br /><br />";
		// echo "wod list : <br />"; print_r($p_wod_list); echo "<br /><br />";
		// echo "non-wod list : <br />"; print_r($p_non_wod_list); echo "<br /><br />";
		$entries = array();
		
		$class = new stdClass();
		$class->key = -1;
		$template->id = -1;
		
		$location = new stdClass();
		$location->id = -1;
		
		foreach ($p_event_list as $entry) {
			if ( $entry->event->wod || $entry->event->log_result ) {
				if ( !$entry->event->wod || $entry->template->id != $template->id ) {
					if ( $location->id != -1 ) {
						// store the last location into the class
						array_push($class->location,$location);
						unset($location);
						$location = new stdClass();
						$location->id = -1;
					}
					if ( $template->id != -1 ) {
						// store the last class in the entry array
						array_push($entries,$class);
						unset($class);
						$class = new stdClass();
						$template->id = -1;
					}
					if ( $entry->event->wod ) {
						$template->id = $entry->template->id;
						$class->key = "W" . $entry->template->id . "_" . $p_date;
						$class->name = $entry->template->name;
						if ( array_key_exists($entry->template->id, $p_wod_list) ) {
							$class->workout = $p_wod_list[$entry->template->id]->workout;
							$class->note = $p_wod_list[$entry->template->id]->note;
						} else {
							$class->workout = array();
							$class->note = '';
						}
					} else if ( $entry->event->log_result ) {
						$class->key = $entry->event->calendar_entry_id . '_' . $entry->event->start;
						$class->name = $entry->event->name;
						$class->note = $entry->event->note;
						if ( array_key_exists($entry->event->key, $p_non_wod_list) ) {
							$class->workout = $p_non_wod_list[$entry->event->key];
						} else {
							$class->workout = array();
						}
					}
					$class->location = array();
				}
				if ( !$entry->event->wod || $entry->location->id != $location->id ) {
					if ( $location->id != -1 ) {
						// store the last location into the class
						array_push($class->location,$location);
						unset($location);
						$location = new stdClass();
						$location->id = -1;
					}
					$location->id = $entry->location->id;
					$location->name = $entry->location->name;
					$location->class_time = array();
					$location->class = array();
				}
				array_push($location->class_time,$entry->event->start);
				
				$info = new stdClass();
				$info->entry_id = $entry->event->calendar_entry_id;
				$info->start = $entry->event->start;
				$info->repeat_type = $p_repeat_type_list[$info->entry_id];
				array_push($location->class,clone $info);
				unset($info);
			}
		}

		if ( $location->id != -1 ) {
			// store the last location into the class
			array_push($class->location,$location);
			unset($location);
		}
		if ( $template->id != -1 ) {
			// store the last class in the entry array
			array_push($entries,$class);
			unset($class);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	// ===================================================================================================================================================
	// Get the list of log results classes and thier scheduled times for a user on a given date (ccyymmdd) in client/location/class/time order
	// ===================================================================================================================================================
	
	public function getLogResultClassesForUserDate($p_user_id,$p_date) {
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Get a list of all clients/locations/calendars a user has access to - convert the date into the start and end UTC date/time for each calendar
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getClientLocationCalendarDateRangeForUserDate',$p_user_id,$p_date);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$clients = $return['response'];
		// echo json_encode($clients);
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Get a list of all Events for the Clients (clients/locations/calendars/start:stop)
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->getEventsForClients',$clients);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$events = $return['response'];
		// echo json_encode($events) . "<br /><br />\n\n";
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Get a list of all log_result Events for the Clients (clients/locations/calendars/start:stop) in client/location/template/date/time structure
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->formatLogResultClasses',$events);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$clients = $return['response'];
		
		return $return;
	}
	
	public function getClientLocationCalendarDateRangeForUserDate( $p_user_id, $p_date ) {
		// Format the start and end date/time
		$start = substr($p_date,0,4) . "-" . substr($p_date,4,2) . "-" . substr($p_date,6,2) . " 00:00:00";
		$end = substr($start,0,10) . " 23:59:59";
					
		// get the client/location/calendars for a client
		$sql  = "SELECT ";
		$sql .= "client_user.id client_user_id, ";
		$sql .= "client.id client_id, client.name client_name, ";
		$sql .= "location.id location_id, location.name location_name, ";
		$sql .= "calendar.id calendar_id, calendar.name calendar_name, calendar.timezone calendar_timezone ";
		$sql .= "FROM ";
		$sql .= "client_user, ";
		$sql .= "client, ";
		$sql .= "location, ";
		$sql .= "calendar ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " ";
		$sql .= "AND client.id = client_user.client_id ";
		$sql .= "AND client_user.deleted IS NULL ";
		$sql .= "AND location.client_id = client.id ";
		$sql .= "AND calendar.location_id = location.id AND calendar.classroom_id IS NULL ";
		$sql .= "ORDER BY client.name, client.id, location.name, location.id, calendar.name, calendar.id";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$client = array();
		$c = -1;
		foreach( $rows as $row ) {
			if ( $c < 0 || $client[$c]->id != $row->client_id ) {
				++$c;
				$client[$c]->id = $row->client_id;
				$client[$c]->name = $row->client_name;
				$client[$c]->client_user_id = $row->client_user_id;
				// initialize the location array
				$client[$c]->location = array();
				$location = &$client[$c]->location;
				$l = -1;
			}
			if ( $l < 0 || $location[$l]->id != $row->location_id ) {
				++$l;
				$location[$l]->id = $row->location_id;
				$location[$l]->name = $row->location_name;
				// initialize the calendar array
				$location[$l]->calendar = array();
				$calendar = &$location[$l]->calendar;
				$cal = -1;
			}
			if ( $cal < 0 || $calendar[$cal]->id != $row->calendar_id ) {
				++$cal;
				$calendar[$cal]->id = $row->calendar_id;
				$calendar[$cal]->name = $row->calendar_name;
				$calendar[$cal]->timezone = $row->calendar_timezone;
				// get the timezone_offset for the calendar's timezone
				$calendar[$cal]->timezone_offset = format_timezone_offset($row->calendar_timezone);
				// Set the server's default timezone
				date_default_timezone_set($row->calendar_timezone);
				// Set the UTC start and end date/time of the date for the calendar
				$calendar[$cal]->start = strtotime($start);
				$calendar[$cal]->end = strtotime($end);
			}
		}
			
		return $this->return_handler->results(200,"",$client);
	}
	
	public function getEventsForClients( $p_clients ) {
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Get a list of projected and created events for the client/location/calendar
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// print_r($client);
		$entries = array();
		foreach( $p_clients as $c ) {
			// echo "client_id:" . $c->id . "<br />";
			// echo json_encode($c);
			$client = new stdClass();
			$client->id = $c->id;
			$client->name = $c->name;
			$client->client_user_id = $c->client_user_id;
			foreach( $c->location as $l ) {
				// echo "location_id:" . $l->id . "<br />";
				$location = new stdClass();
				$location->id = $l->id;
				$location->name = $l->name;
				foreach ( $l->calendar as $calendar ) {
					// echo "calendar_id:" . $calendar->id . "<br />\n";
					// echo "calendar:"; print_r($calendar); echo "<br />";
					
					// Get a list of all entries for the calendar on the date
					$return = $this->perform('this->getEntriesForCalendar',$calendar);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					if ( $return['status'] > 200 ) {
						continue;
					}
					// echo "---- Entries:"; print_r($return['response']); echo "<br /><br /><br />\n\n";
					
					// Convert the list of entries into a list of events
					$return = $this->perform('this->translateEntriesToEventsForCalendar',$return['response'],$calendar);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					if ( $return['status'] > 200 ) {
						continue;
					}
					// echo "---- Tranlated Events:"; print_r($return['response']); echo "<br /><br /><br />";
					// if ( $calendar->id == 15 ) { echo json_encode($return['response']) . "<br /><br />\n\n"; }
					
					// store the translated event list
					$trans_event_list = $return['response'];
					
					// Get a list of all events that the client has during the time period with class and location
					$return = $this->perform('this->getEventsForCalendar',$calendar);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// echo "---- Get Events:"; print_r($return['response']); echo "<br /><br /><br />";
					// if ( $calendar->id == 15 ) { echo json_encode($return['response']) . "<br /><br />\n\n"; }
					
					// Merge the event list with the tranlated event list
					$return = $this->perform('this->mergeTransEventsWithEvents',$trans_event_list,$return['response']);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// echo "---- Meged Events:"; print_r($return['response']); echo "<br /><br /><br />";
					// if ( $calendar->id == 15 ) { echo json_encode($return['response']) . "<br /><br />\n\n"; }
					
					// remove the translated event list from memory
					unset($trans_event_list);
					
					// Create a simple calendar object
					$cal = new stdClass();
					$cal->id = $calendar->id;
					$cal->name = $calendar->name;
					$cal->timezone_offset = $calendar->timezone_offset;
					
					// add a Client, Location, and Calendar object to each event
					$return = $this->perform('this->addClientLocationCalendarToEvents',$return['response'],$client,$location,$cal);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					// echo "----  Add ClientLocationCalendar to Events:"; print_r($return); echo "<br /><br /><br />";
					// if ( $calendar->id == 15 ) { echo json_encode($return['response']) . "<br /><br />\n\n"; }
					
					// remove the cal from memory
					unset($cal);
				
					// Merge the Merged Events to the Entries array
					$entries = array_merge($entries, $return['response']);
				}
				// remove the location from memory
				unset($location);
			}
			// remove the client from memory
			unset($client);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	public function getEntriesForCalendar( $p_calendar ) {
		// --------------------------------------------------------------------------------------------------------------------
		//  get the a list of the calendar entrys at the calendar and date
		// --------------------------------------------------------------------------------------------------------------------
		$event_list = array();
		
		$sql  = "SELECT calendar_entry.*, ";
		$sql .= "calendar_entry_repeat_type.name calendar_entry_repeat_type_name, ";
		$sql .= "calendar_entry_type.name calendar_entry_type_name, ";
		$sql .= "calendar_entry_template.name calendar_entry_template_name ";
		$sql .= "FROM calendar_entry ";
		$sql .= "LEFT OUTER JOIN calendar_entry_type ";
		$sql .= "ON calendar_entry_type.id = calendar_entry.calendar_entry_type_id ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template ";
		$sql .= "ON calendar_entry_template.id = calendar_entry.calendar_entry_template_id, ";
		$sql .= "calendar_entry_repeat_type ";
		$sql .= "WHERE calendar_entry.calendar_id = " . $p_calendar->id . " ";
		$sql .= "AND (calendar_entry.start <= " . $p_calendar->end . " AND (calendar_entry.end IS NULL OR calendar_entry.end >= " . $p_calendar->start . ")) ";
		$sql .= "AND calendar_entry_repeat_type.id = calendar_entry.calendar_entry_repeat_type_id ";

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
			$entry->id = cast_int($row->id);
			$entry->name = $row->name;
			$entry->description = $row->description;
			$entry->duration = cast_int($row->duration);
			$entry->all_day = cast_boolean($row->all_day);
			$entry->start = cast_int($row->start);
			$entry->end = cast_int($row->end);
			
			$entry->calendar_entry_repeat_type = new stdClass();
			$entry->calendar_entry_repeat_type->id = cast_int($row->calendar_entry_repeat_type_id);
			$entry->calendar_entry_repeat_type->name = $row->calendar_entry_repeat_type_name;
			
			$entry->calendar_entry_type = new stdClass();
			$entry->calendar_entry_type->id = cast_int($row->calendar_entry_type_id);
			$entry->calendar_entry_type->name = $row->calendar_entry_type_name;
			
			$entry->calendar_entry_template = new stdClass();
			$entry->calendar_entry_template->id = cast_int($row->calendar_entry_template_id);
			$entry->calendar_entry_template->name = $row->calendar_entry_template_name;
			
			$entry->switch = new stdClass();
			$entry->switch->log_participant = cast_boolean($row->log_participant);
			$entry->switch->wod = cast_boolean($row->wod);
			$entry->switch->log_result = cast_boolean($row->log_result);
			$entry->switch->rsvp = cast_boolean($row->rsvp);
			$entry->switch->waiver = cast_boolean($row->waiver);
			$entry->switch->payment = cast_boolean($row->payment);
			$entry->switch->all_day = cast_boolean($row->all_day);
			
			$entry->removed_dates = json_decode($row->removed_dates);
			// put the entry into the array of entries
			array_push($entries,clone $entry);
			// clear the entry from memory
			unset($entry);
		}
			
		return $this->return_handler->results(200,"",$entries);
	}

	public function translateEntriesToEventsForCalendar( $p_entry_list, $p_calendar ) {
		// print_r($p_calendar);
		//
		// Initialize the response
		$event_list = array();
		
		// Set the server's default timezone
		date_default_timezone_set($p_calendar->timezone);
		
		// Create the events for each entry in the list
		foreach( $p_entry_list as $entry ) {
			// echo json_encode($entry) . "<br />";
			// echo "repeat type: " . $entry->calendar_entry_repeat_type . " start: " . $entry->start . " from: " . $p_from . " thru: " . $p_thru . "<br />";
			if ( $entry->calendar_entry_repeat_type->name == "once" ) {
				if ( $entry->start >= $p_calendar->start && $entry->start <= $p_calendar->end) {
					// clone entry to create the event
					$event = clone $entry;
					// copy the id to the calendar_entry_id
					$event->calendar_entry_id = $event->id;
					// set the calendar_event_id to null
					$event->id = null;
					// create the event_key ( entry_id . "_" . start )
					$event->event_key = $event->calendar_entry_id . "_" . $event->start;
					// create a note field
					$event->note = null;
					// unset the entry fields not found in the event table
					unset($event->calendar_entry_repeat_type);
					unset($event->end);
					unset($event->removed_dates);
					// put the one time only event to the event list
					array_push($event_list,$event);
					// clear the event from memory
					unset($event);
				}
			} else {
				if ( $entry->calendar_entry_repeat_type->name == "weekly" ) {
					// get the 1st date that the repeating entry is geater than or equal to the start of the calendar date range
					$start = $entry->start;
					while ( $start < $p_calendar->start ) {
						$temp = mktime(date("H",$start),date("i",$start),date("s",$start),date("m",$start),date("d",$start)+7,date("Y",$start));
						$start = $temp;
					}
				}
				/*
				$status = "";
				if ( $start >= $p_calendar->start && $start <= $p_calendar->end ) {
					$status = "between";
				}
				echo "from:" . $p_calendar->start . " thru:" . $p_calendar->end . " start:$start entry->start:" . $entry->start . " $status<br />";
				*/
				while ( $start >= $p_calendar->start && $start <= $p_calendar->end ) {
					if ( !in_array($start,$entry->removed_dates) ) {
						// echo "start:$start<br />";
						//
						// create a new entry
						$event = clone $entry;
						// reset the start date
						$event->start = $start;
						// copy the id to the calendar_entry_id
						$event->calendar_entry_id = $event->id;
						// set the calendar_event_id to null
						$event->id = null;
						// create the event_key ( entry_id . "_" . start )
						$event->event_key = $event->calendar_entry_id . "_" . $event->start;
						// create a note field
						$event->note = null;
						// unset the entry fields not found in the event table
						unset($event->calendar_entry_repeat_type);
						unset($event->end);
						unset($event->removed_dates);
						// put the one time only event to the event list
						array_push($event_list,$event);
						// clear the event from memory
						unset($event);
					}
					//
					// increment the start date
					if ( $entry->calendar_entry_repeat_type->name == "weekly" ) {
						$temp = mktime(date("H",$start),date("i",$start),date("s",$start),date("m",$start),date("d",$start)+7,date("Y",$start));
						$start = $temp;
					}
				}
			}
		}
		
		return $this->return_handler->results(200,"",$event_list);
	}
	
	public function getEventsForCalendar(  $p_calendar ) {
		// --------------------------------------------------------------------------------------------------------------------
		// get a list of all existing events and a count of thier participants for the client and date
		//
		// this list is keyed by calendar_entry_id + "_" + start
		// --------------------------------------------------------------------------------------------------------------------
		
		$sql  = "SELECT calendar_event.*, ";
		$sql .= "calendar_entry_type.name calendar_entry_type_name, ";
		$sql .= "calendar_entry_template.name calendar_entry_template_name, calendar_entry_template.calendar_entry_type_id ";
		$sql .= "FROM calendar_event ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template ";
		$sql .= "LEFT OUTER JOIN calendar_entry_type ";
		$sql .= "ON calendar_entry_type.id = calendar_entry_template.calendar_entry_type_id ";
		$sql .= "ON calendar_entry_template.id = calendar_event.calendar_entry_template_id ";
		$sql .= "WHERE calendar_event.calendar_id = " . $p_calendar->id . " ";
		$sql .= "AND calendar_event.start BETWEEN " . $p_calendar->start . " AND " . $p_calendar->end . " ";

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
			$entry->id = cast_int($row->id);
			$entry->calendar_entry_id = cast_int($row->calendar_entry_id);
			$entry->name = $row->name;
			$entry->description = $row->description;
			$entry->note = $row->note;
			$entry->duration = cast_int($row->duration);
			$entry->all_day = cast_boolean($row->all_day);
			$entry->start = cast_int($row->start);
			
			$entry->calendar_entry_type = new stdClass();
			$entry->calendar_entry_type->id = cast_int($row->calendar_entry_type_id);
			$entry->calendar_entry_type->name = $row->calendar_entry_type_name;
			
			$entry->calendar_entry_template = new stdClass();
			$entry->calendar_entry_template->id = cast_int($row->calendar_entry_template_id);
			$entry->calendar_entry_template->name = $row->calendar_entry_template_name;
			
			$entry->switch = new stdClass();
			$entry->switch->log_participant = cast_boolean($row->log_participant);
			$entry->switch->wod = cast_boolean($row->wod);
			$entry->switch->log_result = cast_boolean($row->log_result);
			$entry->switch->rsvp = cast_boolean($row->rsvp);
			$entry->switch->waiver = cast_boolean($row->waiver);
			$entry->switch->payment = cast_boolean($row->payment);
			$entry->switch->all_day = cast_boolean($row->all_day);
			// create the event_key ( entry_id . "_" . start )
			$entry->event_key = $row->calendar_entry_id . "_" . $row->start;
			// put the entry in the array of entries under that event_key
			$entries[$entry->event_key] = clone $entry;
			// clear the entry from memory
			unset($entry);
		}
			
		return $this->return_handler->results(200,"",$entries);
	}

	public function mergeTransEventsWithEvents( $trans_list, $event_list ) {
		// --------------------------------------------------------------------------------------------------------------------
		//  Merge the event list with the translated entry list
		// --------------------------------------------------------------------------------------------------------------------
		foreach( $trans_list as $event ) {
			// Does the projected event exist as an actual event already?
			if ( !array_key_exists($event->event_key, $event_list) ) {
				$event_list[$event->event_key] = clone $event;
			}
		}
		return $this->return_handler->results(200,"",$event_list);
	}
	
	public function addClientLocationCalendarToEvents( $event_list, $client, $location, $calendar ) {
		foreach( $event_list as &$event ) {
			// add client, location, and calendar to each event
			$event->client = clone $client;
			$event->location = clone $location;
			$event->calendar = clone $calendar;
		}
		return $this->return_handler->results(200,"",$event_list);
	}
	
	public function formatLogResultClasses( $p_events ) {
		// sort the event list by client/location/template/date/time
		$this->load->helper('compare_helper');
		usort($p_events,'client_location_template_start_compare');
		
		$client = array();
		$c = -1;
		foreach ( $p_events as $event ) {
			// echo json_encode($event) . "<br /><br />\n\n";
			// Only load the log_result classes
			if ( $event->switch->log_participant ) {
				if ( $c < 0 || $client[$c]->id != $event->client->id ) {
					++$c;
					$client[$c] = new stdClass();
					$client[$c]->id = $event->client->id;
					$client[$c]->name = $event->client->name;
					$client[$c]->client_user_id = $event->client->client_user_id;
					// initialize the location array
					$client[$c]->location = array();
					$location = &$client[$c]->location;
					$l = -1;
				}
				if ( $l < 0 || $location[$l]->id != $event->location->id ) {
					++$l;
					$location[$l] = new stdClass();
					$location[$l]->id = $event->location->id;
					$location[$l]->name = $event->location->name;
					$location[$l]->timezone_offset = $event->calendar->timezone_offset;
					// initialize the template array
					$location[$l]->template = array();
					$template = &$location[$l]->template;
					$t = -1;
				}
				if ( $t < 0 || $template[$t]->id != $event->calendar_entry_template->id ) {
					++$t;
					$template[$t] = new stdClass();
					$template[$t]->id = $event->calendar_entry_template->id;
					$template[$t]->name = $event->calendar_entry_template->name;
					// initialize the schedule array
					$template[$t]->schedule = array();
					$schedule = &$template[$t]->schedule;
					$s = -1;
				}
				if ( $s < 0 || $schedule[$s] != $event->event_key ) {
					++$s;
					$schedule[$s] = $event->event_key;
				}
			}
		}

		return $this->return_handler->results(200,"",$client);
	}
	
}