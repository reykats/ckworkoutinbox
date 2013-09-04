<?php

class api_widget extends common_perform {

	protected $application = '';
	protected $token = '';
	protected $action = '';
	protected $request_method = '';
	
	protected $client = null;

	public function __construct() {
		parent::__construct();
	}

	public function process($params) {
		// echo "process";
		$params = (array) $params;

		// echo "params:"; print_r($params); echo "<br />";
		// echo "request method:" . $_SERVER['REQUEST_METHOD'] . "<br />";
		// echo "_GET:"; print_r($_GET); echo "<br />";
		// echo "data:"; print_r( json_decode(file_get_contents("php://input")) ); echo "<br />";

		$this->application = array_shift($params); // w_widget
		$this->token = array_shift($params);
		$this->action = array_shift($params);
		if ( isset($_SERVER['REQUEST_METHOD']) ) {
			$this->request_method = $_SERVER['REQUEST_METHOD'];
		} else {
			$this->request_method = "GET";
		}
		/*
		 echo "application:" . $this->application . "<br />";
		 echo "token:" . $this->token . "<br />";
		 echo "action:" . $this->action . "<br />;"
		 echo "request_method:" . $this->request_method . "<br />";
		*/
		$return = $this->perform('this->getClientForToken');
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// print_r($return);
		// store the response to as a class variable
		$this->client = $return['response'];
		
		// print_r($this->client);
		
		// Is the widget call calling a valid process
		if ( !method_exists($this,$this->action) ) {
			return $this->return_handler->results(400,$this->action . " is not a valid widget.",new stdClass() );
		}
		
		// get the POST/PUT data
		$return = $this->get_data();
		if ( $return['status'] >= 300 ) {
			return $return;
		}
		$data = $return['response'];
		
		// Process the Action
		return $this->{$this->action}($params,$data);
	}

	public function getClientForToken() {
		// echo "getWidgetForToken $p_token<br />";
		// ---------------------------------------------------------------------------------------------------------
		// Get the record entries
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "";
		$sql .= "SELECT ";
		$sql .= "c.id, c.name, ";
		$sql .= "IF(cal.timezone IS NOT NULL AND cal.timezone <> '',cal.timezone,s.timezone) timezone ";
		$sql .= "FROM ";
		$sql .= "server s, ";
		$sql .= "client c, ";
		$sql .= "location l, ";
		$sql .= "calendar cal ";
		$sql .= "WHERE c.widget_token = '" . mysql_real_escape_string($this->token) . "' ";
		$sql .= "AND l.client_id = c.id ";
		$sql .= "AND cal.location_id = l.id ";
		$sql .= "limit 1 ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",new stdClass());
		}
		$row = $query->row();
		
		// echo('|' . $row->json_config . '|');
		// print_r($row);
		
		$reponse = new stdClass();
		$response->id = cast_int($row->id);
		$response->name = $row->name;
		$response->timezone = $row->timezone;
		
		return $this->return_handler->results(200,"",$response);
	}
	
	public function ScheduleWOD($params,$data) {

		if ( $this->request_method == 'GET' ) {
			if ( count($params) == 0 ) {
				// ------------------------------------------------------------------
				// getWorkoutLogForId
				// ------------------------------------------------------------------
				return $this->perform('widget_schedule_wod->getScheduleWODForClient',$this->client);
			}
		}
		
		return $this->return_handler->results(500,"Invalid URL parameters",new stdClass());
	}

}
?>