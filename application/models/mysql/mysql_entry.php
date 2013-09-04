<?php

class mysql_entry extends CI_Model {

	// Which table is this defining
	protected $database_name;
	protected $table_name;

	protected $database;

	// the table definition from the schema
	protected $table;

	public function __construct() {
		// echo "-mysql_entry start-";
		
		parent::__construct();
		
		// echo "-mysql_entry end-";
	}

	protected function loadEntry() {
		// echo "mysql_entry->loadEntry " . $this->database_name . " " . $this->table_name . "<br />";
		
		$return = mysql_schema::get($this->database_name,$this->table_name);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$this->table = $return['response'];

		foreach ( $this->table->columns as $key => &$column ) {
			$column->value = $column->default;
			$column->value_set = FALSE;
		}

		return $this->return_handler->results(200,"",null);
	}

	protected function getSchema() {
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Get all table entries
		//
	    // ---------------------------------------------------------------------------------------------------------
	    return $this->return_handler->results(200,"",$this->table);
	}

	protected function get_value($p_column_name) {
		// Has a valid column been selected
		$return = $this->has_column($p_column_name);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// create a pointer to the column's table entry
		$column = &$this->table->columns->{$p_column_name};

		return $this->return_handler->results(200,"",$column->value);
	}

	protected function has_column($p_column_name) {
		//
		// is the column found within the tables schema definition
		if ( property_exists($this->table->columns,$p_column_name) ) {
			return $this->return_handler->results(200,"",null);
		} else {
			return $this->return_handler->results(400,"Invalid column name '" . $p_column_name . "'",null);
		}
	}

	protected function get_columns() {
		//
		// return an array of values (correctly typed) referenced by column name
		$columns = array();
		foreach ( $this->table->columns as $column_name => $column ) {
			if ( $column->value_set ) {
				$columns[$column_name] = $column->value;
			} else {
				$columns[$column_name] = $column->default;
			}
		}

		return $this->return_handler->results(200,"",$columns);
	}

	protected function get_set_columns() {
		//
		// return an array of values (correctly typed) refrenced by column name
		// for columns set with a __set() or initialize() or set_columns or set_column
		$columns = array();
		foreach ( $this->table->columns as $column_name => $column ) {
			if ( $column->value_set ) {
				$columns[$column_name] = $column->value;
			}
		}

		return $this->return_handler->results(200,"",$columns);
	}

	protected function set_columns($p_data) {
		//
		// set the columns to the values in the array passed in
		$p_data = (array) $p_data;
		foreach ( $p_data as $column => $value ) {
			$return = $this->has_column($column);
			if ( $return['status'] == 200 ) {
				if ( !is_array($column) && !is_object($column) ) {
					$this->set_column($column,$value);
				}
			}
		}

		return $this->return_handler->results(200,"",null);
	}

	protected function set_column($p_column_name,$p_value) {
		// echo "mysql_entry->set_column column:$p_column_name value:$p_value<br />";
		// echo "schema:"; print_r($this->table); echo "<br />";
		// ------------------------------------------------------
		// Set the column value (do not tag it as being changed/set)
		// ------------------------------------------------------
		// Has a valid column been selected
		$return = $this->has_column($p_column_name);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// create an easy link to the column
		$column = &$this->table->columns->{$p_column_name};
		//
		// set the column value to the cast value passed in
		$return = mysql_schema::cast_value($p_value,$column->type);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$column->value = $return['response'];

		return $this->return_handler->results(200,"",null);
	}

	protected function set_values($p_columns) {
		// echo "mysql_entry->set_values columns:"; print_r($p_columns); echo "<br />";
		//
		// set the columns to the values in the array passed in
		$p_columns = (array) $p_columns;
		foreach ( $p_columns as $column => $value ) {
			$return = $this->has_column($column);
			if ( $return['status'] == 200 ) {
				if ( !is_array($column) && !is_object($column) ) {
					$this->set_value($column,$value);
				}
			}
		}

		return $this->return_handler->results(200,"",null);
	}

	protected function set_value($p_column_name,$p_value) {
		// echo "mysql_entry->set_value column:$p_column_name value:$p_value<br />";
		// ------------------------------------------------------
		// Set the column value and tag it as being changed/set
		// ------------------------------------------------------
		// Has a valid column been selected
		$return = $this->has_column($p_column_name);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// create an easy link to the column
		$column = &$this->table->columns->{$p_column_name};
		//
		// set the column value to the cast value passed in
		$return = mysql_schema::cast_value($p_value,$column->type);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$column->value = $return['response'];
		//
		// tag the column as set
		$column->value_set = TRUE;
		
		return $this->return_handler->results(200,"",null);
	}

	protected function has_value_set($p_column_name) {
		// Has a valid column been selected
		$return = $this->has_column($p_column_name);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// Has the column's value been changed/set?
		if ( $this->table->columns->{$p_column_name}->value_set ) {
			return $this->return_handler->results(200,"",null);
		} else {
			return $this->return_handler->results(400,"",null);
		}
	}

	protected function mandatory_columns_set() {
		// echo "mysql_entry->mandatory_columns_set table:"; print_r($this->table); echo "<br />";
		//
		// Have all mandatory columns been set or have a default value
		//
		// if a column is not nullable and its value is null and its default value is null, the mandatory column is NOT set.
		$missing = array();
		foreach ( $this->table->columns as $key => $column ) {
			// The created field will get populated at the time of creation, so it should not be populated now.
			if ( $key != "id" && !$column->nullable ) {
				// is not NULL
				if ( is_null($column->value) ) {
					$missing[] = $key;
				}
			}
		}
		if ( count($missing) != 0 ) {
			return $this->return_handler->results(400,"Mandatory fields missing (" . implode(',',$missing) . ")",null);
		}

		return $this->return_handler->results(200,"",null);
	}

	protected function column_names($p_type = null) {
		//
		// get a list of all column name ()
		$entries = array();
		foreach($this->table->columns as $key => $column) {
			if ( is_null($p_type) || $column->type == $p_type ) {
				$entries[] = $key;
			}
		}

		return $this->return_handler->results(200,"",$entries);
	}

	protected function cast_columns($p_data) {
		//
		// return the data passed in cast for the column types
		$p_data = (array) $p_data;
		// clear all column values from the table object
		$this->clear();
		// set and cast the column values to the values in $p_data
		$this->set_values($p_data);
		// return the set column values
		$return = $this->get_set_columns();

		return $return;
	}

	protected function clear() {
		//
		// initialize all column values to their default value and set the column to value not set
		foreach($this->table->columns as $key => &$column) {
			// echo "key:$key<br />";
			$column->value = $column->default;
			$column->value_set = FALSE;
		}

		return $this->return_handler->results(200,"",null);
	}
}