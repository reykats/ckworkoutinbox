<?php

class table_workoutdb_user extends mysql_table {

	public function __construct() {
		parent::__construct();
		
		$this->initializeTable('workoutdb','user');
	}

	// ==================================================================================================================
	// Create a user if needed
	// ==================================================================================================================

	public function insert($p_fields) {
		// echo "table_workoutdb_user->create fields:"; print_r($p_fields);
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
		if ( !property_exists($fields,'email') || is_null($fields->email) || empty($fields->email) || !is_string($fields->email) ) {
			return $this->return_handler->results(400,"Email must be provided",$response);
		}
		// --------------------------------------------------------------------------------------------------------------
		// check to see if the client_user exists and if so has it been logically deleted
		// --------------------------------------------------------------------------------------------------------------
		$keys = array();
		$keys['email'] = $fields->email;
		$return = $this->getForAndKeys($keys);
		// echo "getUser by Email:"; print_r($return); echo "<br />";
		if ( $return['status'] > 200 ) {
			// --------------------------------------------------------------------------------------------------------------
			// Insert the entry
			// --------------------------------------------------------------------------------------------------------------
			// create entry
			$return = $this->insertTableFields($fields);
			$return['response']->action = "insert";
			return $return;
		} else {
			$user = clone $return['response'][0];
			$response->id = $user->id;
			if ( !is_null($user->deleted) ) {
				// --------------------------------------------------------------------------------------------------------------
				// restart the existing deleted user entry
				// --------------------------------------------------------------------------------------------------------------
				$response->action = "reactivate";
				// create the update object
				$fields = (object) $p_fields;
				$fields->id = $p_user_id;
				// update the client user to restart them
				$return = $this->reactivate($fields);
				return $this->return_handler->results($return['status'],$return['message'],$response);
			} else {
				// --------------------------------------------------------------------------------------------------------------
				// the user entry already exists and is active, so update it
				// --------------------------------------------------------------------------------------------------------------
				$response->action = "update";
				// create the update object
				$fields = clone $p_fields;
				$fields->id = $user->id;
				// update the user
				$return = $this->update($fields);
				return $this->return_handler->results($return['status'],$return['message'],$response);
			}
		}
	}
	
	public function update($p_fields) {
		return $this->updateTableFields($p_fields);
	}
	
	public function delete($p_id) {
		return $this->deactivateTable($p_id);
	}
	
	public function reactivate($p_fields) {
		return $this->reactivateTable($p_fields);
	}
}