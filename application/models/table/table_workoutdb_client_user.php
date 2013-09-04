<?php

class table_workoutdb_client_user extends mysql_table {

	public function __construct() {
		parent::__construct();
		
		$this->initializeTable('workoutdb','client_user');
	}

	// ==================================================================================================================
	// Create a client_user if needed
	// ==================================================================================================================

	public function insert($p_fields) {
		// echo "c_table_client_user fields:" . json_encode($p_fields) . "<br />";
		$fields = (object) $p_fields;
		// --------------------------------------------------------------------------------------------------------------
		// initialize the response
		// --------------------------------------------------------------------------------------------------------------
		$response = new stdClass();
		$response->id = null;
		$response->action = null;
		// --------------------------------------------------------------------------------------------------------------
		// mandatory fields for the logic in this model
		// --------------------------------------------------------------------------------------------------------------
		if ( !property_exists($fields,'user_id') || is_null($fields->user_id) || empty($fields->user_id) || !is_numeric($fields->user_id) ||
		     !property_exists($fields,'client_id') || is_null($fields->client_id) || empty($fields->client_id) || !is_numeric($fields->client_id) ) {
			return $this->return_handler->results(400,"User and Client Id must be provided",$response);
		}
		// --------------------------------------------------------------------------------------------------------------
		// check to see if the client_user exists and if so has it been logically deleted
		// --------------------------------------------------------------------------------------------------------------
		$keys = array();
		$keys['user_id'] = (int) $fields->user_id;
		$keys['client_id'] = (int) $fields->client_id;
		$return = $this->getForAndKeys($keys);
		if ( $return['status'] > 200 ) {
			// --------------------------------------------------------------------------------------------------------------
			// Insert the entry
			// --------------------------------------------------------------------------------------------------------------
			// create the entry
			$return = $this->insertTableFields($fields);
			$return['response']->action = "insert";
			return $return;
		} else {
			$client_user = clone $return['response'][0];
			$response->id = $client_user->id;
			if ( $client_user->deleted ) {
				// --------------------------------------------------------------------------------------------------------------
				// restart the existing deleted client_user entry
				// --------------------------------------------------------------------------------------------------------------
				$response->action = "reactivate";
				// create the update object
				$fields = clone $p_fields;
				$fields->id = $client_user->id;
				// update the client user to restart them
				$return = $this->reactivate($fields);
				return $this->return_handler->results($return['status'],$return['message'],$response);
			} else {
				// --------------------------------------------------------------------------------------------------------------
				// the client_user entry already exists and is active, so update it
				// --------------------------------------------------------------------------------------------------------------
				$response->action = "update";
				// create the update object
				$fields = clone $p_fields;
				$fields->id = $client_user->id;
				// update the client user
				$return = $this->update($fields);
				return $this->return_handler->results($return['status'],$return['message'],$response);
			}
		}
	}
	
	public function update($p_fields) {
		return $this->updateTableFields($p_fields);
	}
	
	public function delete($p_id) {
		// echo "table_workoutdb_client_user->delete id:$p_id<br />";
		return $this->deactivateTable($p_id);
	}
	
	public function reactivate($p_fields) {
		return $this->reactivateTable($p_fields);
	}

}