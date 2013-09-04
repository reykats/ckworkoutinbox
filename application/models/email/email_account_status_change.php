<?php
class email_account_status_change extends email_generic {

	public function __construct() {
		parent::__construct();

	}

	public function sendEmail($p_user,$p_client_user) {
		// echo "email_account_status_change->sendEmail<br />";
		// sprint_r($p_user); print_r($p_client_user);
		// -------------------------------------------------------------------------------------------
		// Format the Email
		// -------------------------------------------------------------------------------------------
		// Get the detail information about the client_user, user, and client for the client_user
		$return = $this->perform('this->getInformationForClientUser',$p_client_user->id);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,'invalid client_user used',new stdClass());
		}
		$user = $return['response'];
		// Format the User's name and add it to the user object
		$user->member_name = $this->format_name($user);
		// Format the change password url
		$this->load->helper('access');
		$user->token_url = "http://" . $this->host . "/app/#login?action=setpwd&token=" . encrypt_email_token($user->email,$user->token);
		// -------------------------------------------------------------------------------------------
		// get the the template file (the base directory is private)
		// -------------------------------------------------------------------------------------------
		$filename = '../public/template/email/AccountStatusChange/AccountStatusChange.html';
		$html_template = file_get_contents($filename);
		// echo $html_template . "<br />";
		
		$filename = '../public/template/email/AccountStatusChange/Body.html';
		$html_body_template = file_get_contents($filename);
		// echo $html_body_template . "<br />";
		
		$filename = '../public/template/email/AccountStatusChange/Body.txt';
		$text_body_template = file_get_contents($filename);
		// echo $text_body_template . "<br />";
		// -------------------------------------------------------------------------------------------
		// Use the templates and create the html and text content of the email
		// -------------------------------------------------------------------------------------------
		$html_body = $this->replace_workoutinbox_vars($html_body_template,$user);
		$html_content = str_replace('<workoutinbox var="html_content" />',$html_body,$html_template);
		
		$text_content = $this->replace_workoutinbox_vars($text_body_template,$user);
		
		// echo $html_body . "<br />";
		// echo $html_content . "<br />";
		// echo $text_content . "<br />";
		// -------------------------------------------------------------------------------------------
		// format the email subject
		// -------------------------------------------------------------------------------------------
		$subject = $this->format_subject('Important Info About Your ' . $user->client_name . ' WorkoutInbox Account');
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
		$param['tag'][] = $this->tag_prefix . '-user-' . $p_user->id;
		// echo "param:"; print_r($param); echo "<br /><br />";

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the email 'to address' array ( if this is the test system use "support" )
		$emails = array(
			0=>array('email'=> $this->get_valid_email($user->email) )
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
		$sql .= "u.email,u.first_name, u.last_name, u.token, u.email, ";
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