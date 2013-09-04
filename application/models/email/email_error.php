<?php
class email_error extends email_generic {

	public function __construct() {
		parent::__construct();

	}

	public function sendEmail($p_method,$p_url,$p_input_data,$p_message) {
		// echo "url:$p_url<br />";
		// echo "message:$p_message<br />";
		// -------------------------------------------------------------------------------------------
		// Get the session data
		// -------------------------------------------------------------------------------------------
		$return = $this->perform('action_login->getSessionUserdata');
		if ( $return['status'] != 200 ) {
			return $return;
		}
		$session = clone $return['response'];
		// echo "session:"; print_r($session); echo "<br />";

		// -------------------------------------------------------------------------------------------
		// Format the System Error Email
		// -------------------------------------------------------------------------------------------
		// Format the client_user object for display in the html and text version of the email
		$text_session = $this->format_text('','session',$session);
		$html_session = $this->format_html('',$session);
		// echo $html_client_user . "<br />";
		// echo $text_client_user . "<br />";

		// create html replacement object
		$html_values = new stdClass();
		$html_values->method = $this->utf8RawUrlDecode($p_method);
		$html_values->url = $this->utf8RawUrlDecode($p_url);
		$html_values->input_data = $this->utf8RawUrlDecode($p_input_data);
		$html_values->message = $this->utf8RawUrlDecode($p_message);
		$html_values->session = $html_session;
		// create text replacement object
		$text_values = new stdClass();
		$text_values->method = $this->utf8RawUrlDecode($p_method);
		$text_values->url = $this->utf8RawUrlDecode($p_url);
		$text_values->input_data = json_encode($this->utf8RawUrlDecode($p_input_data));
		$text_values->message = $this->utf8RawUrlDecode($p_message);
		$text_values->session = $text_session;
		// echo "html_values:"; print_r($html_values); echo "<br />";
		// echo "text_values:"; print_r($text_values); echo "<br />";

		// get the email content from the template files (the base directory is private)
		$html_template_filename = '../public/template/email/SystemError/SystemError.html';
		$html_content = file_get_contents($html_template_filename);
		$text_template_filename = '../public/template/email/SystemError/SystemError.txt';
		$text_content = file_get_contents($text_template_filename);

		// echo $html_content . "<br />";
		// echo $text_content . "<br />";
		// -------------------------------------------------------------------------------------------
		// format the email subject
		// -------------------------------------------------------------------------------------------
		$subject = $this->format_subject('WorkoutInbox System Error');
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the content array
		$content = array(
			'subject'=>$subject,
			'html' => $this->replace_workoutinbox_vars($html_content,$html_values),
			'text' => $this->replace_workoutinbox_vars($text_content,$text_values)
		);
		// echo "content:"; print_r($content); echo "<br /><br />";

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the parameter array for support
		$param = $this->from_support();
		// Add the user id to the tags
		$param['tag'][] = $this->tag_prefix . '-user-' . $session->user_id;
		// echo "param:"; print_r($param); echo "<br /><br />";

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Setup the email 'to address' array
		$emails = array(
			0=>array('email'=>$this->support_email)
		);
		// echo "emails:"; print_r($emails); echo "<br /><br />";

		// echo "content:"; print_r($content); echo "<br />";
		// echo "param:"; print_r($param); echo "<br />";
		// echo "emails:"; print_r($emails); echo "<br />";

		// -------------------------------------------------------------------------------------------
		// Send and log the email
		// -------------------------------------------------------------------------------------------
		$return = $this->send_email($content,$param,$emails);

		return $return;
	}
}
?>