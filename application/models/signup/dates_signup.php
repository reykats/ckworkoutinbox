<?php

class dates_signup extends CI_Model {
	
	public function __construct() {
		parent::__construct();
		//
		// used if generic_api methods ar used
		$this->database_name = 'workoutdb';
		$this->table_name = 'calendar';
	}
	
	public function get( $params = array() ) {
		$params = (array) $params;
		
		$client_id = 1;
		$yyyymmdd = "20121106";
		
		$return = $this->getLocationCalendarForClient($client_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$locations = $return['response'];
		// print_r($locations);
		
		foreach( $locations as $location ) {
			$location_id = $location->id;
			$location_name = $location->name;
			foreach ( $location->calendar as $calendar ) {
				// echo $calendar->id . " - " . $calendar->name . " - " . $calendar->timezone . "<br />";
				$return = $this->getEntriesForCalendarDate($location->id,$location->name,$calendar->id,$calendar->timezone,$yyyymmdd);
				// print_r($return); echo "<br /><br />";
			}
		}
		
		
		return $this->return_handler->results(200,"",new stdClass());
	}
	
	public function getLocationCalendarForClient( $p_client_id ) {
		
		// get the location calendars for a client
		$sql  = "SELECT l.id location_id, l.name location_name, ";
		$sql .= "cal.id calendar_id, cal.name calendar_name, cal.timezone calendar_timezone ";
		$sql .= "FROM client c, ";
		$sql .= "location l, ";
		$sql .= "calendar cal ";
		$sql .= "WHERE c.id = " . $p_client_id . " ";
		$sql .= "AND l.client_id = c.id ";
		$sql .= "AND cal.location_id = l.id AND cal.classroom_id IS NULL ";
		
		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$rows = $query->result();

			$entries = array();
			$location = new stdClass();
			
			foreach ( $rows as $row ) {
				// echo "Row:"; print_r($row); echo "<br />";
				
				if ( !property_exists($location,'id') || $location->id != $row->location_id ) {
					if ( property_exists($location,'id') && $location->id != $row->location_id ) {
						array_push($entries, $location);
						unset($location);
						$location = new stdClass();
					}
					$location->id = (int) $row->location_id;
					$location->name = $row->location_name;
					$location->calendar = array();
				}
				if ( !is_null($row->calendar_id) ) {
					$calendar = new stdClass;
					$calendar->id = (int) $row->calendar_id;
					$calendar->name = $row->calendar_name;
					$calendar->timezone = $row->calendar_timezone;
					array_push($location->calendar,$calendar);
					unset($calendar);
				}
			}
			
			if ( property_exists($location,'id') ) {
				array_push($entries, $location);
				unset($location);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(202,"No Entries found.",array());
		}
	}

	public function getEntriesForCalendarDate( $p_location_id, $p_location_name, $p_calendar_id, $p_calendar_timezone, $p_yyyymmdd ) {
		// echo "calendar:$p_calendar_id timezone:$p_calendar_timezone YYYYMMDD:$p_yyyymmdd<br />";
		$location = new stdClass();
		$location->id = $p_location_id;
		$location->name = $p_location_name;
		// --------------------------------------------------------------------------------------------------------------
		// Get the UTC date/time for the beginning and end of the date
		// --------------------------------------------------------------------------------------------------------------
		// Set the server's default timezone
		date_default_timezone_set($p_calendar_timezone);
		// get the start date/time
		$start = substr($p_yyyymmdd,0,4) . "-" . substr($p_yyyymmdd,4,2) . "-" . substr($p_yyyymmdd,6,2) . " 00:00:00";
		$start_date = strtotime($start);
		// get the end date/time
		$end = substr($p_yyyymmdd,0,4) . "-" . substr($p_yyyymmdd,4,2) . "-" . substr($p_yyyymmdd,6,2) . " 23:59:59";
		$end_date = strtotime($end);
		
		// echo "$start = $start_date   $end = $end_date<br />";
		
		// --------------------------------------------------------------------------------------------------------------
		// Get the entries for the calendar and date range
		// --------------------------------------------------------------------------------------------------------------
		$sql  = "SELECT en.*, ";
		$sql .= "t.name calendar_entry_tempate_name, ";
		$sql .= "r.name calendar_entry_repeat_type_name ";
		$sql .= "FROM calendar_entry en ";
		$sql .= "LEFT OUTER JOIN calendar_entry_template t ";
		$sql .= "ON t.id = en.calendar_entry_template_id, ";
		$sql .= "calendar_entry_repeat_type r ";
		$sql .= "WHERE en.calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND (en.start <= " . $start_date . " AND (en.end IS NULL OR en.end >= " . $end_date . ")) ";
		$sql .= "AND r.id = en.calendar_entry_repeat_type_id ";
		// echo "$sql<br />";
	
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$entries = array();
			
			$rows = $query->result();
			
			foreach ( $rows as $row ) {
				// print_r($row); echo "<br />";
				
				$template = new stdClass();
				if ( is_null($row->calendar_entry_template_id) ) {
					$template->id = null;
					$template->name = null;
				} else {
					$template->id = (int) $row->calendar_entry_template_id;
					$template->name = $row->calendar_entry_tempate_name;
				}
				
				// store the entry info
				$entry = new stdClass();
				$entry->id = (int) $row->id;
				$entry->calendar_id = (int) $row->calendar_id;
				$entry->calendar_timezone = $p_calendar_timezone;
				$entry->calendar_entry_type_id = (int) $row->calendar_entry_type_id;
				$entry->calendar_entry_repeat_type_id = (int) $row->calendar_entry_repeat_type_id;
				$entry->calendar_entry_repeat_type = $row->calendar_entry_repeat_type_name;
				if ( is_null($row->calendar_entry_template_id) ) {
					$entry->calendar_entry_template_id = null;
					$entry->calendar_entry_template = null;
				} else {
					$entry->calendar_entry_template_id = (int) $row->calendar_entry_template_id;
					$entry->calendar_entry_template = $row->calendar_entry_tempate_name;
				}
				$entry->log_participant = (boolean) $row->log_participant;
				$entry->wod = (boolean) $row->wod;
				$entry->log_result = (boolean) $row->log_result;
				$entry->rsvp = (boolean) $row->rsvp;
				$entry->waiver = (boolean) $row->waiver;
				$entry->payment = (boolean) $row->payment;
				$entry->all_day = (boolean) $row->all_day;
				$entry->duration = (int) $row->duration;
				$entry->start = (int) $row->start;
				$entry->end = (int) $row->end;
				$entry->name = $row->name;
				$entry->description = $row->description;
				$entry->location = $row->location;
				$entry->removed_dates = json_decode($row->removed_dates);
				
				$response = new stdClass();
				$response->wod = new stdClass();
				$response->template = $template;
				$response->location = $location;
				$response->entry = $entry;
				
				array_push($entries,$response);
				unset($response);
				unset($template);
				unset($entry);
			}
			
			return $this->return_handler->results(200,"",$entries);
		} else {
			return $this->return_handler->results(202,"No Entries found.",array());
		}
	}
}
?>