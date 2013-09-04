<?php
class email_gt_30_day extends email_generic {

	public function __construct() {
		parent::__construct();

	}
	
	public function sendOneTimeEmail() {
		// echo "email_one_time->sendOneTimeEmail<br />";
		// -------------------------------------------------------------------------------------------
		// get a list of client_users to send the email to
		// -------------------------------------------------------------------------------------------
		$return = $this->getMemberListForClient($p_client=3);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'invalid client used',new stdClass());
		}
		// -------------------------------------------------------------------------------------------
		// Send the Email to the list of client_users
		// -------------------------------------------------------------------------------------------
		$cnt = 0;
		foreach( $return['response'] as $client_user ) {
			$return = $this->sendEmail($client_user->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			++$cnt;
		}
		
		return $this->return_handler->results(200,"",$cnt);
	}

	public function getMemberListForClient($p_client_id) {
		$date = time() - 4 * 7 * 24 * 60 * 60;
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "c.id, MAX(ev.start) last_participation ";
		$sql .= "FROM ";
		$sql .= "client_user c, ";
		$sql .= "calendar_event_participation p, ";
		$sql .= "calendar_event ev ";
		$sql .= "WHERE c.client_id = " . $p_client_id . " AND c.deleted IS NULL ";
		$sql .= "AND p.client_user_id = c.id ";
		$sql .= "AND ev.id = p.calendar_event_id ";
		$sql .= "GROUP BY c.id ";
		$sql .= "HAVING MAX(ev.start) < " . $date . " ";
		if ( $this->test_mode ) {
			$sql .= "LIMIT 2 ";
		}

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
		$entries = array();
		foreach($rows as $row){
			$entry = new stdClass();
			$entry->id = (int) $row->id;
			array_push($entries,$entry);
			unset($entry);
		}
		
		return $this->return_handler->results(200,"",$entries);
	}

	public function sendEmail($p_client_user_id) {
		// echo "email_account_status_change->sendEmail client_user:$p_client_user_id<br />";
		// -------------------------------------------------------------------------------------------
		// Format the Email
		// -------------------------------------------------------------------------------------------
		// Get the detail information about the client_user, user, and client for the client_user
		$return = $this->perform('this->getInformationForClientUser',$p_client_user_id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'invalid client_user used',new stdClass());
		}
		$values = $return['response'];
		// Format the User's name and add it to the user object
		$values->member_name = $this->format_name($values);
		// -------------------------------------------------------------------------------------------
		// get the the template file (the base directory is private)
		// -------------------------------------------------------------------------------------------
		$filename = '../public/template/email/GT30Day/Form.html';
		$html_template = file_get_contents($filename);
		// echo $html_template . "<br />";
		
		$filename = '../public/template/email/GT30Day/Body.html';
		$html_body_template = file_get_contents($filename);
		// echo $html_body_template . "<br />";
		
		$filename = '../public/template/email/GT30Day/Body.txt';
		$text_body_template = file_get_contents($filename);
		// echo $text_body_template . "<br />";
		// -------------------------------------------------------------------------------------------
		// Format the body of the Email
		// -------------------------------------------------------------------------------------------
		// Use the templates and create the html and text content of the email
		// -------------------------------------------------------------------------------------------
		$html_body = $this->replace_workoutinbox_vars($html_body_template,$values);
		$html_content = str_replace('<workoutinbox var="html_content" />',$html_body,$html_template);
		
		$text_content = $this->replace_workoutinbox_vars($text_body_template,$values);
		
		// echo $html_body . "<br />";
		// echo $html_content . "<br />";
		// echo $text_content . "<br />";
		// -------------------------------------------------------------------------------------------
		// format the email subject
		// -------------------------------------------------------------------------------------------
		$subject = $this->format_subject('Important Info About Your ' . $values->client_name . ' WorkoutInbox Account');
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
		$param['tag'][] = $this->tag_prefix . '-user-' . $values->user_id;
		// echo "param:"; print_r($param); echo "<br /><br />";

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the email 'to address' array ( if this is the test system use "support" )
		$emails = array(
			0=>array('email'=> $this->get_valid_email($values->email) )
		);
		// echo "emails:"; print_r($emails); echo "<br /><br />";

		// -------------------------------------------------------------------------------------------
		// Send and log the email
		// -------------------------------------------------------------------------------------------
		$return = $this->send_email($content,$param,$emails);

		return $return;
	}
	
	public function getInformationForClientUser($p_client_user_id) {
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "u.id user_id, u.email,u.first_name, u.last_name, ";
		$sql .= "c.name client_name ";
		$sql .= "FROM client_user cu, ";
		$sql .= "user u, ";
		$sql .= "client c ";
		$sql .= "WHERE cu.id = " . $p_client_user_id . " ";
		$sql .= "AND u.id = cu.user_id ";
		$sql .= "AND c.id = cu.client_id ";

		// echo "$sql<br />";
		
		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$row = $query->row(); 
		
		return $this->return_handler->results(200,"",$row);
	}
}
?>