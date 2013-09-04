<?php
class email_user_notification extends email_generic {

	public function __construct() {
		parent::__construct();

		$this->load->helper('cast');
	}

	public function sendEmail($p_user_id) {
		// echo "email_user_notification->sendEmail user:$p_user_id<br />";
		// print_r($p_user); print_r($p_client_user);
		// ===========================================================================================
		// Format the Email
		// ===========================================================================================
		$response = new stdClass();
		$response->id = null;
		// -------------------------------------------------------------------------------------------
		// Get the detail information about the client_user, user, and client for the client_user
		// -------------------------------------------------------------------------------------------
		$return = $this->perform('this->getInformationForUser',$p_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'invalid client_user used',$response);
		}
		$data = $return['response'];
		
		if ( $data->count == 0 ) {
			return $this->return_handler->results(204,'Logging Email not needed',$response);
		}

		// echo json_encode($data) . "<br />";

		// -------------------------------------------------------------------------------------------
		// get the the template file (the base directory is private)
		// -------------------------------------------------------------------------------------------
		$html_template_filename = '../public/template/email/WorkoutLogReminder/WorkoutLogReminder.html';
		$html_template = file_get_contents($html_template_filename);
		// -------------------------------------------------------------------------------------------
		// Format the body of the Email
		// -------------------------------------------------------------------------------------------
		$return = $this->perform('this->formatEmailBody',$data);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		
		$email = $return['response'];
		
		
		// Format the User's name
		$user->name = $this->format_name($data->user);

		// initialize the email content
		$html_content = '';
		$text_content = '';
		
		// create the greeting
		$html_content .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">Hi ' . $user->name . ',</p>';
		$text_content .= 'Hi ' . $user->name . ',\n';
		
		$html_content .= $email->html_body;
		$text_content .= $email->text_body;
		
		$html_content .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">Recording results and completing your personal profile will allow you to take full advantage of new features, available shortly.</p>';
		$text_content .= 'Recording results and completing your personal profile will allow you to take full advantage of new features, available shortly.\n';
		
		$html_content .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">Thank you,<br />WorkoutInbox Support Team<br />Bringing Science to Fitness</p>';
		$text_content .= 'Thank you,\nWorkoutInbox Support Team\nBringing Science to Fitness';
		// -------------------------------------------------------------------------------------------
		// insert the html_content into the template
		// -------------------------------------------------------------------------------------------
		$html_content = str_replace('<workoutinbox var="html_content" />',$html_content,$html_template);

		// echo $html_content . "<br />";
		// echo $text_content . "<br />";
		// -------------------------------------------------------------------------------------------
		// format the email subject
		// -------------------------------------------------------------------------------------------
		$subject = $this->format_subject('Reminder: Log your Workout');
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the content array
		$content = array(
			'subject'=>$subject,
			'html' => $html_content,
			'text' => $text_content
		);
		// echo "content:"; print_r($content); echo "<br /><br />";

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the parameter array for support
		$param = $this->from_support();
		// Add the user id to the tags
		$param['tag'][] = $this->tag_prefix . '-user-' . $p_user_id;
		// echo "param:"; print_r($param); echo "<br /><br />";

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the email 'to address' array ( if this is the test system use "support" )
		$emails = array(
			0=>array('email'=> $this->get_valid_email($data->user->email) )
		);
		// echo "emails:"; print_r($emails); echo "<br /><br />";

		// -------------------------------------------------------------------------------------------
		// Send and log the email
		// -------------------------------------------------------------------------------------------
		$return = $this->send_email($content,$param,$emails);

		return $return;
	}
	
	public function getInformationForUser($p_user_id) {
		// echo "email_user_notification->getInformationForUser user:$p_user_id<br />";
		$return = $this->perform('this->getUserForId',$p_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] != 200 ) {
			return $this->return_handler->results(204,"Invalid User",new stdClass());
		}
		$user = $return['response'];
		// print_r($user);
		// get the calendars (and client_user IDs at the calendar) for the user
		$return = $this->perform('this->getCalendarsForUser',$p_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$clients = $return['response'];
		$count = 0;
		if ( $return['status'] == 200 ) {
			// echo json_encode($clients);
			foreach( $clients as &$client ) {
				foreach( $client->calendars as &$calendar ) {
					// get the user's pending workout_logs on this calendar.
					$return = $this->perform('this->getWorkoutLogsForCalendarClientUser',$calendar->id,$client->client_user->id);
					if ( $return['status'] >= 300 ) {
						return $return;
					}
					$calendar->workout_logs = $return['response'];
					$count += count($calendar->workout_logs);
				}
			}
		}
		
		$response = new stdClass();
		$response->count = $count;
		$response->user = new stdClass();
		$response->user = clone $user;
		$response->clients = $clients;
		
		return $this->return_handler->results(200,"",$response);
	}
	
	public function getUserForId($p_user_id) {
		// get the needed values for the url :
		// http://alpha.workoutinbox.com/app/#user/info?id=2161
		
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "u.email, u.first_name, u.last_name, u.phone, u.gender, u.birthday, u.token, u.password, u.height, uom_h.name height_uom, u.weight, uom_w.name weight_uom, media.media_url ";
		$sql .= "FROM ";
		$sql .= "user u ";
		$sql .= "LEFT OUTER JOIN library_measurement_system_unit uom_h ";
		$sql .= "ON uom_h.id = u.height_uom_id ";
		$sql .= "LEFT OUTER JOIN library_measurement_system_unit uom_w ";
		$sql .= "ON uom_w.id = u.weight_uom_id ";
		$sql .= "LEFT OUTER JOIN user_profile_media_last_entered media ";
		$sql .= "ON media.user_id = u.id ";
		$sql .= "WHERE u.id = " . $p_user_id . " ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$row = $query->row(); 
		
		// print_r($row);
		
		$user = new stdClass();
		$user->id = cast_int($p_user_id);
		$user->email = $row->email;
		$user->first_name = $row->first_name;
		$user->last_name = $row->last_name;
		$user->phone = $row->phone;
		$user->gender = $row->gender;
		$user->birthday = cast_int($row->birthday);
		$user->password = $row->password;
		$user->token = $row->token;
		$user->height = new stdClass();
		if ( !is_null($row->height) ) {
			$user->height->value = cast_real($row->height);
			$user->height->id = $row->height_uom;
		}
		$user->weight = new stdClass();
		if ( !is_null($row->weight) ) {
			$user->weight->value = cast_real($row->weight);
			$user->weight->id = $row->weight_uom;
		}
		$user->photo = $row->media_url;
	
		return $this->return_handler->results(200,"",$user);
	}

	public function getCalendarsForUser($p_user_id) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "client.id client_id, client.name client_name, ";
		$sql .= "cu.id client_user_id, ";
		$sql .= "r.name client_user_role, ";
		$sql .= "cal.* ";
		$sql .= "FROM ";
		$sql .= "client_user cu ";
		$sql .= "LEFT OUTER JOIN client_user_role r ";
		$sql .= "ON r.id = cu.client_user_role_id, ";
		$sql .= "calendar cal, ";
		$sql .= "client client ";
		$sql .= "WHERE cu.user_id = " . $p_user_id . " ";
		$sql .= "AND cal.client_id = cu.client_id ";
		$sql .= "AND client.id = cu.client_id ";
		$sql .= "ORDER BY client.name, client.id, cal.name, cal.id ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$clients = array();
		$client = new stdClass();
		$client->id = null;
		foreach($rows as $row){
			if ( is_null($client->id) || $row->client_id != $client->id ) {
				if ( !is_null($client->id) ) {
					array_push($clients,$client);
					unset($client);
					$client = new stdClass();
				}
				$client->id = cast_int($row->client_id);
				$client->name = $row->client_name;
				$client->client_user = new stdClass();
				$client->client_user->id = cast_int($row->client_user_id);
				$client->client_user->role = $row->client_user_role;
				$client->calendars = array();
			}
			if ( !is_null($row->id) ) {
				$calendar = new stdClass();
				$calendar->id = cast_int($row->id);
				$calendar->name = $row->name;
				$calendar->timezone = $row->timezone;
				array_push($client->calendars,$calendar);
				unset($calendar);
			}
		}
		
		if ( !is_null($client->id) ) {
			array_push($clients,$client);
			unset($client);
		}
		
		return $this->return_handler->results(200,"",$clients);
	}

	public function getWorkoutLogsForCalendarClientUser($p_calendar_id,$p_client_user_id) {
		// get the needed values for the url :
		// incomplete workout log : http://alpha.workoutinbox.com/app/#user/track?date=20130211&view=log&detail=log_builder&workout_id=112&log_id=1
		
		$sql  = "";
		// get the incomplete workout_logs
		$sql .= "( ";
		$sql .= "SELECT ";
		$sql .= "ev.name event_name, ev.start event_start, ";
		$sql .= "p.id participation_id, ";
		$sql .= "l.id workout_log_id, ";
		$sql .= "w.id workout_id, w.name workout_name ";
		$sql .= "FROM ";
		$sql .= "calendar_event_participation p, ";
		$sql .= "calendar_event ev, ";
		$sql .= "workout_log l, ";
		$sql .= "library_workout w ";
		$sql .= "WHERE p.client_user_id = " . $p_client_user_id . " ";
		$sql .= "AND ev.id = p.calendar_event_id ";
		$sql .= "AND ev.calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND l.calendar_event_participation_id = p.id ";
		$sql .= "AND NOT l.workout_log_completed ";
		$sql .= "AND w.id = l.library_workout_id ";
		$sql .= ") ";
		// union
		$sql .= "UNION ";
		// get the pending workout_logs
		$sql .= "( ";
		$sql .= "SELECT ";
		$sql .= "ev.name event_name, ev.start event_start, ";
		$sql .= "p.id participation_id, ";
		$sql .= "null workout_log_id, ";
		$sql .= "w.id workout_id, w.name workout_name ";
		$sql .= "FROM ";
		$sql .= "calendar_event_participation p, ";
		$sql .= "calendar_event ev, ";
		$sql .= "workout_log_pending pend, ";
		$sql .= "library_workout w ";
		$sql .= "WHERE p.client_user_id = " . $p_client_user_id . " ";
		$sql .= "AND ev.id = p.calendar_event_id ";
		$sql .= "AND ev.calendar_id = " . $p_calendar_id . " ";
		$sql .= "AND pend.calendar_event_participation_id = p.id ";
		$sql .= "AND w.id = pend.library_workout_id ";
		$sql .= ") ";
		$sql .= "ORDER BY event_start DESC, workout_name ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach($rows as $row){
			$entry = new stdClass();
			$entry->event_name = $row->event_name;
			$entry->event_start = $row->event_start;
			$entry->participation_id = cast_int($row->participation_id);
			$entry->workout_log_id = cast_int($row->workout_log_id);
			$entry->workout_id = cast_int($row->workout_id);
			$entry->workout_name = $row->workout_name;
			array_push($entries,$entry);
			unset($entry);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	public function formatEmailBody( $p_data ) {
		// initialize the html and text bodies
		$html_body = "";
		$text_body = "";
		
		if ( is_null($p_data->user->password) || empty($p_data->user->password) ) {
			$line = 'Welcome to WorkoutInbox for ' . $p_data->clients[0]->name . '! Your account has been created. Now it will be easier than ever to track, analyze and improve your workouts!';
			$html_body .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">' . $line . '</p>';
			$text_body .= '\n' . $line . '\n';
			
			// Format the change password url
			$this->load->helper('access');
			$url = 'http://' . $this->host . '/app/#login?action=setpwd&token=' . encrypt_email_token($p_data->user->email,$p_data->user->token);
			
			$html_body .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">Below you will find your login credentials.<br /><b>Username: ' . $p_data->user->email . '</b><br /><b>Password: <a href="' . $url . '">Click here to set your password</a></b></p>';
			$text_body .= 'Below you will find your login credentials.\nUsername: ' . $p_data->user->email . '\nPassword: go to ' . $url . ' to set your password\n\n';
			
		}
		
		foreach( $p_data->clients as $client ) {
			foreach( $client->calendars as $calendar ) {
				if ( count($calendar->workout_logs) > 0 ) {
					// echo json_encode($calendar->workout_logs);
					// set the timezone
					date_default_timezone_set($calendar->timezone);
					// header
					$line = 'This is a friendly reminder to log your recent workout(s) at ' . $client->name . ' - ' . $calendar->name . ':';
					$html_body .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">' . $line . '</p>';
					$text_body .= '\n' . $line . '\n';
					
					$html_body .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">';
					foreach( $calendar->workout_logs as $log ) {
						if ( is_null($log->workout_log_id) ) {
							$url = $this->host . '/app/#user/track?date=' . date('Ymd',$log->event_start) . '&view=log&detail=log_builder&workout_id=' . $log->workout_id . '&participation_id=' . $log->participation_id;
						} else {
							$url = $this->host . '/app/#user/track?date=' . date('Ymd',$log->event_start) . '&view=log&detail=log_builder&workout_id=' . $log->workout_id . '&log_id=' . $log->workout_log_id;
						}
						$event_workout = date('n/j/Y g:i a',$log->event_start) . ' ' . $log->event_name . ' - ' . $log->workout_name;
						$html_body .= '<a href="http://' . $url . '">' . $event_workout . '</a><br />';
						$text_body .= '\n' . $event_workout . " go to " . $url;
					}
					$html_body .= "</p>";
					$text_body .= '\n';
				}
			}
		}

		// Get a list of missing User data
		$return = $this->perform('this->getMissingUserData',$p_data->user);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$missing_user_data = $return['response'];
		
		if ( count($missing_user_data) > 0 ) {
			// Format missing profile data
			$line = 'Please <a href="http://' . $this->host . '/app/#user/info">update</a> the following information in your profile: <b>' . implode(', ', $missing_user_data) . '<b/>';
			$html_body .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">' . $line . '</p>';
			$text_body .= $line . "\n";
		} else {
			// Format update your profile
			$html_body .= '<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">Click <a href="http://' . $this->host . '/app/#user/info">here</a> to review and update your profile.<p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #000;">';
			$text_body .= 'Go to ' . $this->host . '/app/#user/info?id=' . $p_data->user->id . ' to review and update your profile.\n\n';
		}
		
		$response = new stdClass();
		$response->html_body = $html_body;
		$response->text_body = $text_body;
		
		return $this->return_handler->results(200,"",$response);
	}
	
	public function getMissingUserData( $p_user ) {
		$fields = array();
		foreach( $p_user as $key => $value ) {
			if ( $key == 'token' ) {
				continue;
			} else if ( is_array($value) || is_object($value) ) {
				// empty object or array
				if ( count((array) $value) == 0 ) {
					$fields[] = $key;
					continue;
				}
			} else if ( is_null($value) ) {
				// field with NULL value
				$fields[] = $key;
				continue;
			} else if ( is_string($value) ) {
				// empty string or string with spaces
				if ( trim($value) == '' ) {
					$fields[] = $key;
				}
			}
		}
		
		return $this->return_handler->results(200,"",$fields);
	}
}
?>