<?php

class lookup_table {
	
	private $key_count = 0;
	private $lookup = array();

	// ===================================================================================================================================================
	// Create/construct the lookup table
	// ===================================================================================================================================================
	
	public function __construct($params) {
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Get a link to the CodeIgniter instance
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		$ci =& get_instance();
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Connect to the database
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		if ( $params['database_name'] != 'workoutdb' ) {
			$ci->load->database($params['database_name'],TRUE);
		}
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// clean-up the keys parameter
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		if ( !isset($params['keys']) ) {               // if params['keys'] is not passed in, use id as the keys
			$params['keys'] = array('id');
		} else if ( !is_array($params['keys']) ) {     // if params['keys'] is not an array, cast it as one
			$params['keys'] = (array) $params['keys'];
		}
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// store off the number of keys used for this lookup
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		$this->key_count = count($params['keys']);
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Get the entries for the lookup table from the database
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// create the list of keys to be used in the ORDER BY section of the SELECT
		$keys = implode(', ',$params['keys']);
		// create the list of columns and alieaes to be used in the SELECT list section of the SELECT
		$columns = $keys . ', ' . $params['result'] . ' result';
		
		// Get the entries for the lookup table
		$query = $ci->db->select($columns)->order_by($keys)->get($params['table_name']);
		$rows = $query->result();
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Connect to workoutdb
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		if ( $params['database_name'] != 'workoutdb' ) {
			$ci->load->database('workoutdb',TRUE);
		}
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// load the lookup table
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		foreach ( $rows as $row ) {
			// create a pointer/reference to the lookup table
			$lookup = &$this->lookup;
			for( $i=0; $i < $this->key_count; ++$i ) {
				// Calculate the value of this lookup level's key
				$key_value = (int) $row->{$params['keys'][$i]};
				// if the key value is not in this level of the lookup table
				if ( !array_key_exists($key_value,$lookup) ) {
					if ( $i == ($this->key_count - 1) ) {
						// If this is the last level in the lookup table, add the entry as the lookup value
						$lookup[$key_value] = $row->result;
					} else {
						// If this is not the last level in the lookup table, add the entry as an array()
						$lookup[$key_value] = array();
					}
				}
				// reference this level as the current level of the the lookup table
				$lookup = &$lookup[$key_value];
			}
		}
	}

	// ===================================================================================================================================================
	// Get a value from the lookup table based on the value of the arguments/parameters passed in
	// ===================================================================================================================================================
	
	public function getValue() {
		// get a list of and a count of the arguments/parameters passed into this method
        $args = func_get_args();
        $args_count = func_num_args();
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Has the correct number of arguments been provided? If not pass back null
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		if ( $this->key_count != $args_count ) {
			return null;
		}
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Do the arguments correspond to a valid entry in the lookup table?  If yes, pass back the entry's result.  If not, pass back null
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// create a pointer/reference to the lookup table
		$lookup = &$this->lookup;
		for ($i=0; $i < $args_count; ++$i ) {
			// if the argument's value is null, not numeric, or does not exist as a index in this level of the lookup table, pass back null
			if ( is_null($args[$i]) || !is_numeric($args[$i]) || !array_key_exists($args[$i],$lookup) ) {
				return null;
			}
			// reference this level as the current level of the the lookup table
			$lookup = &$lookup[$args[$i]];
		}
		
		// The arguments do correspond to a valid entry in the lookup tabe, so pass back the value of the entry
		return $lookup;
	}

	// ===================================================================================================================================================
	// Does the list of values passed in, correspond to a entry in the lookup table
	// ===================================================================================================================================================
	
	public function validId() {
		// get a list of and a count of the arguments/parameters passed into this method
        $args = func_get_args();
        $args_count = func_num_args();
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Has the correct number of arguments been provided? If not pass back false
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		if ( $this->key_count != $args_count ) {
			return false;
		}
		
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// Do the arguments correspond to a valid entry in the lookup table?  If yes, pass back the true.  If not, pass back false
		// -----------------------------------------------------------------------------------------------------------------------------------------------
		// create a pointer/reference to the lookup table
		$lookup = &$this->lookup;
		for ($i=0; $i < $args_count; ++$i ) {
			// if the argument's value is null, not numeric, or does not exist as a index in this level of the lookup table, pass back false
			if ( is_null($args[$i]) || !is_numeric($args[$i]) || !array_key_exists($args[$i],$lookup) ) {
				return false;
			}
			// reference this level as the current level of the the lookup table
			$lookup = &$lookup[$args[$i]];
		}
		
		// The arguments do correspond to a valid entry in the lookup tabe, so pass back true
		return true;
	}

	// ===================================================================================================================================================
	// Return the lookup table at a the level of the value of the arguments/parameters passed in
	// ===================================================================================================================================================
	
	public function get() {
		// get a list of and a count of the arguments/parameters passed into this method
        $args = func_get_args();
        $args_count = func_num_args();
		
		// create a pointer/reference to the lookup table
		$lookup = &$this->lookup;
		for ($i=0; $i < $args_count; ++$i ) {
			// if the argument's value is null, not numeric, or does not exist as a index in this level of the lookup table, pass back false
			if ( is_null($args[$i]) || !is_numeric($args[$i]) || !array_key_exists($args[$i],$lookup) ) {
				return null;
			}
			// reference this level as the current level of the the lookup table
			$lookup = &$lookup[$args[$i]];
		}
		
		return $lookup;
	}

	// ===================================================================================================================================================
	// Print all entries in the lookup table
	// ===================================================================================================================================================
	
	
	public function printAll() {
		var_dump($this->lookup); echo "<br />";
	}
}
