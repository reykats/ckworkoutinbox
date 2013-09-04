<?php

class action_streak extends action_generic {
	
	protected $user_timezone = null;
	protected $system_timezone = null;

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// The results for a competition
	// ==================================================================================================================
	
	public function getForUserGoal( $p_user_id, $p_goal = 3, $p_weeks = 7 ) {
		// echo "getForUserGoal $p_user_id $p_goal<br />";
		// get the system timezone, user timezone, and the calendar timezone of one of the client's calendars
		$return = $this->perform('this->getTimezonesForUser',$p_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$this->system_timezone = $return['response']->system_timezone;
		$this->user_timezone = $return['response']->user_timezone;
		// get the 1st and last checkin for a user
		$return = $this->perform('this->get1stCheckinForUser',$p_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			$first_ccyymmdd = null;
			$last_ccyymmdd = null;
		} else {
			$first_ccyymmdd = $return['response']->first_ccyymmdd;
			$last_ccyymmdd = $return['response']->last_ccyymmdd;
		}
		
		// echo "first:$first_ccyymmdd last:$last_ccyymmdd<br />\n";
		// Get the dates of participation for this week
		$return = $this->perform('this->getParticipationThisWeekForUserWeeksByDay',$p_user_id,1);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$this_week = $return['response'];
		
		// print_r($this_week);
		// Get the weekly participation for this user
		$return = $this->perform('this->getParticipationForUserByWeek',$p_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$weekly_count = $return['response'];
		// echo json_encode($weekly_count) . "<br />\n";
		
		// initialize the weekly participation array
		$return = $this->perform('this->initializeLastXWeekArray');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$lastXweeks = $return['response'];
		// echo json_encode($lastXweeks) . "<br />\n";
		
		// load the weekly counts into the weekly participation array
		$return = $this->perform('this->loadWeeklyCountsIntoWeeklyParticipation',$weekly_count,$lastXweeks);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$lastXweeks = $return['response'];
		// echo json_encode($lastXweeks) . "<br />\n";
		
		$response = new stdClass();
		$response->this_week = $this_week;
		$response->weekly_participation = $this->getLastXWeeks($lastXweeks);
		$response->current_streak = $this->getCurrentStreakForWeeklyGoal($weekly_count,$p_goal,$this_week);
		$response->longest_streak = $this->getLongestStreakForWeeklyGoal($weekly_count,$p_goal,$this_week);
		return $this->return_handler->results(200,"",$response);
	}

	public function getTimezonesForUser( $p_user_id ) {
		// echo "getTimezonesForUser $p_user_id $p_weeks<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "if(user.timezone IS NOT NULL AND user.timezone <> '',user.timezone,if(calendar.timezone IS NOT NULL AND calendar.timezone <> '',calendar.timezone,server.timezone)) user_timezone, ";
		$sql .= "server.timezone system_timezone ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "user, ";
		$sql .= "client_user, ";
		$sql .= "calendar ";
		$sql .= "WHERE user.id = " . $p_user_id . " ";
		$sql .= "AND client_user.user_id = user.id ";
		$sql .= "AND calendar.client_id = client_user.client_id ";
		$sql .= "LIMIT 1 ";
		
		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$row = $query->row();
		
		return $this->return_handler->results(200,"",$row);
	}

	public function get1stCheckinForUser( $p_user_id ) {
		// echo "get1stCheckinForUser $p_user_id $p_weeks<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "MIN(CONVERT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),server.timezone,calendar.timezone),'%Y%m%d'),UNSIGNED INTEGER)) first_ccyymmdd, ";
		$sql .= "MAX(CONVERT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),server.timezone,calendar.timezone),'%Y%m%d'),UNSIGNED INTEGER)) last_ccyymmdd ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "client_user, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " ";
		$sql .= "AND calendar.client_id = client_user.client_id ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "AND calendar_event_participation.client_user_id = client_user.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$row = $query->row();
		
		return $this->return_handler->results(200,"",$row);
	}
	
	public function getParticipationThisWeekForUserWeeksByDay( $p_user_id, $p_weeks ) {
		// echo "getParticipationThisWeekForUserWeeksByDay $p_user_id $p_weeks<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		 
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "DISTINCT CONVERT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),server.timezone,calendar.timezone),'%Y%m%d'),UNSIGNED INTEGER) event_start_ccyymmdd ";
		$sql .= "FROM ";
		$sql .= "server, ";
		$sql .= "client_user, ";
		$sql .= "calendar, ";
		$sql .= "calendar_event, ";
		$sql .= "calendar_event_participation ";
		$sql .= "WHERE client_user.user_id = " . $p_user_id . " ";
		$sql .= "AND calendar.client_id = client_user.client_id ";
		$sql .= "AND calendar_event.calendar_id = calendar.id ";
		$sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$sql .= "AND calendar_event_participation.client_user_id = client_user.id ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),server.timezone,calendar.timezone),'%X_%V') ";
		$sql .= "BETWEEN ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL " . ($p_weeks - 1) . " WEEK,server.timezone,calendar.timezone),'%X_%V') ";
		$sql .= "AND ";
		$sql .= "DATE_FORMAT(CONVERT_TZ(NOW(),server.timezone,calendar.timezone),'%X_%V') ";
		$sql .= "ORDER BY calendar_event.start  ASC ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$days = array();
		foreach ( $rows as $row ) {
			// print_r($row);
			$days[] = $row->event_start_ccyymmdd;
		}

		return $this->return_handler->results(200,"",$days);
	}

	public function getParticipationForUserByWeek( $p_user_id ) {
		// echo "getParticipationForUserByWeek $p_user_id<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		
		$participation_sql  = "";
		$participation_sql .= "SELECT ";
		$participation_sql .= "DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start ),server.timezone,calendar.timezone),'%X%V Sunday'),'%X%V %W'),'%Y%m%d') ccyymmdd_sunday, ";
		$participation_sql .= "CONVERT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(calendar_event.start),server.timezone,calendar.timezone),'%Y%m%d'),UNSIGNED INTEGER) ccyymmdd ";
		$participation_sql .= "FROM ";
		$participation_sql .= "server, ";
		$participation_sql .= "client_user, ";
		$participation_sql .= "calendar, ";
		$participation_sql .= "calendar_event, ";
		$participation_sql .= "calendar_event_participation ";
		$participation_sql .= "WHERE client_user.user_id = " . $p_user_id . " ";
		$participation_sql .= "AND calendar.client_id = client_user.client_id ";
		$participation_sql .= "AND calendar_event.calendar_id = calendar.id ";
		$participation_sql .= "AND calendar_event_participation.calendar_event_id = calendar_event.id ";
		$participation_sql .= "AND calendar_event_participation.client_user_id = client_user.id ";
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "p.ccyymmdd_sunday, ";
		$sql .= "COUNT(DISTINCT p.ccyymmdd) cnt ";
		$sql .= "FROM ";
		$sql .= "(" . $participation_sql . ") p ";
		$sql .= "GROUP BY p.ccyymmdd_sunday ";
		$sql .= "ORDER BY p.ccyymmdd_sunday desc ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$weeks = array();
		foreach ( $rows as $row ) {
			// print_r($row); echo "<br />\n";
			$weeks[$row->ccyymmdd_sunday] = $row->cnt;
		}
		
		return $this->return_handler->results(200,"",$weeks);
	}

	public function initializeLastXWeekArray(  ) {
		// echo "initializeWeeklyParticipationArray $p_weeks<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "";
		for( $week = 0; $week < 7; ++$week ) {
			// echo "$date<br />";
			if ( $sql != '' ) {
				$sql .= "UNION ";
			}
			$sql .= "(SELECT DATE_FORMAT(STR_TO_DATE(DATE_FORMAT(CONVERT_TZ(NOW() - INTERVAL " . $week . " WEEK,'" . $this->system_timezone . "','" . $this->user_timezone . "'),'%X%V Sunday'),'%X%V %W'),'%Y%m%d') ccyymmdd_sunday) ";
		}
		if ( $sql == '' ) {
			return $this->return_handler->results(204,"You asked for 0 weeks",array());
		}
		$sql .= "ORDER BY ccyymmdd_sunday DESC ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$weekly_participation = array();
		foreach ( $rows as $row ) {
			// print_r($row);
			$weekly_participation[$row->ccyymmdd_sunday] = 0;
		}
		
		return $this->return_handler->results(200,"",$weekly_participation);
	}

	public function loadWeeklyCountsIntoWeeklyParticipation( $p_weekly_count, $p_lastXweeks ) {
		// echo "loadWeeklyCountsIntoWeeklyParticipation <br />"; print_r($p_weekly_count); print_r($p_weekly_participation);
		foreach ( $p_lastXweeks as $key => &$value ) {
			if ( array_key_exists($key,$p_weekly_count) ) {
				$value = (int) $p_weekly_count[$key];
			}
		}
		
		return $this->return_handler->results(200,"",$p_lastXweeks);
	}

	public function getLastXWeeks( $p_lastXweeks ) {
		$lastXweeks = array();
		foreach ( $p_lastXweeks as $key => $value ) {
			$lastXweeks[] = $value;
		}
		
		return $lastXweeks;
	}

	public function getCurrentStreakForWeeklyGoal( $p_weekly_participation, $p_goal, $p_this_week ) {
		date_default_timezone_set($this->user_timezone); // set timezone
		$dow = date('w',time());                     // Day of week (0=Sun ... 6=Sat)
		$days_left_this_week = 7 - $dow;             // How many days are left in this week (including today)
		
		// has participation for today already been counted?
		$today = date('Ymd');
		if ( in_array($today,$p_this_week) ) {
			--$days_left_this_week;
		}
		
		$sunday = date('Ymd',mktime(0,0,0,date('m'),date('d') - $dow + 7,date('Y'))); // Next Sunday
		
		
		
		$limit = count($p_weekly_participation);
		
		$initial = true;
		$streak = 0;
		$count = 0;
		while( $count < $limit ) {
			$sunday = date('Ymd',mktime(0,0,0,substr($sunday,4,2),substr($sunday,6,2) - 7,substr($sunday,0,4)));
			// echo "sunday:$sunday count:$count<br />\n";
			if ( array_key_exists($sunday,$p_weekly_participation) ) {
				++$count;
				if ( $p_weekly_participation[$sunday] >= $p_goal ) {
					$initial = false;
					++$streak;
					// echo "sunday:$sunday streak:$streak<br />\n";
				} else if ( $initial && ($p_weekly_participation[$sunday] + $days_left_this_week) >= $p_goal ) {
					$initial = false;
					// echo "sunday:$sunday streak:$streak<br />\n";
				} else {
					break;
				}
			} else {
				if ( $initial && $days_left_this_week >= $p_goal ) {
					$initial = false;
				} else {
					break;
				}
			}
		}
		
		return $streak;
	}

	public function getLongestStreakForWeeklyGoal( $p_weekly_participation, $p_goal, $p_this_week ) {
		// echo "getLongestStreakForWeeklyGoal<br />\n";
		date_default_timezone_set($this->user_timezone);
		$dow = date('w',time()); // Day of week (0=Sun ... 6=Sat)
		$days_left_this_week = 7 - $dow;   // How many days are left in this week (including today)
		$sunday = date('Ymd',mktime(0,0,0,date('m'),date('d') - $dow + 7,date('Y')));  // Next Sunday
		// echo "sunday:$sunday<br />\n";
		
		$limit = count($p_weekly_participation);
		
		$longest = 0;
		$streak = 0;
		$count = 0;
		while( $count < $limit ) {
			$sunday = date('Ymd',mktime(0,0,0,substr($sunday,4,2),substr($sunday,6,2) - 7,substr($sunday,0,4)));
			// echo "sunday:$sunday count:$count<br />\n";
			if ( array_key_exists($sunday,$p_weekly_participation) ) {
				++$count;
				if ( $p_weekly_participation[$sunday] >= $p_goal ) {
					++$streak;
					// echo "sunday:$sunday streak:$streak<br />\n";
					if ( $longest < $streak ) {
						$longest = $streak;
					}
				} else {
					$streak = 0;
				}
			} else {
				$streak = 0;
			}
		}
		
		return $longest;
	}
	
}