<?php

class action_facebook extends action_generic {
	
	protected $test_mode = null;
	protected $server = null;
	
	
	public function __construct() {
		parent::__construct();
		
		$this->load->helper('facebook_php_sdk');
 		
		$this->facebook = new Facebook(array(
		  'appId'  => FACEBOOK_API_LOGIN_ID,
		  'secret' => FACEBOOK_TRANSACTION_KEY,
		));
		
		// Setup class variables test_mode and server based on whether you are running on the production system or a development system.
		if ( isset($_SERVER['SERVER_NAME']) ) {
			$host = $_SERVER['SERVER_NAME'];
			$name = explode('.', $host);
			if ( $host == "workoutinbox.com" || $host == "www.workoutinbox.com" ) {
				// production
				$this->test_mode = false;
				$this->server = 'prod';
			} else {
				$name = explode('.',$host);
				if ( $name[1] == "workoutinbox" && $name[2] == "com" ) {
					// known development
					$this->test_mode = true;
					$this->server = $name[0];
				}
			}
		} else {
			$host = php_uname('n');
			if ( $host == 'workoutinbox.com' ) {
				// production
				$this->test_mode = false;
				$this->server = 'prod';
			} else {
				// known development
				$this->test_mode = true;
				$this->server = $host;
			}
		}
	}

	// ==================================================================================================================
	// Receive short-lived token and make http get request to fb api to get extended user token
	// ==================================================================================================================

	public function extendUserToken($p_fb_user_token) {
		// echo "action_member->update fields:"; print_r($p_fields); echo "<br />";
		$app_id = FACEBOOK_API_LOGIN_ID;
		$app_secret = FACEBOOK_TRANSACTION_KEY;
		
		$url = "https://graph.facebook.com";
		$url .= "/oauth/access_token?";
		$url .= "grant_type=fb_exchange_token";
		$url .= "&client_id=" . $app_id;
		$url .= "&client_secret=" . $app_secret ;
		$url .= "&fb_exchange_token=" . $p_fb_user_token ;
		
		$return = $this->perform('rest_request->get',$url);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		if ( $return['response']->data == null ) {
			return $this->return_handler->results(201,"Not Successfull",$return['response']);
		}
		$data = $return['response']->data;
		
		$return = new stdClass();
		foreach (explode('&', $data) as $chunk) {
		    $param = explode("=", $chunk);
		
		    if ($param && $param[0] == "access_token") {
		        //printf("Value for parameter \"%s\" is \"%s\"<br/>\n", urldecode($param[0]), urldecode($param[1]));
				$return->access_token = $param[1];
		    }
		}
		//$return['response']->data = json_decode($return['response']->data);  // Convert the data from a json string to an object.
		
		return $this->return_handler->results(200,"Successfull",$return);
	}


	// ==================================================================================================================
	// Make a facebook post to all Clients who have Facebook page ids and a wod for today
	// ==================================================================================================================
	
	public function postWorkoutToFacebook() {
		$ccyymmdd = date('Ymd',time());
		$fmt_date = date('l, F j Y');
		$return = $this->perform('action_client->getForWithFacebookAndWOD',$ccyymmdd);
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$clients = $return['response'];
		
		// print_r($clients);
		foreach( $clients as $client ) {
			$return = $this->perform('this->submitPost',$client, $ccyymmdd, $fmt_date);
			if ( $return > 200 ) {
				return $return;
			}
		}
		
		return $this->return_handler->results(200,"Posted to " . count($clients) . " Clients.",array());
	}

	// ==================================================================================================================
	// Make a facebook post to indicated page 
	// ==================================================================================================================

	public function submitPost($p_client, $p_ccyymmdd, $p_fmt_date) {
		//print_r($c_info);
		$fb_page_id = $p_client->fb_page_id;
		$fb_page_token = $p_client->fb_page_token;
		
		$host = "";
		if ( $this->server != "prod" ) {
			$host = $this->server . ".";
		}
		
		$attachment = array(
		    'access_token' => $fb_page_token,
		    'message' => '',
		    'name' => 'Workout for '. $p_fmt_date . '.',
		    'link' => 'http://' . $host . 'workoutinbox.com/widget/#schedule?token=' . $p_client->widget_token . '&sfw=true&view=workout&date=' . $p_ccyymmdd . '',
		    'description' => 'WorkoutInbox is an evidence based platform that is bringing science to the fitness industry. Track your workouts, view class leaderboards and more!',
		    'picture' => 'http://alpha.workoutinbox.com/widget/themes/workoutinbox/images/wod/facebook_share_image.png'
		);
		
		
		try{
			$res = $this->facebook->api('/'.$fb_page_id.'/feed','POST',$attachment);
		} catch(FacebookApiException $e) {
			$result = $e->getResult();
			// error_log(json_encode($result));
			return $this->return_handler->results(400,"",$result);
			
		}
		
		return $this->return_handler->results(200,"",$res);
	}
}