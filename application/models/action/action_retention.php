<?php

class action_retention extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// get the members who have :
	//
	// 1) not been to the gym for the time period
	// 2) just back to the gym after the time period
	// 3) come consistantly to the gym for the time period
	//
	// Where the time period is 1 week, 2 weeks, or 1 month.
	//
	// ==================================================================================================================
	
	public function getForClient($p_client_id,$p_format="short",$p_bucket=null,$p_period=null) {
		// get the timezone for the client
		$return = $this->perform('this->getTimezonesForClient',$p_client_id);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// Set today to the client's current ccyymmdd
		date_default_timezone_set($return['response']->timezone);
		$this->today = date('Ymd',time());
		// get the weekly days of retention totals for all active client users of a client
		$return = $this->perform('this->getWeeklyCheckinsForClient',$p_client_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$weekly_checkins = $return['response'];
		
		$return = $this->getRetention($weekly_checkins,$p_format,$p_bucket,$p_period);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		return $return;
	}
	
	public function getWeeklyCheckinsForClient($p_client_id) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for role_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_client_user_role_id = "";
		if ( isset($_GET['q_r']) && !empty($_GET['q_r']) && is_numeric($_GET['q_r']) ) {
			$and_client_user_role_id = "AND client_user.client_user_role_id = " . $_GET['q_r'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional check for location_id
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_location_id = "";
		if ( isset($_GET['q_loc']) && !empty($_GET['q_loc']) && is_numeric($_GET['q_loc']) ) {
			$and_location_id = "AND client_user.location_id = " . $_GET['q_loc'] . " ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional search field values
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$and_user_name = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			// concatinate varchar field values
			//
			// for count, user table is in main query
			$concat  = "concat(";
			$concat .= "if(isnull(user.first_name),'',concat(' ',user.first_name))";
			$concat .= ",";
			$concat .= "if(isnull(user.last_name),'',concat(' ',user.last_name))";
			$concat .= ",";
			$concat .= "if(isnull(user.email),'',concat(' ',user.email))";
			$concat .= ")";
			$and_user_name  = "AND " . $concat . " LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get all active client_users for a client.
		// Get the days of participation count for each of the last 4 weeks for each client_user.
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Get the days of participation for a client's client users for the last 4 weeks (including today)
		$participation_sql  = "";
		$participation_sql .= "SELECT ";
		$participation_sql .= "DISTINCT ";
		$participation_sql .= "calendar_event_participation.client_user_id client_user_id, ";
		$participation_sql .= "calendar_event.start start, ";
		$participation_sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d') ccyymmdd, ";
		$participation_sql .= "CONVERT(TRUNCATE((TO_DAYS(CONVERT_TZ(NOW(),'America/Chicago','America/Los_Angeles')) - TO_DAYS(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),'America/Chicago','America/Los_Angeles')) + 1) / 7,0),SIGNED) week ";
		$participation_sql .= "FROM ";
		$participation_sql .= "server, ";
		$participation_sql .= "calendar, ";
		$participation_sql .= "calendar_event, ";
		$participation_sql .= "calendar_event_participation ";
		$participation_sql .= "WHERE ";
		$participation_sql .= "calendar.client_id = " . $p_client_id . " ";
		$participation_sql .= "AND calendar_event.calendar_id = calendar.id ";
		$participation_sql .= "AND CONVERT(TRUNCATE((TO_DAYS(CONVERT_TZ(NOW(),'America/Chicago','America/Los_Angeles')) - TO_DAYS(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),'America/Chicago','America/Los_Angeles')) + 1) / 7,0),SIGNED) BETWEEN 0 AND 3 ";
		$participation_sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$participation_sql .= "ORDER BY ";
		$participation_sql .= "calendar_event_participation.client_user_id, ";
		$participation_sql .= "calendar_event.start ";
		
		$weekly_participation_sql  = "";
		$weekly_participation_sql .= "SELECT ";
		$weekly_participation_sql .= "p.client_user_id, max(p.start) last_start, max(p.ccyymmdd) last_checkin, p.week, count(p.week) checkin_count_for_week ";
		$weekly_participation_sql .= "FROM ";
		$weekly_participation_sql .= "( ";
		$weekly_participation_sql .= $participation_sql;
		$weekly_participation_sql .= ") p ";
		$weekly_participation_sql .= "GROUP BY p.client_user_id, p.week ";
		
		$client_user_participation_sql  = "";
		$client_user_participation_sql .= "SELECT ";
		$client_user_participation_sql .= "client_user.id 'client_user.id.int', ";
		$client_user_participation_sql .= "user.first_name 'client_user.first_name', ";
		$client_user_participation_sql .= "user.last_name 'client_user.last_name', ";
		$client_user_participation_sql .= "user.email 'client_user.email', ";
		$client_user_participation_sql .= "user.phone 'client_user.phone', ";
		$client_user_participation_sql .= "client_user_role.id 'client_user_role.id.int', ";
		$client_user_participation_sql .= "client_user_role.name 'client_user_role.name', ";
		$client_user_participation_sql .= "location.id 'location.id.int', ";
		$client_user_participation_sql .= "location.name 'location.name', ";
		$client_user_participation_sql .= "media.id 'media.id.int', ";
		$client_user_participation_sql .= "media.media_url 'media.url', ";
		$client_user_participation_sql .= "p.last_start 'checkins.last_start', p.last_checkin 'checkins.last_checkin', ";
		$client_user_participation_sql .= "p.week 'checkins.week.int', ";
		$client_user_participation_sql .= "p.checkin_count_for_week 'checkins.count.int' ";
		$client_user_participation_sql .= "FROM ";
		$client_user_participation_sql .= "client_user ";
		$client_user_participation_sql .= "LEFT OUTER JOIN ";
		$client_user_participation_sql .= "( ";
		$client_user_participation_sql .= $weekly_participation_sql;
		$client_user_participation_sql .= ") p ";
		$client_user_participation_sql .= "ON p.client_user_id = client_user.id ";
		$client_user_participation_sql .= "LEFT OUTER JOIN client_user_role ";
		$client_user_participation_sql .= "ON client_user_role.id = client_user.client_user_role_id ";
		$client_user_participation_sql .= "LEFT OUTER JOIN location ";
		$client_user_participation_sql .= "ON location.id = client_user.location_id, ";
		$client_user_participation_sql .= "user ";
		$client_user_participation_sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$client_user_participation_sql .= "ON media.user_id = user.id ";
		$client_user_participation_sql .= "WHERE client_user.client_id = " . $p_client_id . " ";
		$client_user_participation_sql .= "AND client_user.deleted IS NULL ";
		$client_user_participation_sql .= $and_client_user_role_id;
		$client_user_participation_sql .= $and_location_id;
		$client_user_participation_sql .= "AND user.id = client_user.user_id ";
		$client_user_participation_sql .= $and_user_name;
		$client_user_participation_sql .= "ORDER BY user.first_name,user.last_name,client_user.id,p.week ";

		$sql = $client_user_participation_sql;
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$client_user = array();
		$cu = -1;
		foreach( $rows as $row ) {
			// print_r($row); echo "<br />\n";
			
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row);
			// print_r($row); echo "<br />";
			if ( $cu < 0 || $client_user[$cu]->id != $row->client_user->id ) {
				// increment the Entry index
				$cu++;
				// Initialize the new Client User
				$client_user[$cu] = clone $row->client_user;
				$client_user[$cu]->client_user_role = clone $row->client_user_role;
				$client_user[$cu]->location = clone $row->location;
				$client_user[$cu]->media = clone $row->media;
				$client_user[$cu]->last_start = cast_int($row->checkins->last_start);
				$client_user[$cu]->last_checkin = $row->checkins->last_checkin;
				$client_user[$cu]->weekly_count = array(0,0,0,0);
				// create a pointer to the Client User's weekly count array
				$weekly_count = &$client_user[$cu]->weekly_count;
			}
			if ( !is_null($row->checkins->week) && $row->checkins->week >= 0 && $row->checkins->week < 4 ) {
				$weekly_count[$row->checkins->week] = $row->checkins->count;
			}
		}
		
		return $this->return_handler->results(200,"",$client_user);
	}

	public function getTimezonesForClient( $p_client_id ) {
		// echo "getTimezonesForUser $p_user_id $p_weeks<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "if(cal.timezone IS NOT NULL AND cal.timezone <> '',cal.timezone,s.timezone) timezone ";
		// $sql .= "u.timezone user_timezone, cal.timezone calendar_timezone, s.timezone system_timezone ";
		$sql .= "FROM ";
		$sql .= "server s, ";
		$sql .= "client c, ";
		$sql .= "calendar cal ";
		$sql .= "WHERE c.id = " . $p_client_id . " ";
		$sql .= "AND cal.client_id = c.id ";
		$sql .= "LIMIT 1 ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$row = $query->row();
		
		return $this->return_handler->results(200,"",$row);
	}
	
	public function getRetention( &$p_client_users, $p_format, $p_bucket, $p_period ) {
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// create and initialize the buckets
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$not_seen_in = array();
		$not_seen_in['1week'] = new stdClass();
		$not_seen_in['1week']->count = 0;
		$not_seen_in['1week']->results = array();
		$not_seen_in['2weeks'] = new stdClass();
		$not_seen_in['2weeks']->count = 0;
		$not_seen_in['2weeks']->results = array();
		$not_seen_in['1month'] = new stdClass();
		$not_seen_in['1month']->count = 0;
		$not_seen_in['1month']->results = array();
		$just_came_back = array();
		$just_came_back['1week'] = new stdClass();
		$just_came_back['1week']->count = 0;
		$just_came_back['1week']->results = array();
		$just_came_back['2weeks'] = new stdClass();
		$just_came_back['2weeks']->count = 0;
		$just_came_back['2weeks']->results = array();
		$just_came_back['1month'] = new stdClass();
		$just_came_back['1month']->count = 0;
		$just_came_back['1month']->results = array();
		$came_consistently_for = array();
		$came_consistently_for['1week'] = new stdClass();
		$came_consistently_for['1week']->count = 0;
		$came_consistently_for['1week']->results = array();
		$came_consistently_for['2weeks'] = new stdClass();
		$came_consistently_for['2weeks']->count = 0;
		$came_consistently_for['2weeks']->results = array();
		$came_consistently_for['1month'] = new stdClass();
		$came_consistently_for['1month']->count = 0;
		$came_consistently_for['1month']->results = array();
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// get the limit on maximum number of Client Users are to be in any bucket
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$page_start = null;
		$page_end = null;
		if ( isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) ) {
			$page_start = 1;
			$page_end = $_GET['limit'];
		}
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$page_start = (($_GET['page'] - 1) * $_GET['page_length']);
			$page_end = $page_start + $_GET['page_length'] - 1;
		}
		if ( !isset($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$page_start = 1;
			$page_end = $_GET['page_length'];
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// load the buckets
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		foreach( $p_client_users as &$client_user ) {
			// Haven't seen in more than a month
			if ( $client_user->weekly_count[0] == 0 && 
			     $client_user->weekly_count[1] == 0 && 
				 $client_user->weekly_count[2] == 0 && 
				 $client_user->weekly_count[3] == 0 ) {
				$not_seen_in['1month']->count++;
				if ( is_null($page_start) || ($not_seen_in['1month']->count >= $page_start && $not_seen_in['1month']->count <= $page_end) ) {
					$result = $this->format_result($client_user,$p_format);
					array_push($not_seen_in['1month']->results,clone $result);
					unset($result);
				}
				continue;
			}
			// Haven't seen in 2 weeks
			if ( $client_user->weekly_count[0] == 0 && 
			     $client_user->weekly_count[1] == 0 && 
				 ( $client_user->weekly_count[2] != 0 || 
				   $client_user->weekly_count[3] != 0 ) ) {
				$not_seen_in['2weeks']->count++;
				if ( is_null($page_start) || ($not_seen_in['2weeks']->count >= $page_start && $not_seen_in['2weeks']->count <= $page_end) ) {
					$result = $this->format_result($client_user,$p_format);
					array_push($not_seen_in['2weeks']->results,clone $result);
					unset($result);
				}
				continue;
			}
			// Haven't seen in 1 week
			if ( $client_user->weekly_count[0] == 0 && 
			     $client_user->weekly_count[1] != 0 ) {
				$not_seen_in['1week']->count++;
				if ( is_null($page_start) || ($not_seen_in['1week']->count >= $page_start && $not_seen_in['1week']->count <= $page_end) ) {
					$result = $this->format_result($client_user,$p_format);
					array_push($not_seen_in['1week']->results,clone $result);
					unset($result);
				}
				continue;
			}
			// Just back after
			if ( $client_user->last_checkin == $this->today && $client_user->weekly_count[0] == 1 ) {
				// 1 week
				if ( $client_user->weekly_count[1] != 0 ) {
					$just_came_back['1week']->count++;
					if ( is_null($page_start) || ($just_came_back['1week']->count >= $page_start && $just_came_back['1week']->count <= $page_end) ) {
						$result = $this->format_result($client_user,$p_format);
						array_push($just_came_back['1week']->results,clone $result);
						unset($result);
					}
					continue;
				}
				// 2 weeks
				if ( $client_user->weekly_count[2] != 0 || $client_user->weekly_count[3] != 0 ) {
					$just_came_back['2weeks']->count++;
					if ( is_null($page_start) || ($just_came_back['2weeks']->count >= $page_start && $just_came_back['2weeks']->count <= $page_end) ) {
						$result = $this->format_result($client_user,$p_format);
						array_push($just_came_back['2weeks']->results,clone $result);
						unset($result);
					}
					continue;
				}
				// More than a month
				$just_came_back['1month']->count++;
				if ( is_null($page_start) || ($just_came_back['1month']->count >= $page_start && $just_came_back['1month']->count <= $page_end) ) {
					$result = $this->format_result($client_user,$p_format);
					array_push($just_came_back['1month']->results,clone $result);
					unset($result);
				}
				continue;
			}
			// Has come consistantly for a month ( more than 1 checkin in 7 days is consistantly )
			if ( $client_user->weekly_count[0] > 1 &&
			     $client_user->weekly_count[1] > 1 &&
			     $client_user->weekly_count[2] > 1 &&
			     $client_user->weekly_count[3] > 1 ) {
				$came_consistently_for['1month']->count++;
				if ( is_null($page_start) || ($came_consistently_for['1month']->count >= $page_start && $came_consistently_for['1month']->count <= $page_end) ) {
					$result = $this->format_result($client_user,$p_format);
					array_push($came_consistently_for['1month']->results,clone $result);
					unset($result);
				}
				continue;
			}
			// Has come consistantly for 2 weeks ( more than 1 checkin in 7 days is consistantly )
			if ( $client_user->weekly_count[0] > 1 &&
			     $client_user->weekly_count[1] > 1 ) {
				$came_consistently_for['2weeks']->count++;
				if ( is_null($page_start) || ($came_consistently_for['2weeks']->count >= $page_start && $came_consistently_for['2weeks']->count <= $page_end) ) {
					$result = $this->format_result($client_user,$p_format);
					array_push($came_consistently_for['2weeks']->results,clone $result);
					unset($result);
				}
				continue;
			}
			// Has come consistantly for 1 week ( more than 1 checkin in 7 days is consistantly )
			if ( $client_user->weekly_count[0] > 1 ) {
				$came_consistently_for['1week']->count++;
				if ( is_null($page_start) || ($came_consistently_for['1week']->count >= $page_start && $came_consistently_for['1week']->count <= $page_end) ) {
					$result = $this->format_result($client_user,$p_format);
					array_push($came_consistently_for['1week']->results,clone $result);
					unset($result);
				}
				continue;
			}
		}
		// echo "bucket:$p_bucket period:$p_period<br />\n";
		if ( ($p_bucket == 'not_seen_in' || $p_bucket == 'just_came_back' || $p_bucket == 'came_consistently_for') &&
		     ($p_period == '1week' || $p_period == '2weeks' || $p_period == '1month') ) {
			$response = clone ${$p_bucket}[$p_period];
		} else {
			$response = new stdClass();
			$response->not_seen_in = $not_seen_in;
			$response->just_came_back = $just_came_back;
			$response->came_consistently_for = $came_consistently_for;
		}
		return $this->return_handler->results(200,"",$response);
	}

	public function format_result( $p_client_user, $p_format ) {
		$cleint_user = new stdClass();
		$client_user->id = cast_int($p_client_user->id);
		if ( $p_format == "short" ) {
			$client_user->media = $p_client_user->media->url;
		} else {
			$client_user->first_name = $p_client_user->first_name;
			$client_user->last_name = $p_client_user->last_name;
			$client_user->email = $p_client_user->email;
			$client_user->phone = $p_client_user->phone;
			$client_user->role = $p_client_user->client_user_role->name;
			$client_user->location = $p_client_user->location->name;
			$client_user->media = format_media($p_client_user->media->id,$p_client_user->media->url);
			$client_user->last_checkin = $p_client_user->last_start;
		}
		return $client_user;
	}
}