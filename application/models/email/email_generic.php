<?php
class email_generic extends common_perform {

	protected $support_email;
	protected $test_mode = true;
	protected $server = 'x';
	protected $system = 'wk';
	protected $tag_prefix = 'wk-x';
	
	protected $host = "unknow";

	public function __construct() {
		// echo "-email_generic start-";
		parent::__construct();

		// load the CritSend library
		// $this->load->library("MxmConnect");
		// load the codeigniter file helper
		$this->load->helper('file');
		
		// Setup the support email address
		$this->support_email = "support" . '@' . "workoutinbox.com";

		// Setup class variables based on whether you are running on the production system or a development system.
		if ( isset($_SERVER['SERVER_NAME']) ) {
			$this->host = $_SERVER['SERVER_NAME'];
			$name = explode('.', $_SERVER['SERVER_NAME']);
			if ( count($name) == 2  ) {
				if ( $name[0] == "workoutinbox" && $name[1] == "com" ) {
					// production
					$this->test_mode = false;
					$this->server = 'p';
					$this->system = 'wk';
					$this->tag_prefix = $this->system . '-' . $this->server;
				}
			} else if ( count($name) == 3 ) {
				if ( $name[1] == "workoutinbox" && $name[2] == "com" ) {
					if ( $name[0] == 'www' ) {
						// production
						$this->test_mode = false;
						$this->server = 'p';
						$this->system = 'wk';
						$this->tag_prefix = $this->system . '-' . $this->server;
					} else {
						// known development
						$this->test_mode = true;
						$this->server = $name[0][0];
						$this->system = 'wk';
						$this->tag_prefix = $this->system . '-' . $this->server;
					}
				}
			}
		} else {
			$this->host = php_uname('n');
			if ( php_uname('n') == 'workoutinbox.com' ) {
				// production
				$this->test_mode = false;
				$this->server = 'p';
				$this->system = 'wk';
				$this->tag_prefix = $this->system . '-' . $this->server;
			} else {
				$this->host .= '.workoutinbox.com';
				// known development
				$this->test_mode = true;
				$this->server = $this->server[0];
				$this->system = 'wk';
				$this->tag_prefix = $this->system . '-' . $this->server;
			}
		}

		// print_r($this);
		// echo "-email_generic end-";
	}

	public function from_support() {
		// setup the array for support
		$from =  array(
						'tag'=> array($this->tag_prefix),
		                'mailfrom'=>$this->support_email,
		                'mailfrom_friendly'=>'WorkoutInbox Support',
		                'replyto'=>$this->support_email,
		                'replyto_friendly'=>'WorkoutInbox Support',
		                'replyto_filtered'=>'true'
		              );
		return $from;
	}
	
	public function get_valid_email($p_email) {
		if ( isset($_SERVER['SERVER_NAME']) ) {
			if ( $_SERVER['SERVER_NAME'] == "workoutinbox.com" || $_SERVER['SERVER_NAME'] == "www.workoutinbox.com" ) {
				return $p_email;
			} else {
				return $this->support_email;
			}
		} else {
			if ( php_uname('n') == 'workoutinbox.com' ) {
				return $p_email;
			} else {
				return $this->support_email;
			}
		}
	}
	
	public function format_subject($p_subject) {
		if ( isset($_SERVER['SERVER_NAME']) ) {
			if ( $_SERVER['SERVER_NAME'] == "workoutinbox.com" || $_SERVER['SERVER_NAME'] == "www.workoutinbox.com" ) {
				return $p_subject;
			} else {
				return $this->host . " - " . $p_subject;
			}
		} else {
			if ( php_uname('n') == 'workoutinbox.com' ) {
				return $p_subject;
			} else {
				return $this->host . " - " . $p_subject;
			}
		}
	}

	public function replace_workoutinbox_vars($content,$data) {
		foreach( $data as $key => $value ) {
			// now replace <workoutinbox var="$key" /> with $value every where in $content
			$content = str_replace('<workoutinbox var="' . $key . '" />',$value,$content);
		}
		return $content;
	}

	public function format_html($html,$data) {
		if ( is_object($data) || is_array($data) ) {
			$html .= '<div style="margin:5px 0px 5px 25px">';
			foreach( $data as $key => $value ) {
				if ( is_object($value) || is_array($value) ) {
					$html .= $key . " : <br />";
					$html = $this->format_html($html,$value);
				} else {
					$html .= $key . " : " . $value . "<br />";
				}
			}
			$html .= '</div>';
		} else {
			$html .= $value . "<br />";
		}
		return $html;
	}

	public function format_text($text,$title,$data) {
		if ( is_object($data) || is_array($data) ) {
			foreach( $data as $key => $value ) {
				if ( is_object($value) || is_array($value) ) {
					$text = $this->format_text($text,$title . "." . $key,$value);
				} else {
					$text .=  $title . "." . $key . " : " . $value . "\n";
				}
			}
		} else {
			$text .=  $title .  " : " . $value . "/n";
		}
		return $text;
	}

	public function format_name($p_user) {
		$name = "";
		$first_name_len = strlen($p_user->first_name);
		$last_name_len = strlen($p_user->last_name);
		if ( $first_name_len > 0 && $last_name_len > 0 ) {
			$name = trim($p_user->first_name) . " " . trim($p_user->last_name);
		} else if ( $first_name_len > 0 ) {
			$name = trim($p_user->first_name);
		} else if ( $last_name_len > 0 ) {
			$name = trim($p_user->last_name);
		} else {
			$name = "Member at Workoutinbox";
		}
		
		return $name;
	}

	public function send_email( $p_content,$p_param,$p_emails ) {

		// -------------------------------------------------------------------------------------------
		// store the email to the email log
		// -------------------------------------------------------------------------------------------
		// store the email's log without tags
		$return = $this->perform('table_workoutdb_email_log->insert',$email_status='Sending',$p_content,$p_param,$p_emails);
		// echo "return post_email_log:"; print_r($return); echo "<br />";
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$email_log_id = $return['response']->id;

		$response = new stdClass();
		$response->id = $email_log_id;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Now that you know the email log id, add it to the email's tags
		$p_param['tag'][] = $this->tag_prefix . '-Email-' . $email_log_id;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// update the email's log, thus storing the tags with the email log.
		$return = $this->perform('table_workoutdb_email_log->update',$email_log_id,$email_status='Sending',$p_content,$p_param,$p_emails);
		// echo "return put_email_log:"; print_r($return); echo "<br />";
		if ( $return['status'] >= 300 ) {
			return $this->return_handler->results($return['status'],$return['message'],$response);
		}

		// -------------------------------------------------------------------------------------------
		// Send the email
		// -------------------------------------------------------------------------------------------
		$send_email_return = $this->sendMXMConnectEmail($p_content,$p_param,$p_emails);
		// echo "return sendMXMConnectEmail:"; print_r($return); echo "<br />";
		if ( $send_email_return['status'] >= 300 ) {
			// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			// Update the email's log status to failed
			$return = $this->perform('table_workoutdb_email_log->update',$email_log_id,'Failed',$p_content,$p_param,$p_emails);
		    // echo "return put_email_log:"; print_r($return); echo "<br />";
			if ( $return['status'] >= 300 ) {
				return $this->return_handler->results($ruturn['status'],$return['message'],$response);
			}
		}

		// -------------------------------------------------------------------------------------------
		// Update the email's log status to OK
		// -------------------------------------------------------------------------------------------
		$return = $this->perform('table_workoutdb_email_log->update',$email_log_id,'Sent',$p_content,$p_param,$p_emails);
		// echo "return put_email_log:"; print_r($return); echo "<br />";
		if ( $return['status'] >= 300 ) {
			return $this->return_handler->results($ruturn['status'],$return['message'],$response);
		}

		return $this->return_handler->results(200,"Email Sent",$response);
	}

	public function sendMXMConnectEmail( $p_content,$p_param,$p_emails ) {
		// Send the emails
		$this->load->helper('critsend');
		$mxm = new MxmConnect();
		try {
			$result = $mxm->sendCampaign($p_content,$p_param,$p_emails);
			if ( $result ) {
				return $this->return_handler->results(200,"Email Sent",new stdClass());
			} else {
				return $this->return_handler->results(400,"Email could not be sent by CritSend",new stdClass());
			}
		} catch (MxmException $e) {
			$result = $e->getMessage();
			return $this->return_handler->results(400,"Email could not be sent",$result);
		}

	}
	
	public function utf8RawUrlDecode_object($p_object) {
		$utf8RawUrlDecode_object = new stdClass();
		foreach ( $p_object as $key => $value ) {
			if ( is_string($value) ) {
				$utf8RawUrlDecode_object->{$key} = $this->utf8RawUrlDecode($value);
			}
		}
		return $utf8RawUrlDecode_object;
	}
	
	function utf8RawUrlDecode ($source) {
		$decodedStr = '';
		$pos = 0;
		$len = strlen ($source);
		while ($pos < $len) {
			$charAt = substr ($source, $pos, 1);
			if ($charAt == '%') {
				$pos++;
				$charAt = substr ($source, $pos, 1);
				if ($charAt == 'u') {
					// we got a unicode character
					$pos++;
					$unicodeHexVal = substr ($source, $pos, 4);
					$unicode = hexdec ($unicodeHexVal);
					$entity = "&#". $unicode . ';';
					$decodedStr .= utf8_encode ($entity);
					$pos += 4;
				} else {
					// we have an escaped ascii character
					$hexVal = substr ($source, $pos, 2);
					$decodedStr .= chr (hexdec ($hexVal));
					$pos += 2;
				}
			} else {
				$decodedStr .= $charAt;
				$pos++;
			}
		}
		return $decodedStr;
	}
}
?>