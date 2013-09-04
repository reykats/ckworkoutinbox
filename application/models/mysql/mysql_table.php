<?php
class mysql_table extends mysql_entry {

	public function __construct() {
		parent::__construct();
	}

	protected function initializeTable($p_database_name,$p_table_name) {
		$this->database_name = $p_database_name;
		$this->table_name = $p_table_name;

		// Create the table object
		$return = $this->loadEntry();
		if ( $return['status'] > 200 ) {
			return $return;
		}

		// echo "databse:" . $this->database_name . " table:" . $this->table_name . "<br />";
		// echo "table : " . json_encode($this->table); echo "<br />";
		
		return $return;
	}

	public function getAll( $p_cast_output = false ) {
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Get all table entries
		//
	    // ---------------------------------------------------------------------------------------------------------
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional column search values for all varchar columns
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$query = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$varchar_columns = $this->column_names('varchar');
			$temp = "";
			foreach ( $varchar_columns as $column ) {
				if ( !empty($temp) ) {
					$temp .= ',';
				}
				$temp .= "if(isnull(" . $column . "),'',concat(' '," . $column . "))";
			}

			$query  = "WHERE ";
			$query .= "concat(";
			$query .= $temp;
			$query .= ") LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['page']) && !empty($_GET['page']) && is_numeric($_GET['page']) &&
		     isset($_GET['page_length']) && !empty($_GET['page_length']) && is_numeric($_GET['page_length']) ) {
			$limit = "LIMIT " . (($_GET['page'] - 1) * $_GET['page_length']) . ", " . $_GET['page_length'] . " ";
		}
		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the total record count without paging limits
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT count(id) cnt ";
		$sql .= "FROM " . $this->table_name . " ";
		$sql .= $query;

		// echo "$sql<br />";

		$row = $this->db->query($sql)->row();
		
		$count = $row->cnt;

		// ---------------------------------------------------------------------------------------------------------
		//
		// Get the results from the full select
		//
		// ---------------------------------------------------------------------------------------------------------
		$sql  = "SELECT * ";
		$sql .= "FROM " . $this->table_name . " ";
		$sql .= $query;
		$sql .= "ORDER BY id ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			$response->count = $count;
			$response->results = array();
			return $this->return_handler->results(204,"No Entry Found",$response);
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			if ( $p_cast_output ) {
				// cast the column data and only return the columns in the table
				$return = $this->cast_columns($row);
				$columns = $return['response'];
				unset($return);
			} else {
				// return the query results without casting the
				$columns = $row;
			}
			// cast the columns array into an object
			$entry = (object) $columns;
			// put the entry object into the entries array
			array_push($entries,$entry);
			// clear the columns array and entry object from memory
			unset($columns);
			unset($entry);
		}

		$response->count = $count;
		$response->results = $entries;
		return $this->return_handler->results(200,"",$response);
	}

	public function getForId( $p_id, $p_cast_output = false ) {
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Get the table entry for an id
		//
	    // ---------------------------------------------------------------------------------------------------------
		//
		// initialize the response data
		$entry = new stdClass();
		//
		// select
		$sql  = "SELECT * ";
		$sql .= "FROM " . $this->table_name . " ";
		$sql .= "WHERE id = " . $p_id . " ";

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() != 1) {
			return $this->return_handler->results(204,"No Entry Found",$entry);
		}
		$row = $query->row();

		if ( $p_cast_output ) {
			// cast the column data and only return the columns in the table
			$return = $this->cast_columns($row);
			$columns = $return['response'];
			unset($return);
		} else {
			// return the query results without casting the
			$columns = $row;
		}

		// cast the columns array into an object
		$entry = (object) $columns;

		return $this->return_handler->results(200,"",$entry);

	}

	public function getSearchList( $p_order_by = 'name' ) {
		// echo "mysql_table->getSearchAll";
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Get all table entries
		//
	    // ---------------------------------------------------------------------------------------------------------
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair the optional name query
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$query = "";
		if ( isset($_GET['q_n']) && !empty($_GET['q_n']) ) {
			$query  = "WHERE name LIKE '%" . mysql_real_escape_string($_GET['q_n']) . "%' ";
		}
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Prepair optional paging limits
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		$limit = "";
		if ( isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric($_GET['limit']) ) {
			$limit = "LIMIT 0, " . $_GET['limit'] . " ";
		}
		//
		// select
		$sql  = "SELECT id '" . $this->table_name . ".id', name '" . $this->table_name . ".name' ";
		$sql .= "FROM " . $this->table_name . " ";
		$sql .= $query;
		$sql .= "ORDER BY " . $p_order_by . " ";
		$sql .= $limit;

		// echo "$sql<br />";

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();
		
        $entries = array();
        $e = -1;
		foreach( $rows as $row ) {
			// cast the column values in the row to their column type
			mysql_schema::cast_row('workoutdb',$row);
			// objectify the row by table
			$row = mysql_schema::objectify_row($row,$p_use_alias=false);
         	// echo json_encode($row) . "<br /><br />\n\n";
            ++$e;
            $entries[$e] = clone $row->{$this->table_name};
		}

		return $this->return_handler->results(200,"",$entries);
	}

	public function getForAndKeys($p_keys = false, $p_select = '*', $p_cast_output = false ) {
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Select based on the keys in the key/value pair array connected with an AND
		//
	    // ---------------------------------------------------------------------------------------------------------
		$query = $this->db->select($p_select)->where($p_keys)->get($this->table_name);

		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			if ( $p_cast_output ) {
				// cast the column data and only return the columns in the table
				$return = $this->cast_columns($row);
				$columns = $return['response'];
				unset($return);
			} else {
				// return the query results without casting the
				$columns = $row;
			}
			// cast the columns array into an object
			$entry = (object) $columns;
			// put the entry object into the entries array
			array_push($entries,$entry);
			// clear the columns array and entry object from memory
			unset($columns);
			unset($entry);
		}

		return $this->return_handler->results(200,"",$entries);
	}

	public function getForOrKeys($p_keys = false, $p_select = '*', $p_cast_output = false ) {
	    // ---------------------------------------------------------------------------------------------------------
		//
		// Select based on the keys in the key/value pair array connected with an OR
		//
	    // ---------------------------------------------------------------------------------------------------------
		$query = $this->db->select($p_select)->or_where($p_keys)->get($this->table_name);

		$query = $this->db->query($sql);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(204,"No Entry Found",array());
		}
		$rows = $query->result();

		$entries = array();
		foreach ( $rows as $row ) {
			if ( $p_cast_output ) {
				// cast the column data and only return the columns in the table
				$return = $this->cast_columns($row);
				$columns = $return['response'];
				unset($return);
			} else {
				// return the query results without casting the
				$columns = $row;
			}
			// cast the columns array into an object
			$entry = (object) $columns;
			// put the entry object into the entries array
			array_push($entries,$entry);
			// clear the columns array and entry object from memory
			unset($columns);
			unset($entry);
		}

		return $this->return_handler->results(200,"",$entries);
	}

	protected function insertTableFields( $p_fields = null ) {
		// echo "mysql_table->insert_fields database:" . $this->database_name . " table:" . $this->table_name . " fields:" . json_encode($p_fields) . "<br />";
		$fields = (object) $p_fields;
		// -----------------------------------------------------------------------------------------
		// Translate the field object into a column array for the table.
		// -----------------------------------------------------------------------------------------
		$return = $this->fields_to_columns($fields);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$columns = $return['response'];
		// -----------------------------------------------------------------------------------------
		// Format column data with special needs ( gender, phone, email, . . .)
		// -----------------------------------------------------------------------------------------
		$return = $this->format_validate($columns);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$columns = $return['response'];

		return $this->insertTable($columns);
	}

	protected function insertTable( $p_columns = array() ) {
		// echo "mysql_table->insert database:" . $this->database_name . " table:" . $this->table_name . " columns:" . json_encode($p_columns) . "<br />";
		$columns = (array) $p_columns;
		// -----------------------------------------------------------------------------------------
		// Initialize the response object
		// -----------------------------------------------------------------------------------------
		$response = new stdClass();
		$response->id = null;
		// -----------------------------------------------------------------------------------------
		// Format and Validate the gender column value if it exists
		// -----------------------------------------------------------------------------------------
		if ( array_key_exists('id', $columns) ) {
			if ( is_null($columns['id']) ) {
				unset($columns['id']);
			} else {
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " You can not pass a value for the new entry's id.",$response);
			}
		}
		// -----------------------------------------------------------------------------------------
		// Add create date if needed
		// -----------------------------------------------------------------------------------------
		if ( !array_key_exists('created',$columns) || is_null($columns['created']) || empty($columns['created']) ) {
			// add the create date to the field list
			date_default_timezone_set('UTC');
			// get current date time in UTC
			$columns['created'] = strtotime(date('m/d/Y H:i:s'));
		}
		if ( !array_key_exists('created_by_app',$columns) || is_null($columns['created_by_app']) || empty($columns['created_by_app']) ) {
			$columns['created_by_app'] = $this->uri->segment(1);
		}
		if ( !array_key_exists('created_by_user_id',$columns) || is_null($columns['created_by_user_id']) || empty($columns['created_by_user_id']) ) {
			$session = $this->session->all_userdata();
			if ( is_array($session) && array_key_exists('user', $session) && is_object($session['user']) && property_exists($session['user'], 'user_id') && is_numeric($session['user']->user_id) ) {
				$columns['created_by_user_id'] = $user = $session['user']->user_id;
			}
		}
		// -----------------------------------------------------------------------------------------
		// Add removed if needed
		// -----------------------------------------------------------------------------------------
		if ( !array_key_exists('removed',$columns) || is_null($columns['removed']) || empty($columns['removed']) ) {
			$columns['removed_dates'] = json_encode(array());
		}
		// -----------------------------------------------------------------------------------------
		// clear and set the column values in the table object
		// -----------------------------------------------------------------------------------------
		$return = $this->clear();
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$return = $this->set_values($columns);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// -----------------------------------------------------------------------------------------
		// Make sure all mandatory columns have been set
		// -----------------------------------------------------------------------------------------
		$return = $this->mandatory_columns_set();
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results($return['status'],$this->table_name . " " . $return['message'],$return['response']);
		}
		// -----------------------------------------------------------------------------------------
		// Get an array of just the set columns for the table.
		// -----------------------------------------------------------------------------------------
		$return = $this->get_set_columns();
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$table_columns = $return['response'];
		// -----------------------------------------------------------------------------------------
		// You must be setting at least 1 column
		// -----------------------------------------------------------------------------------------
		if ( count($table_columns) == 0 ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " No data provided for the entry",$response);
		}
		// -----------------------------------------------------------------------------------------
		// Make sure all unique keys have unique values (including the primary key)
		// -----------------------------------------------------------------------------------------
		$return = $this->validateUniqueKeyValuesForInsert($table_columns);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// -----------------------------------------------------------------------------------------
		// Insert
		// -----------------------------------------------------------------------------------------
		$this->db->insert($this->table_name, $table_columns);
		$new_id = $this->db->insert_id();

		$response->id = $new_id;
		return $this->return_handler->results(201,"Entry saved",$response);

	}

	protected function validateUniqueKeyValuesForInsert() {
		foreach ( $this->table->unique_keys as $unique_key ) {
			// create key => value array for the unique key test
			$keys = array();
			$empty = TRUE;
			foreach ( $unique_key as $unique_key_column ) {
				$return = $this->get_value($unique_key_column);
				if ( $return['status'] == 200 ) {
					$keys[$unique_key_column] = $return['response'];
					if ( !is_null($keys[$unique_key_column]) ) {
						$empty = FALSE;
					}
				}
			}
			if ( !$empty ) {
				// if an entry is found, the key will not be unique if added
				$return = $this->getForAndKeys($keys,'id');
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				$entries = $return['response'];
				// entries found
				if ( count($entries) > 0 ) {
					return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " key not unique (" . implode(',', $unique_key) . ")",null);
				}
			}
		}

		return $this->return_handler->results(200,"",null);
	}

	protected function updateTableFields( $p_fields = null ) {
		// echo "mysql_table->updateTableFields database:" . $this->database_name . " table:" . $this->table_name . " fields:" . json_encode($p_fields) . "<br />";
		$fields = (object) $p_fields;
		// -----------------------------------------------------------------------------------------
		// Translate the field object into a column array for the table.
		// -----------------------------------------------------------------------------------------
		$return = $this->fields_to_columns($fields);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$columns = $return['response'];
		// -----------------------------------------------------------------------------------------
		// Format column data with special needs ( gender, phone, email, . . .)
		// -----------------------------------------------------------------------------------------
		$return = $this->format_validate($columns);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$columns = $return['response'];

		return $this->updateTable($columns);
	}

	protected function updateTable( $p_columns = array() ) {
		// echo "mysql_table->updateTable database:" . $this->database_name . " table:" . $this->table_name . " columns:" . json_encode($p_columns) . "<br />";
		// -----------------------------------------------------------------------------------------
		//
		// Update
		//
		// -----------------------------------------------------------------------------------------
		$columns = (array) $p_columns;
		// -----------------------------------------------------------------------------------------
		// Has the id been provided?
		// -----------------------------------------------------------------------------------------
		if ( !array_key_exists('id',$columns) || is_null($columns['id']) || empty($columns['id']) || !is_numeric($columns['id']) ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " ID not provided",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// Is this a valid ID?
		// -----------------------------------------------------------------------------------------
		$id = $columns['id'];
		$query = $this->db->get_where($this->table_name,array('id' => $id));
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " Valid ID not provided",new stdClass());
		}
		$current_columns = $query->row();
		// -----------------------------------------------------------------------------------------
		// clear and initialize the values in the table object to the current values (do not tag them as set)
		// -----------------------------------------------------------------------------------------
		$return = $this->clear();
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$return = $this->set_columns($current_columns);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// -----------------------------------------------------------------------------------------
		// set the column values in the table object
		// -----------------------------------------------------------------------------------------
		$return = $this->set_values($columns);
		// -----------------------------------------------------------------------------------------
		// Make sure all mandatory columns have been set
		// -----------------------------------------------------------------------------------------
		$return = $this->mandatory_columns_set();
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results($return['status'],$this->table_name . " " . $return['message'],$return['response']);
		}
		// -----------------------------------------------------------------------------------------
		// Get an array of just the set columns for the table.
		// -----------------------------------------------------------------------------------------
		$return = $this->get_set_columns();
		if ( $return['status'] > 200 ) {
			return $return;
		}
		$table_columns = $return['response'];
		// echo "table_columns : "; print_r ($table_columns); echo '<br />';
		// -----------------------------------------------------------------------------------------
		// Make sure all unique keys have unique values (including the primary key)
		// -----------------------------------------------------------------------------------------
		$return = $this->validateUniqueKeyValuesForUpdate($table_columns['id']);
		if ( $return['status'] > 200 ) {
			return $return;
		}
		// -----------------------------------------------------------------------------------------
		// Remove id from $table_columns (a primary key can not be set in the update list)
		// -----------------------------------------------------------------------------------------
		unset($table_columns['id']);
		// -----------------------------------------------------------------------------------------
		// Has any columns (other than id) been passed to update.  Do not update, do not fail.
		// -----------------------------------------------------------------------------------------
		if ( count($table_columns) == 0 ) {
			return $this->return_handler->results(202,"Nothing to chage",new stdClass());
		}
		// echo $this->database_name . "." . $this->table_name . "." . $id . " table columns:"; print_r($table_columns); echo "<br />";
		// -----------------------------------------------------------------------------------------
		// Update
		// -----------------------------------------------------------------------------------------
		$this->db->where('id',$id)->update($this->table_name,$table_columns);

		return $this->return_handler->results(202,"Entry changed",new stdClass());
	}

	protected function fields_to_columns($p_fields) {
		// echo "mysql_table->field_to_columns database:" . $this->database_name . " table:" . $this->table_name . " fields:" . json_encode($p_fields) . "<br />";
		// echo "this->table:"; print_r($this->table); echo "<br />";
		$columns = array();
		foreach ( $p_fields as $key => $value ) {
			if ( !is_object($value) ) {
				if ( property_exists($this->table->column_alias,$key) ) {
					$columns[$this->table->column_alias->{$key}] = $value;
				} else {
					$columns[$key] = $value;
				}
			}
		}

		// translate height from an object to fields
		if ( property_exists($p_fields,'height') && is_object($p_fields->height) ) {
			if ( property_exists($p_fields->height,'value') && !is_null($p_fields->height->value) ) {
				$columns['height'] = $p_fields->height->value;
				$columns['height_uom_id'] = $p_fields->height->id;
			} else {
				$columns['height'] = null;
				$columns['height_uom_id'] = null;
			}
		} 

		// translate weight from an object to fields
		if ( property_exists($p_fields,'weight') && is_object($p_fields->weight) ) {
			if ( property_exists($p_fields->weight,'value') && !is_null($p_fields->weight->value) ) {
				$columns['weight'] = $p_fields->weight->value;
				$columns['weight_uom_id'] = $p_fields->weight->id;
			} else {
				$columns['weight'] = null;
				$columns['weight_uom_id'] = null;
			}
		}

		// translate result_unit from an object to fields
		if ( property_exists($p_fields,'result_unit') && is_object($p_fields->result_unit) ) {
			if ( property_exists($p_fields->result_unit,'input') && !is_null($p_fields->result_unit->input) && $p_fields->result_unit->input != '' ) {
				$columns['result'] = $p_fields->result_unit->input;
				$columns['result_uom_id'] = $p_fields->result_unit->id;
			} else {
				$columns['result'] = null;
				$columns['result_uom_id'] = null;
			}
		}
		
		// translate time_limit from an object to fields
		if ( property_exists($p_fields,'time_limit') && is_object($p_fields->time_limit) ) {
			if ( property_exists($p_fields->time_limit,'id') && property_exists($p_fields->time_limit,'value') ) {
				if ( is_integer($p_fields->time_limit->id) && is_object($p_fields->time_limit->value) ) {
					$columns['time_limit_uom_id'] = $p_fields->time_limit->id;
					if ( property_exists($p_fields->time_limit->value,'input') && property_exists($p_fields->time_limit->value,'note') ) {
						$columns['time_limit'] = $p_fields->time_limit->value->input;
						$columns['time_limit_note'] = $p_fields->time_limit->value->note;
					}
				}
			}
		}

		// echo "columns:"; print_r($columns); echo "<br />";
		return $this->return_handler->results(200,"",$columns);
	}

	protected function format_validate( $p_columns ) {
		// where $p_columns is an array of column values referenced by column name
		$columns = (array) $p_columns;
		// -----------------------------------------------------------------------------------------
		// Format and Validate the gender column value if it exists
		// -----------------------------------------------------------------------------------------
		// echo "format gender<br />";
		if ( array_key_exists('gender',$columns) ) {
			if ( is_null($columns['gender']) || empty($columns['gender']) ) {
				$columns['gender'] = null;
			} else {
				$return = $this->format_gender($columns['gender']);
				if ( $return['status'] > 200 ) {
					return $return;
				}
				if ( is_null($return['response']) || empty($return['response']) ) {
					$columns['gender'] = null;
				} else {
					$columns['gender'] = $return['response'];
				}
			}
		}
		// -----------------------------------------------------------------------------------------
		// Format and Validate the phone column value if it exists
		// -----------------------------------------------------------------------------------------
		// echo "format gender<br />";
		if ( array_key_exists('phone',$columns) ) {
			if ( is_null($columns['phone']) || empty($columns['phone']) ) {
				$columns['phone'] = null;
			} else {
				$return = $this->format_phone($columns['phone']);
				if ( $return['status'] > 200 ) {
					return $return;
				}
				if ( is_null($return['response']) || empty($return['response']) ) {
					$columns['phone'] = null;
				} else {
					$columns['phone'] = $return['response'];
				}
			}
		}
		// -----------------------------------------------------------------------------------------
		// Validate the email column value if it exists
		// -----------------------------------------------------------------------------------------
		// echo "format email<br />";
		if ( array_key_exists('email',$columns) ) {
			if ( (is_null($columns['email']) || empty($columns['email'])) ) {
				$columns['email'] = null;
			} else {
				$return = $this->validate_email($columns['email']);
				if ( $return['status'] > 200 ) {
					return $return;
				}
			}
		}
		// -------------------------------------------------------------------------------------------------
		// convert the removed array into a json string if exists 
		// -------------------------------------------------------------------------------------------------
		if ( array_key_exists('removed_dates',$columns) ) {
			if ( !is_array($columns['removed_dates']) ) {
				$columns['removed_dates'] = array();
			}
			$columns['removed_dates'] = json_encode($columns['removed_dates']);
		}

		return $this->return_handler->results(200,"",$columns);
	}

	protected function format_gender( $p_value ) {
		// remove all spaces, get 1st character, upper case
		$value = strtoupper(substr(str_replace(' ','',trim($p_value)),0,1));

		// if not M or F, return empty
		if ( $value != 'M' && $value != 'F' ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " Invalid gender '" . $p_value . "'",null);
		}

		return $this->return_handler->results(200,"",$value);
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

		$isValid = true;
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

			if ( $isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")) ) {
				// domain not found in DNS
				return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " email address domain not found in DNS",null);
			}
		}

		return $this->return_handler->results(200,"",null);
	}

	protected function validateUniqueKeyValuesForUpdate( $p_id ) {
		foreach ( $this->table->unique_keys as $unique_key ) {
			// create key => value array for the unique key test
			$keys = array();
			$empty = TRUE;
			foreach ( $unique_key as $unique_key_column ) {
				$return = $this->get_value($unique_key_column);
				if ( $return['status'] == 200 ) {
					$keys[$unique_key_column] = $return['response'];
					if ( !is_null($keys[$unique_key_column]) ) {
						$empty = FALSE;
					}
				}
			}
			if ( !$empty ) {
				// if an entry is found, the key will not be unique if added
				$return = $this->getForAndKeys($keys,'id');
				if ( $return['status'] >= 300 ) {
					return $return;
				}
				$entries = $return['response'];
				// entries found
				if ( count($entries) > 0 ) {
					// not the entry we are updating
					if ( $entries[0]->id != $p_id ) {
						return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " key not unique (" . implode(',', $unique_key) . ")",null);
					}
				}
			}
		}

		return $this->return_handler->results(200,"",null);
	}

	protected function deleteTable($p_id = null) {
		// -----------------------------------------------------------------------------------------
		// Has the id been provided?
		// -----------------------------------------------------------------------------------------
		if ( is_null($p_id) || empty($p_id) || !is_numeric($p_id) ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " ID not provided",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// Is this a valid ID?
		// -----------------------------------------------------------------------------------------
		$query = $this->db->get_where($this->table_name,array('id' => $p_id));
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(202,"ID already deleted",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// Delete
		// -----------------------------------------------------------------------------------------
		$this->db->where('id',$p_id)->delete($this->table_name);

		return $this->return_handler->results(202,"Entry removed",new stdClass());
	}

	protected function deleteTableForAndKeys($p_keys = array() ) {
		// -----------------------------------------------------------------------------------------
		// Has the id been provided?
		// -----------------------------------------------------------------------------------------
		if ( !is_array($p_keys) || count($p_keys) == 0 ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " keys not provided",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// Are there any entries to delete?
		// -----------------------------------------------------------------------------------------
		$query = $this->db->get_where($this->table_name,$p_keys);
		if ($query->num_rows() == 0) {
			return $this->return_handler->results(202,"No entries to delete",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// Delete
		// -----------------------------------------------------------------------------------------
		$this->db->delete($this->table_name,$p_keys);

		return $this->return_handler->results(202,"Entry removed",new stdClass());
	}

	protected function deactivateTable( $p_id = null ) {
		// -----------------------------------------------------------------------------------------
		// Does the table have a deleted column
		// -----------------------------------------------------------------------------------------
		$return = $this->has_column('deleted');
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " This table's entries can not be deactivated",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// set the deleted column
		// -----------------------------------------------------------------------------------------
		// Set time zone to UTC
		date_default_timezone_set('UTC');
		// Create entry object
		$columns = new stdClass();
		$columns->id = $p_id;
		$columns->deleted = strtotime(date('m/d/Y H:i:s'));
		// Update the entry
		return $this->updateTable($columns);
	}

	protected function reactivateTable( $p_fields ) {
		// -----------------------------------------------------------------------------------------
		// Has the id been provided?
		// -----------------------------------------------------------------------------------------
		if ( is_null($p_fields->id) || empty($p_fields->id) || !is_numeric($p_fields->id) ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " ID not provided",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// Does the table have a deleted column
		// -----------------------------------------------------------------------------------------
		$return = $this->has_column('deleted');
		if ( $return['status'] > 200 ) {
			return $this->return_handler->results(400,$this->database_name . ":" . $this->table_name . " This table's entries can not be deactivated",new stdClass());
		}
		// -----------------------------------------------------------------------------------------
		// set the deleted column
		// -----------------------------------------------------------------------------------------
		// Create entry object
		$fields = clone $p_fields;
		$fields->deleted = null;
		// Update the entry
		$return = $this->updateTableFields($fields);
		
		return $return;
	}
}