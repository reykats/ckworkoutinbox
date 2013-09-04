<?php

class cli_user_notification extends action_generic {
	
	private $notification_offset; 
	
	public function __construct() {
		parent::__construct();
		
		$this->notification_offset = 2 * 60 * 60; // 2 hours
	}
	
	// ----------------------------------------------------------------------------------------------------------------------------------------------
	// Put the checkin's user into the User Notification Queue with the timestamp for the end of the event the user checked into 
	// if the even is log_results.
	// ----------------------------------------------------------------------------------------------------------------------------------------------
	
	public function queueCheckin( $p_calendar_event_participation_id ) {
		// get the user and event for the checkin
		$return = $this->perform('action_checkin->getForId',$p_calendar_event_participation_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,"invalid Checkin",new stdClass());
		}
		$checkin = $return['response'];
		// Not a log_results event
		if ( !$checkin->event->log_result ) {
			return $this->return_handler->results(200,"",new stdClass());
		}
		// User does not want log notifications
		if ( !$checkin->user->send_log_notification ) {
			return $this->return_handler->results(200,"",new stdClass());
		}
		
		$end_of_event = $checkin->event->start + $checkin->event->duration;
		return $this->perform('this->insertForUserTimestamp',$checkin->user->id,$end_of_event);
	}
	
	// ----------------------------------------------------------------------------------------------------------------------------------------------
	// Put a User into the user notification queue (p_timestamp is a UTC data/time)
	//
	// If the User is already in the gym and the timestamp is newer than the timestamp on the queue entry, update the queue entry's timestamp.
	// ----------------------------------------------------------------------------------------------------------------------------------------------
	
	public function insertForUserTimestamp( $p_user_id, $p_timestamp ) {
		// initialize the return response
		$response = new stdClass();
		$response->id = null;
		// Does the user already have an entry in the queue
		$key = array();
		$key['user_id'] = $p_user_id;
		$return = $this->perform('table_workoutdb_user_notification_queue->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $this->return_handler->results($return['status'],$return['message'],$response);
		}
		if ( $return['status'] == 200 ) {
			$queue_entry = $return['response'][0];
			$response->id = $queue_entry->id;
			if ( $queue_entry->timestamp < $p_timestamp) {
				// update the entry's time stamp
				$data = new stdClass();
				$data->id = $queue_entry->id;
				$data->timestamp = $p_timestamp;
				$return = $this->perform('table_workoutdb_user_notification_queue->update',$data);
				return $this->return_handler->results($return['status'],$return['message'],$response);
			}
		} else {
			// insert a new user notification entry into the queue
			$data = new stdClass();
			$data->user_id = $p_user_id;
			$data->timestamp = $p_timestamp;
			$return = $this->perform('table_workoutdb_user_notification_queue->insert',$data);
			return $return;
		}
		return $this->return_handler->results(200,"",$response);
	}
		
	// ----------------------------------------------------------------------------------------------------------------------------------------------
	// User Notificaion Queue Daemon process
	// ----------------------------------------------------------------------------------------------------------------------------------------------
	
	public function daemon() {
		$return = $this->perform('this->getForOffset');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$entries = $return['response'];
		
		$emails_sent =  0;
		$emails_not_needed = 0;
		foreach( $entries as $entry ) {
			// sendEmail will return the email_log id of the email is sent ( $return['response']->id == null if email not sent )
			$return = $this->perform('email_user_notification->sendEmail',$entry->user_id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			// print_r($return);
			date_default_timezone_set('America/Los_Angeles');
			if ( !is_null($return['response']->id) ) {
				echo "email sent " . date('Ymd His') . " user_id:" . $entry->user_id . "\n";
				++$emails_sent;
			} else {
				echo "email not needed " . date('Ymd His') . " user_id:" . $entry->user_id . "\n";
				++$emails_not_needed;
			}
			// remove the user from the user_notification_queue
			$return = $this->perform('table_workoutdb_user_notification_queue->delete',$entry->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
		}
		
		$response = new stdClass();
		$response->emails_sent = $emails_sent;
		$response->emails_not_needed = $emails_not_needed;
		return $this->return_handler->results(200,"",$response);
	}
	
	public function getForOffset() {
		// get the queue entries that are ready to be notified
		$sql  = "";
		$sql .= "SELECT * ";
		$sql .= "FROM user_notification_queue ";
		$sql .= "WHERE timestamp < " . (time() - $this->notification_offset) . " ";
		$sql .= "ORDER BY user_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0 ) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		return $this->return_handler->results(200,"",$rows);
	}
	
}
?>