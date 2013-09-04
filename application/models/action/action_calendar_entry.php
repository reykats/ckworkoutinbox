<?php

class action_calendar_entry extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get al list of all calendar_entries
	// ==================================================================================================================

	public function getForCalendar($p_calendar_id,$p_use_alias=true) {
		//
		// select
		$sql  = "SELECT calendar_entry.id 'calendar_entry.id', calendar_entry.calendar_id 'calendar_entry.calendar_id', calendar_entry.all_day 'calendar_entry.all_day', calendar_entry.duration 'calendar_entry.duration', ";
		$sql .= "calendar_entry.name 'calendar_entry.name', calendar_entry.description 'calendar_entry.description', calendar_entry.location 'calendar_entry.location', ";
		$sql .= "calendar_entry.rsvp 'calendar_entry.rsvp', calendar_entry.log_participant 'calendar_entry.log_participant', calendar_entry.wod 'calendar_entry.wod', ";
		$sql .= "calendar_entry.log_result 'calendar_entry.log_result', calendar_entry.payment 'calendar_entry.payment', calendar_entry.waiver 'calendar_entry.waiver', ";
		$sql .= "calendar_entry.calendar_entry_repeat_type_id 'calendar_entry.calendar_entry_repeat_type_id', calendar_entry.start 'calendar_entry.start', calendar_entry.end 'calendar_entry.end', ";
		$sql .= "calendar_entry.removed_dates 'calendar_entry.removed_dates.json', ";
		$sql .= "calendar_entry_template.id 'calendar_entry_template.id', calendar_entry_template.name 'calendar_entry_template.name', ";
		$sql .= "calendar_entry_type.id 'calendar_entry_type.id', calendar_entry_type.name 'calendar_entry_type.name' ";
		$sql .= "FROM calendar_entry ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template ";
		$sql .= "ON calendar_entry_template.id = calendar_entry.calendar_entry_template_id ";
		$sql .= "LEFT OUTER JOIN calendar_entry_type ";
		$sql .= "ON calendar_entry_type.id = calendar_entry.calendar_entry_type_id ";
		$sql .= "WHERE calendar_entry.calendar_id = " . $p_calendar_id . " ";
		$sql .= "ORDER by calendar_entry.start, calendar_entry.calendar_entry_template_id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		// get node names
		$table = new stdClass();
		$table->calendar_entry_type = mysql_schema::getTableAlias('workoutdb','calendar_entry_type',$p_use_alias);
		$table->calendar_entry_template = mysql_schema::getTableAlias('workoutdb','calendar_entry_template',$p_use_alias);
		
		$calendar_entry = array();
		$e = -1;
		foreach ( $rows as $row ) {
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
            // objectify the row by table and use column aliases if needed
			$row = mysql_schema::objectify_row($row,$p_use_alias);
         	// echo json_encode($row) . "<br /><br />\n\n";
            
            $e++;
            $calendar_entry[$e] = clone $row->calendar_entry;
			$calendar_entry[$e]->{$table->calendar_entry_template} = format_object_with_id($row->calendar_entry_template);
			$calendar_entry[$e]->{$table->calendar_entry_type} = format_object_with_id($row->calendar_entry_type);
		}
		
		return $this->return_handler->results(200,"",$calendar_entry);
	}

	// ==================================================================================================================
	// Get a single calendar_entry using the calendar_entry id
	// ==================================================================================================================

	public function getForId($p_calendar_entry_id,$p_use_alias=true) {
		//
		// select
		$sql  = "SELECT calendar_entry.id 'calendar_entry.id', calendar_entry.all_day 'calendar_entry.all_day', calendar_entry.duration 'calendar_entry.duration', ";
		$sql .= "calendar_entry.name 'calendar_entry.name', calendar_entry.description 'calendar_entry.description', calendar_entry.location 'calendar_entry.location',  ";
		$sql .= "calendar_entry.calendar_entry_template_id 'calendar_entry.calendar_entry_template_id', calendar_entry.calendar_entry_type_id 'calendar_entry.calendar_entry_type_id', ";
		$sql .= "calendar_entry.rsvp 'calendar_entry.rsvp', calendar_entry.log_participant 'calendar_entry.log_participant', calendar_entry.wod 'calendar_entry.wod', ";
		$sql .= "calendar_entry.log_result 'calendar_entry.log_result', calendar_entry.waiver 'calendar_entry.waiver', calendar_entry.payment 'calendar_entry.payment', ";
		$sql .= "calendar_entry.start 'calendar_entry.start', calendar_entry.end 'calendar_entry.end', calendar_entry.removed_dates 'calendar_entry.removed_dates.json', ";
		$sql .= "calendar_entry_repeat_type.id 'calendar_entry_repeat_type.id', calendar_entry_repeat_type.name 'calendar_entry_repeat_type.name', ";
		$sql .= "calendar.id 'calendar.id', calendar.timezone 'calendar.timezone' ";
		$sql .= "FROM calendar_entry ";
		$sql .= "LEFT OUTER JOIN calendar ";
		$sql .= "ON calendar.id = calendar_entry.calendar_id ";
		$sql .= "LEFT OUTER JOIN calendar_entry_repeat_type ";
		$sql .= "ON calendar_entry_repeat_type.id = calendar_entry.calendar_entry_repeat_type_id ";
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
		
		// setup the new entry
		$entry = clone $row->calendar_entry;
		$entry->calendar = clone $row->calendar;
		$entry->calendar->timezone_offset = format_timezone_offset($row->calendar->timezone);
		$entry->repeat_type = clone $row->repeat_type;

		// --------------------------------------------------------------------------------------
		// Get the blocked dates (the entry has participants between these 2 dates)
		// --------------------------------------------------------------------------------------
		$return = $this->perform('this->getBlockedDates',$p_calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$entry->blocked = clone $return['response'];
		// --------------------------------------------------------------------------------------
		// Does the entry have any events?
		// If so, do not allow the start date/time, repeat type, and template to be changed
		// --------------------------------------------------------------------------------------
		$return = $this->perform('this->getHasEvent',$p_calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$hasEvent = $return['response']->hasEvent;
		
		$entry->blocked->start = false;
		$entry->blocked->repeat_type = false;
		$entry->blocked->template = false;
		$entry->blocked->calendar = false;
		if ( $hasEvent ) {
			$entry->blocked->start = true;
			$entry->blocked->repeat_type = true;
			$entry->blocked->template = true;
			$entry->blocked->calendar = true;
		}

		return $this->return_handler->results(200,"",$entry);
	}

	public function getHasEvent($p_calendar_entry_id) {
		//
		// select
		$sql  = "SELECT calendar_event.id 'calendar_event.id' ";
		$sql .= "FROM calendar_event ";
		$sql .= "WHERE calendar_event.calendar_entry_id = " .  $p_calendar_entry_id . " ";
		$sql .= "LIMIT 1 ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		
		$entry = new stdClass();
		if ($query->num_rows() == 0) {
			$entry->hasEvent = false;
		} else {
			$entry->hasEvent = true;
		}

		return $this->return_handler->results(200,"",$entry);
	}

	public function getBlockedDates($p_calendar_entry_id,$p_use_alias=true) {
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Get the min and max date for a calendar_entry that have participation
		//
		// Deleting and modifying the entry will NOT be allowed between these dates
		//
	    // ---------------------------------------------------------------------------------------------------------
		//
		// select
		$sql  = "SELECT min(calendar_event.start) 'calendar_event.blocked_from.int', max(calendar_event.start) 'calendar_event.blocked_to.int' ";
		$sql .= "FROM calendar_event ";
		$sql .= "LEFT OUTER JOIN calendar_event_participation ";
		$sql .= "ON calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "WHERE calendar_event.calendar_entry_id = " .  $p_calendar_entry_id . " ";
		
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

		return $this->return_handler->results(200,"",$row->calendar_event);
	}

	// ==================================================================================================================
	// Create a new calendar_entry entry
	// ==================================================================================================================

	public function create( $p_fields ) {
		// echo "action_calendar_entry->create fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------
		// if the entry is created using a template, make sure the entry is set to the template values
		// -------------------------------------------------------------------------------------------------
		if ( property_exists($p_fields,'template_id') && !is_null($p_fields->template_id) && !empty($p_fields->template_id) ) {
			$return = $this->perform('this->getFieldsUsingTemplate',$p_fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			$p_fields = $return['response'];
		}
		// -------------------------------------------------------------------------------------------------
		// insert the data to calendar_entry
		// -------------------------------------------------------------------------------------------------
		return $this->perform('table_workoutdb_calendar_entry->insert',$p_fields);
	}

	public function getFieldsUsingTemplate($p_fields) {
		// ----------------------------------------------------------------------------
		// get the template
		// ----------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_calendar_entry_template->getForId',$p_fields->template_id,$cast_output=TRUE);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			return $this->return_handler->results(400,"Invalid template used",$p_fields);
		}
		$template = $return['response'];
		// ----------------------------------------------------------------------------
		// create a new fields object with all the fields and values in the template (except the id and client_id) added in
		// ----------------------------------------------------------------------------
		$fields = clone $p_fields;
		foreach($template as $key => $value) {
			if ( $key != 'id' && $key != 'client_id' ) {
				// add or change the field value
				$fields->{$key} = $value;
			}
		}
		
		return $this->return_handler->results(200,"",$fields);
	}

	// ==================================================================================================================
	// Update an existing calendar_entry entry
	// ==================================================================================================================

	public function update( $p_fields ) {
		// echo "action_calendar_entry->update fields:"; print_r($p_fields); echo "<br />";
		$p_fields = (object) $p_fields;
		// -------------------------------------------------------------------------------------------------------------
		// Client User Id is mandatory
		// -------------------------------------------------------------------------------------------------------------
		if ( !property_exists($p_fields,'id') || is_null($p_fields->id) || empty($p_fields->id) || !is_numeric($p_fields->id) ) {
			return $this->return_handler->results(400,"Id must be provided",new stdClass());
		}
		// ----------------------------------------------------------------------------
		// get the blocked dates (Entry has participants between these 2 dates)
		// ----------------------------------------------------------------------------
		$return = $this->perform('this->getBlockedDates',$p_fields->id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$blocked_from = $return['response']->from;
		$blocked_to = $return['response']->to;
		// ----------------------------------------------------------------------------
		// If set, the end date can not be less than the last blocked date
		// ----------------------------------------------------------------------------
		if ( !is_null($blocked_to) && property_exists($p_fields,'end') ) {
			if ( !is_null($p_fields->end) && !empty($p_fields->end) ) {
				if ( $p_fields->end < $blocked_to ) {
					$p_fields->end = $blocked_to;
				}
			}
		}
		// ----------------------------------------------------------------------------
		// If set, the start date can not be greater than the first blocked date
		// ----------------------------------------------------------------------------
		if ( !is_null($blocked_from) && property_exists($p_fields,'start') ) {
			if ( !is_null($p_fields->start) && !empty($p_fields->start) ) {
				if ( $p_fields->start > $blocked_from ) {
					$p_fields->start = $blocked_from;
				}
			}
		}
		// ----------------------------------------------------------------------------
		// update the entry
		// ----------------------------------------------------------------------------
		// print_r($p_fields);
		$return_update = $this->perform('table_workoutdb_calendar_entry->update',$p_fields);
		if ( $return_update['status'] >= 300 ) {
			return $return_update;
		}
		// ----------------------------------------------------------------------------
		// Delete the events greater than the end date
		// ----------------------------------------------------------------------------
		$return = $this->perform('this->deleteCalendarEventsForEntry',$p_fields->id,$blocked_from,$blocked_to);
		if ( $return['status'] >= 300 ) {
			return $return;
		}

		return $return_update;
	}

	public function deleteCalendarEventsForEntry( $p_calendar_entry_id, $p_blocked_from = null, $p_blocked_to = null ) {
		// ----------------------------------------------------------------------------
		// Get the events and their workouts greater than the end date
		// ----------------------------------------------------------------------------
		$return = $this->perform('this->getDeletableCalendarEventsForEntry',$p_calendar_entry_id,$p_blocked_from,$p_blocked_to);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// ----------------------------------------------------------------------------
		// Delete the events and their workouts
		// ----------------------------------------------------------------------------
		foreach ( $return['response'] as $event ) {
			$return = $this->perform('table_workoutdb_calendar_event->delete',$event->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		
		return $return;
	}

	public function getDeletableCalendarEventsForEntry( $p_calendar_entry_id, $p_blocked_from = null, $p_blocked_to = null ) {
		// ------------------------------------------------------------------------------
		// Get a list of event ids for events that start after the entry's end date/time
		// ------------------------------------------------------------------------------

		$sql  = "SELECT ev.id id, ev.start ";
		$sql .= "FROM calendar_entry en, ";
		$sql .= "calendar_event ev ";
		$sql .= "WHERE en.id = " .  $p_calendar_entry_id . " ";
		$sql .= "AND ev.calendar_entry_id = en.id ";
		if ( !is_null($p_blocked_from) && !is_null($p_blocked_to) ) {
			$sql .= "AND (ev.start < " . $p_blocked_from . " OR ev.start > " . $p_blocked_to . ") ";
		}
		$sql .= "ORDER BY ev.start ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			$entry = new stdClass();
			$entry->id = cast_int($row->id);
			$entry->start = cast_int($row->start);
			array_push($entries,clone $entry);
			unset($entry);
		}

		return $this->return_handler->results(200,"",$entries);
	}

	// ==================================================================================================================
	// Delete an existing calendar_entry entry
	// ==================================================================================================================

	public function delete( $p_calendar_entry_id ) {
		// ----------------------------------------------------------------------------
		// get the blocked dates (Entry has participants between these 2 dates)
		// ----------------------------------------------------------------------------
		$return = $this->perform('this->getBlockedDates',$p_calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$blocked_from = $return['response']->from;
		$blocked_to = $return['response']->to;
		// ----------------------------------------------------------------------------
		// If the entry has dates blocked from deleting, UPDATE the entry's start and end dates
		// ----------------------------------------------------------------------------
		if ( !is_null($blocked_from) && !is_null($blocked_to) ) {
			$fields = new stdClass();
			$fields->id = $p_calendar_entry_id;
			$fields->start = $blocked_from;
			$fields->end = $blocked_to;
			return $this->perform('this->update',$fields);
		} 
		// -----------------------------------------------------------------------------------------------
		// DELETE attached events and workouts
		// -----------------------------------------------------------------------------------------------
		$return = $this->perform('this->deleteCalendarEventsForEntry',$p_calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// -----------------------------------------------------------------------------------------------
		// DELETE a calendar_entry entry
		// -----------------------------------------------------------------------------------------------
		return $this->perform('table_workoutdb_calendar_entry->delete',$p_calendar_entry_id);
	}

	// ==================================================================================================================
	// Add a date to a calendar_entry's removed field.
	// ==================================================================================================================

	public function AddRemovedDate( $p_calendar_entry_id, $p_date ) {
		// -------------------------------------------------------------------------------------------------------------------------
		// Move a date to the calendar_entry removed array
		//
		// All workouts linked to the calendar_events (without participants) will be deleted
		// all calendar_events (without participants) will be deleted.
		// -------------------------------------------------------------------------------------------------------------------------
		// Get the current value of the calendar_entry entry
		// ----------------------------------------------------------------
		$return = $this->perform('this->getForId',$p_calendar_entry_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		// print_r($return);
		if ( $return['status'] != 200 ) {
			return $this->return_handler->results(400,"Invalid Entry",new stdClass());
		}
		$entry = $return['response'];
		// you can not do this to a repeat once entry
		if ( $entry->repeat_type_id == 1 ) {
			return $this->return_handler->results(400,"You can not add a removed date to a repeat once entry.",new stdClass());
		}
		// -------------------------------------------------------------------------------------------
		// Is the date to be removed, actually a valid date for the entry?
		// -------------------------------------------------------------------------------------------
		// validate the removed date for the entry
		$return = $this->perform('this->valid_removed_date',$entry,$p_date);
		if ( $return['status'] >= 300) {
			return $return;
		}
		$valid_removed_date = $return['response'];
		if ( !$valid_removed_date ) {
			return $this->return_handler->results(400,"Date not in entry series",new stdClass());
		}
		// ----------------------------------------------------------------
		// is the date already in the removed array?
		// ----------------------------------------------------------------
		$found = FALSE;
		foreach ( $entry->removed as $date ) {
			if ( $date == $p_date ) {
				$found = TRUE;
				break;
			}
		}
		if ( $found ) {
			return $this->return_handler->results(200,"",new stdClass());
		}
		// ----------------------------------------------------------------
		// delete the event if it exists for the entry/date
		// ----------------------------------------------------------------
		// does the event already exist
		$key = array();
		$key['calendar_entry_id'] = $p_calendar_entry_id;
		$key['start'] = $p_date;
		$return = $this->perform('table_workoutdb_calendar_event->getForAndKeys',$key);
		if ( $return['status'] >= 300) {
			return $return;
		}
		// print_r($return_event); echo "<br />";
		if ( $return['status'] == 200 ) {
			$return = $this->perform('table_workoutdb_calendar_event->delete',$return['response']->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		// ----------------------------------------------------------------
		// put the date into the removed array
		// ----------------------------------------------------------------
		$update = new stdClass();
		$update->id = $entry->id;
		$update->removed_dates = $entry->removed;
		$update->removed_dates[] = (int) $p_date;
		// echo "update:"; print_r($update); echo "<br />";
		// ----------------------------------------------------------------
		// put the data to calendar_entry
		// ----------------------------------------------------------------
		return $this->perform('table_workoutdb_calendar_entry->update',$update);
	}

	public function valid_removed_date( $p_entry, $p_date ) {
		if ( $p_entry->repeat_type_id == 2 ) {
			// weekly
			date_default_timezone_set($p_entry->calendar_timezone);
			if ( date('w',$p_entry->start) == date('w',$p_date) ) {
				return $this->return_handler->results(200,"",true);
			}
		}
		return $this->return_handler->results(200,"",false);
	}
}