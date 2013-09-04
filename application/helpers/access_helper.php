<?php
	
function generate_password($length) {
	// start with a blank password
	$password = "";

	// define possible characters - any character in this string can be
	// picked for use in the password, so if you want to put vowels back in
	// or add special characters such as exclamation marks, this is where
	// you should do it
	$possible = "1234567890!abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

	// we refer to the length of $possible a few times, so let's grab it now
	$maxlength = strlen($possible);
  
	// check for length overflow and truncate if necessary
	if ($length > $maxlength) {
		$length = $maxlength;
	}
	
	// set up a counter for how many characters are in the password so far
	$i = 0; 
    
	// add random characters to $password until $length is reached
	while ($i < $length) { 

		// pick a random character from the possible ones
		$char = substr($possible, mt_rand(0, $maxlength-1), 1);
        
		// have we already used this character in $password?
		if (!strstr($password, $char)) { 
			// no, so it's OK to add it onto the end of whatever we've already got...
			$password .= $char;
			// ... and increase the counter by one
			$i++;
		}
	}
	return $password;
}
	
function generate_token($length) {
	// start with a blank password
	$password = "";

	// define possible characters - any character in this string can be
	// picked for use in the password, so if you want to put vowels back in
	// or add special characters such as exclamation marks, this is where
	// you should do it
	$possible = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	// we refer to the length of $possible a few times, so let's grab it now
	$maxlength = strlen($possible);
  
	// check for length overflow and truncate if necessary
	if ($length > $maxlength) {
		$length = $maxlength;
	}
	
	// set up a counter for how many characters are in the password so far
	$i = 0; 
    
	// add random characters to $password until $length is reached
	while ($i < $length) { 

		// pick a random character from the possible ones
		$char = substr($possible, mt_rand(0, $maxlength-1), 1);
        
		// have we already used this character in $password?
		if (!strstr($password, $char)) { 
			// no, so it's OK to add it onto the end of whatever we've already got...
			$password .= $char;
			// ... and increase the counter by one
			$i++;
		}
	}
	return $password;
}
	
function generate_expire_date() {
	// add 2 weeks to the current time to ceate the expire date
	$two_weeks = 2 * 7 * 24 * 60 * 60;
	return time() + $two_weeks;
}

function encrypt_email_token($p_email,$p_token) {
	return base64_encode($p_email . ":" . $p_token);
}

function decrypt_email_token($p_string) {
	$string = base64_decode($p_string);
	if ( !$string ) {
		return false;
	}
	// split the email and token
	$part = explode(":",$string);
	if ( count($part) != 2 ) {
		return false;
	}
	
	$response = new stdClass();
	$response->email = $part[0];
	$response->token = $part[1];
	
	return $response;
}
