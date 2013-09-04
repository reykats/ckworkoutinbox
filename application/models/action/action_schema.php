<?php

class action_schema extends action_generic {

	public function __construct() {
		parent::__construct();
	}

	// ==================================================================================================================
	// Get the metadata for the web app
	// ==================================================================================================================
	
	public function get() {
		// use the database to load the schema
		mysql_schema::loadSchema('workoutdb');
		
		return mysql_schema::get();
	}
	
	public function refresh() {
		// use the database to load the schema
		mysql_schema::loadSchema('workoutdb');
		
		return mysql_schema::refreshSchemaFile('workoutdb');
	}
	
	public function file() {
		$temp = json_decode(file_get_contents('../private/application/config/schema/workoutdb.json'));
		return $this->return_handler->results(200,'',$temp);
	}
	
	public function display($param) {
		echo "param:$param<br />";
		return $this->return_handler->results(200,"",new stdClass() );
	}
	
	public function lookup() {
		$this->load->library('simplify');
		
		return $this->simplify->printLookupTables();
	}
	
	public function workout() {
		$workout = '{"time_limit":{"id":0,"value":{"input":"30","note":""}},"repeats":{},"set":[{"time_limit":{"id":0,"value":{}},"repeats":{"input":"1","note":""},"break_time":{},"exercise_group":[{"time_limit":{"id":0,"value":{}},"repeats":{"input":"{#brn}*10:1","note":""},"break_time":{},"exercise":{"id":37,"name":"Sit Up","media":{},"distance_measurement":{},"equipment":[{"id":4,"name":"Weight Plate","deletable":true,"media":{},"unit":{"id":3,"man":{},"woman":{"input":"20","note":""}}}]}},{"time_limit":{"id":0,"value":{}},"repeats":{"input":"{#brn}*1:1","note":""},"break_time":{},"exercise":{"id":39,"name":"Bench Press","media":{},"distance_measurement":{},"equipment":[{"id":1,"name":"Barbell","deletable":true,"media":{},"unit":{"id":3,"man":{"input":"{#bbw}*0.01","note":""},"woman":{}}}]}}]}]}';

		$workout = json_decode($workout);

		$this->load->library('simplify');
		return $this->simplify->workout($workout);
	}
	
	public function workout_log() {
		$workout_log = '{"complete_round":{"repeats":{"input":"5","note":""},"time_limit":{},"set":[{"time_limit":{},"repeats":{"input":5,"note":""},"break_time":{},"exercise_group":[{"time_limit":{"id":0,"value":{}},"repeats":{"input":10,"note":""},"break_time":{},"exercise":{"id":57,"name":"Hang Squat Clean","media":{},"distance_measurement":{},"equipment":[{"id":1,"name":"Barbell","deletable":true,"measurement":[1],"media":{},"unit":{"id":3,"man":{"input":155,"note":""},"woman":{"input":110,"note":""}}}]}},{"time_limit":{"id":0,"value":{}},"repeats":{"input":20,"note":""},"break_time":{},"exercise":{"id":33,"name":"Push Up","media":{},"distance_measurement":{},"equipment":[]}}]}]},"incomplete_round":{"repeats":{"input":1,"note":""},"time_limit":{},"set":[{"time_limit":{},"repeats":{"input":1,"note":""},"break_time":{},"exercise_group":[{"time_limit":{"id":0,"value":{}},"repeats":{"input":10,"note":""},"break_time":{},"exercise":{"id":57,"name":"Hang Squat Clean","media":{},"distance_measurement":{},"equipment":[{"id":1,"name":"Barbell","deletable":true,"measurement":[1],"media":{},"unit":{"id":3,"man":{"input":155,"note":""},"woman":{"input":110,"note":""}}}]}},{"time_limit":{"id":0,"value":{}},"repeats":{"input":2,"note":""},"break_time":{},"exercise":{"id":33,"name":"Push Up","media":{},"distance_measurement":{},"equipment":[]}}]}]}}';

		$workout_log = json_decode($workout_log);

		$this->load->library('simplify');
		return $this->simplify->workout_log($workout_log);
	}
	
	public function workout_summary() {
		$workout = '{"time_limit":{},"repeats":{"input":"1","note":""},"set":[{"time_limit":{},"repeats":{"input":"1","note":""},"break_time":{"id":0,"value":{"input":"3","note":""}},"exercise_group":[{"time_limit":{},"repeats":{"input":"12","note":""},"break_time":{},"exercise":{"id":5,"distance_measurement":{},"equipment":[{"id":1,"unit":{"id":3,"man":{"input":"{#bbw}*0.5**","note":""},"woman":{"input":"{#bbw}*0.25**","note":""}}}]}},{"time_limit":{},"repeats":{"input":"12","note":""},"break_time":{},"exercise":{"id":14,"distance_measurement":{},"equipment":[{"id":1,"unit":{"id":3,"man":{"input":"75","note":""},"woman":{"input":"50","note":""}}}]}},{"time_limit":{},"repeats":{"input":"12","note":""},"break_time":{},"exercise":{"id":39,"distance_measurement":{},"equipment":[{"id":1,"unit":{"id":3,"man":{"input":"{#1RM}*1**","note":""},"woman":{"input":"75","note":""}}}]}}]},{"time_limit":{},"repeats":{"input":"5","note":""},"break_time":{},"exercise_group":[{"time_limit":{},"repeats":{"input":"{#brn}*10*","note":""},"break_time":{"id":0,"value":{"input":"{#brn}*2*","note":""}},"exercise":{"id":37,"distance_measurement":{},"equipment":[{"id":12,"unit":{}}]}}]}]}';
		
		$workout = json_decode($workout);
		
		$this->load->library('summary');
		return $this->summary->workout($workout);
	}
}