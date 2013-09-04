<?php

class zp_membership extends action_generic {
	
	// The directory and filename of the zenplanner post file
	protected $base_filename = '/zenplanner/posted_trans.log';

	public function __construct() {
		parent::__construct();
		
		$this->base_filename = $this->config->item('workoutinbox_client_data') . $this->base_filename;
	}
	
	public function post($data) {
		//
		// Only process the zenplanner entries that have the correct SECRET code
		if ( !property_exists($data, 'SECRET') || is_null($data->SECRET) || empty($data->SECRET) || $data->SECRET != 'zzx' ) {
			
			$return = $this->return_handler->results(400,"missing or invalid secret code",new stdClass());
			
			$this->log_trans($data,'missing or invalid secret code',$return);
			
			return $return;
		}
		//
		// Only process the zenplanner entries that have the correct UN
		if ( !property_exists($data, 'UN') || is_null($data->UN) || empty($data->UN) || $data->UN != '1' ) {
			
			$return = $this->return_handler->results(400,"missing or invalid UN",new stdClass());
			
			$this->log_trans($data,'missing or invalid UN',$return);
			
			return $return;
		}
		//
		// Only process the zenplanner entries that have an email addresses
		if ( !property_exists($data, 'email') || is_null($data->email) || empty($data->email) ) {
			
			$return = $this->return_handler->results(400,"No Email provided",new stdClass());
			
			$this->log_trans($data,'No Email provided',$return);
			
			return $return;
		}
		//
		// Only process the zenplanner entries that have a "valid" email addresses
		$return = $this->validate_email($data->email);
		if ( $return['status'] >= 300 ) {
			
			$this->log_trans($data,'Invalid Email provided',$return);
			
			return $return;
		}
		// --------------------------------------------------------------------------------------------------------------
		// strip the formatting from the phone
		// --------------------------------------------------------------------------------------------------------------
		$return = $this->perform('this->format_phone',$data->phoneNumber);
		$data->formatted_phone = $return['response'];
		// --------------------------------------------------------------------------------------------------------------
		// Set the data's client based on the "UN" and "email"
		// --------------------------------------------------------------------------------------------------------------
		$part = explode('@',$data->email);
		if ( count($part) >= 2 && strtolower($part[1]) == 'hgst.com' ) {
			$data->client_id = 2;
		} else {
			$data->client_id = $data->UN;
		}
		// --------------------------------------------------------------------------------------------------------------
		// Get the User Id of any user with this email
		// --------------------------------------------------------------------------------------------------------------
		//
		// does a user exist in our system with the email
		$key = array();
		$key['email'] = $data->email;
		$return = $this->perform('table_workoutdb_user->getForAndKeys',$key,'id');
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$email_user = new stdClass();
		if ( $return['status'] > 200 ) {
			$email_user->id = null;
		} else {
			$email_user = clone $return['response'][0];
		}
		//
		if ( is_null($email_user->id) && ($data->status == "STUDENT" || $data->status == "PROSPECT") ) {
			// --------------------------------------------------------------------------------------------------------------
			// the email is not in our sytem, so create the user and client_user
			// --------------------------------------------------------------------------------------------------------------
			$fields = $this->create_user_object($data);
			$return = $this->perform('action_member->create',$fields);
			//
			$this->log_trans($data,'Create user and client_user(' . $return['response']->id . ')',$return);
			//
			return $return;
		}
		// --------------------------------------------------------------------------------------------------------------
		// There is a user with the email
		// --------------------------------------------------------------------------------------------------------------
		// get the client_user with the email address
		$key = array();
		$key['user_id'] = $email_user->id;
		$key['client_id'] = $data->client_id;
		$return = $this->perform('table_workoutdb_client_user->getForAndKeys',$key);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['status'] > 200 ) {  //   user found but, client_user not found
			if ( $data->status == "STUDENT" || $data->status == "PROSPECT" ) {
				// --------------------------------------------------------------------------------------------------------------
				// The client_user is not in our system, so create the client_user if status is STUDENT OR PROSPECT
				// --------------------------------------------------------------------------------------------------------------
				$fields = $this->create_client_user_object($data,$email_user->id);
				
				$return = $this->perform('action_member->create',$fields);
				//
				$this->log_trans($data,'Create client_user(' . $return['response']->id . ')',$return);
				//
				return $return;
			} else {
				// --------------------------------------------------------------------------------------------------------------
				// The client_user is not in our system and the data wants to create an ALUMI, No Action
				// --------------------------------------------------------------------------------------------------------------
				$return = $this->return_handler->results(200,"client_user does not exist and zp status=" . $data->status,new stdClass());
				
				$this->log_trans($data,'NONE',$return);
				
				return $return;
				
			}
		}
		$client_user = clone $return['response'][0];
		if ( is_null($client_user->deleted) && $data->status == "ALUMNI" ) {
			// --------------------------------------------------------------------------------------------------------------
			// deactivate the client_user
			// --------------------------------------------------------------------------------------------------------------
			$return = $this->perform('table_workoutdb_client_user->delete',$client_user->id);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			// --------------------------------------------------------------------------------------------------------------
			// update the client_user
			// --------------------------------------------------------------------------------------------------------------
			$fields = new stdClass();
			$fields->id = $client_user->id;
			$fields->note = 'This member was removed because it was inactivated in ZenPlanner';
			$return = $this->perform('table_workoutdb_client_user->update',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			//
			$this->log_trans($data,'Deactivate client_user(' . $client_user->id . ')',$return);
			//
			return $return;
		}
		if ( !is_null($client_user->deleted) && ($data->status == "STUDENT" || $data->status == "PROSPECT") ) {
			// --------------------------------------------------------------------------------------------------------------
			// reactivate the client_user
			// --------------------------------------------------------------------------------------------------------------
			$fields = new stdClass();
			$fields->client_id = $data->client_id;
			$fields->user_id = $email_user->id;
			$fields->email = $data->email;
			$fields->phone = $data->formatted_phone;
			$fields->first_name = str_replace('  ',' ',trim($data->firstName));
			$fields->last_name = str_replace('  ',' ',trim($data->lastName));
			if ( $data->status == "STUDENT" ) {
				$fields->role_id = 2;
			} else if ( $data->status == "PROSPECT" ) {
				$fields->role_id = 3;
			}
			$fields->note = 'This member was restarted because it was activated in ZenPlanner';
			
			$return = $this->perform('action_member->create',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			//
			$this->log_trans($data,'Restart client_user(' . $client_user->id . ')',$return);
			//
			return $return;
		}
		if ( is_null($client_user->deleted) && $data->status == "STUDENT" && $client_user->client_user_role_id == 3 ) {
			// --------------------------------------------------------------------------------------------------------------
			// update the client_user role from guest to member
			// --------------------------------------------------------------------------------------------------------------
			$fields = new stdClass();
			$fields->id = $client_user->id;
			$fields->role_id = 2;
			$fields->note = 'Changed from Guest to Member to match ZenPlanner';
			$return = $this->perform('table_workoutdb_client_user->update',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			//
			$this->log_trans($data,'From Guest To Member(' . $client_user->id . ')',$return);
			//
			return $return;
		}
		if ( is_null($client_user->deleted) && $data->status == "PROSPECT" && $client_user->client_user_role_id == 2 ) {
			// --------------------------------------------------------------------------------------------------------------
			// update the client_user role from guest to member
			// --------------------------------------------------------------------------------------------------------------
			$fields = new stdClass();
			$fields->id = $client_user->id;
			$fields->role_id = 3;
			$fields->note = 'Changed from Member to Guest to match ZenPlanner';
		
			$return = $this->perform('table_workoutdb_client_user->update',$fields);
			if ( $return['status'] >= 300 ) {
				return $return;
			}
			//
			$this->log_trans($data,'From Member To Guest(' . $client_user->id . ')',$return);
			//
			return $return;
		}
		
		$return = $this->return_handler->results(200,"",new stdClass());
		
		$this->log_trans($data,'NONE',$return);
		
		return $return;
	}
	
	public function create_user_object( $data ) {
		$return = new stdClass();
		$return->email = $data->email;
		$return->phone = $data->formatted_phone;
		$return->first_name = str_replace('  ',' ',trim($data->firstName));
		$return->last_name = str_replace('  ',' ',trim($data->lastName));
		$return->client_id = $data->client_id;
		$return->address = $data->address;
		if ( !empty($data->birthDate) ) {
			$return->birthday = strtotime($data->birthDate);
		} else {
			$return->birthday = null;
		}
		// remove all spaces, get 1st character, upper case
		$return->gender = strtoupper(substr(str_replace(' ','',trim($data->gender)),0,1));
		// if not M or F, return empty
		if ( $return->gender != 'M' && $return->gender != 'F' ) {
			$return->gender = null;
		}
		$return->note = "This user was automatically created because it was created in ZenPlanner";
		if ( $data->status == "STUDENT" ) {
			$return->role_id = 2;
		} else if ( $data->status == "PROSPECT" ) {
			$return->role_id = 3;
		}
		return $return;
	}
	
	public function create_client_user_object( $data, $p_user_id ) {
		$return = new stdClass();
		$return->client_id = $data->client_id;
		$return->user_id = $p_user_id;
		$return->email = $data->email;
		$return->phone = $data->formatted_phone;
		$return->first_name = str_replace('  ',' ',trim($data->firstName));
		$return->last_name = str_replace('  ',' ',trim($data->lastName));
		$return->note = "This member was automatically created because it was created in ZenPlanner";
		if ( $data->status == "STUDENT" ) {
			$return->role_id = 2;
		} else if ( $data->status == "PROSPECT" ) {
			$return->role_id = 3;
		}
		return $return;
	}
	
	public function log_trans($data,$action = null,$return=null) {
		
		/* Open a file for writing */
		$fp_log = fopen($this->base_filename, "a");
		
		if ( !is_object($data) ) {
			$object = new stdClass();
			$object->zen_planner_data = $data;
		} else {
			$object = clone $data;
		}
		/* add action to data */
		$object->action = $action;
		$object->return = $return;
		
		/* Write to the file */
		fwrite($fp_log, json_encode($object) . "\r\n");
		
		/* Close the streams */
		fclose($fp_log);
		
	}

	protected function format_phone( $p_value ) {
		// get all numeric chars
		$value = '';
		$i_len = strlen($p_value);
		for ( $i=0; $i < $i_len; $i++ ) {
			if ( is_numeric(substr($p_value,$i,1)) ) {
				$value .= substr($p_value,$i,1);
			}
		}

		// if there are not 10 numbers, return empty
		if ( strlen($value) != 10 ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " Invalid phone '" . $p_value . "'",null);
		}

		return $this->return_handler->results(200,"",$value);
	}

	protected function validate_email( $p_value ) {
		// remove leading and trailing spaces
		$email = trim($p_value);

		$atIndex = strrpos($email, "@");
		if ( is_bool($atIndex) && !$atIndex ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address missing a @",null);
		} else {
			// get domain and local string
			$domain = substr($email, $atIndex + 1);
			$local = substr($email, 0, $atIndex);
			// get length of domain and local string
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ( $localLen < 1 ) {
				// local part length exceeded
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address prior to @ is missing",null);
			} else if ( $localLen > 64 ) {
				// local part length exceeded
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address prior to @ is more than 64 chars",null);
			} else if ( $domainLen < 1 ) {
				// domain part length exceeded
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address domain missing",null);
			} else if ( $domainLen > 255 ) {
				// domain part length exceeded
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address domain is more than 255 chars",null);
			} else if ( $local[0] == '.' ) {
				// local part starts or ends with '.'
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address starts with '.'",null);
			} else if ( $local[$localLen - 1] == '.' ) {
				// local part starts or ends with '.'
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address prior to @ ends with '.'",null);
			} else if ( preg_match('/\\.\\./', $local) ) {
				// local part has two consecutive dots
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address prior to @ contains '..'",null);
			} else if ( !preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain) ) {
				// character not valid in domain part
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address domain contains invalid character",null);
			} else if ( preg_match('/\\.\\./', $domain) ) {
				// domain part has two consecutive dots
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address domain contains '..'",null);
			} else if ( !preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)) ) {
				// character not valid in local part unless
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
					return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address prior to @ must be quoted due to special character",null);
				}
			}

			if ( !checkdnsrr($domain) && !checkdnsrr($domain,"A") && !checkdnsrr($domain,"AAAA") ) {
				// domain not found in DNS
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address domain not found in DNS",null);
			}
		}

		return $this->return_handler->results(200,"",null);
	}
	
}