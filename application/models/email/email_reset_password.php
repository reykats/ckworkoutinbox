<?php
class email_reset_password extends email_generic {

	public function __construct() {
		parent::__construct();

	}

	public function sendEmail($p_user) {
		echo "email_reset_password->sendEmail User:"; print_r($p_user);
		// -------------------------------------------------------------------------------------------
		// Format the Email
		// -------------------------------------------------------------------------------------------
		// Format the User's name
		$formatted_name = $this->format_name($p_user);
		// Format the change password url
		$this->load->helper('access');
		$url = "http://" . $this->host . "/app/#login?action=setpwd&token=" . encrypt_email_token($p_user->email,$p_user->token);
		// create html replacement object
		$html_values = new stdClass();
		$html_values->member_name = $formatted_name;
		$html_values->token_url = $url;
		// -------------------------------------------------------------------------------------------
		// get the the template file (the base directory is private)
		// -------------------------------------------------------------------------------------------
		$filename = '../public/template/email/ResetPassword/ResetPassword.html';
		$html_template = file_get_contents($filename);
		// echo $html_template . "<br />";
		
		$filename = '../public/template/email/ResetPassword/Body.html';
		$html_body_template = file_get_contents($filename);
		// echo $html_body_template . "<br />";
		
		$filename = '../public/template/email/ResetPassword/Body.txt';
		$text_body_template = file_get_contents($filename);
		// echo $text_body_template . "<br />";
		// -------------------------------------------------------------------------------------------
		// Use the templates and create the html and text content of the email
		// -------------------------------------------------------------------------------------------
		$html_body = $this->replace_workoutinbox_vars($html_body_template,$html_values);
		$html_content = str_replace('<workoutinbox var="html_content" />',$html_body,$html_template);
		
		$text_content = $this->replace_workoutinbox_vars($text_body_template,$html_values);
		
		// echo $html_content . "<br />";
		// echo $text_content . "<br />";
		// -------------------------------------------------------------------------------------------
		// format the email subject
		// -------------------------------------------------------------------------------------------
		$subject = $this->format_subject('WorkoutInbox Account Info Change');
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
			0=>array('email'=> $this->get_valid_email($p_user->email) )
		);
		// echo "emails:"; print_r($emails); echo "<br /><br />";

		// -------------------------------------------------------------------------------------------
		// Send and log the email
		// -------------------------------------------------------------------------------------------
		$return = $this->send_email($content,$param,$emails);

		return $return;
	}
}
?>